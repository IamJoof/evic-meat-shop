<?php

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;

test('support conversation generates access token and relates to messages', function () {
    $conversation = SupportConversation::create([
        'customer_name' => 'Juan Dela Cruz',
        'customer_email' => 'juan@example.com',
        'customer_phone' => '09171234567',
        'subject' => 'Inquiry about Lechon Belly',
    ]);

    expect($conversation->id)->not->toBeNull();
    expect($conversation->access_token)->not->toBeEmpty();
    expect(strlen($conversation->access_token))->toBeGreaterThanOrEqual(32);
    expect($conversation->status)->toBe('open');

    // Add customer message
    $message = $conversation->messages()->create([
        'sender_type' => 'customer',
        'message' => 'Do you have 5kg pork belly available tomorrow morning?',
    ]);

    expect($message->id)->not->toBeNull();
    expect($conversation->fresh()->messages)->toHaveCount(1);
    expect($message->conversation->id)->toBe($conversation->id);
    expect($message->sender_type)->toBe('customer');

    // Add staff response
    $staff = User::factory()->create(['is_admin' => true]);
    $staffMessage = $conversation->messages()->create([
        'sender_type' => 'staff',
        'user_id' => $staff->id,
        'message' => 'Yes! We have fresh pork belly arriving at 4:00 AM.',
    ]);

    expect($staffMessage->user->id)->toBe($staff->id);
    expect($conversation->fresh()->messages)->toHaveCount(2);
});
