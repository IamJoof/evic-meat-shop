<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $query = Order::with(['items.product', 'payment'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'currentStatus' => $status,
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['items.product', 'payment', 'user']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,preparing,shipped,received,cancelled'],
        ]);

        $newStatus = OrderStatus::from($validated['status']);
        $updates = ['status' => $newStatus];

        if ($newStatus === OrderStatus::Received) {
            $updates['actual_delivery_date'] = now();
            if ($order->payment && $order->payment->option === 'COD') {
                $order->payment->update(['status' => 'paid']);
            }
        } elseif ($newStatus === OrderStatus::Cancelled) {
            $order->payment?->update(['status' => 'cancelled']);
        }

        $order->update($updates);

        return back()->with('success', "Order status updated to {$newStatus->label()}.");
    }
}
