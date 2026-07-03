<?php

use Illuminate\Support\Facades\Route;
use SGCart\Hero\Http\Controllers\Admin\HeroController;

Route::middleware(['web'])->group(function () {
    Route::middleware(['auth', 'permission:manage hero section'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/hero-settings', [HeroController::class, 'index'])->name('hero.settings');
        Route::post('/hero-settings', [HeroController::class, 'update'])->name('hero.settings.update');
        Route::delete('/hero-settings/image/{image}', [HeroController::class, 'deleteImage'])->name('hero.settings.delete-image');
    });
});
