<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;

test('cart calculates accurate subtotal for decimal weights', function () {
    $cart = Cart::factory()->create();
    $beef = Product::factory()->create(['price_per_kg' => 400.00, 'is_available' => true]);
    $chicken = Product::factory()->create(['price_per_kg' => 220.00, 'is_available' => true]);

    $service = app(CartService::class);
    $service->addItem($cart, $beef->id, 1.5, 'Thinly sliced for yakiniku'); // 400 * 1.5 = 600.00
    $service->addItem($cart, $chicken->id, 0.75, 'Adobo cuts'); // 220 * 0.75 = 165.00

    expect($service->calculateSubtotal($cart))->toEqual(765.00);
});

test('cart rejects non-positive weights with invalid argument exception', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price_per_kg' => 200.00]);
    $service = app(CartService::class);

    $this->expectException(InvalidArgumentException::class);
    $service->addItem($cart, $product->id, 0.0);
});

test('cart controller manages items via http endpoints', function () {
    $product = Product::factory()->create(['price_per_kg' => 300.00, 'is_available' => true]);

    // Add item to cart
    $response = $this->post(route('cart.items.store'), [
        'product_id' => $product->id,
        'weight_kg' => 1.25,
        'cutting_notes' => 'Deboned please',
    ]);

    $response->assertRedirect();
    $cart = Cart::latest()->first();
    expect($cart->items)->toHaveCount(1);
    $item = $cart->items->first();
    expect((float) $item->weight_kg)->toEqual(1.25);
    expect($item->cutting_notes)->toBe('Deboned please');

    // Update weight
    $this->patch(route('cart.items.update', $item->id), [
        'weight_kg' => 2.50,
    ])->assertRedirect();

    expect((float) $item->fresh()->weight_kg)->toEqual(2.50);

    // Remove item
    $this->delete(route('cart.items.destroy', $item->id))
        ->assertRedirect();

    expect($cart->fresh()->items)->toHaveCount(0);
});

test('cart controller prevents modifying or deleting items belonging to another cart', function () {
    $otherCart = Cart::factory()->create();
    $product = Product::factory()->create(['price_per_kg' => 250.00]);
    $item = CartItem::factory()->create([
        'cart_id' => $otherCart->id,
        'product_id' => $product->id,
        'weight_kg' => 1.0,
    ]);

    // Active session has a different cart
    $activeCart = Cart::factory()->create();

    $this->withSession(['cart_id' => $activeCart->id])
        ->patch(route('cart.items.update', $item->id), ['weight_kg' => 2.0])
        ->assertStatus(403);

    $this->withSession(['cart_id' => $activeCart->id])
        ->delete(route('cart.items.destroy', $item->id))
        ->assertStatus(403);
});
