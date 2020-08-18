<?php

namespace App\Http\Controllers;

use App\Services\MonitorService;
use Illuminate\Http\Request;
use stdClass;
use Telegram\Bot\Laravel\Facades\Telegram;

class MonitorController extends Controller
{
    protected $monitorService;

    public function __construct()
    {
        $this->monitorService = new MonitorService();
    }

    public function index()
    {

        $processList = $this->monitorService->processList();

        $domainList = [];

        $domainList[0]['domain'] = 'www.google.com';
        $domainList[0]['port'] = 80;
        $domainList[1]['domain'] = 'www.facebook.com';
        $domainList[1]['port'] = 80;
        $domainList[2]['domain'] = 'securepay.sslcommerz.com';
        $domainList[2]['port'] = 80;
        $domainList[3]['domain'] = 'localhost';
        $domainList[3]['port'] = 8080;

        $message = '';
        foreach ($domainList as $domainItem) {
            $connStatus = $this->monitorService->getConnectionStatus($domainItem['domain'], $domainItem['port']);
            $message .= $connStatus;
            $message .= "\n";
        }

        $response = Telegram::sendMessage([
//            'chat_id' => '-493747197',
            'chat_id' => '572610417',
            'text' => $message
        ]);

        dd($response);

        exit;

        return view('monitor.index')->with(compact('processList'));
    }
}
