<?php

use App\Models\Category;
use App\Models\Product;

test('storefront lists available products and filters by category', function () {
    $poultry = Category::factory()->create(['name' => 'Poultry', 'slug' => 'poultry']);
    $pork = Category::factory()->create(['name' => 'Pork', 'slug' => 'pork']);

    $chicken = Product::factory()->for($poultry)->create(['name' => 'Whole Dressed Chicken', 'is_available' => true]);
    $ribs = Product::factory()->for($pork)->create(['name' => 'Pork Spareribs', 'is_available' => true]);

    $response = $this->get(route('storefront.index', ['category' => 'poultry']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Storefront/Index')
        ->has('products.data', 1)
        ->where('products.data.0.name', 'Whole Dressed Chicken')
    );
});

test('storefront supports search keyword filtering', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->create(['name' => 'Sirloin Steak', 'is_available' => true]);
    Product::factory()->for($category)->create(['name' => 'Chicken Wings', 'is_available' => true]);

    $response = $this->get(route('storefront.index', ['search' => 'Sirloin']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Storefront/Index')
        ->has('products.data', 1)
        ->where('products.data.0.name', 'Sirloin Steak')
    );
});

test('storefront passes cart and subtotal to index component', function () {
    $product = Product::factory()->create(['price_per_kg' => 350.00, 'is_available' => true]);

    $this->post(route('cart.items.store'), [
        'product_id' => $product->id,
        'weight_kg' => 2.0,
        'cutting_notes' => 'Thinly sliced',
    ]);

    $response = $this->get(route('storefront.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Storefront/Index')
        ->has('cart.items', 1)
        ->where('cart.items.0.product_id', $product->id)
        ->where('subtotal', 700)
    );
});
