<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

test('admin can create product and toggle daily stock availability', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_admin' => true,
    ]);
    $category = Category::factory()->create();

    $this->actingAs($admin)->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Pork Chops',
        'price_per_kg' => 320.00,
        'description' => 'Bone-in loin chops',
        'is_available' => true,
    ])->assertRedirect();

    $product = Product::where('name', 'Pork Chops')->first();
    expect($product)->not->toBeNull();
    expect((float) $product->price_per_kg)->toEqual(320.00);

    // Toggle out-of-stock
    $this->actingAs($admin)->patch(route('admin.products.toggle-availability', $product->id))
        ->assertRedirect();

    expect($product->fresh()->is_available)->toBeFalse();

    // Toggle back to available
    $this->actingAs($admin)->patch(route('admin.products.toggle-availability', $product->id))
        ->assertRedirect();

    expect($product->fresh()->is_available)->toBeTrue();
});

test('non-admin user is denied access to admin products endpoints', function () {
    $customer = User::factory()->create([
        'role' => 'customer',
        'is_admin' => false,
    ]);

    $this->actingAs($customer)->get(route('admin.products.index'))
        ->assertForbidden();
});
