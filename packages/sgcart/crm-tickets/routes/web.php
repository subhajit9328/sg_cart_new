<?php

use Illuminate\Support\Facades\Route;
use SGCart\CrmTickets\Http\Controllers\TicketController;
use SGCart\CrmTickets\Http\Controllers\Admin\TicketController as AdminTicketController;

Route::middleware(['web'])->group(function () {

    // Customer-facing backend routes (no views yet)
    Route::middleware(['auth:customer', 'verified.customer'])->group(function () {
        Route::post('/store/tickets', [TicketController::class, 'store'])->name('store.tickets.store');
        Route::get('/store/tickets/{ticket}', [TicketController::class, 'show'])->name('store.tickets.show');
        Route::post('/store/tickets/{ticket}/comments', [TicketController::class, 'addComment'])->name('store.tickets.comments.store');
    });

    // Admin routes
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        Route::middleware(['permission:view tickets'])->group(function () {
            Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        });

        Route::middleware(['permission:manage tickets'])->group(function () {
            Route::post('/tickets/bulk-action', [AdminTicketController::class, 'bulkAction'])->name('tickets.bulkAction');
            Route::delete('/tickets/{ticket}', [AdminTicketController::class, 'destroy'])->name('tickets.destroy');
            Route::post('/tickets/{ticket}/comments', [AdminTicketController::class, 'addComment'])->name('tickets.comments.store');
            Route::post('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('tickets.updateStatus');
            Route::post('/tickets/{ticket}/priority', [AdminTicketController::class, 'updatePriority'])->name('tickets.updatePriority');
        });
    });
});
