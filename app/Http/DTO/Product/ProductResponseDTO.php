<?php

namespace App\Http\DTO\Product;

class ProductResponseDTO{
    
    public function __construct(
        public string $name,
        public string $image_path,
        public float $price_per_kg,
        public bool $is_available,
        public string $category_name,


        // Optional Params 
        public ?int $id             = null,
        public ?int $current_page   = null,
        public ?int $total_page     = null,
        public ?string $filter      = null,
    ){}
}