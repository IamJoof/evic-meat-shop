<?php

namespace App\Http\DTO\Product;

class UpdateProductDTO{
    
    public function __construct(
        public string $name,
        public string $image_path,
        public float $price_per_kg,
        public bool $is_available
    ){}
}
