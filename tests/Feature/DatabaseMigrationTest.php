<?php

use Illuminate\Support\Facades\Schema;

test('all meat shop tables and columns migrate successfully', function () {
    expect(Schema::hasColumns('categories', ['id', 'name', 'slug', 'description']))->toBeTrue();
    expect(Schema::hasColumns('products', ['id', 'category_id', 'name', 'slug', 'price_per_kg', 'is_available', 'description']))->toBeTrue();
    expect(Schema::hasColumns('product_images', ['id', 'product_id', 'image_path', 'is_primary']))->toBeTrue();
    expect(Schema::hasColumns('carts', ['id', 'user_id', 'session_id']))->toBeTrue();
    expect(Schema::hasColumns('cart_items', ['id', 'cart_id', 'product_id', 'weight_kg', 'cutting_notes']))->toBeTrue();
    expect(Schema::hasColumns('orders', [
        'id', 'user_id', 'order_number', 'customer_name', 'customer_phone',
        'customer_address', 'fulfillment_type', 'expected_delivery_date',
        'actual_delivery_date', 'subtotal', 'delivery_fee', 'total_amount', 'status', 'notes',
    ]))->toBeTrue();
    expect(Schema::hasColumns('order_items', [
        'id', 'order_id', 'product_id', 'product_name', 'price_per_kg',
        'weight_kg', 'line_total', 'cutting_notes',
    ]))->toBeTrue();
    expect(Schema::hasColumns('payments', [
        'id', 'order_id', 'amount', 'option', 'status', 'transaction_reference',
    ]))->toBeTrue();
});
