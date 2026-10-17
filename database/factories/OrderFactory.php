<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'guest_id' => fake()->uuid(),
            'order_number' => 'EVIC-'.date('Ymd').'-'.strtoupper(fake()->unique()->bothify('##??')),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'fulfillment_type' => fake()->randomElement(['delivery', 'pickup']),
            'expected_delivery_date' => now()->addDays(1)->setTime(10, 0),
            'actual_delivery_date' => null,
            'subtotal' => 500.00,
            'delivery_fee' => 50.00,
            'total_amount' => 550.00,
            'status' => OrderStatus::Pending,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
