<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = 350.00;
        $weight = 1.0;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(2, true),
            'price_per_kg' => $price,
            'weight_kg' => $weight,
            'line_total' => $price * $weight,
            'cutting_notes' => null,
        ];
    }
}
