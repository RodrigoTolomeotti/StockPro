<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Exception;

class SMTPController extends Controller
{

    private $user = null;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function enviar($assunto, $mensagem, $destinos, $copias = [], $copias_ocultas = [], $user = null) {

        if($user){
            $this->user = $user;
        }

        try {

            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->SMTPAuth = true;
            $mail->Host = $this->user->smtp_host;
            $mail->Username = $this->user->conta_usuario;
            $mail->Password = Crypt::decryptString($this->user->conta_senha);

            switch ($this->user->smtp_security) {
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

            $mail->Port = $this->user->smtp_port;

            $mail->setFrom($this->user->conta_email, $this->user->nome);

            foreach ($destinos as $destino) $mail->addAddress($destino);
            foreach ($copias as $copia) $mail->addCC($copia);
            foreach ($copias_ocultas as $copia_oculta) $mail->addBCC($copia_oculta);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $mensagem;
            $mail->AltBody = $mensagem;

            $mail->send();

        } catch (PHPMailer\PHPMailer\Exception $e) {

            throw new Exception($e->getMessage());

        }
    }

}
