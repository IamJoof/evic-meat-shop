<?php

namespace App\Http\DTO\ProductImage;

class ProductImageDTO {

    public function __construct(
        public int $product_id,
        public string $productName,
        public string $image_path,
        public bool $is_primary
    ){}
}