<?php

use Illuminate\Support\Facades\Route;
use SGCart\Reporting\Http\Controllers\Admin\OrderReportController;
use SGCart\Reporting\Http\Controllers\Admin\CustomerReportController;
use SGCart\Reporting\Http\Controllers\Admin\RevenueReportController;
use SGCart\Reporting\Http\Controllers\Admin\ProductReportController;
use SGCart\Reporting\Http\Controllers\Admin\BehaviorReportController;
use SGCart\Reporting\Http\Controllers\Admin\ConversionReportController;
use SGCart\Reporting\Http\Controllers\Admin\TrafficReportController;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware(['permission:view reports'])->group(function () {
        // Orders
        Route::get('/reports/orders', [OrderReportController::class, 'index'])->name('reports.orders');
        Route::get('/reports/orders/export', [OrderReportController::class, 'export'])->name('reports.orders.export');

        // Customers
        Route::get('/reports/customers', [CustomerReportController::class, 'index'])->name('reports.customers');
        Route::get('/reports/customers/export', [CustomerReportController::class, 'export'])->name('reports.customers.export');

        // Revenue
        Route::get('/reports/revenue', [RevenueReportController::class, 'index'])->name('reports.revenue');
        Route::get('/reports/revenue/export', [RevenueReportController::class, 'export'])->name('reports.revenue.export');

        // Products
        Route::get('/reports/products', [ProductReportController::class, 'index'])->name('reports.products');
        Route::get('/reports/products/export', [ProductReportController::class, 'export'])->name('reports.products.export');

        // Behavior
        Route::get('/reports/behavior', [BehaviorReportController::class, 'index'])->name('reports.behavior');
        Route::get('/reports/behavior/export', [BehaviorReportController::class, 'export'])->name('reports.behavior.export');

        // Conversion & Funnel
        Route::get('/reports/conversion', [ConversionReportController::class, 'index'])->name('reports.conversion');
        Route::get('/reports/conversion/export', [ConversionReportController::class, 'export'])->name('reports.conversion.export');

        // Traffic
        Route::get('/reports/traffic', [TrafficReportController::class, 'index'])->name('reports.traffic');
        Route::get('/reports/traffic/export', [TrafficReportController::class, 'export'])->name('reports.traffic.export');
    });
});
