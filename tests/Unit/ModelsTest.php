<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;

test('models associate correctly through eloquent relationships', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['price_per_kg' => 350.00]);
    $image = ProductImage::factory()->for($product)->create(['is_primary' => true]);
    $cart = Cart::factory()->create();
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'weight_kg' => 1.5,
        'cutting_notes' => 'Chop into 8 pieces',
    ]);
    $order = Order::factory()->create(['user_id' => null]);
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'price_per_kg' => 350.00,
        'weight_kg' => 1.5,
        'line_total' => 525.00,
    ]);
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    expect($product->category->id)->toBe($category->id);
    expect($product->images)->toHaveCount(1);
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->product->id)->toBe($product->id);
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->order->id)->toBe($order->id);
    expect($order->payment->id)->toBe($payment->id);
});
