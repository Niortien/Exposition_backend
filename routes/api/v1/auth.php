<?php

// Routes du module Utilisateurs & KYC.

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KycController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::patch('me', [AuthController::class, 'updateMe']);

    Route::post('otp/request', [AuthController::class, 'requestOtp']);
    Route::post('otp/verify', [AuthController::class, 'verifyOtp']);

    Route::post('kyc', [KycController::class, 'submit']);

    Route::middleware('role:admin,moderateur')->group(function () {
        Route::get('kyc', [KycController::class, 'index']);
        Route::patch('kyc/{kycRequest}', [KycController::class, 'moderate']);
    });
});
