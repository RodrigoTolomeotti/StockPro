<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MailSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send e-mails';

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

            if($this->argument('email') != null){
                app('App\Http\Controllers\SchedulerController')->envia();
            }else{
                app('App\Http\Controllers\SchedulerController')->envia();

            }


        } catch (\Exception $e) {

            $this->error($e->getMessage());

        }


    }
}
