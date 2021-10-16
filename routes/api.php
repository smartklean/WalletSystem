<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;


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



Route::group(['prefix' => 'v1'], function () {

    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register',  [UserController::class, 'register']);

    Route::middleware('auth:api')->group(function(){
        Route::get('/logout', [UserController::class, 'logout']);
        Route::get('/other-users',  [UserController::class, 'getOtherUser']);
        Route::get('/balance', [AccountController::class, 'getBalance']);
        Route::post('/account', [AccountController::class, 'create']);
        Route::post('/topup', [AccountController::class, 'topUp']);
        Route::post('/payment', [AccountController::class, 'payment']);
    });
});


