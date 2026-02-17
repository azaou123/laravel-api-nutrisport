<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return'welcome';
});

Route::post('login/customer', [AuthController::class, 'loginCustomer']);
Route::post('login/agent', [AuthController::class, 'loginAgent']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::get('me', [AuthController::class, 'me']);