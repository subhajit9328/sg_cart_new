<?php

use Illuminate\Support\Facades\Route;
use SGCart\LogisticTracking\Http\Controllers\Admin\ShippingCourierController;

Route::middleware(['web', 'auth', 'can:manage products'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('couriers', ShippingCourierController::class)->except(['show', 'edit', 'update']);
});
