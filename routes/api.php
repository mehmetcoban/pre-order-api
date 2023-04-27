<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\CartController;
use App\Http\Controllers\Api\v1\PreOrderController;
use App\Http\Controllers\Api\v1\ProductController;

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


Route::prefix('v1')->group(function () {
    //Auth routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Product routes
    Route::resource('products', ProductController::class)->only(['index', 'show']);

    Route::middleware(['auth:api'])->group(function () {
        //Cart routes
        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart', [CartController::class, 'addToCart']);
        Route::put('/cart/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/cart/{id}', [CartController::class, 'removeFromCart']);
        Route::delete('/cart', [CartController::class, 'destroy']);

        // Pre-order routes
        Route::get('/pre-order', [PreOrderController::class, 'index']);
        Route::post('/pre-order', [PreOrderController::class, 'store']);
        Route::put('/pre-order/{preOrder}', [PreOrderController::class, 'update']);

        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('test', [AuthController::class, 'test']);
    });
});
