<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\easymarket\API\AuthController;
use App\Http\Controllers\easymarket\API\MeController;
use App\Http\Controllers\easymarket\API\ProductController;
use Illuminate\Support\Facades\Log;





/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/auth/signup', [AuthController::class, 'signup']);
Route::post('/auth/signup/verify', [AuthController::class, 'signupVerify']);
Route::post('/auth/signin', [AuthController::class, 'signin']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);



Route::middleware(['auth:easymarket_api', 'verified'])->group(function () {
    Route::post('/auth/signout', [AuthController::class, 'signout']);

    Route::get('/me', [MeController::class, 'show']);
    Route::put('/me', [MeController::class, 'update']);
    Route::get('/me/purchased_products', [MeController::class, 'getPurchasedProducts']);
    Route::get('/me/purchased_products/{product}/deal', [MeController::class, 'getPurchasedProductDeal']);
    Route::get('/me/listed_products', [MeController::class, 'getListedProducts']);
    Route::get('/me/listed_products/{product}/deal', [MeController::class, 'getListedProductDeal']);

    Route::post('/products', [ProductController::class, 'store']);


});

