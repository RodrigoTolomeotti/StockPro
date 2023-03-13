<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SMTPController;
use App\Usuario;
use App\Retorno;
use App\Erro;
use App\Campanha;
use App\Contato;
use App\Sequencia;
use App\Template;
use App\Bloqueio;
use App\Envio;
use App\Notificacao;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SchedulerController extends Controller
{

    public function recebe() {
        $timeStart = microtime(true);
        $total_emails = 0;
        $total_bounces = 0;
        $date = date("d M Y", strToTime("-15 days"));

        Log::channel('receive')->info('Iniciando a rotina de receber');

        set_time_limit(0);

        $usuarios = Usuario::whereNotNull('imap_port')
                           ->where('imap_port', '!=', '')
                           ->whereNotNull('imap_host')
                           ->where('imap_host', '!=', '')
                           ->whereNotNull('conta_email')
                           ->where('conta_email', '!=', '')
                           ->whereNotNull('conta_senha')
                           ->where('conta_senha', '!=', '')
                           ->whereNull('data_inativacao')->get();

        $mensagem = null;

        foreach ($usuarios as $key => $usuario) {

            try {

                Log::channel('receive')->info('Recebendo emails do(a) '.$usuario->email);
                // Inicia a conexão IMAP
                $mailbox = new \PhpImap\Mailbox(
                    '{' .
                        $usuario->imap_host . ':' . $usuario->imap_port .
                        '/imap' .
                        '/novalidate-cert' .
                        ($usuario->imap_security ? '/' . $usuario->imap_security : '') .
                    '}INBOX',
                    $usuario->conta_email,
                    Crypt::decryptString($usuario->conta_senha),
                    null,
                    'UTF-8'
                );


                try {
                    // Busca os bounces
                    $bounces = array_merge($mailbox->searchMailbox('FROM mailer-daemon@'), $mailbox->searchMailbox('FROM postmaster@'));

                    // Emails de retorno no climb
                    $emails = $mailbox->searchMailbox('BODY "::CLIMB::" SINCE "'.$date.'"');

                    // Remove os bounces dos e-mails do climb
                    $emails = array_diff($emails, $bounces);
                    $total_emails += count($emails);
                    $total_bounces += count($bounces);
                    Log::channel('receive')->info('Encontrado '.count($emails).' emails e '.count($bounces).' bounces');

                } catch (Exception $e) {
                    Log::channel('receive')->error($e->getMessage());
                    $mailbox->disconnect();
                    // Grava erro no usuário
                    $usuario->erros()->create([
                        'titulo' => 'Erro de autenticação IMAP',
                        'mensagem' => $e->getMessage()
                    ]);

                    continue;

                }

                // Caso não exista e-mail nem bounce vai para o próximo usuário
                if (!$emails and !$bounces) {

                    // Finaliza a conexão IMAP
                    $mailbox->disconnect();
                    continue;
                }
                $i = 0;
                // Percorre os e-mails
                foreach ($emails as $key => $id) {
                    $where = '';

                    $email = $mailbox->getMail($emails[$key], true);

                    // Busca o envio_id do corpo do e-mail
                    preg_match("/::CLIMB::(.*?)::CLIMB::/", $email->textHtml, $match);

                    try {

                        $token = explode('-', $match[1]);

                        if(sizeof($token) == 5) {

                            //Separação dos dados em variáveis diferentes
                            $retorno_usuario_id = $token[0];
                            $retorno_campanha_id = $token[1];
                            $retorno_sequencia_id = $token[2];
                            $retorno_template_id = $token[3];
                            $envio_id = $token[4];

                            //Construção da WHERE clause.
                            $where = " usuario.id = $retorno_usuario_id
                                AND campanha.id = $retorno_campanha_id
                                AND envio.template_id = $retorno_template_id
                                AND sequencia.id = $retorno_sequencia_id
                            ";
                        } else {
                            $envio_id = Crypt::decryptString($match[1]);
                        }

                        // Verifica se já existe algum retorno para o envio
                        $recibido = Retorno::select($id)->where('envio_id', $envio_id);

                        // Caso já exista um retorno ele parte para o próximo e-mail
                        if ($recibido->count() != 0) continue;
                        // Busca o id e email do usuário que enviou o e-mail
                        if($where != '' || $where != null) {
                            $envio_info = Envio::select('usuario.id as usuario_id',
                                'contato.email as contato_email',
                                'contato.nome as nome',
                                'campanha.nome as nome_campanha',
                                'envio.template_id as template_id',
                                'usuario.nome as nome_usuario',
                                'contato.empresa as empresa'
                                )
                                ->join('sequencia', 'envio.sequencia_id', 'sequencia.id')
                                ->join('contato', 'envio.contato_id', 'contato.id')
                                ->join('campanha', 'sequencia.campanha_id', 'campanha.id')
                                ->join('usuario', 'campanha.usuario_id', 'usuario.id')
                                ->where('envio.id', $envio_id)
                                ->whereRaw($where)
                                ->first();
                        } else {
                            $envio_info = Envio::select('usuario.id as usuario_id',
                                'contato.email as contato_email',
                                'contato.nome as nome',
                                'campanha.nome as nome_campanha',
                                'envio.template_id as template_id',
                                'usuario.nome as nome_usuario',
                                'contato.empresa as empresa'
                                )
                                ->join('sequencia', 'envio.sequencia_id', 'sequencia.id')
                                ->join('contato', 'envio.contato_id', 'contato.id')
                                ->join('campanha', 'sequencia.campanha_id', 'campanha.id')
                                ->join('usuario', 'campanha.usuario_id', 'usuario.id')
                                ->where('envio.id', $envio_id)->first();
                        }

                        //     $i++;
                        // if($i == 2) dd($envio_id);
                        // Caso não encontra as informações do envio o sistema desconsidera
                        //if (!$envio_info) throw new Exception("Envio não pode ser identificado a partir do e-mail");

                        // Caso o contato tenha o mesmo e-mail do usuário o sistema desconsidera
                        if ($envio_info->contato_email == $usuario->conta_email)  continue;

                        // Caso o usuário do envio for diferente do usuário da caixa o sistema ignora
                        if ($envio_info->usuario_id != $usuario->id)  continue;
                        // Cadastra o retorno na base
                        $recibo = new Retorno();
                        $textoHtml = $email->textHtml;
                        $textoHtml = preg_replace('/charset=UTF-8/', 'charset=iso-8859-1', $textoHtml);
                        $textoHtml = preg_replace('<img src="' . url('api/receive/' . $match[1]) . '">', 'br', $textoHtml);

                        $recibo->mensagem = utf8_encode($textoHtml);
                        $recibo->origem_id = 1;
                        $recibo->envio_id = $envio_id;
                        $recibo->save();

                    } catch (Exception $e) {

                        if(!empty($envio_info)) {
                            $msg .= "Olá <b>$envio_info->nome_usuario</b>,";
                            $msg .= "<br><br>Foi identificado um problema ao incluir o retorno do <b>$envio_info->nome</b> da empresa <b>$envio_info->empresa</b>.";
                            $msg .= "<br>Campanha <b>$envio_info->nome_campanha</b>, cadência <b>$envio_info->template_id</b>.";
                            $msg .= "<br><br>Por favor, realize o retorno do contato manualmente para interromper a cadência de e-mails.";
                            $msg .= "<br>Para cadastrar o retorno, <link><a href=\"retornos\">clique aqui.</a></link>";
                            $msg .= "<br><br><b>Repasse essas informações ao suporte.";
                            $msg .= "<br>Desde já, agradecemos sua atenção. 😃😃</b>";

                            Notificacao::create([
                                'titulo' => "Retorno de contato",
                                'mensagem' => $msg,
                                'usuario_id' => $usuario->id
                            ]);

                        } else {
                            Log::channel('receive')->error($e->getMessage());
                        }
                        $mailbox->disconnect();

                        continue;

                    }

                }

                // Percorre os bounces
                foreach ($bounces as $key => $id) {

                    // Busca os dados do e-mail
                    $mensagem = $mailbox->getMailMboxFormat($bounces[$key], true);
                    $email = $mailbox->getMail($bounces[$key], true);

                    $date_timestamp = strtotime(substr($email->date, 0, 31));
                    $email_date = date("Y-m-d H:i:s", $date_timestamp);

                    // Busca todos os endereços de e-mails contidos no bounce
                    preg_match_all("/([\w0-9._-]+@[\w0-9._-]+\.[\w0-9_-]+)/i", $mensagem, $enderecos);

                    // Busca os e-mails distintos dentro do array
                    $enderecos = array_unique($enderecos[0]);

                    // Percorre os endereços de email
                    foreach ($enderecos as $key => $endereco) {

                        // Se o endereço de email for o do usuário desconsidera
                        if ($endereco == $usuario->conta_email) continue;

                        // Verifica se o e-mail faz partes dos contatos do usuário e se o bloqueio já não foi cadastrado
                        $contato = \DB::select("
                            select *
                              from contato c
                             where usuario_id = '$usuario->id'
                               and email = '$endereco'
                               and not exists (
                                   SELECT 1
                                     FROM bloqueio b
                                    WHERE c.id = b.contato_id
                                      AND b.data_bloqueio = '$email_date'
                                   )
                        ");

                        // Se o contato for encontrado
                        if (count($contato) > 0) {

                            try {

                                // Busca as campanhas ativas que o contato tem envio
                                $campanhas = \DB::table('campanha')
                                    ->select('campanha.id')->distinct()
                                    ->join('sequencia', 'sequencia.campanha_id', '=', 'campanha.id')
                                    ->join('envio', 'envio.sequencia_id', '=', 'sequencia.id')
                                    ->where('envio.contato_id', '=', $contato[0]->id)
                                    ->where('campanha.data_inicio', '<=', \DB::raw('CURDATE()'))
                                    ->whereNull('campanha.data_inativacao')
                                    ->get();

                                foreach ($campanhas as $campanha) {

                                    // Coloca a data de bounce dos últimos envios para esse contato
                                    $envio_id = \DB::table('campanha')
                                        ->select('envio.id')
                                        ->join('sequencia', 'sequencia.campanha_id', '=', 'campanha.id')
                                        ->join('envio', 'envio.sequencia_id', '=', 'sequencia.id')
                                        ->where('envio.contato_id', '=', $contato[0]->id)
                                        ->where('campanha.data_inicio', '<=', \DB::raw('CURDATE()'))
                                        ->whereNull('campanha.data_inativacao')
                                        ->where('campanha.id', '=', $campanha->id)
                                        ->orderBy('envio.data_criacao', 'desc')
                                        ->first()
                                        ->id;

                                    Envio::find($envio_id)->update([
                                        'data_bounce' => \DB::raw('now()')
                                    ]);

                                }

                                // Cadastra um novo bloqueio
                                $bloqueio = new Bloqueio();

                                $bloqueio->descricao = utf8_encode($mensagem);
                                $bloqueio->usuario_id = $usuario->id;
                                $bloqueio->contato_id = $contato[0]->id;
                                $bloqueio->motivo_bloqueio_id = 1;
                                $bloqueio->data_bloqueio = $email_date;

                                $bloqueio->save();

                            } catch (Exception $e) {
                                Log::channel('receive')->error($e->getMessage());
                                $mailbox->disconnect();
                                continue;
                            }

                        }
                    }
                }

                Log::channel('receive')->info('Finalizado para '.$usuario->email);
                $mailbox->disconnect();

            } catch (Exception $e) {
                Log::channel('receive')->error($e->getMessage());
                $mailbox->disconnect();
            }

        }

        Log::channel('receive')->info('Recebidos um total de '.$total_emails.' emails e '.$total_bounces.' bounces');

        $diff = microtime(true) - $timeStart;

        Log::channel('receive')->info('Finalizado em '.intval($diff).' segundos');
    }

    public function envia($email_usuario = null) {
        $timeStart = microtime(true);
        $total_emails = 0;
        Log::channel('send')->info('Iniciando a rotina de enviar');

        set_time_limit(10800);

        if($email_usuario != null){
            $usuarios = Usuario::whereNotNull('smtp_port')
                                ->where('smtp_port', '!=', '')
                                ->whereNotNull('smtp_host')
                                ->where('smtp_host', '!=', '')
                                ->whereNotNull('conta_email')
                                ->where('conta_email', '!=', '')
                                ->whereNotNull('conta_senha')
                                ->where('conta_senha', '!=', '')
                                ->whereNull('data_inativacao')
                                ->where('conta_email', '=', $email_usuario)->get();
        }else{
            $usuarios = Usuario::whereNotNull('smtp_port')
                                ->where('smtp_port', '!=', '')
                                ->whereNotNull('smtp_host')
                                ->where('smtp_host', '!=', '')
                                ->whereNotNull('conta_email')
                                ->where('conta_email', '!=', '')
                                ->whereNotNull('conta_senha')
                                ->where('conta_senha', '!=', '')
                                ->whereNull('data_inativacao')->get();
        }

       foreach ($usuarios as $key => $usuario) {
           Log::channel('send')->info('Enviando emails do(a) '.$usuario->email);

           $campanhasativas = $usuario->campanhas()
                                      ->whereNull('data_inativacao')
                                      ->where('origem_id', 1)
                                      ->get();

           $email_enviados = 0;
           foreach ($campanhasativas as $key => $campanha) {

                $query_template = Template::join('sequencia_template', 'sequencia_template.template_id', '=', 'template.id')
                                          ->join('sequencia', 'sequencia.id', '=', 'sequencia_template.sequencia_id')->groupBy('template.id');

                $obj_templates = \DB::connection('mysql_sem_strict')->select($query_template->toSql());
                \DB::disconnect('mysql_sem_strict');

                $templates = [];

                foreach ($obj_templates as $key => $template) {
                    $templates[$template->template_id] = $template;
                }

                $sql = 'select * from (select ct.*,
                		ee.sequencia_id,
                		(SELECT stt.template_id FROM sequencia_template stt WHERE stt.sequencia_id = ee.sequencia_id AND stt.ordem = MAX(st.ordem) + 1) next_template,
                        MAX(st.ordem) + 1 next_template_ordem
                   from envio ee,
                		sequencia s,
                		sequencia_template st,
                        contato ct,
                        template te
                  where ee.sequencia_id = s.id
                	and ee.sequencia_id = st.sequencia_id
                	and ee.template_id = st.template_id
                    and ee.contato_id = ct.id
                    and s.campanha_id = '.$campanha->id.'
                    and ee.data_bounce is null
                    AND NOT EXISTS (SELECT 1 FROM bloqueio bl WHERE bl.contato_id = ee.contato_id AND bl.data_inativacao IS NULL)
                  group by ee.contato_id, ee.sequencia_id, ct.nome) x
                  where next_template is not null';

                $contatos = \DB::connection('mysql_sem_strict')->select($sql);

                \DB::disconnect('mysql_sem_strict');

                $email_enviados = $this->envia_email($contatos, $usuario, $campanha->id, $email_enviados);

                if($email_enviados == 50){
                    $total_emails += $email_enviados;
                    break 1;
                }

                $contatos = \DB::connection('mysql')->select('select co.*
                                                                from campanha ca
                                                                LEFT OUTER JOIN campanha_cargo cg ON ca.id = cg.campanha_id
                                                                LEFT OUTER JOIN campanha_grupo_contato cgc ON ca.id = cgc.campanha_id
                                                                LEFT OUTER JOIN campanha_profissao cp ON ca.id = cp.campanha_id
                                                                LEFT OUTER JOIN campanha_departamento cd ON ca.id = cd.campanha_id
                                                                INNER JOIN contato co ON (
                                                            	(
                                                                    (cg.cargo_id IS null OR cg.cargo_id = co.cargo_id) AND
                                                                    (cgc.grupo_contato_id IS null OR cgc.grupo_contato_id = co.grupo_contato_id) AND
                                                                    (cp.profissao_id IS null OR cp.profissao_id = co.profissao_id) AND
                                                                    (cd.departamento_id IS null OR cd.departamento_id = co.departamento_id)
                                                                )
                                                                    AND co.usuario_id = ca.usuario_id
                                                                )
                                                                WHERE ca.id = '.$campanha->id.'
                                                                  AND NOT EXISTS (SELECT 1 FROM bloqueio bl WHERE bl.contato_id = co.id AND bl.data_inativacao IS NULL)
                                                                  AND NOT EXISTS (SELECT 1
                                                                					FROM envio ee
                                                                				   INNER JOIN sequencia sq ON ee.sequencia_id = sq.id
                                                                				   WHERE sq.campanha_id = ca.id
                                                                					 AND ee.contato_id = co.id
                                                                                     AND ee.data_bounce is null)');

                $email_enviados = $this->envia_email($contatos, $usuario, $campanha->id, $email_enviados);

                $total_emails += $email_enviados;

                if($email_enviados == 50){
                    break 1;
                }
           }
           Log::channel('send')->info('Enviado '. $email_enviados .' emails do usuário '.$usuario->email);

       }

       Log::channel('send')->info('Rotina de envio finalizada. Enviados um total de '.$total_emails.' emails.');

       $diff = microtime(true) - $timeStart;

       Log::channel('send')->info('Finalizado em '.intval($diff).' segundos');
    }


    private function envia_email($contatos, $usuario, $campanha_id, $email_enviados){

        foreach ($contatos as $key => $contato) {

            $ultimos_templates = '';
            $total_enviados = '';

            if(isset($contato->next_template)){
                $template = Template::find($contato->next_template);

                $ultimos_templates = \DB::connection('mysql')->select('select template.id,
                                                                               sequencia_template.sequencia_id,
                                                                               template.mensagem,
                                                                               template.assunto,
                                                                               sequencia_template.template_id,
                                                                               template.dias_enviar,
                                                                               envio.data_criacao,
                                                                               template.encaminhar,
                                                                               template.dias_enviar,
                                                                               DATEDIFF(now(), envio.data_criacao) passaram
                                                                          from template
                                                                         inner join sequencia_template on template.id = sequencia_template.template_id
                                                                         inner join envio on sequencia_template.sequencia_id = envio.sequencia_id
                                                                          left join retorno on retorno.envio_id = envio.id
                                                                         where envio.template_id = template.id
                                                                           and retorno.id is null
                                                                           and envio.data_bounce is null
                                                                           and envio.contato_id = '.$contato->id.'
                                                                           and sequencia_template.ordem < '.$contato->next_template_ordem.'
                                                                           AND (template.dias_enviar <= DATEDIFF(NOW(), envio.data_criacao)
                                                                            or template.dias_enviar IS NULL)
                                                                           and template.hora_inicial <= hour(now())
                                                                           and template.dias_semana like CONCAT(\'%\', WEEKDAY(NOW())+2, \'%\')
                                                                           and sequencia_template.sequencia_id = '.$contato->sequencia_id.'
                                                                           order by sequencia_template.ordem desc');
                // dd($ultimos_templates);
                $total_enviados = Envio::select('envio.id')
                                             ->where('envio.contato_id', $contato->id)
                                             ->where('envio.sequencia_id', $contato->sequencia_id)
                                             ->whereNull('envio.data_bounce')
                                             ->count();

                if (count($ultimos_templates) == 0) continue;

                if($template->dias_enviar > $ultimos_templates[0]->passaram){
                    continue;
                }

            }else{
                $template = \DB::connection('mysql')->select('select te.id, max(st.sequencia_id) sequencia_id, st.template_id, te.mensagem, te.assunto, te.encaminhar
                                                                from template te, sequencia_template st, sequencia se, campanha ca
                                                               where te.id = st.template_id
                                                                 and st.sequencia_id = se.id
                                                                 and se.campanha_id = ca.id
                                                                 and se.campanha_id = '.$campanha_id.'
                                                                 and st.ordem = 1
                                                                 and ca.data_inicio <= now()
                                                                 and te.dias_semana like CONCAT(\'%\', WEEKDAY(NOW())+2, \'%\')
                                                                 and te.hora_inicial <= hour(now())
                                                               group by te.id, st.template_id, te.mensagem, te.assunto, te.encaminhar
                                                               order by te.id desc
                                                               limit 1');

                if(!$template){
                    continue;
                }else{
                    $template = $template[0];
                }
            }

            if($ultimos_templates != '' and $total_enviados != ''){
                if(count($ultimos_templates) == 0 or $total_enviados > count($ultimos_templates)){
                    continue;
                }
            }

            $template->mensagem .= '<br>' . $usuario->assinatura;

            if($ultimos_templates != '' and $template->encaminhar == 1){
                $template->mensagem  .= $this->adicionaEncaminhados($ultimos_templates, $contato, $usuario);
            }

            $smtp = new SMTPController();

            \DB::beginTransaction();

            $emailEnviado = new Envio();

            if(isset($contato->next_template)){
                $emailEnviado->contato_id   = $contato->id;
                $emailEnviado->sequencia_id = $contato->sequencia_id;
                $emailEnviado->template_id  = $template->id;
            }else{
                $emailEnviado->contato_id   = $contato->id;
                $emailEnviado->sequencia_id = $template->sequencia_id;
                $emailEnviado->template_id  = $template->template_id;
            }

            $emailEnviado->save();

            $dados = array($usuario->id, $campanha_id, $emailEnviado->sequencia_id, $emailEnviado->template_id, $emailEnviado->id);
            $token = join('-', $dados);

            ## Reformulado a forma de criar um Token. Não será mais criptografado.
            // $token = Crypt::encryptString($emailEnviado->id);

            $template->mensagem .= '<p style="display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; mso-hide:all; line-height:0px; font-size:0px">::CLIMB::' . $token . '::CLIMB::</p>';
            $template->mensagem .= '<img src="' . url('api/receive/' . $token) . '"/>';
            $template->mensagem .= '<p style="display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; mso-hide:all; line-height:0px; font-size:0px">::DATA::'.$emailEnviado->data_criacao->format('d/m/Y H:i:s').'::DATA::</p>';

            try {
                $smtp->enviar($template->assunto, $this->substituirVariaveisTeste($template->mensagem, $contato), [$contato->email], [], [], $usuario);

                \DB::commit();
            } catch  (Exception $e) {
                \DB::rollback();

                $bloqueio = new Bloqueio();

                $bloqueio->descricao = $e->getMessage();
                $bloqueio->usuario_id = $usuario->id;
                $bloqueio->contato_id = $contato->id;
                $bloqueio->motivo_bloqueio_id = 2;
                $bloqueio->data_bloqueio = \DB::raw('NOW()');
                $bloqueio->save();

                continue;
            }

            $email_enviados++;

            if($email_enviados == 50){
                break 1;
            }

            sleep(30);
        }

        return $email_enviados;
    }

    public function envia_wpp() {
        $timeStart = microtime(true);
        $total_whatsapps = 0;
        Log::channel('send')->info('Iniciando a rotina de enviar');

        set_time_limit(10800);

        if($email_usuario != null){
            $usuarios = Usuario::whereNotNull('smtp_port')
                                ->where('smtp_port', '!=', '')
                                ->whereNotNull('smtp_host')
                                ->where('smtp_host', '!=', '')
                                ->whereNotNull('conta_email')
                                ->where('conta_email', '!=', '')
                                ->whereNotNull('conta_senha')
                                ->where('conta_senha', '!=', '')
                                ->whereNull('data_inativacao')
                                ->where('conta_email', '=', $email_usuario)->get();
        }else{
            $usuarios = Usuario::whereNotNull('smtp_port')
                                ->where('smtp_port', '!=', '')
                                ->whereNotNull('smtp_host')
                                ->where('smtp_host', '!=', '')
                                ->whereNotNull('conta_email')
                                ->where('conta_email', '!=', '')
                                ->whereNotNull('conta_senha')
                                ->where('conta_senha', '!=', '')
                                ->whereNull('data_inativacao')->get();
        }

       foreach ($usuarios as $key => $usuario) {
           Log::channel('send')->info('Enviando mensagens do(a) '.$usuario->email);

           $campanhasativas = $usuario->campanhas()
                                      ->whereNull('data_inativacao')
                                      ->where('origem_id', 6)
                                      ->get();

           $whatsapp_enviados = 0;
           foreach ($campanhasativas as $key => $campanha) {

                $query_template = Template::join('sequencia_template', 'sequencia_template.template_id', '=', 'template.id')
                                          ->join('sequencia', 'sequencia.id', '=', 'sequencia_template.sequencia_id')->groupBy('template.id');

                $obj_templates = \DB::connection('mysql_sem_strict')->select($query_template->toSql());
                \DB::disconnect('mysql_sem_strict');

                $templates = [];

                foreach ($obj_templates as $key => $template) {
                    $templates[$template->template_id] = $template;
                }

                $sql = 'select * from (select ct.*,
                        ee.sequencia_id,
                        (SELECT stt.template_id FROM sequencia_template stt WHERE stt.sequencia_id = ee.sequencia_id AND stt.ordem = MAX(st.ordem) + 1) next_template,
                        MAX(st.ordem) + 1 next_template_ordem
                   from envio ee,
                        sequencia s,
                        sequencia_template st,
                        contato ct,
                        template te
                  where ee.sequencia_id = s.id
                    and ee.sequencia_id = st.sequencia_id
                    and ee.template_id = st.template_id
                    and ee.contato_id = ct.id
                    and s.campanha_id = '.$campanha->id.'
                    and ee.data_bounce is null
                    AND NOT EXISTS (SELECT 1 FROM bloqueio bl WHERE bl.contato_id = ee.contato_id AND bl.data_inativacao IS NULL)
                  group by ee.contato_id, ee.sequencia_id, ct.nome) x
                  where next_template is not null';

                $contatos = \DB::connection('mysql_sem_strict')->select($sql);

                \DB::disconnect('mysql_sem_strict');

                $whatsapp_enviados = $this->envia_whatsapp($contatos, $usuario, $campanha->id, $whatsapp_enviados);

                if($whatsapp_enviados == 50){
                    $total_whatsapps += $whatsapp_enviados;
                    break 1;
                }

                $contatos = \DB::connection('mysql')->select('select co.*
                                                                from campanha ca
                                                                LEFT OUTER JOIN campanha_cargo cg ON ca.id = cg.campanha_id
                                                                LEFT OUTER JOIN campanha_grupo_contato cgc ON ca.id = cgc.campanha_id
                                                                LEFT OUTER JOIN campanha_profissao cp ON ca.id = cp.campanha_id
                                                                LEFT OUTER JOIN campanha_departamento cd ON ca.id = cd.campanha_id
                                                                INNER JOIN contato co ON (
                                                                (
                                                                    (cg.cargo_id IS null OR cg.cargo_id = co.cargo_id) AND
                                                                    (cgc.grupo_contato_id IS null OR cgc.grupo_contato_id = co.grupo_contato_id) AND
                                                                    (cp.profissao_id IS null OR cp.profissao_id = co.profissao_id) AND
                                                                    (cd.departamento_id IS null OR cd.departamento_id = co.departamento_id)
                                                                )
                                                                    AND co.usuario_id = ca.usuario_id
                                                                )
                                                                WHERE ca.id = '.$campanha->id.'
                                                                  AND NOT EXISTS (SELECT 1 FROM bloqueio bl WHERE bl.contato_id = co.id AND bl.data_inativacao IS NULL)
                                                                  AND NOT EXISTS (SELECT 1
                                                                                    FROM envio ee
                                                                                   INNER JOIN sequencia sq ON ee.sequencia_id = sq.id
                                                                                   WHERE sq.campanha_id = ca.id
                                                                                     AND ee.contato_id = co.id
                                                                                     AND ee.data_bounce is null)');

                $whatsapp_enviados = $this->envia_whatsapp($contatos, $usuario, $campanha->id, $whatsapp_enviados);

                $total_whatsapps += $whatsapp_enviados;

                if($whatsapp_enviados == 50){
                    break 1;
                }
           }
           Log::channel('send')->info('Enviado '. $whatsapp_enviados .' mensagens do usuário '.$usuario->email);

       }

       Log::channel('send')->info('Rotina de envio finalizada. Enviados um total de '.$total_whatsapps.' mensagens.');

       $diff = microtime(true) - $timeStart;

       Log::channel('send')->info('Finalizado em '.intval($diff).' segundos');
    }

    private function envia_whatsapp($contatos, $usuario, $campanha_id, $whatsapp_enviados){

        foreach ($contatos as $key => $contato) {

            $ultimos_templates = '';
            $total_enviados = '';

            if(isset($contato->next_template)){
                $template = Template::find($contato->next_template);

                $ultimos_templates = \DB::connection('mysql')->select('select template.id,
                                                                               sequencia_template.sequencia_id,
                                                                               template.mensagem,
                                                                               sequencia_template.template_id,
                                                                               template.dias_enviar,
                                                                               envio.data_criacao,
                                                                               template.dias_enviar,
                                                                               DATEDIFF(now(), envio.data_criacao) passaram
                                                                          from template
                                                                         inner join sequencia_template on template.id = sequencia_template.template_id
                                                                         inner join envio on sequencia_template.sequencia_id = envio.sequencia_id
                                                                          left join retorno on retorno.envio_id = envio.id
                                                                         where envio.template_id = template.id
                                                                           and retorno.id is null
                                                                           and envio.data_bounce is null
                                                                           and envio.contato_id = '.$contato->id.'
                                                                           and sequencia_template.ordem < '.$contato->next_template_ordem.'
                                                                           AND (template.dias_enviar <= DATEDIFF(NOW(), envio.data_criacao)
                                                                            or template.dias_enviar IS NULL)
                                                                           and template.hora_inicial <= hour(now())
                                                                           and template.dias_semana like CONCAT(\'%\', WEEKDAY(NOW())+2, \'%\')
                                                                           and sequencia_template.sequencia_id = '.$contato->sequencia_id.'
                                                                           order by sequencia_template.ordem desc');

                $total_enviados = Envio::select('envio.id')
                                             ->where('envio.contato_id', $contato->id)
                                             ->where('envio.sequencia_id', $contato->sequencia_id)
                                             ->whereNull('envio.data_bounce')
                                             ->count();

                if (count($ultimos_templates) == 0) continue;

                if($template->dias_enviar > $ultimos_templates[0]->passaram){
                    continue;
                }

            }else{
                $template = \DB::connection('mysql')->select('select te.id, max(st.sequencia_id) sequencia_id, st.template_id, te.mensagem
                                                                from template te, sequencia_template st, sequencia se, campanha ca
                                                               where te.id = st.template_id
                                                                 and st.sequencia_id = se.id
                                                                 and se.campanha_id = ca.id
                                                                 and se.campanha_id = '.$campanha_id.'
                                                                 and st.ordem = 1
                                                                 and ca.data_inicio <= now()
                                                                 and te.dias_semana like CONCAT(\'%\', WEEKDAY(NOW())+2, \'%\')
                                                                 and te.hora_inicial <= hour(now())
                                                               group by te.id, st.template_id, te.mensagem, te.assunto, te.encaminhar
                                                               order by te.id desc
                                                               limit 1');

                if(!$template){
                    continue;
                }else{
                    $template = $template[0];
                }
            }

            if($ultimos_templates != '' and $total_enviados != ''){
                if(count($ultimos_templates) == 0 or $total_enviados > count($ultimos_templates)){
                    continue;
                }
            }

            $whatsapp = new WhatsappController();

            \DB::beginTransaction();

            $whatsappEnviado = new Envio();

            if(isset($contato->next_template)){
                $whatsappEnviado->contato_id   = $contato->id;
                $whatsappEnviado->sequencia_id = $contato->sequencia_id;
                $whatsappEnviado->template_id  = $template->id;
            }else{
                $whatsappEnviado->contato_id   = $contato->id;
                $whatsappEnviado->sequencia_id = $template->sequencia_id;
                $whatsappEnviado->template_id  = $template->template_id;
            }

            $whatsappEnviado->save();

            $dados = array($usuario->id, $campanha_id, $whatsappEnviado->sequencia_id, $whatsappEnviado->template_id, $whatsappEnviado->id);
            $token = join('-', $dados);

            try {
                $template->mensagem = substituirTagsWhatsapp($template->mensagem);
                $whatsapp->enviar($this->substituirVariaveisTeste($template->mensagem, $contato), [$contato->telefone], $usuario);
                \DB::commit();
            } catch  (Exception $e) {
                \DB::rollback();

                $bloqueio = new Bloqueio();

                $bloqueio->descricao = $e->getMessage();
                $bloqueio->usuario_id = $usuario->id;
                $bloqueio->contato_id = $contato->id;
                $bloqueio->motivo_bloqueio_id = 2;
                $bloqueio->data_bloqueio = \DB::raw('NOW()');
                $bloqueio->save();

                continue;
            }

            $whatsapp_enviados++;

            if($whatsapp_enviados == 50){
                break 1;
            }

            sleep(10);
        }

        return $whatsapp_enviados;
    }

    private function substituirVariaveisTeste($mensagem, $contato) {
        return str_replace([
            '[[primeiro-nome]]',
            '[[sobrenome]]',
            '[[nome-completo]]',
            '[[empresa]]'
        ], [
            substr($contato->nome.' ', 0, strpos($contato->nome.' ', ' ')),
            substr($contato->nome.' ', strrpos($contato->nome.' ', ' ')),
            $contato->nome,
            $contato->empresa
        ], $mensagem);
    }

    private function adicionaEncaminhados($encaminhados, $contato, $usuario) {
        $mensagem = '';
        foreach ($encaminhados as $email) {

            $mensagem .= "
                <p>&nbsp;</p>
                <p>-------- Mensagem original --------</p>
                <table border=\0\ cellspacing=\0\ cellpadding=\0\>
                    <tbody>
                        <tr>
                            <th align=\"right\" valign=\"baseline\" nowrap=\"nowrap\">Assunto:</th>
                            <td>" . $email->assunto . "</td>
                        </tr>
                        <tr>
                            <th align=\"right\" valign=\"baseline\" nowrap=\"nowrap\">Data:</th>
                            <td>" . $email->data_criacao . "</td>
                        </tr>
                        <tr>
                            <th align=\"right\" valign=\"baseline\" nowrap=\"nowrap\">De:</th>
                            <td>" . $usuario->nome . " &lt;" . $usuario->conta_usuario . "&gt;</td>
                        </tr>
                        <tr>
                            <th align=\"right\" valign=\"baseline\" nowrap=\"nowrap\">Para:</th>
                            <td>" . $contato->nome . " &lt;" . $contato->email . "&gt;</td>
                        </tr>
                    </tbody>
                </table>
                <p>&nbsp;</p>
                <div class=\"WordSection1\">
                    " . $email->mensagem . "
                    <br>
                    " . $usuario->assinatura . "
                </div>
            ";

            if($email->encaminhar == 0){
                break;
            }

        }
        return $mensagem;
    }

    // function iniciarLog() {
    //
    //     if (!is_dir(__DIR__ . '/../logs/')) {
    //         mkdir(__DIR__ . '/../logs/');
    //     }
    //     if (!is_dir(__DIR__ . '/../errors/')) {
    //         mkdir(__DIR__ . '/../errors/');
    //     }
    //
    //     $log = new Logger('name');
    //     $log->pushHandler(new \Monolog\Handler\RotatingFileHandler(__DIR__ . '/../logs/logs.log', 30, Logger::DEBUG));
    //     $log->pushHandler(new \Monolog\Handler\RotatingFileHandler(__DIR__ . '/../errors/errors.log', 30, Logger::ERROR));
    //
    //     $log->pushProcessor(function ($record) {
    //         global $errors;
    //         if ($record['level'] == 400) {
    //             $errors[] = $record['message'];
    //         }
    //         return $record;
    //     });
    //
    //     return $log;
    // }
}
