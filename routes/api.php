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

Route::group(['middleware' => ['cors', 'decrypt']], function () {

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('auth')->namespace('Api')->group(function () {
        Route::post('register', 'AuthController@register');
        Route::post('registe-set-password', 'AuthController@registerSetPassword');
        Route::post('login', 'AuthController@login');
        Route::post('login_without_encryption', 'AuthController@loginWithoutEncryption');
        Route::post('check-email', 'AuthController@loginFirstPart');
        Route::post('forgot-password', 'AuthController@forgotPassword');
        Route::post('reset-password', 'AuthController@resetPassword');
    });

    Route::namespace('Api')->middleware('auth:api')->group(function () {

        Route::prefix('help')->group(function () {
            Route::post('create', 'HelpController@createHelp');
        });
    });
});
