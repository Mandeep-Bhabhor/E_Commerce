<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RazorPayPaymentController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\WishlistController;
use App\Models\LegalPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Put these outside your auth middleware so guest users can access them!
Route::get('/contact', [ContactController::class, 'contactForm'])->name('contact.contactForm');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');


Route::middleware(['auth'])->group(function () {
    Route::get('/customer/profile', function () {
        return view('customer.profile');
    })->name('customer.profile');

    Route::get('/customer/starred-messages', function () {
        return view('customer.starred');
    })->name('customer.starred');
});

Route::middleware(['auth', 'customer', '2fa'])->group(function () {
    Route::get('/dashboard', function () {
        return view('customer.dashboard');
    })->name('customer.dashboard');
});
// Customer routes — auth + customer role
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::post('cart-list', [CartController::class, 'store'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');

    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/clear_cart', [CartController::class, 'destroy'])->name('cart.destroy');
    // Add customer routes here

    Route::get('/wishlist', [WishlistController::class, 'index'])
        ->name('wishlist.index');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])
        ->name('wishlist.remove');

    Route::post('/wishlist/add', [WishlistController::class, 'store'])
        ->name('wishlist.add');

    Route::get('/customer-search', [CartController::class, 'liveSearch'])
        ->name('customer.products.search');
    Route::resource('address', AddressController::class);
    Route::post('/checkout/address/select', [AddressController::class, 'selectAddress'])
        ->name('checkout.address.select');
    Route::get('/checkout', [AddressController::class, 'check_index'])
        ->name('checkout.index');
    Route::post('/select_address', [AddressController::class, 'selectAddress'])->name('select.address');
    // Route::post('/order_store', [OrderController::class, 'store_order'])->name('order.store');

    // Route::get('/new_route',[ProductController::class,'new_route'])->name('new_route');
    Route::post('/order/store', [OrderController::class, 'store_order'])->name('order.store');
    Route::get('/orders/{id}', [OrderController::class, 'orders'])->name('orders.show');
    Route::get('/my-orders', [OrderController::class, 'index'])
        ->name('orders.index')
        ->middleware('auth');

    Route::get('razorpay-payment', [RazorPayPaymentController::class, 'index']);
    Route::post('razorpay-payments', [RazorpayPaymentController::class, 'store'])->name('razorpay.payment.store');
    Route::get('razorpay-payment-form', [RazorpayPaymentController::class, 'paymentForm'])->name('razorpay.payment.form');
    Route::post('razorpay-success', [RazorPayPaymentController::class, 'success'])
        ->name('razorpay.success');

    // //paypal

    // Put these inside your Route::middleware(['auth'])->group(...)
    Route::get('/checkout/paypal/{order}', [PaypalController::class, 'processPayment'])->name('paypal.process');
    Route::get('/checkout/paypal/success/{order}', [PaypalController::class, 'success'])->name('paypal.success');
    Route::get('/checkout/paypal/cancel/{order}', [PaypalController::class, 'cancel'])->name('paypal.cancel');

    Route::post('/order-item/{id}/return', [OrderController::class, 'requestReturn'])
        ->name('order.item.return');
    // /firbase notification


    Route::get('products', [CartController::class, 'product_listing'])->name('products.list');
    // --- LEGAL PAGES (Dynamic Customer View) ---
    // Note: Put this near the bottom of your routes file so it doesn't accidentally catch other URLs!
    Route::get('/pages/{slug}', function ($slug) {
        $page = LegalPage::where('slug', $slug)->firstOrFail();

        return view('page', compact('page'));
    })->name('customer.page');
});

Route::get('/2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
Route::post('/2fa/setup', [TwoFactorController::class, 'verifySetup'])->name('2fa.setup.verify');

Route::get('/2fa/verify', [TwoFactorController::class, 'verifyPage'])->name('2fa.verify');
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])
    ->middleware('auth')
    ->name('2fa.verify.post');
Route::post('/save-fcm-token', function (Request $request) {
    // Save the token to the currently logged-in admin

    auth()->user()->update(['fcm_token' => $request->token]);

    // dd($request->token);
    return response()->json(['message' => 'Token saved successfully!']);
})->middleware('auth');

Route::get('/inquiry/{product}/create', [InquiryController::class, 'create'])
    ->name('inquiry.create');
Route::post('/inquiry/store', [InquiryController::class, 'store'])
    ->name('inquiry.store');
