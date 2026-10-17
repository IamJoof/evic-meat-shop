<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_token',
        'user_id',
        'guest_id',
        'order_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subject',
        'status',
        'last_message_at',
    ];

    protected $attributes = [
        'status' => 'open',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportConversation $conversation) {
            if (empty($conversation->access_token)) {
                $conversation->access_token = Str::random(40);
            }
            if (empty($conversation->last_message_at)) {
                $conversation->last_message_at = now();
            }
        });
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'conversation_id')->oldest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
