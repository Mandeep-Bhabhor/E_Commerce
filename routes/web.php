<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Route;

// Google / Auth0 routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])
        ->name('auth.google');
    Route::get('/callback', [SocialAuthController::class, 'handleCallback'])
        ->name('auth.callback');
    Route::get('/auth/facebook', [SocialAuthController::class, 'redirectToFacebook'])
        ->name('auth.facebook');
});

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('auth');
// Admin routes — Breeze auth + admin role
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('colors', ColorController::class);
    Route::resource('sizes', SizeController::class);
    Route::resource('taxes', TaxController::class);
    Route::resource('discounts', DiscountController::class);

    Route::get('/search-products', [ProductController::class, 'search'])->name('products.search');
    Route::get('/search-sizes', [SizeController::class, 'search'])->name('sizes.search');
    Route::get('/search-colors', [ColorController::class, 'search'])->name('colors.search');
    Route::get('/search-categories', [CategoryController::class, 'search'])->name('categories.search');

    Route::get('/orders', [OrderController::class, 'adminIndex'])
        ->name('admin.orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'adminShow'])
        ->name('admin.orders.show');
    Route::get('/search', [OrderController::class, 'adminSearch'])
        ->name('admin.orders.search');
    Route::put('/order-items/{id}/status', [OrderController::class, 'updateItemStatus'])
        ->name('order.items.updateStatus');

    Route::put('/order/{id}/status', [OrderController::class, 'updateOrderStatus'])
        ->name('order.updateStatus');
    // //
    Route::get('/returns', [OrderController::class, 'returnRequests'])
        ->name('admin.returns');

    Route::put('/return/{id}/approve', [OrderController::class, 'approveReturn'])
        ->name('admin.return.approve');

    Route::put('/return/{id}/reject', [OrderController::class, 'rejectReturn'])
        ->name('admin.return.reject');

});

require __DIR__.'/auth.php';
