<?php

use Illuminate\Support\Facades\Route;
use SGCart\Reviews\Http\Controllers\ReviewController;
use SGCart\Reviews\Http\Controllers\Admin\ReviewController as AdminReviewController;

Route::middleware(['web'])->group(function () {
    
    // Storefront protected routes for customer reviews submission/editing
    Route::middleware(['auth:customer', 'verified.customer'])->group(function () {
        Route::post('/store/reviews', [ReviewController::class, 'store'])->name('store.reviews.store');
    });

    // Admin protected routes for review moderation
    Route::middleware(['auth', 'permission:manage reviews'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    });
});
