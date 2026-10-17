<?php

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/storefront', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/catalog', [StorefrontController::class, 'index'])->name('catalog.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.items.destroy');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::get('/orders/{order:order_number}', [CustomerOrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order:order_number}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
Route::post('/claim-account', [\App\Http\Controllers\ClaimAccountController::class, 'store'])->name('claim-account.store');

Route::prefix('support')->name('support.')->group(function () {
    Route::get('/faqs', [\App\Http\Controllers\SupportController::class, 'faqs'])->name('faqs');
    Route::get('/conversation', [\App\Http\Controllers\SupportController::class, 'conversation'])->name('conversation');
    Route::post('/messages', [\App\Http\Controllers\SupportController::class, 'storeMessage'])->name('messages.store')->middleware('throttle:15,1');
    Route::post('/order-lookup', [\App\Http\Controllers\SupportController::class, 'orderLookup'])->name('order-lookup')->middleware('throttle:15,1');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/slip', [OrderController::class, 'show'])->name('orders.slip');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/support', [\App\Http\Controllers\Admin\SupportController::class, 'index'])->name('support.index');
    Route::post('/support/{conversation}/reply', [\App\Http\Controllers\Admin\SupportController::class, 'reply'])->name('support.reply');
    Route::patch('/support/{conversation}/status', [\App\Http\Controllers\Admin\SupportController::class, 'updateStatus'])->name('support.status');
});

require __DIR__.'/settings.php';
