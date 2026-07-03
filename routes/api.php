<?php

use App\Http\Controllers\Api\CustomerAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Customer Auth Endpoints
Route::prefix('customer')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/resend-otp', [CustomerAuthController::class, 'resendOtp']);
});

// Protected Customer Auth Endpoints (JWT Guard)
Route::middleware('auth:customer-api')->prefix('customer')->group(function () {
    Route::get('/me', [CustomerAuthController::class, 'me']);
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
});
