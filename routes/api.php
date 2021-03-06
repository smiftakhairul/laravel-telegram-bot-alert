<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('monitor.api.key')
    ->namespace('API')->name('monitor.')->prefix('monitor')->group(function () {
    Route::post('send-message', 'MonitorController@sendMessage')->name('send-message');
    Route::post('check-domain', 'MonitorController@checkDomain')->name('check-domain');
    Route::post('check-db', 'MonitorController@checkDb')->name('check-db');
    Route::post('check-directory', 'MonitorController@checkDirectory')->name('check-directory');
});
