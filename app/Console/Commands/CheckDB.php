<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking the db';

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
     * @return int
     */
    public function handle()
    {
        try {
            $baseURl = config('app.url');
            $params['api_key'] = config('monitor.api_key');
            $data = monitoringCurlCall($baseURl.'/api/monitor/check-db',$params);

            if(isset($data['status']) && $data['status'] == 'SUCCESS') {
                throw new \Exception("Failed to monitoring call");
            }
        }catch (\Exception $exception) {
            return false;
        }
        return true;
    }
}
