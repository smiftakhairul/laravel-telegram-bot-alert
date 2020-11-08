<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Responses\CustomAPIResponse;
use App\Models\TelegramBotLog;
use App\Services\MonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * MonitorController constructor.
     */
    public function __construct()
    {
        $this->apiResponse = new CustomAPIResponse();
        $this->monitorService = new MonitorService();
        $this->telegramBotToken = config('telegram.bots.mybot.token');
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telegram_chat_id' => 'array',
            'message' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($this->apiResponse->customErrorResponse($validator->errors(), false));
        }

        $response = null;

        try {
            $messageList = $request->input('message');

            $telegramChatId = ($request->has('telegram_chat_id') && !empty($request->input('telegram_chat_id')))
                ? $request->input('telegram_chat_id') : config('monitor.telegram_chat_id');

            foreach ($messageList as $message) {
                if (!empty($message)) {
                    $message = strval($message);
                    $messageResponse = $this->pushTelegramMessage($request, $telegramChatId, $message, true);

                    if( count($messageResponse) && ! $messageResponse[0]) {

                        throw new \Exception("Failed to send message");
                    }
                    $response['telegram_info'][] = $messageResponse;
                }
            }
            return response()->json($this->apiResponse->customSuccessResponse("Message sent succssfully", $response));
        } catch (Exception $exception) {
            $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $error .= $exception->getMessage();
            Log::error('SendMessage: ' . $error);
            $response = $error;
            return response()->json($this->apiResponse->customErrorResponse($response, false));
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkDomain(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telegram_chat_id' => 'array',
            'domain_list' => 'required|array',
            'domain_list.*.domain' => 'required',
            'domain_list.*.port' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($this->apiResponse->customErrorResponse($validator->errors(), false));
        }

        $responseData = null;

        try {
            $domainList = $request->input('domain_list');

            $telegramChatId = ($request->has('telegram_chat_id') && !empty($request->input('telegram_chat_id')))
                ? $request->input('telegram_chat_id') : config('monitor.telegram_chat_id');

            $message = '';
            foreach ($domainList as $domainItem) {
                $connStatus = $this->monitorService->getConnectionStatus($domainItem['domain'], $domainItem['port']);
                if ($connStatus['status'] == 'FAILED') {
                    $message .= $connStatus['message'] . "\n\n";
                }
            }
            $response = [];
            if (!empty($message)) {
                $response = $this->pushTelegramMessage($request, $telegramChatId, $message);
            }
            $responseData = $this->apiResponse->customSuccessResponse('Run successfully',$response);
        } catch (Exception $exception) {
            $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $error .= $exception->getMessage();
            Log::error('CheckDomain: ' . $error);
            $responseData = $this->apiResponse->customErrorResponse($error, false);
        }

        return response()->json($responseData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkDb(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telegram_chat_id' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json($this->apiResponse->customErrorResponse($validator->errors(), false));
        }

        $response = null;
        $responseData = null;
        try {
            $telegramChatId = ($request->has('telegram_chat_id') && !empty($request->input('telegram_chat_id')))
                ? $request->input('telegram_chat_id') : config('monitor.telegram_chat_id');

            $logs = DB::select('SHOW FULL PROCESSLIST');
            $min_items = ($request->has('min_processlist_item') && !empty($request->input('min_processlist_item')))
                ? $request->input('min_processlist_item') : config('monitor.min_processlist_item');
            $response['total_processlist'] = $logs;
            if (count($logs) >= $min_items) {
                $active = 0; $sleep = 0;
                foreach ($logs as $log) {
                    if (strtoupper($log->Command) == 'SLEEP') {
                        $sleep++;
                    } else {
                        $active++;
                    }
                }

                $message = "PROCESSLIST COMMAND COUNT `active`: $active\nPROCESSLIST COMMAND COUNT `sleep`: $sleep";
                if (!empty($message)) {
                    $response['telegram_info'][] = $this->pushTelegramMessage($request, $telegramChatId, $message);
                }
            }
            $responseData = $this->apiResponse->customSuccessResponse('Run successfully',$response);
        } catch (Exception $exception) {
            $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $error .= $exception->getMessage();
            Log::error('CheckDB: ' . $error);
            $response = $error;
            $responseData = $this->apiResponse->customErrorResponse($error, false);
        }

        return response()->json($responseData);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkDirectory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telegram_chat_id' => 'array',
            'directory_list' => 'required|array',
            'max_alloc_space_percent' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json($this->apiResponse->customErrorResponse($validator->errors(), false));
        }
        $responseData = [];
        date_default_timezone_set('Asia/Dhaka');

        $response = null;

        try {
            $directoryList = $request->input('directory_list');

            $telegramChatId = ($request->has('telegram_chat_id') && !empty($request->input('telegram_chat_id')))
                ? $request->input('telegram_chat_id') : config('monitor.telegram_chat_id');

            $max_alloc = ($request->has('max_alloc_space_percent') && !empty($request->input('max_alloc_space_percent')))
                ? $request->input('max_alloc_space_percent') : config('monitor.max_space_alloc_percent');

            $message = '';
            foreach ($directoryList as $dir) {
                $spaceStatus = $this->monitorService->getSpaceStatus($dir, $max_alloc);
                if ($spaceStatus['status'] == 'FAILED') {
                    $message .= $spaceStatus['message'] . "\n\n";
                }
                $response['space_status_details'][] = $spaceStatus;
            }


            if (!empty($message)) {
                $response['telegram_info'] = $this->pushTelegramMessage($request, $telegramChatId, $message);
            }
            $responseData = $this->apiResponse->customSuccessResponse('Run successfully',$response);
        } catch (Exception $exception) {
            $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $error .= $exception->getMessage();
            Log::error('CheckSpaceStatus: ' . $error);
            $response = $error;
            $responseData = $this->apiResponse->customErrorResponse($error,false);
        }

        return response()->json($responseData);
    }

    /**
     * @param Request $request
     * @param array $telegramChatIds
     * @param $message
     * @return array
     */
    protected function pushTelegramMessage(Request $request, array $telegramChatIds, $message, bool $is_general = false)
    {
        $response = [];
        $telegramBotToken = null;

        $telegramBotToken = ($request->has('telegram_bot_token') && !empty($request->input('telegram_bot_token')))
            ? $request->input('telegram_bot_token') : $this->telegramBotToken;

        if (!$is_general) {
            $messagePrefixTitle = ($request->has('title') && !empty($request->input('title')))
                ? $request->input('title') : config('monitor.app_title');
            $message = 'Title: ' . $messagePrefixTitle . "\n" . $message;
        }

        foreach ($telegramChatIds as $telegramChatId) {
            try {
                $telegram = new TelegramBot($telegramBotToken);

                $res = $telegram->sendMessage([
                    'chat_id' => $telegramChatId,
                    'text' => $message,
                ]);
            } catch (Exception $exception) {
                $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
                $error .= $exception->getMessage();
                Log::error('PushTelegramMessage: ' . $error);
                $res = $error;
                $res = false;
            }

            $this->generateTelegramBotLog($telegramBotToken, $telegramChatId, $message, $res);
            $response[] = $res;
        }

        return $response;
    }

    /**
     * @param $telegramBotToken
     * @param $telegramChatId
     * @param $message
     * @param array $response
     */
    protected function generateTelegramBotLog($telegramBotToken, $telegramChatId, $message, $response = [])
    {
        try {
            $monitor_log_enabled = config('monitor.monitor_log_enabled');
            $inpurArrayData = [
                'bot_token' => $telegramBotToken,
                'chat_id' => $telegramChatId,
                'message' => $message,
                'response' => json_encode($response)
            ];
            if ($monitor_log_enabled) {
                TelegramBotLog::create($inpurArrayData);
            }
            Log::info("TelegramBotLog : ". json_encode($inpurArrayData));
        } catch (Exception $exception) {
            $error = '[' . $exception->getCode() . ', ' . $exception->getFile() . ', ' . $exception->getLine() . ']: ';
            $error .= $exception->getMessage();
            Log::error('GenerateTelegramBotLog: ' . $error);
        }
    }
}
