<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
        'price_per_kg',
        'is_available',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
