<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\CatalogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function __construct(
        private CatalogService $catalogService
    ){}

    public function index()
    {
        $products = $this->catalogService->getAvailableProducts();
        return Inertia::render('Catalog/Index', [
            'products' => $products
        ]);
    }
}
