<?php

namespace App\Http\DTO\Product;

class ProductResponseDTO{
    
    public function __construct(
        public ?int $id,
        public string $name,
        public string $image_path,
        public float $price_per_kg,
        public bool $is_available,
        public string $category_name,
        public int $current_page,
        public int $total_page,
        public ?string $filter,
    ){}
}