<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Generate a random meat-sounding name like "Juicy Flank Steak"
            'name' => fake()->words(3, true), 
            
            // Random price between 150.00 and 1500.00
            'price_per_kg' => fake()->randomFloat(2, 150, 1500), 
            
            // 85% chance the product is marked as available
            'is_available' => fake()->boolean(85), 
            
            'image_path' => null, // Leave null for now until we handle file uploads
        ];
    }
}
