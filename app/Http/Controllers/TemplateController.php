<?php

namespace App\Http\Controllers;

use App\Contato;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;

class TemplateController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function uploadFile(Request $request)
    {
        try {

            if (!isset($_FILES['image'])) {
                throw new Exception('Imagem não encontrada');
            }

            $path = "../public/template_images/";

            $date = new \DateTime();
            $file_name = $date->format('YmdHisu') . "_" . $this->user->id . ".jpg";

            $file = $_FILES['image']['tmp_name'];
            $save = $path . $file_name;

            list($width, $height) = getimagesize($file);
            $info = getimagesize($file);

            switch ($info['mime']) {
                case 'image/jpg':
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($file);
                break;
                case 'image/gif':
                    $image = imagecreatefromgif($file);
                break;
                case 'image/png':
                case 'image/x-png':
                    $image = imagecreatefrompng($file);
                break;
                default:
                    throw new Exception('Tipo do arquivo não suportado');
                break;
            }

            $ratio = $width / $height;
            $newWidth = $width;
            $newHeight = $height;

            if($width > 750 || $height > 750){
                if($ratio > 1) {
                    $newWidth = 750;
                    $newHeight = 750/$ratio;
                }else{
                    $newWidth = 750*$ratio;
                    $newHeight = 750;
                }
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            imagejpeg($newImage, $save, 75);

            return response()->json([
                'url' => url("template_images/$file_name")
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }

    }

    public function testarEnvio(Request $request) {

        try {

            $data = $this->validate($request, [
                'destino' => [
                    'required',
                    'string'
                ],
                'assunto' => [
                    'required',
                    'string'
                ],
                'mensagem' => [
                    'required',
                    'string'
                ],
                'encaminhados.*.assunto' => [
                    'required',
                    'string'
                ],
                'encaminhados.*.mensagem' => [
                    'required',
                    'string'
                ]
            ]);

            $destinos = array_map('trim', explode(';', $data['destino']));
            $assunto = $this->substituirVariaveisTeste($data['assunto']);

            $mensagem = $data['mensagem'] . '<br>' . $this->user->assinatura;

            $encaminhados = $request->input('encaminhados');

            if (count($encaminhados)) $mensagem .= $this->montarMensagemTeste($encaminhados);

            $mensagem = $this->substituirVariaveisTeste($mensagem);

            app('App\Http\Controllers\SMTPController')->enviar($assunto, $mensagem, $destinos);

            return ['data' => true];

        } catch (ValidationException $e) {

            return ['errors' => $e->errors()];

        } catch (Exception $e) {

            return ['errors' => [$e->getMessage()]];

        }

    }

    public function testarEnvioWhatsapp(Request $request) {

        try {

            $data = $this->validate($request, [
                'destino' => [
                    'required',
                    'string'
                ],
                'mensagem' => [
                    'required',
                    'string'
                ]
            ]);

            $destinos = array_map('trim', explode(';', $data['destino']));

            $mensagem = $data['mensagem'];

            $mensagem = $this->substituirVariaveisTeste($mensagem);

            $mensagem = substituirTagsWhatsapp($mensagem);

            app('App\Http\Controllers\WhatsappController')->enviar(nl2br($mensagem), $destinos);

            return ['data' => true];

        } catch (Exception $e) {

            return ['errors' => [$e->getMessage()]];

        }

    }

    private function montarMensagemTeste($encaminhados) {
        $mensagem = '';
        foreach ($encaminhados as $email) {
            $mensagem .= "
                <p>&nbsp;</p>
                <p>-------- Mensagem original --------</p>
                <table border=\0\ cellspacing=\0\ cellpadding=\0\>
                    <tbody>
                        <tr>
                            <th align=\"right\" valign=\"baseline\" nowrap=\"nowrap\">Assunto::</th>
                            <td>$email[assunto]</td>
                        </tr>
                        <tr>
                            <th align=\"right\" valign=\"baseline\" nowrap=\"nowrap\">Data:</th>
                            <td>" . date('d/m/Y') . "</td>
                        </tr>
                        <tr>
                            <th align=\"right\" valign=\"baseline\" nowrap=\"nowrap\">De:</th>
                            <td>" . $this->user->nome . " &lt;" . $this->user->email_usuario . "&gt;</td>
                        </tr>
                        <tr>
                            <th align=\"right\" valign=\"baseline\" nowrap=\"nowrap\">Para::</th>
                            <td>" . $this->user->nome . " &lt;" . $this->user->email_usuario . "&gt;</td>
                        </tr>
                    </tbody>
                </table>
                <p>&nbsp;</p>
                <div class=\"WordSection1\">
                    $email[mensagem]
                    <br>
                    " . $this->user->assinatura . "
                </div>
            ";
        }
        return $mensagem;
    }

    private function substituirVariaveisTeste($mensagem) {
        return str_replace([
            '[[primeiro-nome]]',
            '[[sobrenome]]',
            '[[nome-completo]]',
            '[[empresa]]'
        ], [
            substr($this->user->nome . ' ', 0, strpos($this->user->nome . ' ', ' ')),
            substr($this->user->nome . ' ', strrpos($this->user->nome . ' ', ' ')),
            $this->user->nome,
            'Empresa do contato'
        ], $mensagem);
    }
}
