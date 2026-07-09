<?php

use Illuminate\Support\Facades\Route;
use SGCart\SocialShare\Http\Controllers\SocialShareController;
use SGCart\SocialShare\Http\Controllers\Admin\SocialShareController as AdminSocialShareController;

Route::middleware(['web'])->group(function () {
    
    // Frontend feed and follow actions
    Route::get('/studio', [SocialShareController::class, 'index'])->name('store.social-share.index');
    
    Route::middleware(['auth:customer', 'verified.customer'])->group(function () {
        Route::post('/studio/post', [SocialShareController::class, 'store'])->name('store.social-share.store');
        Route::post('/studio/follow/{influencerId}', [SocialShareController::class, 'toggleFollow'])->name('store.social-share.follow');
    });

    // Admin dashboard moderation routes
    Route::middleware(['auth', 'permission:manage social shares'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/social-shares', [AdminSocialShareController::class, 'index'])->name('social-shares.index');
        Route::post('/social-shares/{id}/approve', [AdminSocialShareController::class, 'approve'])->name('social-shares.approve');
        Route::post('/social-shares/{id}/reject', [AdminSocialShareController::class, 'reject'])->name('social-shares.reject');
        Route::delete('/social-shares/{id}', [AdminSocialShareController::class, 'destroy'])->name('social-shares.destroy');
    });
});
