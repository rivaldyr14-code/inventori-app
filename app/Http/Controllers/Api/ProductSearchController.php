<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController extends Controller
{
    /**
     * Search active products by name or SKU for autocomplete.
     *
     * GET /api/products/search?q={query}
     * Returns max 10 active products matching the query.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $products = Product::query()
            ->where('is_active', true)
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', "%{$query}%")
                          ->orWhere('sku', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'sku', 'name', 'current_stock']);

        return response()->json($products);
    }
}
