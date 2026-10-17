<?php

namespace Database\Factories;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportMessageFactory extends Factory
{
    protected $model = SupportMessage::class;

    public function definition(): array
    {
        return [
            'conversation_id' => SupportConversation::factory(),
            'sender_type' => 'customer',
            'user_id' => null,
            'message' => 'Hello, can you slice this into 1-inch adobo cubes?',
            'is_read' => false,
        ];
    }
}
