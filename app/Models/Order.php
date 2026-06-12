<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $casts = [
        'status' => OrderStatus::class,
    ];
}
