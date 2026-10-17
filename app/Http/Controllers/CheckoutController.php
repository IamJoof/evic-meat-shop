<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $cart = $this->cartService->getCartFromRequest($request);
        $cart->load('items.product.images');

        if ($cart->items->isEmpty()) {
            return redirect()->route('storefront.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = $this->cartService->calculateSubtotal($cart);

        return Inertia::render('Storefront/Checkout', [
            'cart' => $cart,
            'subtotal' => $subtotal,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $cart = $this->cartService->getCartFromRequest($request);
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return back()->withErrors(['cart' => 'Your cart is empty.']);
        }

        // Check if any product is sold out
        $hasUnavailableItem = $cart->items->contains(function ($item) {
            return ! $item->product || ! $item->product->is_available;
        });

        if ($hasUnavailableItem) {
            return back()->withErrors(['cart' => 'One or more items in your cart are currently sold out.']);
        }

        $validated = $request->validated();
        $subtotal = $this->cartService->calculateSubtotal($cart);
        $deliveryFee = $validated['fulfillment_type'] === 'delivery' ? 50.00 : 0.00;
        $totalAmount = round($subtotal + $deliveryFee, 2);

        // Link to existing user by email or active session, or maintain guest_id
        $user = $request->user() ?? \App\Models\User::where('email', $validated['customer_email'])->first();
        $guestId = $user ? null : ($request->session()->get('guest_id') ?? (string) Str::uuid());
        if (! $user && ! $request->session()->has('guest_id')) {
            $request->session()->put('guest_id', $guestId);
        }

        $order = DB::transaction(function () use ($user, $guestId, $cart, $validated, $subtotal, $deliveryFee, $totalAmount) {
            $orderNumber = 'EVIC-'.date('Ymd').'-'.strtoupper(Str::random(5));

            $order = Order::create([
                'user_id' => $user?->id,
                'guest_id' => $guestId,
                'order_number' => $orderNumber,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'fulfillment_type' => $validated['fulfillment_type'],
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'status' => OrderStatus::Pending,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $lineTotal = round((float) $item->weight_kg * (float) $item->product->price_per_kg, 2);

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price_per_kg' => $item->product->price_per_kg,
                    'weight_kg' => $item->weight_kg,
                    'line_total' => $lineTotal,
                    'cutting_notes' => $item->cutting_notes,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'amount' => $totalAmount,
                'option' => $validated['payment_option'],
                'status' => 'pending',
            ]);

            $cart->items()->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order->order_number)
            ->with('success', 'Your meat order has been placed successfully!');
    }
}
