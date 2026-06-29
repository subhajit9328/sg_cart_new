<?php

use Illuminate\Support\Facades\Route;
use SGCart\Shipping\Http\Controllers\ShippingRateController;

Route::middleware(['web'])->group(function () {
    // Admin Protected Routes
    Route::middleware(['auth', 'permission:manage shipping'])->prefix('admin')->name('admin.')->group(function () {
        Route::post('shipping/settings', [ShippingRateController::class, 'updateSettings'])->name('shipping.settings.update');
        Route::resource('shipping', ShippingRateController::class)->except(['show']);
    });
});
