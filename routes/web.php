<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;

// Restore the ecommerce welcome view for the main site
Route::get('/', function () {
    return view('welcome');
});

// Admin Routes (prefixed with admin)
Route::prefix('admin')->group(function () {

    // Guest Auth Routes (admin/login)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected Admin Routes
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Admin Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('permission:view dashboard')
            ->name('admin.dashboard');

        // CRUD routes under admin namespace (e.g. admin.users.index)
        Route::name('admin.')->group(function () {
            // User Management CRUD
            Route::resource('users', UserController::class)
                ->middleware('permission:manage users');

            // Role Management CRUD
            Route::resource('roles', RoleController::class)
                ->middleware('permission:manage roles');
        });
    });
});
