<?php

use Illuminate\Support\Facades\Route;
use SGCart\Blog\Http\Controllers\Store\BlogController;

Route::middleware(['api'])->prefix('api')->name('api.')->group(function () {
    // Storefront API Routes
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
});
