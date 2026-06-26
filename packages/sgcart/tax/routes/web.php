<?php

use Illuminate\Support\Facades\Route;
use SGCart\Tax\Http\Controllers\TaxRateController;

Route::middleware(['web'])->group(function () {
    // Admin Protected Routes
    Route::middleware(['auth', 'permission:manage tax'])->prefix('admin')->name('admin.')->group(function () {
        Route::post('tax/settings', [TaxRateController::class, 'updateSettings'])->name('tax.settings.update');
        Route::resource('tax', TaxRateController::class)->except(['show']);
    });
});
