<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WhatsappSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:send {whatsapp?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send whatsapp';

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

            if($this->argument('whatsapp') != null){
                app('App\Http\Controllers\SchedulerController')->envia_wpp();
            }else{
                app('App\Http\Controllers\SchedulerController')->envia_wpp();

            }


        } catch (\Exception $e) {

            $this->error($e->getMessage());

        }


    }
}
