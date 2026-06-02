<?php

namespace App\Http\Services;

use App\Http\DTO\Product\ProductResponseDTO;
use App\Http\Repository\Contracts\ProductRepositoryInterface;

class ProductService
{

    public function __construct(
        protected ProductRepositoryInterface $productRepo
    ) {
    }

    public function getAllProduct(int $currentPage, string $filter)
    {
        $result = $this->productRepo->getAllProducts($currentPage, $filter);
        $data = $result['data'];
        $totalPages = $result['total_pages'];

        return array_map(function ($item) use ($currentPage, $totalPages, $filter) {
            return new ProductResponseDTO(
                id: $item->id,
                name: $item->name,
                image_path: $item->image_path ?? '',
                price_per_kg: (float) $item->price_per_kg,
                is_available: (bool) $item->is_available,
                category_name: $item->category_name,
                current_page: $currentPage,
                total_page: $totalPages,
                filter: $filter === 'all' ? null : $filter
            );
        }, $data);
    }
}