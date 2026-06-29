<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real-time inventory statistics.
     *
     * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5
     */
    public function index(): Response
    {
        // Total active products
        $totalProducts = Product::where('is_active', true)->count();

        // Total active categories
        $totalCategories = Category::where('is_active', true)->count();

        // Total stock transactions created today
        $totalTransactionsToday = StockTransaction::whereDate('created_at', today())->count();

        // Total stock value: sum of (price * current_stock) for active products
        $totalStockValue = Product::where('is_active', true)
            ->selectRaw('SUM(price * current_stock) as total_value')
            ->value('total_value') ?? 0;

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProducts'          => $totalProducts,
                'totalCategories'        => $totalCategories,
                'totalTransactionsToday' => $totalTransactionsToday,
                'totalStockValue'        => (float) $totalStockValue,
            ],
        ]);
    }
}
