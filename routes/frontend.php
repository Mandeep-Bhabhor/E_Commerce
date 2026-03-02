<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RazorPayPaymentController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('show')->group(function () {
    Route::get('products', [CartController::class, 'product_listing'])->name('products.list');
    Route::post('cart-list', [CartController::class, 'store'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index')
        ->middleware('auth');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/clear_cart', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/wishlist', [WishlistController::class, 'index'])
        ->name('wishlist.index')
        ->middleware('auth');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])
        ->name('wishlist.remove');

    Route::post('/wishlist/add', [WishlistController::class, 'store'])
        ->name('wishlist.add')
        ->middleware('auth');

    Route::get('/customer-search', [CartController::class, 'liveSearch'])
        ->name('customer.products.search');
    Route::resource('address', AddressController::class);
    Route::post('/checkout/address/select', [AddressController::class, 'selectAddress'])
        ->name('checkout.address.select');
    Route::get('/checkout', [AddressController::class, 'check_index'])
        ->name('checkout.index');
    Route::post('/select_address', [AddressController::class, 'selectAddress'])->name('select.address');
    // Route::post('/order_store', [OrderController::class, 'store_order'])->name('order.store');

});

// Route::get('/new_route',[ProductController::class,'new_route'])->name('new_route');
Route::post('/order/store', [OrderController::class, 'store_order'])->name('order.store');
Route::get('/orders/{id}', [OrderController::class, 'orders'])->name('orders.show');
Route::get('/my-orders', [OrderController::class, 'index'])
    ->name('orders.index')
    ->middleware('auth');
Route::middleware(['auth', 'isAdmin'])->group(function () {
    Route::get('/admin/orders', [OrderController::class, 'adminIndex'])
        ->name('admin.orders.index');
    Route::get('/admin/orders/{id}', [OrderController::class, 'adminShow'])
        ->name('admin.orders.show');
    Route::get('/admin/search', [OrderController::class, 'adminSearch'])
        ->name('admin.orders.search');
    Route::put('/order-items/{id}/status', [OrderController::class, 'updateItemStatus'])
        ->name('order.items.updateStatus');

    Route::put('/order/{id}/status', [OrderController::class, 'updateOrderStatus'])
        ->name('order.updateStatus');
////
Route::get('/admin/returns',[OrderController::class,'returnRequests'])
    ->name('admin.returns');

Route::put('/admin/return/{id}/approve',[OrderController::class,'approveReturn'])
    ->name('admin.return.approve');

Route::put('/admin/return/{id}/reject',[OrderController::class,'rejectReturn'])
    ->name('admin.return.reject');
});
 Route::get('razorpay-payment', [RazorPayPaymentController::class, 'index']);
    Route::post('razorpay-payments', [RazorpayPaymentController::class, 'store'])->name('razorpay.payment.store');
    Route::get('razorpay-payment-form', [RazorpayPaymentController::class, 'paymentForm'])->name('razorpay.payment.form');
    Route::post('razorpay-success', [RazorPayPaymentController::class, 'success'])
        ->name('razorpay.success');
Route::post('/order-item/{id}/return',[OrderController::class,'requestReturn'])
->name('order.item.return');