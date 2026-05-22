<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\LegalPageController;
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

use App\Http\Controllers\Auth\OtpController;

// Put these outside your auth middleware, but maybe group them nicely
Route::get('/verify-otp', [OtpController::class, 'showVerifyPage'])->name('otp.verify.page');
Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->name('otp.verify.submit');

Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('auth');

use App\Http\Controllers\Auth\PhoneLoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\SetingsController;
use App\Http\Controllers\UserController;

Route::middleware('guest')->group(function () {
    Route::get('/login/phone', [PhoneLoginController::class, 'create'])->name('login.phone');
    Route::post('/login/phone', [PhoneLoginController::class, 'store'])->name('login.phone.store');
});
// Admin routes — Breeze auth + admin role
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('admin.dashboard');
    // /import , export product module
    Route::post('/products/import', [ProductController::class, 'import'])
        ->name('products.import');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
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
    Route::post('/setting/store', [SetingsController::class, 'saveSettings'])
        ->name('settings.save');
    Route::get('/setting', [SetingsController::class, 'showSettings'])
        ->name('settings.view');
    //    Route::post('/admin/settings/update-visibility', [SetingsController::class, 'updateVisibility'])
    //     ->name('settings.updateVisibility');

    // Inside your Admin route group:
    Route::get('/contacts', [ContactController::class, 'index'])->name('admin.contacts.index');
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('admin.contacts.show');
    Route::post('/contacts/{contact}/reply', [ContactController::class, 'reply'])->name('admin.contacts.reply');

    // 1. List all pages
    Route::get('/legal-pages', [LegalPageController::class, 'index'])->name('admin.legal-pages.index');

    // 2. Create a new page
    Route::get('/legal-pages/create', [LegalPageController::class, 'create'])->name('admin.legal-pages.create');
    Route::post('/legal-pages/store', [LegalPageController::class, 'store'])->name('admin.legal-pages.store');

    // 3. Edit and Update an existing page
    Route::get('/legal-pages/{legalPage}/edit', [LegalPageController::class, 'edit'])->name('admin.legal-pages.edit');
    Route::put('/legal-pages/{legalPage}', [LegalPageController::class, 'update'])->name('admin.legal-pages.update');

    // 4. Delete a page
    Route::delete('/legal-pages/{legalPage}', [LegalPageController::class, 'destroy'])->name('admin.legal-pages.destroy');


    //Inquiry 
    Route::get('/product-inquiries', [InquiryController::class, 'index'])->name('inquiry.index');

    Route::get('/admin/inquiries/{id}', [InquiryController::class, 'show'])
        ->name('admin.inquiries.show');
    //  Route::get('/product-inquiries',[InquiryController::class,'index'])->name('inquiry.index');

    Route::post('/admin/product-inquiries/{id}/reply', [InquiryController::class, 'reply'])
        ->name('admin.inquiries.reply');

    Route::get('approve_customers', [CustomController::class, 'approve_customers_page'])->name('admin.approve.customers');

    Route::put('/approve-customer/{id}', [CustomController::class, 'approveCustomer'])
        ->name('admin.customer.approve');

    Route::get('/chat', function () {
        return view('admin.chat.index');
    });

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER PROFILE — for admin chat panel
    |--------------------------------------------------------------------------
    | Session-authenticated (Blade admin), returns profile picture URL + basic
    | info from MySQL. The "about" field is fetched client-side from Firestore.
    */
    Route::get(
        '/customer/{userId}/profile',
        [\App\Http\Controllers\Api\AdminProfileController::class, 'profile']
    )->name('admin.customer.profile');
});




require __DIR__ . '/auth.php';
