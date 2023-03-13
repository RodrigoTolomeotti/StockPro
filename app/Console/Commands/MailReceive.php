<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MailReceive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:receive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Receive e-mails';

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

            app('App\Http\Controllers\SchedulerController')->recebe();

        } catch (\Exception $e) {

            $this->error($e->getMessage());

        }


    }
}
