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
            $directory_list[] = '/var/www/html';
            $directory_list[] = '/var/log';
            $directory_list[] = '/home';

            $max_alloc_space_percent = 15;
            $params['directory_list'] = $directory_list;
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
