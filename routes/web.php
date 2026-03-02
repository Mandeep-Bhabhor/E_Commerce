<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazorPayPaymentController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth','isAdmin')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('colors', ColorController::class);
    Route::resource('sizes', SizeController::class);
    Route::get('/search-products', [ProductController::class, 'search'])->name('products.search');
    Route::get('/search-sizes', [SizeController::class, 'search'])->name('sizes.search');
    Route::get('/search-colors', [ColorController::class, 'search'])->name('colors.search');
    Route::get('/search-categories', [CategoryController::class, 'search'])->name('categories.search');

   
    Route::resource('taxes', TaxController::class);
    Route::resource('discounts', DiscountController::class);
    // rule the url also cant be same
});

require __DIR__.'/auth.php';
