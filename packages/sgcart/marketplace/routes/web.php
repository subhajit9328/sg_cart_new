<?php

use Illuminate\Support\Facades\Route;
use SGCart\Marketplace\Http\Controllers\Seller\AuthController as SellerAuthController;
use SGCart\Marketplace\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use SGCart\Marketplace\Http\Controllers\Seller\ProductController as SellerProductController;
use SGCart\Marketplace\Http\Controllers\Seller\OrderController as SellerOrderController;
use SGCart\Marketplace\Http\Controllers\Seller\CommissionController as SellerCommissionController;

use SGCart\Marketplace\Http\Controllers\Admin\SellerController as AdminSellerController;
use SGCart\Marketplace\Http\Controllers\Admin\ProductApprovalController as AdminProductApprovalController;
use SGCart\Marketplace\Http\Controllers\Admin\PlatformCommissionController as AdminPlatformCommissionController;

// Public Onboarding & Auth Routes (for Sellers, using web middleware)
Route::middleware(['web'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/login', [SellerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [SellerAuthController::class, 'login'])->name('login.submit');
    Route::get('/registration', [SellerAuthController::class, 'showRegister'])->name('register');
    Route::post('/registration', [SellerAuthController::class, 'register'])->name('register.submit');

    // OTP verification routes
    Route::get('/otp/verify', [SellerAuthController::class, 'showOtpVerify'])->name('otp.verify');
    Route::post('/otp/verify', [SellerAuthController::class, 'otpVerify'])->name('otp.verify.submit');
    Route::post('/otp/resend', [SellerAuthController::class, 'otpResend'])->name('otp.resend');
});

// Protected Seller Portal Routes (completely separate prefix and auth:seller guard)
Route::middleware(['web', 'auth:seller', 'seller.onboarded'])->prefix('seller')->name('seller.')->group(function () {
    Route::post('/logout', [SellerAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');

    // Onboarding routes
    Route::get('/onboarding', [SellerAuthController::class, 'showOnboarding'])->name('onboarding');
    Route::post('/onboarding', [SellerAuthController::class, 'submitOnboarding'])->name('onboarding.submit');

    // Restricted routes that require the seller to be fully approved
    Route::middleware(['seller.approved'])->group(function () {
        Route::delete('products/image/{productImage}', [SellerProductController::class, 'deleteImage'])->name('products.delete-image');
        Route::post('products/{product}/resubmit', [SellerProductController::class, 'resubmit'])->name('products.resubmit');
        Route::resource('products', SellerProductController::class);
        Route::resource('orders', SellerOrderController::class)->only(['index', 'show']);
        Route::get('/commissions', [SellerCommissionController::class, 'index'])->name('commissions');

        // Product Variant Management Endpoints (copied from SGCart\ProductVariants package for Sellers)
        if (class_exists(\SGCart\ProductVariants\Http\Controllers\VariantController::class)) {
            Route::get('products/{product_id}/variants', [\SGCart\ProductVariants\Http\Controllers\VariantController::class, 'getGrid'])->name('products.variants.grid');
            Route::post('products/{product_id}/variants', [\SGCart\ProductVariants\Http\Controllers\VariantController::class, 'saveGrid'])->name('products.variants.save');
            Route::post('products/{product_id}/variants/quick-add-attribute', [\SGCart\ProductVariants\Http\Controllers\VariantController::class, 'quickAddAttribute'])->name('products.variants.quick-add-attribute');
            Route::delete('variants/image/{productVariantImage}', [\SGCart\ProductVariants\Http\Controllers\VariantController::class, 'deleteProductVariantImage'])->name('variants.delete-image');
            
            Route::resource('colors', \SGCart\Marketplace\Http\Controllers\Seller\ColorController::class);
            Route::resource('sizes', \SGCart\Marketplace\Http\Controllers\Seller\SizeController::class);
        }
    });
});

// Admin Area Extensions (for Administrators, managing marketplace vendors & approvals)
Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['role:Super Admin|Admin|super-admin|admin'])->group(function () {
        // Manage Sellers
        Route::get('/sellers', [AdminSellerController::class, 'index'])->name('sellers.index');
        Route::get('/sellers/{seller}', [AdminSellerController::class, 'show'])->name('sellers.show');
        Route::post('/sellers/{seller}/approve', [AdminSellerController::class, 'approve'])->name('sellers.approve');
        Route::post('/sellers/{seller}/suspend', [AdminSellerController::class, 'suspend'])->name('sellers.suspend');
        Route::post('/sellers/{seller}/reject', [AdminSellerController::class, 'reject'])->name('sellers.reject');
        Route::post('/sellers/{seller}/update-commission', [AdminSellerController::class, 'updateCommission'])->name('sellers.update-commission');

        // Product Approvals
        Route::get('/product-approvals', [AdminProductApprovalController::class, 'index'])->name('products.approvals');
        Route::post('/product-approvals/{product}/approve', [AdminProductApprovalController::class, 'approve'])->name('products.approvals.approve');
        Route::post('/product-approvals/{product}/reject', [AdminProductApprovalController::class, 'reject'])->name('products.approvals.reject');

        // Platform Commission Ledger
        Route::get('/commissions', [AdminPlatformCommissionController::class, 'index'])->name('commissions.index');
    });
});
