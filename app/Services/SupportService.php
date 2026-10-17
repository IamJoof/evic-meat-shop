<?php

namespace App\Services;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportService
{
    /**
     * @return array<int, array{id: string, title: string, summary: string, details: string}>
     */
    public function getFaqs(): array
    {
        return [
            [
                'id' => 'payments',
                'title' => 'Payment Options & Installments',
                'summary' => 'Cash on Delivery (COD), GCash, Maya, and bulk installment terms',
                'details' => 'We accept Cash on Delivery (COD) upon receiving your cuts, GCash, Maya, and BDO/BPI bank transfers. For wholesale restaurant or bulk carcass orders above 20kg, we offer 15-day or 30-day scheduled installment terms upon account approval.',
            ],
            [
                'id' => 'restock',
                'title' => 'Restock & Freshness Cycle',
                'summary' => 'Cut fresh daily at 4:00 AM from NMIS partner farms',
                'details' => 'Our partner farms deliver fresh, NMIS-inspected livestock daily at 4:00 AM. Master butchers prepare prime cuts from 5:00 AM to 7:00 AM. Fresh afternoon cuts arrive by 2:00 PM for dinner delivery.',
            ],
            [
                'id' => 'location',
                'title' => 'Store Location & Pickup',
                'summary' => 'Evic Meat Counter, open 6:00 AM to 7:00 PM daily',
                'details' => 'Our physical counter is located at 124 Fresh Market Boulevard, Meat Section Counter #12-14. Store hours are 6:00 AM to 7:00 PM daily. Self-pickup orders are packed with insulated gel pads ready for counter handover.',
            ],
            [
                'id' => 'orders',
                'title' => 'Order Tracking & Custom Cuts',
                'summary' => 'Track live butchery progress with your Order Reference',
                'details' => 'Enter your EVIC-XXXXXXXX order number in the order tracking tab to see whether your cut is queued, currently on the butcher table, or dispatched.',
            ],
        ];
    }

    public function getActiveConversation(Request $request): ?SupportConversation
    {
        $token = $request->query('token');
        if ($token) {
            $conversation = SupportConversation::with(['messages.user'])->where('access_token', $token)->first();
            if ($conversation) {
                $request->session()->put('support_conversation_id', $conversation->id);

                return $conversation;
            }
        }

        $user = $request->user();
        if ($user) {
            $conversation = SupportConversation::with(['messages.user'])
                ->where('user_id', $user->id)
                ->latest('last_message_at')
                ->first();

            if ($conversation) {
                return $conversation;
            }
        }

        $conversationId = $request->session()->get('support_conversation_id');
        if ($conversationId) {
            $conversation = SupportConversation::with(['messages.user'])->find($conversationId);
            if ($conversation) {
                return $conversation;
            }
        }

        $guestId = $request->session()->get('guest_id');
        if ($guestId) {
            return SupportConversation::with(['messages.user'])
                ->where('guest_id', $guestId)
                ->latest('last_message_at')
                ->first();
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function sendMessage(Request $request, array $data): array
    {
        $user = $request->user();
        $guestId = $user ? null : ($request->session()->get('guest_id') ?? (string) Str::uuid());
        if (! $user && ! $request->session()->has('guest_id')) {
            $request->session()->put('guest_id', $guestId);
        }

        $conversation = $this->getActiveConversation($request);

        if (! $conversation) {
            $conversation = SupportConversation::create([
                'user_id' => $user?->id,
                'guest_id' => $guestId,
                'order_id' => $data['order_id'] ?? null,
                'customer_name' => $user ? $user->name : ($data['customer_name'] ?? 'Customer'),
                'customer_email' => $user ? $user->email : ($data['customer_email'] ?? 'customer@example.com'),
                'customer_phone' => $data['customer_phone'] ?? null,
                'subject' => $data['subject'] ?? 'Meat Counter Inquiry',
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            $request->session()->put('support_conversation_id', $conversation->id);
        } else {
            $conversation->update([
                'status' => 'open',
                'last_message_at' => now(),
            ]);
        }

        /** @var SupportMessage $message */
        $message = $conversation->messages()->create([
            'sender_type' => 'customer',
            'user_id' => $user?->id,
            'message' => $data['message'],
            'is_read' => false,
        ]);

        return [
            'conversation' => $conversation->fresh()->load('messages.user'),
            'message' => $message,
        ];
    }
}
