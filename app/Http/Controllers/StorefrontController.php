<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): Response
    {
        $categorySlug = $request->query('category');
        $search = $request->query('search');

        $query = Product::query()
            ->with(['category', 'images'])
            ->orderBy('is_available', 'desc')
            ->orderBy('name');

        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::withCount('products')->get();

        $cart = $this->cartService->getCartFromRequest($request);
        $cart->load(['items.product.images']);
        $subtotal = $this->cartService->calculateSubtotal($cart);

        return Inertia::render('Storefront/Index', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $categorySlug,
            'search' => $search,
            'cart' => $cart,
            'subtotal' => $subtotal,
        ]);
    }
}
