<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;

test('customer can view order status timeline', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Preparing,
    ]);
    $product = Product::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);
    Payment::factory()->create(['order_id' => $order->id]);

    $response = $this->get(route('orders.show', $order->order_number));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Storefront/OrderSuccess')
        ->where('order.order_number', $order->order_number)
        ->where('order.status', 'preparing')
    );
});

test('customer can cancel pending order but cannot cancel preparing order', function () {
    $pendingOrder = Order::factory()->create(['status' => OrderStatus::Pending]);
    Payment::factory()->create(['order_id' => $pendingOrder->id, 'status' => 'pending']);

    $preparingOrder = Order::factory()->create(['status' => OrderStatus::Preparing]);
    Payment::factory()->create(['order_id' => $preparingOrder->id, 'status' => 'pending']);

    // Cancel pending order
    $this->post(route('orders.cancel', $pendingOrder->order_number))
        ->assertRedirect();

    expect($pendingOrder->fresh()->status->value)->toBe('cancelled');
    expect($pendingOrder->fresh()->payment->status)->toBe('cancelled');

    // Attempt to cancel preparing order (should be forbidden)
    $this->post(route('orders.cancel', $preparingOrder->order_number))
        ->assertForbidden();

    expect($preparingOrder->fresh()->status->value)->toBe('preparing');
});
