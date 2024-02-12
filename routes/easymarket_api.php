<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\easymarket\API\AuthController;



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

