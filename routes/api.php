<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\TwoFactorController;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    $user = User::all();
    return $user;
})->middleware('auth:sanctum');

// products
Route::get('/products/all', [ProductController::class, 'index'])->middleware('auth:sanctum');
Route::get('/products/{id}', [ProductController::class, 'show'])->middleware('auth:sanctum');
Route::post('/products/create', [ProductController::class, 'store']);
Route::patch('/products/update/{product}', [ProductController::class, 'update']);
Route::patch('/products/delete/{product}', [ProductController::class, 'destroy']);
Route::get('/products/search', [ProductController::class, 'search']);

// /colors
Route::get('/colors', [ColorController::class, 'index']);
Route::get('/color/{id}', [ColorController::class, 'show']);
Route::post('/colors/create', [ColorController::class, 'store']);
Route::patch('/colors/update/{color}', [ColorController::class, 'update']);
Route::patch('/colors/delete/{color}', [ColorController::class, 'destroy']);

// sizes
Route::get('/sizes', [SizeController::class, 'index']);
Route::get('/size/{id}', [SizeController::class, 'show']);
Route::post('/sizes/create', [SizeController::class, 'store']);
Route::patch('/sizes/update/{size}', [SizeController::class, 'update']);
Route::patch('/sizes/delete/{size}', [SizeController::class, 'destroy']);

// /categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/category/{id}', [CategoryController::class, 'show']);
Route::post('/categories/create', [CategoryController::class, 'store']);
Route::patch('/categories/update/{category}', [CategoryController::class, 'update']);
Route::patch('/categories/delete/{category}', [CategoryController::class, 'destroy']);

// Create address for a user
Route::get('customers/{user_id}/address', [AddressController::class, 'index']);
Route::post('customers/{user_id}/address', [AddressController::class, 'store']);
Route::patch('address/{user_id}', [AddressController::class, 'update']);
Route::delete('address/{user_id}', [AddressController::class, 'destroy']);

// Order Store API
Route::post('orders/store', [OrderController::class, 'store_order']);

// Cart Store API
Route::post('carts/store', [CartController::class, 'store']);
Route::post('cart/update/{id}', [CartController::class, 'update']);
Route::delete('cart/delete/{id}', [CartController::class, 'destroy']);
Route::post('carts/show/{id}', [CartController::class, 'show']);
Route::get('carts/{user_id}', [CartController::class, 'index']);

route::post('/carts/truncate',[CartController::class,'cart_truncate']);


////2Fa

// ── Social Auth ──────────────────────────────────────────
// Step 1: Get Auth0 login URL
Route::get('/auth/google/redirect', [SocialAuthController::class, 'apiRedirectToGoogle']);
Route::post('/auth/social-login', [SocialAuthController::class, 'apiTokenLogin']);

// Step 2: Exchange Auth0 code for Laravel session + return status
Route::get('/auth/callback', [SocialAuthController::class, 'apiHandleCallback']);

// ── 2FA ─────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/2fa/setup',          [TwoFactorController::class, 'apiSetup']);
    Route::post('/2fa/setup/verify',  [TwoFactorController::class, 'apiVerifySetup']);
    Route::post('/2fa/verify',        [TwoFactorController::class, 'apiVerify']);
      Route::post('logout',[AuthController::class,'logout']);
      Route::post('/generateToken',[AuthController::class,'refreshtoken']);
});

  Route::post('register',[AuthController::class,'register']);
    Route::post('login',[AuthController::class,'login']);
