<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\CustomAPIResponse;
use App\Models\TelegramBotLog;
use App\Services\MonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api as TelegramBot;

class MonitorController extends Controller
{
    private $apiResponse;
    private $monitorService;
    private $telegramBotToken;

    public function __construct()
    {
        $this->apiResponse = new CustomAPIResponse();
        $this->monitorService = new MonitorService();
        $this->telegramBotToken = config('telegram.bots.mybot.token');
    }

    public function checkDomain(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telegram_chat_id' => 'required',
            'domain_list' => 'required|array',
            'domain_list.*.domain' => 'required',
            'domain_list.*.port' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($this->apiResponse->customErrorResponse($validator->errors(), false));
        }

        $response = null;

        try {
            $domainList = $request->input('domain_list');
            $telegramChatId = $request->input('telegram_chat_id');

            $message = '';
            foreach ($domainList as $domainItem) {
                $connStatus = $this->monitorService->getConnectionStatus($domainItem['domain'], $domainItem['port']);
                if ($connStatus['status'] == 'FAILED') {
                    $message .= $connStatus['message'] . "\n\n";
                }
            }

            if (!empty($message)) {
                $response = $this->pushTelegramMessage($request, $telegramChatId, $message);
            }
        } catch (Exception $exception) {
            $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $error .= $exception->getMessage();
            Log::error('CheckDomain: ' . $error);
            $response = $error;
        }

        return response()->json($response);
    }

    protected function pushTelegramMessage(Request $request, $telegramChatId, $message)
    {
        $response = null;
        $telegramBotToken = null;

        try {
            $telegramBotToken = ($request->has('telegram_bot_token') && !empty($request->input('telegram_bot_token')))
                ? $request->input('telegram_bot_token') : $this->telegramBotToken;

            $telegram = new TelegramBot($telegramBotToken);

            $response = $telegram->sendMessage([
                'chat_id' => $telegramChatId,
                'text' => $message,
            ]);
        } catch (Exception $exception) {
            $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $error .= $exception->getMessage();
            Log::error('PushTelegramMessage: ' . $error);
            $response = $error;
        }

        $this->generateTelegramBotLog($telegramBotToken, $telegramChatId, $message, $response);

        return $response;
    }

    protected function generateTelegramBotLog($telegramBotToken, $telegramChatId, $message, $response = [])
    {
        try {
            TelegramBotLog::create([
                'bot_token' => $telegramBotToken,
                'chat_id' => $telegramChatId,
                'message' => $message,
                'response' => json_encode($response)
            ]);
        } catch (Exception $exception) {
            $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $error .= $exception->getMessage();
            Log::error('GenerateTelegramBotLog: ' . $error);
        }
    }
}
