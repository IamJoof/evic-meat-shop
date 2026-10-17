<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;

test('admin can transition order through fulfillment lifecycle and mark payment paid', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_admin' => true,
    ]);
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);
    $payment = Payment::factory()->create(['order_id' => $order->id, 'status' => 'pending']);

    // Butcher starts preparing
    $this->actingAs($admin)->patch(route('admin.orders.update-status', $order->id), [
        'status' => 'preparing',
    ])->assertRedirect();
    expect($order->fresh()->status->value)->toBe('preparing');

    // Handed over to courier
    $this->actingAs($admin)->patch(route('admin.orders.update-status', $order->id), [
        'status' => 'shipped',
    ])->assertRedirect();
    expect($order->fresh()->status->value)->toBe('shipped');

    // Delivered & paid
    $this->actingAs($admin)->patch(route('admin.orders.update-status', $order->id), [
        'status' => 'received',
    ])->assertRedirect();
    expect($order->fresh()->status->value)->toBe('received');
    expect($order->fresh()->actual_delivery_date)->not->toBeNull();
    expect($payment->fresh()->status)->toBe('paid');
});

test('admin can view butcher preparation slip', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_admin' => true,
    ]);
    $order = Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_name' => 'Chicken Breast',
        'weight_kg' => 2.5,
        'cutting_notes' => 'Skinless and sliced',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.orders.slip', $order->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Orders/Show')
        ->where('order.id', $order->id)
        ->has('order.items', 1)
    );
});
