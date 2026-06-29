<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        $search  = $request->input('search');
        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = ['name', 'guard_name', 'is_active', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = Role::query()->withCount('users');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Roles/Index', [
            'roles'   => $roles,
            'filters' => [
                'search'  => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create');
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['settings']) && is_string($data['settings']) && $data['settings'] !== '') {
            $decoded = json_decode($data['settings'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['settings' => 'Settings harus berupa JSON yang valid.'])->withInput();
            }
            $data['settings'] = $decoded;
        }
        $role = Role::create($data);

        session()->flash('success', 'Role berhasil ditambahkan.');

        return redirect()->route('roles.show', $role);
    }

    public function show(Role $role): Response
    {
        $audits = $role->audits()->with('user')->latest()->get();
        $usersCount = $role->users()->count();

        return Inertia::render('Roles/Show', [
            'role'       => $role,
            'audits'     => $audits,
            'usersCount' => $usersCount,
        ]);
    }

    public function edit(Role $role): Response
    {
        $audits = $role->audits()->with('user')->latest()->get();

        return Inertia::render('Roles/Edit', [
            'role'   => $role,
            'audits' => $audits,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        if (isset($data['settings']) && is_string($data['settings']) && $data['settings'] !== '') {
            $decoded = json_decode($data['settings'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['settings' => 'Settings harus berupa JSON yang valid.'])->withInput();
            }
            $data['settings'] = $decoded;
        }
        $role->update($data);

        session()->flash('success', 'Role berhasil diperbarui.');

        return redirect()->route('roles.show', $role);
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        session()->flash('success', 'Role berhasil dihapus.');

        return redirect()->route('roles.index');
    }
}
