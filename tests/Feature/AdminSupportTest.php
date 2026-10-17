<?php

use App\Models\SupportConversation;
use App\Models\User;
use App\Notifications\SupportReplyNotification;
use Illuminate\Support\Facades\Notification;

test('admin can view support inbox and list conversations', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $regularUser = User::factory()->create(['is_admin' => false]);

    SupportConversation::factory()->count(3)->create(['status' => 'open']);
    SupportConversation::factory()->create(['status' => 'resolved']);

    // Regular users blocked
    $this->actingAs($regularUser)
        ->get(route('admin.support.index'))
        ->assertForbidden();

    // Admin allowed
    $response = $this->actingAs($admin)
        ->get(route('admin.support.index', ['status' => 'all']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Support/Index')
            ->has('conversations.data', 4)
        );
});

test('admin can reply to customer conversation and triggers email notification', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_admin' => true]);
    $conversation = SupportConversation::factory()->create([
        'customer_name' => 'Aling Nena',
        'customer_email' => 'nena@example.com',
        'status' => 'open',
    ]);
    $conversation->messages()->create([
        'sender_type' => 'customer',
        'message' => 'Do you deliver to Barangay San Antonio?',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin.support.reply', $conversation->id), [
            'message' => 'Yes, Aling Nena! We deliver same-day cold-packed to San Antonio.',
        ]);

    $response->assertRedirect();

    $conversation->refresh();
    expect($conversation->status)->toBe('replied');
    expect($conversation->messages)->toHaveCount(2);

    $staffMessage = $conversation->messages()->where('sender_type', 'staff')->first();
    expect($staffMessage->message)->toContain('San Antonio');
    expect($staffMessage->user_id)->toBe($admin->id);

    // Verify Notification sent to customer email
    Notification::assertSentOnDemand(
        SupportReplyNotification::class,
        function ($notification, $channels, $notifiable) use ($conversation) {
            return $notifiable->routes['mail'] === 'nena@example.com'
                && $notification->conversation->id === $conversation->id;
        }
    );
});

test('admin can update conversation status to resolved', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $conversation = SupportConversation::factory()->create(['status' => 'open']);

    $response = $this->actingAs($admin)
        ->patch(route('admin.support.status', $conversation->id), [
            'status' => 'resolved',
        ]);

    $response->assertRedirect();
    expect($conversation->fresh()->status)->toBe('resolved');
});
