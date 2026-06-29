<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $search         = $request->input('search');
        $filterCategory  = $request->input('filter_category_id');
        $filterActive    = $request->input('filter_is_active');
        $sortBy          = $request->input('sort_by', 'created_at');
        $sortDir         = $request->input('sort_dir', 'desc');

        $allowedSorts = ['sku', 'name', 'price', 'current_stock', 'is_active', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Product::query()->with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($filterCategory !== null && $filterCategory !== '') {
            $query->where('category_id', $filterCategory);
        }

        if ($filterActive !== null && $filterActive !== '') {
            $query->where('is_active', (bool) $filterActive);
        }

        $products = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'categories' => $categories,
            'filters'    => [
                'search'             => $search,
                'filter_category_id' => $filterCategory,
                'filter_is_active'   => $filterActive,
                'sort_by'            => $sortBy,
                'sort_dir'           => $sortDir,
            ],
        ]);
    }

    public function create(): Response
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Products/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Decode attributes if it's a JSON string
        if (isset($data['attributes']) && is_string($data['attributes'])) {
            $data['attributes'] = json_decode($data['attributes'], true);
        }

        if ($request->hasFile('attachment')) {
            $year  = now()->year;
            $month = now()->format('m');
            $data['attachment_path'] = $request->file('attachment')
                ->store("attachments/products/{$year}/{$month}", 'private');
        }

        unset($data['attachment']);

        Product::create($data);

        session()->flash('success', 'Produk berhasil ditambahkan.');

        return redirect()->route('products.index');
    }

    public function show(Product $product): Response
    {
        $product->load('category');
        $audits = $product->audits()->with('user')->latest()->get();
        $transactions = $product->stockTransactions()
            ->with('createdBy')
            ->latest()
            ->paginate(10);

        return Inertia::render('Products/Show', [
            'product'      => $product,
            'audits'       => $audits,
            'transactions' => $transactions,
        ]);
    }

    public function edit(Product $product): Response
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $audits = $product->audits()->with('user')->latest()->get();

        return Inertia::render('Products/Edit', [
            'product'    => $product,
            'categories' => $categories,
            'audits'     => $audits,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        // Decode attributes if it's a JSON string
        if (isset($data['attributes']) && is_string($data['attributes'])) {
            $data['attributes'] = json_decode($data['attributes'], true);
        }

        if ($request->hasFile('attachment')) {
            $year  = now()->year;
            $month = now()->format('m');
            $data['attachment_path'] = $request->file('attachment')
                ->store("attachments/products/{$year}/{$month}", 'private');
        }

        unset($data['attachment']);

        $product->update($data);

        session()->flash('success', 'Produk berhasil diperbarui.');

        return redirect()->route('products.show', $product);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        session()->flash('success', 'Produk berhasil dihapus.');

        return redirect()->route('products.index');
    }
}
