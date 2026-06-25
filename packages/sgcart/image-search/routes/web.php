<?php

use Illuminate\Support\Facades\Route;
use SGCart\ImageSearch\Http\Controllers\ImageSearchController;

Route::middleware(['web'])->group(function () {
    Route::post('/image-search/detect-multiple', [ImageSearchController::class, 'detectMultiple'])->name('image-search.detect-multiple');
    Route::post('/image-search/search', [ImageSearchController::class, 'search'])->name('image-search.search');
});
