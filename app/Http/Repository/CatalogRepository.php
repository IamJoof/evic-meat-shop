<?php

namespace App\Http\Repository;

use App\Models\Product;
use App\Http\Repository\Contracts\CatalogRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CatalogRepository implements CatalogRepositoryInterface
{
    // This class will handle data retrieval and manipulation for the catalog.
    // You can implement methods to fetch products, categories, and manage relationships here.

    public function getAllProducts(bool $isAvailable)
    {
        return Product::with('category')
            ->where('is_available', $isAvailable)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_path' => $product->image_path,
                    'price_per_kg' => $product->price_per_kg,
                    'is_available' => $product->is_available,
                    'category_name' => $product->category ? $product->category->name : null,
                ];
            });
    }
}