<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index($currentPage, string $filter = 'all')
    {
        $products = $this->productService->getAllProduct($currentPage, $filter);
        return response()->json([
            'data' => $products
        ], 200);
    }

    public function store(CreateProductRequest $request)
    {
        $validated = $request->validated();
        $product = $this->productService->storeProduct($validated);
        return response()->json([
            'message' => 'Product Successfully created',
            'data' => $product
        ], 201);
    }

    public function update($id, CreateProductRequest $request)
    {
        $validated = $request->validated();
        $product = $this->productService->updateProduct($id, $validated);
        if(!$product) {
            abort(404, 'Product Not Found');
        }
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    
}
