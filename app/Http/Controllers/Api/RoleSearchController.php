<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleSearchController extends Controller
{
    /**
     * Search roles by name for autocomplete.
     *
     * GET /api/roles/search?q={query}
     * Returns max 10 roles matching the query.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $roles = Role::query()
            ->when($query !== '', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($roles);
    }
}
