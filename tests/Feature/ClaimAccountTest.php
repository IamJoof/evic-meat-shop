<?php

use App\Models\Order;
use App\Models\User;

test('guest can claim account from order confirmation and link past orders', function () {
    $guestEmail = 'guest.shopper@example.com';
    $order1 = Order::factory()->create([
        'user_id' => null,
        'customer_email' => $guestEmail,
        'customer_name' => 'Guest Shopper',
    ]);
    $order2 = Order::factory()->create([
        'user_id' => null,
        'customer_email' => $guestEmail,
        'customer_name' => 'Guest Shopper',
    ]);

    $response = $this->post(route('claim-account.store'), [
        'order_number' => $order1->order_number,
        'email' => $guestEmail,
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    $response->assertRedirect(route('orders.show', $order1->order_number));

    $user = User::where('email', $guestEmail)->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Guest Shopper');
    expect(auth()->check())->toBeTrue();
    expect(auth()->id())->toBe($user->id);

    // Verify both past guest orders are linked to the newly registered account
    expect($order1->fresh()->user_id)->toBe($user->id);
    expect($order2->fresh()->user_id)->toBe($user->id);
});

test('claiming account fails if user with that email already exists', function () {
    User::factory()->create(['email' => 'already.registered@example.com']);
    $order = Order::factory()->create([
        'customer_email' => 'already.registered@example.com',
    ]);

    $response = $this->post(route('claim-account.store'), [
        'order_number' => $order->order_number,
        'email' => 'already.registered@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    $response->assertSessionHasErrors('email');
});
