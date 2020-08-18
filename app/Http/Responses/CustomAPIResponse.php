<?php

namespace App\Http\Responses;
use App\Enum\StatusEnum;
use stdClass;

class CustomAPIResponse
{
    use StatusEnum;

    protected $success;
    protected $error;
    protected $unknown;

    public function __construct()
    {
        $this->success = new stdClass();
        $this->success->status = StatusEnum::$_SUCCESS;
        $this->success->code = StatusEnum::$_SUCCESS_CODE;

        $this->error = new stdClass();
        $this->error->status = StatusEnum::$_ERROR;
        $this->error->code = StatusEnum::$_ERROR_CODE;

        $this->unknown = new stdClass();
        $this->unknown->status = StatusEnum::$_UNKNOWN;
        $this->unknown->code = StatusEnum::$_UNKNOWN_CODE;
    }

    public function customErrorResponse($message, $validated = true, $key = null)
    {
        $message = empty($key) ? $message : [$key => $message];
        return [
            'status' => $this->success->status,
            'code' => $this->success->code,
            'success' => false,
            'validated' => $validated,
            'data' => [],
            'message' => $message,
        ];
    }

    public function customSuccessResponse($message, $data = [], $key = null)
    {
        $message = empty($key) ? $message : [$key => $message];
        return [
            'status' => $this->success->status,
            'code' => $this->success->code,
            'success' => true,
            'validated' => true,
            'data' => $data,
            'message' => $message,
        ];
    }

    public function customResponse($status, $message, $validated = true, $data = [])
    {
//        return ['status' => $status, 'data' => $data, 'message' => $message];
        $resp = new stdClass();
        $resp_data = [];
        if ($status == $this->success->status) {
            $resp->status = $this->success->status;
            $resp->code = $this->success->code;
            $resp->success = true;
            $resp->validated = true;
            $resp_data = $data;
        } elseif ($status == $this->error->status) {
            $resp->status = $this->error->status;
            $resp->code = $this->error->code;
            $resp->success = false;
            $resp->validated = $validated;
        } else {
            $resp->status = $this->unknown->status;
            $resp->code = $this->unknown->code;
            $resp->success = false;
            $resp->validated = false;
        }

        return [
            'status' => $resp->status,
            'code' => $resp->code,
            'success' => $resp->success,
            'validated' => $resp->validated,
            'data' => $resp_data,
            'message' => $message,
        ];
    }
}
