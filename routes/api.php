<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\TwoFactorController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return User::all();
})->middleware('auth:sanctum');

// ─────────────────────────────────────────────
// PRODUCTS — specific named routes FIRST,
// then the {id} wildcard LAST
// ─────────────────────────────────────────────
Route::prefix('products')->group(function () {

    // Static/named routes — must come before any {wildcard}
    Route::post('/create', [ProductController::class, 'store']);
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/api/export', [ProductController::class, 'apiExport']);

    // Protected routes (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [ProductController::class, 'index']);

        Route::post('/api/apiimport', [ProductController::class, 'apiImport']);
        Route::get('/{id}', [ProductController::class, 'show']);      // wildcard LAST
        Route::patch('/update/{product}', [ProductController::class, 'update']);
        Route::patch('/delete/{product}', [ProductController::class, 'destroy']);
    });
});

// Colors
Route::get('/colors', [ColorController::class, 'index']);
Route::get('/color/{id}', [ColorController::class, 'show']);
Route::post('/colors/create', [ColorController::class, 'store']);
Route::patch('/colors/update/{color}', [ColorController::class, 'update']);
Route::patch('/colors/delete/{color}', [ColorController::class, 'destroy']);

// Sizes
Route::get('/sizes', [SizeController::class, 'index']);
Route::get('/size/{id}', [SizeController::class, 'show']);
Route::post('/sizes/create', [SizeController::class, 'store']);
Route::patch('/sizes/update/{size}', [SizeController::class, 'update']);
Route::patch('/sizes/delete/{size}', [SizeController::class, 'destroy']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/category/{id}', [CategoryController::class, 'show']);
Route::post('/categories/create', [CategoryController::class, 'store']);
Route::patch('/categories/update/{category}', [CategoryController::class, 'update']);
Route::patch('/categories/delete/{category}', [CategoryController::class, 'destroy']);

// Addresses
Route::get('customers/{user_id}/address', [AddressController::class, 'index']);
Route::post('customers/{user_id}/address', [AddressController::class, 'store']);
Route::patch('address/{user_id}', [AddressController::class, 'update']);
Route::delete('address/{user_id}', [AddressController::class, 'destroy']);


Route::middleware('auth:sanctum')->group(function () {
// Carts
Route::post('carts/store', [CartController::class, 'store']);
Route::post('cart/update/{id}', [CartController::class, 'update']);
Route::delete('cart/delete/{id}', [CartController::class, 'destroy']);
Route::post('carts/show/{id}', [CartController::class, 'show']);
Route::post('carts', [CartController::class, 'index']);
Route::post('/carts/truncate', [CartController::class, 'cart_truncate']);





    //Wishlists
    Route::post('wishlist/store', [WishlistController::class, 'store']);
    Route::post('wishlist/remove', [WishlistController::class, 'remove']);
    Route::post('wishlist/', [WishlistController::class, 'index']);

    // Orders
    Route::post('orders/store', [OrderController::class, 'store_order']);
    Route::post('orders/search', [OrderController::class, 'index']);
});
// Auth
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Social Auth
Route::get('/auth/google/redirect', [SocialAuthController::class, 'apiRedirectToGoogle']);
Route::post('/auth/social-login', [SocialAuthController::class, 'apiTokenLogin']);
Route::get('/auth/callback', [SocialAuthController::class, 'apiHandleCallback']);

// 2FA + protected auth routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/2fa/setup', [TwoFactorController::class, 'apiSetup']);
    Route::post('/2fa/setup/verify', [TwoFactorController::class, 'apiVerifySetup']);
    Route::post('/2fa/verify', [TwoFactorController::class, 'apiVerify']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('/generateToken', [AuthController::class, 'refreshtoken']);
    Route::post('/user-profile', [AuthController::class, 'userProfile']);
});
