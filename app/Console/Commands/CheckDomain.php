<?php

namespace App\Console\Commands;

use App\Services\MonitorService;
use Illuminate\Console\Command;

class CheckDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:domain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Domain Status';

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

            $params['domain_list'] = __getAllDomainList();
            dd($params);

            $data = monitoringCurlCall($baseURl.'/api/monitor/check-domain',$params);

            if(isset($data['status']) && $data['status'] == 'SUCCESS') {
                throw new \Exception("Failed to monitoring call");
            }
        }catch (\Exception $exception) {
            return false;
        }
        return true;
    }


}
