<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Customer Auth Endpoints
Route::prefix('customer')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/resend-otp', [CustomerAuthController::class, 'resendOtp']);

    // Forgot Password Endpoints
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetOtp']);
    Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/forgot-password/resend', [ForgotPasswordController::class, 'resendOtp']);
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);
});

// Protected Customer Auth Endpoints (JWT Guard)
Route::middleware('auth:customer-api')->prefix('customer')->group(function () {
    Route::get('/me', [CustomerAuthController::class, 'me']);
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
});

