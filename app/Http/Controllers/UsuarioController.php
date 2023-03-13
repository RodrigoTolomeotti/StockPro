<?php

namespace App\Http\Controllers;

use App\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Exception;
use App\Helpers\Notification;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class UsuarioController extends Controller
{

    public function login(Request $request)
    {

        try {

            $this->validate($request, [
                'email' => 'required|email',
                'senha' => 'required|string|max:256'
            ]);

            $usuario = Usuario::where('email', $request->input('email'))->first();

            if (!$usuario || !Hash::check($request->input('senha'), $usuario->senha)) {
                return ['errors' => ['Credenciais inválidas']];
            }

            $token = Str::random(256);

            $usuario->tokens()->create([
                'token' => $token,
                'data_expiracao' => \DB::raw('NOW() + 1'),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);

            return [
                'data' => $usuario,
                'token' => $token
            ];

        } catch (ValidationException $e) {

            $response = [];

            foreach ($e->errors() as $errors) {
                $response = array_merge($response, $errors);
            }

            return ['errors' => $response];

        }

    }

    public function getCurrentUser(Request $request)
    {

        return ['data' => Auth::user()];

    }

    public function updateUsuario(Request $request) {

        try {

            $data = $this->validate($request, [
                'nome' => [
                    'required',
                    'string',
                    'max:128'
                ],
                'senha' => [
                    'nullable',
                    'string'
                ],
                'senha_atual' => [
                    'required_with:senha',
                    'string'
                ],
                'confirmar_senha' => [
                    'required_with:senha',
                    'string',
                    'same:senha'
                ]
            ]);

            if (isset($data['senha'])) {

                if (!Hash::check($data['senha_atual'], Auth::user()->senha)) {
                    return ['errors' => ['Senha atual não está correta']];
                }

                $data['senha'] = Hash::make($data['senha']);

            }

            $update = Auth::user()->update($data);

            return ['data' => $update];

        } catch (ValidationException $e) {

            return ['errors' => $e->errors()];

        } catch(Exception $e) {

            return ['errors' => [$e->getMessage()]];

        }

    }

    public function updateConta(Request $request) {

        try {

            $data = $this->validate($request, [
                'conta_usuario' => [
                    'required',
                    'string',
                    'max:128'
                ],
                'conta_email' => [
                    'required',
                    'string',
                    'max:128'
                ],
                'conta_senha' => [
                    'nullable',
                    'string',
                    'min:3'
                ],
                'conta_confirmar_senha' => [
                    'required_with:conta_senha',
                    'string',
                    'same:conta_senha'
                ],
                'assinatura' => [
                    'nullable',
                    'string'
                ]
            ]);

            if (isset($data['conta_senha'])) $data['conta_senha'] = Crypt::encryptString($data['conta_senha']);

            $update = Auth::user()->update($data);

            return ['data' => $update];

        } catch (ValidationException $e) {

            return ['errors' => $e->errors()];

        } catch(Exception $e) {

            return ['errors' => [$e->getMessage()]];

        }

    }

    public function updateSMTP(Request $request) {

        try {

            $data = $this->validate($request, [
                'smtp_host' => [
                    'required',
                    'string',
                    'max:128'
                ],
                'smtp_port' => [
                    'required',
                    'int',
                    'digits_between:3,5'
                ],
                'smtp_security' => [
                    'nullable',
                    'string',
                    'max:3'
                ]
            ]);


            $update = Auth::user()->update($data);

            // $testSMTP = $this->testarSMTP();

            if(isset($testSMTP['errors'])) {
                return ['data' => $update,
                        'errorsSMTP' => $testSMTP['errors']
                    ];
            } else {
                return ['data' => $update,
                        'smtp' => $testSMTP['data']
                    ];
            }

        } catch (ValidationException $e) {

            return ['errors' => $e->errors()];

        } catch(Exception $e) {

            return ['errors' => [$e->getMessage()]];

        }

    }

    public function updateIMAP(Request $request) {

        try {

            $data = $this->validate($request, [
                'imap_host' => [
                    'required',
                    'string',
                    'max:128'
                ],
                'imap_port' => [
                    'required',
                    'int',
                    'digits_between:3,5'
                ],
                'imap_security' => [
                    'nullable',
                    'string',
                    'max:3'
                ]
            ]);

            $update = Auth::user()->update($data);

            return ['data' => $update];

        } catch (ValidationException $e) {

            return ['errors' => $e->errors()];

        } catch(Exception $e) {

            return ['errors' => [$e->getMessage()]];

        }

    }

    public function uploadImagem(Request $request) {

        try {

            if (!isset($_FILES['imagem'])) {
                throw new Exception('Imagem não encontrada');
            }

            $user = Auth::user();

            $path = "../public/users/";
            $file_name = Str::random(25) . ".jpg";

            $file = $_FILES['imagem']['tmp_name'];
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

            $newWidth = 250;
            $newHeight = 250;

            $xPosition = 0;
            $yPosition = 0;

            if ($width > $height) {
                $xPosition = $width/2 - $height/2;
                $width = $height;
            }

            if ($height > $width) {
                $yPosition = $height/2 - $width/2;
                $height = $width;
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            imagecopyresampled($newImage, $image, 0, 0, $xPosition, $yPosition, $newWidth, $newHeight, $width, $height);

            imagejpeg($newImage, $save, 75);

            if ($user->imagem) unlink($path . $user->imagem);

            $user->imagem = $file_name;
            $user->save();

            return response()->json([
                'imagem' => $file_name
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'errors' => [$e->getMessage()]
            ]);

        }

    }

    public function getTelas(Request $request) {
        return response()->json(['data' => Auth::user()->nivelUsuario->telas]);
    }

    public function getAll(Request $request){

        try{

            $query = Usuario::query()->select('usuario.id', 'usuario.nome', 'usuario.email', 'nivel_usuario.nome AS nivel', 'usuario.nivel_usuario_id', 'usuario.data_inativacao')->join('nivel_usuario', 'usuario.nivel_usuario_id', '=', 'nivel_usuario.id');

            if ($request->has('nome') && $request->input('nome') != '') {
                $query->where('usuario.nome', 'like', '%' . $request->input('nome') . '%');
            }

            if ($request->has('email') && $request->input('email') != '') {
                $query->where('usuario.email', 'like', '%' . $request->input('email') . '%');
            }

            if ($request->has('nivel_usuario_id') && $request->input('nivel_usuario_id') != '') {
                $query->where('usuario.nivel_usuario_id', '=', $request->input('nivel_usuario_id'));
            }

            $total_rows =  $query->count();

            if ($request->has('offset')) {
                $offset = $request->input('offset');
                $query->offset($offset);
            }

            if ($request->has('limit')) {
                $limit = $request->input('limit');
                $query->limit($limit);
            }

            if ($request->has('sort_by')) {
                $sort_by = $request->input('sort_by');
                $order_by = $request->input('order_by', 'asc');
                $query->orderBy($sort_by, $order_by);
            }

            return ['data' => $query->get(),
                    'totalRows' => $total_rows];

        }catch(\Exception $e){
            return response()->json([
                'errors' => [$e->getMessage()]
            ]);
        }
    }

    public function testarSMTP(Request $request) {

        $usuario = Auth::user();
        $mail = new PHPMailer(true);

        try {

            // $mail->SMTPDebug = SMTP::DEBUG_LOWLEVEL;
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = $request->input('smtp_host');
            $mail->Username = $usuario->conta_usuario;
            $mail->Password = Crypt::decryptString($usuario->conta_senha);

            switch ($request->input('smtp_security')) {
                case 'tls':
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                break;
                case 'ssl':
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                break;
                default:
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                break;
            }

            $mail->Port = $request->input('smtp_port');

            $mail->setFrom($usuario->conta_email);
            $mail->addAddress($usuario->conta_email);

            $mail->isHTML(true);
            $mail->Subject = 'SMTP Test';
            $mail->Body    = 'This is a SMTP test';
            $mail->AltBody = 'This is a SMTP test';

            $mail->send();

            return['data' => 'Conexão realizada com sucesso'];

        } catch (Exception $e) {

            return['errorsSMTP' => $e->getMessage()];

        }



    }

    public function testarIMAP(Request $request) {

        $usuario = Auth::user();

        error_reporting(0);

        try {

            $mailbox = new \PhpImap\Mailbox(
                '{' .
                    $request->input('imap_host').
                    ':' .
                    $request->input('imap_port') .
                    '/imap' .
                    '/novalidate-cert' .
                    ($request->input('imap_security') ? "/" . $request->input('imap_security') : "") .
                '}INBOX',
                $usuario->conta_usuario,
                Crypt::decryptString($usuario->conta_senha),
                null,
                'UTF-8'
            );

            $emails = $mailbox->searchMailbox('ALL');

            return['data' => 'Conexão realizada com sucesso'];

        }  catch (PHP\Exceptions\ConnectionException $e) {

            return ['errors' => $e->getMessage()];

        } catch (\UnexpectedValueException $e) {

            if(strpos($e->getMessage(), 'Host not found')) {
                return ['errors' => 'Host/IMAP inválido'];
            }
            if(strpos($e->getMessage(), 'Refused')) {
                return ['errors' => 'Porta/IMAP inválido'];
            }

            return ['errors' => $e->getMessage()];

        } catch (\Exception $e) {

            return ['errors' => $e->getMessage()];

        }

    }
}
