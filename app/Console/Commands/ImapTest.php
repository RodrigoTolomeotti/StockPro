<?php

namespace App\Console\Commands;
use Exception;

use Illuminate\Console\Command;

class ImapTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
        protected $signature = 'imap:test {email} {password} {host} {port} {--ssl} {--tls}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test IMAP server';

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

        try {

            if ($this->option('tls')) {
                $security = "tls";
            } elseif ($this->option('ssl')) {
                $security = "ssl";
            } else {
                $security = "";
            }

            // Inicia a conexão IMAP
            $mailbox = new \PhpImap\Mailbox(
                '{' .
                    $this->argument('host') .
                    ':' .
                    $this->argument('port') .
                    '/imap' .
                    '/novalidate-cert' .
                    ($security ? "/$security" : "") .
                '}INBOX',
                $this->argument('email'),
                $this->argument('password'),
                null,
                'UTF-8'
            );

            $emails = $mailbox->searchMailbox('ALL');

            $this->info("Connection success");

        } catch (Exception $e) {

            $this->error("Error to connect IMAP: " . $e->getMessage());

        }

    }
}
