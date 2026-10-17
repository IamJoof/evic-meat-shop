<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupportConversation;

test('customers can retrieve storefront faqs', function () {
    $response = $this->getJson(route('support.faqs'));

    $response->assertOk()
        ->assertJsonStructure([
            'faqs' => [
                '*' => ['id', 'title', 'summary', 'details'],
            ],
        ]);

    $response->assertJsonFragment(['id' => 'payments']);
    $response->assertJsonFragment(['id' => 'restock']);
    $response->assertJsonFragment(['id' => 'location']);
});

test('guest customer can submit an inquiry and create a conversation thread', function () {
    $response = $this->postJson(route('support.messages.store'), [
        'customer_name' => 'Maria Clara',
        'customer_email' => 'maria.clara@example.com',
        'customer_phone' => '09181112233',
        'message' => 'Can you cut a 3kg pork belly into samgyupsal strips?',
    ]);

    $response->assertCreated()
        ->assertJsonPath('conversation.customer_name', 'Maria Clara')
        ->assertJsonPath('conversation.customer_email', 'maria.clara@example.com')
        ->assertJsonPath('message.sender_type', 'customer')
        ->assertJsonPath('message.message', 'Can you cut a 3kg pork belly into samgyupsal strips?');

    $conversation = SupportConversation::where('customer_email', 'maria.clara@example.com')->first();
    expect($conversation)->not->toBeNull();
    expect($conversation->messages)->toHaveCount(1);
    expect($conversation->access_token)->not->toBeNull();
});

test('customer can fetch active conversation by session or token', function () {
    $conversation = SupportConversation::factory()->create([
        'customer_name' => 'Pedro Penduko',
        'customer_email' => 'pedro@example.com',
    ]);
    $conversation->messages()->create([
        'sender_type' => 'customer',
        'message' => 'Hello butcher team!',
    ]);

    // Fetch via access token
    $response = $this->getJson(route('support.conversation', ['token' => $conversation->access_token]));

    $response->assertOk()
        ->assertJsonPath('conversation.id', $conversation->id)
        ->assertJsonPath('conversation.customer_name', 'Pedro Penduko')
        ->assertJsonCount(1, 'conversation.messages');

    // Fetch via support_token alias (from email links)
    $emailLinkResponse = $this->getJson(route('support.conversation', ['support_token' => $conversation->access_token]));
    $emailLinkResponse->assertOk()
        ->assertJsonPath('conversation.id', $conversation->id);
});

test('guest can lookup order status using order reference and email', function () {
    $order = Order::factory()->create([
        'order_number' => 'EVIC-20261017-TEST1',
        'customer_email' => 'buyer@example.com',
        'customer_name' => 'Test Buyer',
        'status' => OrderStatus::Preparing,
    ]);

    $product = Product::factory()->create(['name' => 'Pork Belly Liempo', 'price_per_kg' => 380]);
    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => 'Pork Belly Liempo',
        'price_per_kg' => 380,
        'weight_kg' => 2.0,
        'line_total' => 760,
        'cutting_notes' => 'Thin slice',
    ]);

    $response = $this->postJson(route('support.order-lookup'), [
        'order_number' => 'EVIC-20261017-TEST1',
        'customer_email' => 'buyer@example.com',
    ]);

    $response->assertOk()
        ->assertJsonPath('order.order_number', 'EVIC-20261017-TEST1')
        ->assertJsonPath('order.status', 'preparing')
        ->assertJsonCount(1, 'order.items');

    // Mismatched email should be rejected with 404
    $badResponse = $this->postJson(route('support.order-lookup'), [
        'order_number' => 'EVIC-20261017-TEST1',
        'customer_email' => 'wrong@example.com',
    ]);

    $badResponse->assertStatus(404);
});
