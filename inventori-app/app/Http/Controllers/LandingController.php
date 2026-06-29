<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __invoke(): Response
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with('category:id,name')
            ->select(['id', 'sku', 'name', 'category_id', 'current_stock', 'price'])
            ->orderBy('name')
            ->paginate(10);

        $stats = [
            'total_products' => Product::where('is_active', true)->count(),
        ];

        return Inertia::render('Landing', [
            'products' => $products,
            'stats'    => $stats,
        ]);
    }
}
