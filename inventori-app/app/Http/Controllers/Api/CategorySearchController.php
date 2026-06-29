<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategorySearchController extends Controller
{
    /**
     * Search active categories by name for autocomplete.
     *
     * GET /api/categories/search?q={query}
     * Returns max 10 active categories matching the query on `name`.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $categories = Category::query()
            ->where('is_active', true)
            ->when($query !== '', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($categories);
    }
}
