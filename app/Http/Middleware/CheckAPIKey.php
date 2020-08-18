<?php

namespace App\Http\Middleware;

use Closure;
use Exception;

class CheckAPIKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $apiKey = config('monitor.api_key');
            if (!$request->has('api_key')) {
                throw new Exception('API Key not found.');
            } else {
                if ($request->input('api_key') != $apiKey) {
                    throw new Exception('API Key is invalid.');
                }
            }
        } catch (Exception $exception) {
            $response = [
                'status' => 'FAILED',
                'code' => 400,
                'message' => $exception->getMessage() ?? 'Something went wrong.'
            ];
            return response()->json($response);
        }

        return $next($request);
    }
}
