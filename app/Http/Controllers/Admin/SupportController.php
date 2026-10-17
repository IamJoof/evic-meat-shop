<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Notifications\SupportReplyNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'open');
        $search = $request->query('search');

        $query = SupportConversation::query()
            ->with(['messages.user', 'order'])
            ->latest('last_message_at');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $conversations = $query->paginate(15)->withQueryString();

        $openCount = SupportConversation::where('status', 'open')->count();
        $repliedCount = SupportConversation::where('status', 'replied')->count();
        $resolvedCount = SupportConversation::where('status', 'resolved')->count();

        // Selected conversation for thread detail pane
        $selectedId = $request->query('selected');
        $selectedConversation = null;
        $customerOrders = [];

        if ($selectedId) {
            $selectedConversation = SupportConversation::with(['messages.user', 'order.items'])
                ->find($selectedId);

            if ($selectedConversation) {
                $customerOrders = Order::with('items')
                    ->where('customer_email', $selectedConversation->customer_email)
                    ->latest()
                    ->take(5)
                    ->get();
            }
        } elseif ($conversations->isNotEmpty()) {
            $selectedConversation = $conversations->first();
            $customerOrders = Order::with('items')
                ->where('customer_email', $selectedConversation->customer_email)
                ->latest()
                ->take(5)
                ->get();
        }

        return Inertia::render('Admin/Support/Index', [
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
            'customerOrders' => $customerOrders,
            'currentStatus' => $status,
            'search' => $search,
            'counts' => [
                'open' => $openCount,
                'replied' => $repliedCount,
                'resolved' => $resolvedCount,
            ],
        ]);
    }

    public function reply(Request $request, SupportConversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        /** @var SupportMessage $message */
        $message = $conversation->messages()->create([
            'sender_type' => 'staff',
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'is_read' => true,
        ]);

        $conversation->update([
            'status' => 'replied',
            'last_message_at' => now(),
        ]);

        try {
            Notification::route('mail', $conversation->customer_email)
                ->notify(new SupportReplyNotification($conversation, $message));
        } catch (\Throwable $e) {
            Log::error('Support notification email delivery error: '.$e->getMessage());
        }

        return back()->with('success', 'Reply sent and customer notified via email.');
    }

    public function updateStatus(Request $request, SupportConversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,replied,resolved'],
        ]);

        $conversation->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Conversation marked as '.$validated['status'].'.');
    }
}
