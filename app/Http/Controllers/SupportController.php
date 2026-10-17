<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\SupportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function __construct(
        protected SupportService $supportService
    ) {}

    public function faqs(): JsonResponse
    {
        return response()->json([
            'faqs' => $this->supportService->getFaqs(),
        ]);
    }

    public function conversation(Request $request): JsonResponse
    {
        $conversation = $this->supportService->getActiveConversation($request);

        return response()->json([
            'conversation' => $conversation,
        ]);
    }

    public function storeMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:1000'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'order_id' => ['nullable', 'exists:orders,id'],
        ]);

        $result = $this->supportService->sendMessage($request, $validated);

        return response()->json($result, 201);
    }

    public function orderLookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'customer_email' => ['required', 'email'],
        ]);

        $order = Order::with('items.product')
            ->where('order_number', $validated['order_number'])
            ->where('customer_email', $validated['customer_email'])
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'No order found matching the provided reference number and email address.',
            ], 404);
        }

        return response()->json([
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'customer_name' => $order->customer_name,
                'expected_delivery_date' => $order->expected_delivery_date?->toIso8601String(),
                'fulfillment_type' => $order->fulfillment_type,
                'total_amount' => $order->total_amount,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'weight_kg' => (float) $item->weight_kg,
                    'price_per_kg' => (float) $item->price_per_kg,
                    'line_total' => (float) $item->line_total,
                    'cutting_notes' => $item->cutting_notes,
                ]),
            ],
        ]);
    }
}
