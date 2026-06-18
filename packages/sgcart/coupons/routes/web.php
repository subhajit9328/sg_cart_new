<?php

use Illuminate\Support\Facades\Route;
use SGCart\Coupons\Http\Controllers\CouponController;

Route::middleware(['web'])->group(function () {
    
    // Storefront Routes
    Route::post('/cart/coupon', [CouponController::class, 'applyCoupon'])->name('store.cart.coupon');
    Route::get('/cart/coupon/remove', function () {
        session()->forget('coupon_code');
        return redirect()->back()->with('success', 'Coupon code removed.');
    })->name('store.cart.coupon.remove');

    // Admin Protected Routes
    Route::middleware(['auth', 'permission:manage coupons'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('coupons', CouponController::class)->except(['show']);
    });
});
