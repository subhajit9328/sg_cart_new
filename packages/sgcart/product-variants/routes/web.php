<?php

use Illuminate\Support\Facades\Route;
use SGCart\ProductVariants\Http\Controllers\VariantController;
use SGCart\ProductVariants\Http\Controllers\ColorController;
use SGCart\ProductVariants\Http\Controllers\SizeController;

Route::middleware(['web'])->group(function () {
    // Storefront AJAX lookup
    Route::get('api/variants/lookup', [VariantController::class, 'lookup'])->name('variants.lookup');

    // Admin Authenticated Routes
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // Group permissions checks
        Route::middleware(['permission:manage variants'])->group(function () {
            
            if (config('product-variants.features.color', true) || config('product-variants.features.size', true)) {
                // Nested Product Variant management endpoints
                Route::get('products/{product_id}/variants', [VariantController::class, 'getGrid'])->name('products.variants.grid');
                Route::post('products/{product_id}/variants', [VariantController::class, 'saveGrid'])->name('products.variants.save');
                Route::post('products/{product_id}/variants/quick-add-attribute', [VariantController::class, 'quickAddAttribute'])->name('products.variants.quick-add-attribute');
                Route::delete('variants/image/{productVariantImage}', [VariantController::class, 'deleteProductVariantImage'])->name('variants.delete-image');

                // Color Swatches CRUD resource routes
                if (config('product-variants.features.color', true)) {
                    Route::resource('colors', ColorController::class);
                }

                // Size Swatches CRUD resource routes
                if (config('product-variants.features.size', true)) {
                    Route::resource('sizes', SizeController::class);
                }
            }
        });
    });
});
