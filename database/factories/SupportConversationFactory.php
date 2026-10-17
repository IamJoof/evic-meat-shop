<?php

namespace Database\Factories;

use App\Models\SupportConversation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SupportConversationFactory extends Factory
{
    protected $model = SupportConversation::class;

    public function definition(): array
    {
        return [
            'access_token' => Str::random(40),
            'customer_name' => 'Maria Santos',
            'customer_email' => 'maria.'.Str::random(5).'@example.com',
            'customer_phone' => '0917'.rand(1000000, 9999999),
            'subject' => 'Fresh Meat Inquiry',
            'status' => 'open',
            'last_message_at' => now(),
        ];
    }
}
