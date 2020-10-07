<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class MonitorService
{
    public function __construct()
    {
        DB::enableQueryLog();
    }

    public function processList()
    {
        $processList = null;
        try {
            $processList = DB::select('SHOW FULL PROCESSLIST');
        } catch (Exception $exception) {
            $message = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $message .= $exception->getMessage();
            Log::error('FullProcessLogError: ' . $message);
        }

        return $processList;
    }

    public function getConnectionStatus($domainName, $domainPort)
    {
        $domainIp = gethostbyname($domainName);

        if(empty($domainIp)) {
            $domainIp = $domainName;
        }

        $message = [];
        $waitTimeoutInSeconds = 3;

        try {
            if ($fp = fsockopen($domainIp, $domainPort, $errCode, $errStr, $waitTimeoutInSeconds)) {
                // It worked
                $message['status'] = 'OK';
                $message['message'] = "Domain: $domainName\nStatus: OK\nDomainPort: $domainPort\nDomainIP: $domainIp\nErrNo: $errCode\nErrStr: $errStr";
            } else {
                // It didn't worked
                $message['status'] = 'FAILED';
                $message['message'] = "Domain: $domainName\nStatus: FAILED\nDomainPort: $domainPort\nDomainIP: $domainIp\nErrNo: $errCode\nErrStr: $errStr";
            }
        } catch (Exception $exception) {
            $message['status'] = 'FAILED';
//            $message['message'] = $exception->getMessage() ?? 'Unknown Exception';
            $message['message'] = "Domain: $domainName\nStatus: FAILED\nDomainPort: $domainPort\nDomainIP: $domainIp\nErrNo: $errCode\nErrStr: $errStr";
        }

        return $message;
    }

    public function getSpaceStatus($drive, $max_alloc_limit = 70)
    {
        $server_time = date('Y-m-d h:i:s');

        $response = [];

        if (!empty($drive)) {
            if (is_dir($drive)) {
                $freespace = disk_free_space($drive);
                $total_space = disk_total_space($drive);
                $percentage_free = $freespace ? round($freespace / $total_space, 2) * 100 : 0;
                $percentage_used = (100 - $percentage_free);

                $response['status'] = $percentage_used <= $max_alloc_limit ? 'OK' : 'FAILED';

                if ($freespace < 1073741824) {
                    $response['message'] = "Drive: $drive\nFree Space: " . round($freespace / 1024 / 1024) . " MB\nPercentage: " . $percentage_free . " %\nTime: " . $server_time;
                } else {
                    $response['message'] = "Drive: $drive\nFree Space: " . round($freespace / 1024 / 1024 / 1024) . " GB\nPercentage: " . $percentage_free . " %\nTime: " . $server_time;
                }

            } else {
                $response['status'] = 'FAILED';
                $response['message'] = "Drive: $drive\nInvalid Directory.";
            }

        } else {
            $response['status'] = 'FAILED';
            $response['message'] = "Drive: $drive\nDirectory Path is not declared.";
        }

        return $response;
    }
}
