<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerOrderController extends Controller
{
    public function show(Order $order): Response
    {
        $order->load(['items.product', 'payment']);

        return Inertia::render('Storefront/OrderSuccess', [
            'order' => $order,
        ]);
    }

    public function cancel(Order $order): RedirectResponse
    {
        if ($order->status !== OrderStatus::Pending) {
            abort(403, 'Orders that are already being prepared or fulfilled cannot be cancelled online.');
        }

        $order->update(['status' => OrderStatus::Cancelled]);
        $order->payment?->update(['status' => 'cancelled']);

        return back()->with('success', 'Order has been cancelled.');
    }
}
