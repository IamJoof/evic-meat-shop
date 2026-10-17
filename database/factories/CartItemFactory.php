<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'weight_kg' => fake()->randomElement([0.5, 1.0, 1.5, 2.0]),
            'cutting_notes' => fake()->optional()->randomElement([
                'Cut into adobo size',
                'Debone please',
                'Slice thinly for sukiyaki',
                'Grind twice',
            ]),
        ];
    }
}
