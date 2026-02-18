<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\SetSite;


Route::get('/hello', function () {
    return response()->json(['message' => 'API fonctionne']);
});

Route::post('login/customer', [AuthController::class, 'loginCustomer']);
Route::post('login/agent', [AuthController::class, 'loginAgent']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::get('me', [AuthController::class, 'me']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);