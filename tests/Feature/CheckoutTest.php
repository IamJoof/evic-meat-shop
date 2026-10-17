<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;

test('checkout converts cart items into orders with immutable price snapshots', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Pork Belly',
        'price_per_kg' => 380.00,
        'is_available' => true,
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'weight_kg' => 2.0,
        'cutting_notes' => 'Sliced for samgyupsal',
    ]);

    $payload = [
        'customer_name' => 'Juan Dela Cruz',
        'customer_email' => 'juan@example.com',
        'customer_phone' => '09171234567',
        'customer_address' => '123 Meat St, Brgy Fresh',
        'fulfillment_type' => 'delivery',
        'expected_delivery_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'payment_option' => 'COD',
        'notes' => 'Leave at front gate',
    ];

    $response = $this->withSession(['cart_id' => $cart->id])->post(route('checkout.store'), $payload);

    $response->assertRedirect();
    $order = Order::latest()->first();
    expect($order)->not->toBeNull();
    expect($order->customer_email)->toBe('juan@example.com');
    expect($order->guest_id)->not->toBeNull();
    expect((float) $order->subtotal)->toEqual(760.00);
    expect((float) $order->delivery_fee)->toEqual(50.00);
    expect((float) $order->total_amount)->toEqual(810.00);
    expect($order->status->value)->toBe('pending');

    expect($order->items)->toHaveCount(1);
    expect((float) $order->items->first()->price_per_kg)->toEqual(380.00);
    expect($order->payment)->not->toBeNull();
    expect($order->payment->option)->toBe('COD');
    expect($order->payment->status)->toBe('pending');

    // Verify mutating the original product price does not alter historical order line total
    $product->update(['price_per_kg' => 450.00]);
    expect((float) $order->items->first()->fresh()->price_per_kg)->toEqual(380.00);
});

test('checkout auto-links order to existing registered user when customer_email matches', function () {
    $user = \App\Models\User::factory()->create(['email' => 'existing@example.com']);
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price_per_kg' => 300.00, 'is_available' => true]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'weight_kg' => 1.0,
    ]);

    $payload = [
        'customer_name' => 'Existing Customer',
        'customer_email' => 'existing@example.com',
        'customer_phone' => '09170000000',
        'customer_address' => 'Sample Address',
        'fulfillment_type' => 'pickup',
        'expected_delivery_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'payment_option' => 'COD',
    ];

    $this->withSession(['cart_id' => $cart->id])->post(route('checkout.store'), $payload);

    $order = Order::latest()->first();
    expect($order->user_id)->toBe($user->id);
    expect($order->customer_email)->toBe('existing@example.com');
});


test('checkout fails if an item in the cart is sold out', function () {
    $cart = Cart::factory()->create();
    $unavailable = Product::factory()->create(['is_available' => false]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $unavailable->id,
        'weight_kg' => 1.0,
    ]);

    $response = $this->withSession(['cart_id' => $cart->id])->post(route('checkout.store'), [
        'customer_name' => 'Maria',
        'customer_email' => 'maria@example.com',
        'customer_phone' => '09170000000',
        'customer_address' => 'Address',
        'fulfillment_type' => 'pickup',
        'expected_delivery_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'payment_option' => 'COD',
    ]);

    $response->assertSessionHasErrors('cart');
});

test('checkout fails if expected delivery date is in the past', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['is_available' => true]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'weight_kg' => 1.0,
    ]);

    $response = $this->withSession(['cart_id' => $cart->id])->post(route('checkout.store'), [
        'customer_name' => 'Maria',
        'customer_email' => 'maria@example.com',
        'customer_phone' => '09170000000',
        'customer_address' => 'Address',
        'fulfillment_type' => 'pickup',
        'expected_delivery_date' => now()->subDay()->format('Y-m-d H:i:s'),
        'payment_option' => 'COD',
    ]);

    $response->assertSessionHasErrors('expected_delivery_date');
});

test('checkout fails if expected delivery date is beyond 30 days', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['is_available' => true]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'weight_kg' => 1.0,
    ]);

    $response = $this->withSession(['cart_id' => $cart->id])->post(route('checkout.store'), [
        'customer_name' => 'Maria',
        'customer_email' => 'maria@example.com',
        'customer_phone' => '09170000000',
        'customer_address' => 'Address',
        'fulfillment_type' => 'pickup',
        'expected_delivery_date' => now()->addDays(35)->format('Y-m-d H:i:s'),
        'payment_option' => 'COD',
    ]);

    $response->assertSessionHasErrors('expected_delivery_date');
});
