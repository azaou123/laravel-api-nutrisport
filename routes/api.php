<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Middleware\SetSite;


Route::get('/hello', function () {
    return response()->json(['message' => 'API fonctionne']);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login/customer', [AuthController::class, 'loginCustomer']);
Route::post('login/agent', [AuthController::class, 'loginAgent']);

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    // Orders Placement 
    Route::post('/orders', [OrderController::class, 'store']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::post('/cart/remove', [CartController::class, 'remove']);
Route::delete('/cart/clear', [CartController::class, 'clear']);