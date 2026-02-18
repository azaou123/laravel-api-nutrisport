<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BackOfficeController;

/*
|--------------------------------------------------------------------------
| Public Routes (site context required)
|--------------------------------------------------------------------------
*/
Route::middleware(['set.site'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/remove', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
});

/*
|--------------------------------------------------------------------------
| Authentication & Profile (no site needed)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login/customer', [AuthController::class, 'loginCustomer']);
Route::post('/login/agent', [AuthController::class, 'loginAgent']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

/*
|--------------------------------------------------------------------------
| Customer Order Placement (needs site context)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'set.site'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| BackOffice (agent only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'agent'])->prefix('backoffice')->group(function () {
    Route::get('/orders/recent', [BackOfficeController::class, 'recentOrders']);
    Route::post('/products', [BackOfficeController::class, 'createProduct']);
});