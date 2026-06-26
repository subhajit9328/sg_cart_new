<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ManufacturerController;
use App\Http\Controllers\Admin\ProductController;

// Storefront Frontend Routes
Route::get('/', [StoreController::class, 'home'])->name('store.home');
Route::get('/shop', [StoreController::class, 'shop'])->name('store.shop');
Route::get('/search-live', [StoreController::class, 'searchLive'])->name('store.search-live');
Route::get('/product/{slug}', [StoreController::class, 'product'])->name('store.product');

Route::get('/cart', [StoreController::class, 'cart'])->name('store.cart');
Route::post('/cart/add', [StoreController::class, 'addToCart'])->name('store.cart.add');
Route::post('/cart/update', [StoreController::class, 'updateCart'])->name('store.cart.update');
Route::get('/cart/remove/{key}', [StoreController::class, 'removeFromCart'])->name('store.cart.remove');


Route::get('/success', [StoreController::class, 'success'])->name('store.success');
Route::post('/wishlist/toggle', [StoreController::class, 'toggleWishlist'])->name('store.wishlist.toggle');

// Storefront Auth Routes for Guest Customers
Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [AuthController::class, 'showStorefrontLogin'])->name('store.login');
    Route::post('/login', [AuthController::class, 'storefrontLogin'])->name('store.login.submit');
    Route::get('/register', [AuthController::class, 'showStorefrontRegister'])->name('store.register');
    Route::post('/register', [AuthController::class, 'storefrontRegister'])->name('store.register.submit');
});

// Storefront Auth Routes for Authenticated Customers
Route::middleware('auth:customer')->group(function () {
    Route::post('/logout', [AuthController::class, 'storefrontLogout'])->name('store.logout');
    Route::get('/checkout', [StoreController::class, 'checkout'])->name('store.checkout');
    Route::post('/checkout/order', [StoreController::class, 'placeOrder'])->name('store.checkout.order');
    Route::get('/account/order/{ulid}', [StoreController::class, 'viewOrder'])->name('store.account.order.view');
    Route::get('/account/order/{ulid}/invoice', [StoreController::class, 'downloadInvoice'])->name('store.account.order.invoice');
    Route::get('/account/{tab?}', [StoreController::class, 'account'])->name('store.account');
    Route::post('/account/profile/update', [StoreController::class, 'updateProfile'])->name('store.account.profile.update');
    Route::post('/account/address/add', [StoreController::class, 'addAddress'])->name('store.account.address.add');
    Route::post('/account/address/update/{id}', [StoreController::class, 'updateAddress'])->name('store.account.address.update');
    Route::get('/account/address/delete/{id}', [StoreController::class, 'deleteAddress'])->name('store.account.address.delete');
    Route::get('/account/order/{ulid}/json', [StoreController::class, 'getOrderDetail'])->name('store.account.order.detail');
});

// Admin Routes (prefixed with admin)
Route::prefix('admin')->group(function () {

    // Guest Auth Routes (admin/login)
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Admin Routes
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Admin Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('permission:view dashboard')
            ->name('admin.dashboard');

        // Payment Settings Route
        Route::get('payments/settings', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'index'])
            ->middleware('permission:manage payments')
            ->name('admin.payments.settings');
        Route::post('payments/settings', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'update'])
            ->middleware('permission:manage payments')
            ->name('admin.payments.settings.update');

        // CRUD routes under admin namespace (e.g. admin.users.index)
        Route::name('admin.')->group(function () {

            // User Management CRUD
            Route::resource('users', UserController::class)
                ->middleware('permission:manage users');

            // Role Management CRUD
            Route::resource('roles', RoleController::class)
                ->middleware('permission:manage roles');

            // Category Management CRUD
            Route::resource('categories', CategoryController::class)
                ->middleware('permission:manage products');

            // Manufacturer Management CRUD
            Route::resource('manufacturers', ManufacturerController::class)
                ->middleware('permission:manage products');

            // Product Management CRUD
            Route::resource('products', ProductController::class)
                ->middleware('permission:manage products');

            // Order Management Invoice print/stream
            Route::get('orders/{order}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'streamInvoice'])
                ->name('orders.invoice')
                ->middleware('permission:manage products');

            // Order Management CRUD
            Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)
                ->middleware('permission:manage products');

            // Extra product sub-routes
            Route::prefix('products')->name('products.')->middleware('permission:manage products')->group(function () {
                Route::post('bulk-upload', [ProductController::class, 'bulkUpload'])->name('bulk-upload');
                Route::delete('image/{productImage}', [ProductController::class, 'deleteImage'])->name('delete-image');
            });
        });
    });
});