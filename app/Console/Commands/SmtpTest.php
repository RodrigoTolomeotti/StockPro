<?php

namespace App\Console\Commands;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Illuminate\Console\Command;

class SmtpTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
        protected $signature = 'smtp:test {email} {password} {host} {port} {--ssl} {--tls} {--mailto=test@climbpro.com.br}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMTP server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer  $drip
     * @return mixed
     */
    public function handle()
    {

        $mail = new PHPMailer(true);

        try {

            $mail->SMTPDebug = SMTP::DEBUG_LOWLEVEL;
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = $this->argument('host');
            $mail->Username = $this->argument('email');
            $mail->Password = $this->argument('password');

            if ($this->option('tls')) {

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            } elseif ($this->option('ssl')) {

                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            } else {

                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

            }

            $mail->Port = $this->argument('port');

            $mail->setFrom($this->argument('email'));
            $mail->addAddress($this->option('mailto'));

            $mail->isHTML(true);
            $mail->Subject = 'SMTP Test';
            $mail->Body    = 'This is a SMTP test';
            $mail->AltBody = 'This is a SMTP test';

            $mail->send();

            $this->info('Message has been sent');

        } catch (Exception $e) {

            $this->error("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");

        }


    }
}
