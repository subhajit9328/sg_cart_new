<?php

use Illuminate\Support\Facades\Route;
use SGCart\Blog\Http\Controllers\Store\BlogController;
use SGCart\Blog\Http\Controllers\Admin\BlogPostController;
use SGCart\Blog\Http\Controllers\Admin\BlogCategoryController;

Route::middleware(['web'])->group(function () {
    // Storefront Routes
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

    // Admin Protected Routes
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        Route::middleware(['permission:manage blog'])->group(function () {
            Route::resource('blog-categories', BlogCategoryController::class)->except(['show']);
            Route::resource('blog-posts', BlogPostController::class);
        });
    });
});
