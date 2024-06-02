<?php

use App\Enum\UserGaurdEnum;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Middleware\AdminGuardMiddleware;
use Illuminate\Support\Facades\Route;;

Route::post('login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum', AdminGuardMiddleware::class]], function () {
    Route::post('create', [AuthController::class, 'create']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
});
