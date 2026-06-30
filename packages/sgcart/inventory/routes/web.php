<?php

use Illuminate\Support\Facades\Route;
use SGCart\Inventory\Http\Controllers\InventoryController;

Route::middleware(['web', 'auth', 'role:Super Admin|Admin|super-admin'])->prefix('admin/inventory')->name('admin.inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::get('/adjust', [InventoryController::class, 'adjustForm'])->name('adjust.form');
    Route::post('/adjust', [InventoryController::class, 'adjust'])->name('adjust');
    Route::get('/logs', [InventoryController::class, 'logs'])->name('logs');
    Route::get('/search-products', [InventoryController::class, 'searchProducts'])->name('search-products');
    Route::get('/products/{productId}/variants', [InventoryController::class, 'getVariants'])->name('products.variants');
});
