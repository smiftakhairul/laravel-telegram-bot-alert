<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:directory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking the disk directory';

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
            $max_alloc_space_percent = config('monitor.max_space_alloc_percent');
            $params['directory_list'] = __getDirectoryList();
            $params['max_alloc_space_percent'] = $max_alloc_space_percent;

            $data = monitoringCurlCall($baseURl.'/api/monitor/check-directory',$params);

            if(isset($data['status']) && $data['status'] == 'SUCCESS') {
                throw new \Exception("Failed to monitoring call");
            }
        }catch (\Exception $exception) {
            return false;
        }
        return true;
    }
}
