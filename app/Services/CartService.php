<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CartService
{
    public function getOrCreateCart(?User $user = null, ?string $sessionId = null): Cart
    {
        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
            if (! $cart) {
                $cart = Cart::create(['user_id' => $user->id]);
            }

            return $cart;
        }

        if ($sessionId) {
            $cart = Cart::where('session_id', $sessionId)->first();
            if (! $cart) {
                $cart = Cart::create(['session_id' => $sessionId]);
            }

            return $cart;
        }

        return Cart::create();
    }

    public function getCartFromRequest(Request $request): Cart
    {
        $user = $request->user();
        $sessionId = $request->session()->getId();

        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();
            if (! $cart) {
                $guestCartId = $request->session()->get('cart_id');
                if ($guestCartId) {
                    $cart = Cart::find($guestCartId);
                    if ($cart) {
                        $cart->update(['user_id' => $user->id, 'session_id' => null]);

                        return $cart;
                    }
                }
                $cart = Cart::create(['user_id' => $user->id]);
            }
            $request->session()->put('cart_id', $cart->id);

            return $cart;
        }

        $cartId = $request->session()->get('cart_id');
        $cart = $cartId ? Cart::find($cartId) : null;

        if (! $cart && $sessionId) {
            $cart = Cart::where('session_id', $sessionId)->first();
        }

        if (! $cart) {
            $cart = Cart::create(['session_id' => $sessionId]);
        }

        $request->session()->put('cart_id', $cart->id);

        return $cart;
    }

    public function addItem(Cart $cart, int $productId, float $weightKg, ?string $cuttingNotes = null): CartItem
    {
        if ($weightKg < 0.1 || $weightKg > 100.0) {
            throw new InvalidArgumentException('Weight must be between 0.1 kg and 100 kg.');
        }

        $product = Product::findOrFail($productId);

        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('cutting_notes', $cuttingNotes)
            ->first();

        if ($existingItem) {
            $newWeight = (float) $existingItem->weight_kg + $weightKg;
            $existingItem->update(['weight_kg' => $newWeight]);

            return $existingItem;
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'weight_kg' => $weightKg,
            'cutting_notes' => $cuttingNotes,
        ]);
    }

    public function updateItemWeight(CartItem $item, float $weightKg): CartItem
    {
        if ($weightKg < 0.1 || $weightKg > 100.0) {
            throw new InvalidArgumentException('Weight must be between 0.1 kg and 100 kg.');
        }

        $item->update(['weight_kg' => $weightKg]);

        return $item;
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    public function calculateSubtotal(Cart $cart): float
    {
        $cart->loadMissing('items.product');

        $subtotal = 0.0;
        foreach ($cart->items as $item) {
            $subtotal += round((float) $item->weight_kg * (float) $item->product->price_per_kg, 2);
        }

        return round($subtotal, 2);
    }
}
