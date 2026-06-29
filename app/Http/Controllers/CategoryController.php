<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with search, filter, sort, and pagination.
     */
    public function index(Request $request): Response
    {
        $search        = $request->input('search');
        $filterActive  = $request->input('filter_is_active');
        $sortBy        = $request->input('sort_by', 'created_at');
        $sortDir       = $request->input('sort_dir', 'desc');

        $allowedSorts = ['name', 'description', 'is_active', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Category::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($filterActive !== null && $filterActive !== '') {
            $query->where('is_active', (bool) $filterActive);
        }

        $categories = $query
            ->withCount('products')
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'filters'    => [
                'search'           => $search,
                'filter_is_active' => $filterActive,
                'sort_by'          => $sortBy,
                'sort_dir'         => $sortDir,
            ],
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        return Inertia::render('Categories/Create');
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Decode metadata if it's a JSON string
        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true);
        }

        // Add WIB timestamp from server
        $metadata = $data['metadata'] ?? [];
        $metadata['created_at'] = now()->toIso8601String();
        $data['metadata'] = $metadata;
        if ($request->hasFile('attachment')) {
            $year  = now()->year;
            $month = now()->format('m');
            $data['attachment_path'] = $request->file('attachment')
                ->store("attachments/categories/{$year}/{$month}", 'private');
        }

        unset($data['attachment']);

        Category::create($data);

        session()->flash('success', 'Kategori berhasil ditambahkan.');

        return redirect()->route('categories.index');
    }

    /**
     * Display the specified category with its audit trail.
     */
    public function show(Category $category): Response
    {
        $audits = $category->audits()->with('user')->latest()->get();

        return Inertia::render('Categories/Show', [
            'category' => $category->loadCount('products'),
            'audits'   => $audits,
        ]);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): Response
    {
        $audits = $category->audits()->with('user')->latest()->get();

        return Inertia::render('Categories/Edit', [
            'category' => $category,
            'audits'   => $audits,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        // Decode metadata if it's a JSON string
        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true);
        }

        // Add WIB timestamp from server
        $metadata = $data['metadata'] ?? [];
        $metadata['updated_at'] = now()->toIso8601String();
        $data['metadata'] = $metadata;

        // Handle new file upload if present
        if ($request->hasFile('attachment')) {
            $year  = now()->year;
            $month = now()->format('m');
            $data['attachment_path'] = $request->file('attachment')
                ->store("attachments/categories/{$year}/{$month}", 'private');
        }

        unset($data['attachment']);

        $category->update($data);

        session()->flash('success', 'Kategori berhasil diperbarui.');

        return redirect()->route('categories.show', $category);
    }

    /**
     * Soft-delete the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        session()->flash('success', 'Kategori berhasil dihapus.');

        return redirect()->route('categories.index');
    }
}
