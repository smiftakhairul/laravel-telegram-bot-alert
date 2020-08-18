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
        $waitTimeoutInSeconds = 1;

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
}
