<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ){}

    public function index($currentPage, string $filter = 'all') {

        $products = $this->productService->getAllProduct($currentPage, $filter);

        // if($products->empty()){
        //     abort('404','There are no products currently!');
        // }

        return response()->json([
            'data' => $products
        ], 200);
    }
}
