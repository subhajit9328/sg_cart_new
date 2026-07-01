<?php

use Illuminate\Support\Facades\Route;
use SGCart\ImageSearch\Http\Controllers\ImageSearchController;
use SGCart\ImageSearch\Http\Controllers\Admin\ImageSearchSettingsController;

Route::middleware(['web'])->group(function () {
    Route::post('/image-search/detect-multiple', [ImageSearchController::class, 'detectMultiple'])->name('image-search.detect-multiple');
    Route::post('/image-search/search', [ImageSearchController::class, 'search'])->name('image-search.search');
});

Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::get('/image-search/settings', [ImageSearchSettingsController::class, 'index'])->name('admin.image-search.settings');
    Route::post('/image-search/settings', [ImageSearchSettingsController::class, 'update'])->name('admin.image-search.settings.update');
});
