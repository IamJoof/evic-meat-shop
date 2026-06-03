<?php

namespace App\Http\Services;

use App\Http\DTO\Product\ProductResponseDTO;
use App\Http\Repository\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    public function storeProduct(array $data){
        
        if(isset($data['image_path']) && $data['image_path'] instanceof UploadedFile) {
            $data['image_path'] = $data['image_path']->store('products','public');
        }

        $newProduct = $this->productRepo->storeProduct($data);
            return new ProductResponseDTO(
                name: $newProduct->name,
                image_path: $newProduct->image_path ?? '',
                price_per_kg: (float) $newProduct->price_per_kg,
                is_available: (bool) $newProduct->is_available,
                category_name: $newProduct->category_name,
            );
    }

    public function updateProduct(int $id, array $data) {
        if(isset($data['image_path']) && $data['image_path'] instanceof UploadedFile) {
            $oldFile = Product::where('id', $id)->value('image_path');

            if($oldFile && Storage::disk('public')->exists($oldFile)){
                Storage::disk('public')->delete($oldFile);
            }
            $data['image_path'] = $data['image_path']->store('products', 'public');
        }

        $updatedProduct = $this->productRepo->updateProduct($id, $data);

        return new ProductResponseDTO(
            name: $updatedProduct->name,
            image_path: $updatedProduct->image_path ?? '',
            price_per_kg: (float) $updatedProduct->price_per_kg,
            is_available: (bool) $updatedProduct->is_available,
            category_name: $updatedProduct->category_name
        );
    }

    public function softDelete(int $id){

        $data = [
            'deleted_at' => now()->toDateTimeString()
        ];

        $item = $this->productRepo->updateProduct($id, $data);

        return new ProductResponseDTO(
            name: $item->name,
            price_per_kg: $item->price_per_kg,
            image_path: $item->image_path,
            is_available: $item->is_available,
            category_name: $item->category_name
        );
    }
}