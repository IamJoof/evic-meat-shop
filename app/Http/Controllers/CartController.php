<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): Response
    {
        $cart = $this->cartService->getCartFromRequest($request);
        $cart->load('items.product.images');

        return Inertia::render('Storefront/Cart', [
            'cart' => $cart,
            'subtotal' => $this->cartService->calculateSubtotal($cart),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'weight_kg' => ['required', 'numeric', 'min:0.1', 'max:50'],
            'cutting_notes' => ['nullable', 'string', 'max:255'],
        ]);

        $cart = $this->cartService->getCartFromRequest($request);

        $this->cartService->addItem(
            $cart,
            (int) $validated['product_id'],
            (float) $validated['weight_kg'],
            $validated['cutting_notes'] ?? null
        );

        return back()->with('success', 'Item added to cart.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->cartService->getCartFromRequest($request);
        abort_if($item->cart_id !== $cart->id, 403, 'Unauthorized cart item access.');

        $validated = $request->validate([
            'weight_kg' => ['required', 'numeric', 'min:0.1', 'max:50'],
        ]);

        $this->cartService->updateItemWeight($item, (float) $validated['weight_kg']);

        return back()->with('success', 'Cart item updated.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->cartService->getCartFromRequest($request);
        abort_if($item->cart_id !== $cart->id, 403, 'Unauthorized cart item access.');

        $this->cartService->removeItem($item);

        return back()->with('success', 'Item removed from cart.');
    }
}
