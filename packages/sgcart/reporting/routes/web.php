<?php

use Illuminate\Support\Facades\Route;
use SGCart\Reporting\Http\Controllers\Admin\OrderReportController;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['permission:view reports'])->group(function () {
        Route::get('/reports/orders', [OrderReportController::class, 'index'])
            ->name('reports.orders');
    });
});
