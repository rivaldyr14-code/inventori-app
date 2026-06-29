<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search    = $request->input('search');
        $filterRole = $request->input('filter_role');
        $sortBy    = $request->input('sort_by', 'created_at');
        $sortDir   = $request->input('sort_dir', 'desc');

        $allowedSorts = ['name', 'email', 'is_active', 'created_at'];
        if (! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($filterRole !== null && $filterRole !== '') {
            $query->whereHas('roles', function ($q) use ($filterRole) {
                $q->where('name', $filterRole);
            });
        }

        $users = $query
            ->with('roles')
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Users/Index', [
            'users'   => $users,
            'roles'   => $roles,
            'filters' => [
                'search'      => $search,
                'filter_role' => $filterRole,
                'sort_by'     => $sortBy,
                'sort_dir'    => $sortDir,
            ],
        ]);
    }

    public function create(): Response
    {
        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $roleName = $data['role'];
        unset($data['role']);

        if (isset($data['preferences']) && is_string($data['preferences']) && $data['preferences'] !== '') {
            $decoded = json_decode($data['preferences'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['preferences' => 'Preferences harus berupa JSON yang valid.'])->withInput();
            }
            $decoded['created_at'] = now()->toIso8601String();
            $data['preferences'] = $decoded;
        }

        $user = User::create($data);
        $user->assignRole($roleName);

        session()->flash('success', 'User berhasil ditambahkan.');

        return redirect()->route('users.show', $user);
    }

    public function show(User $user): Response
    {
        $user->load('roles');
        $audits = $user->audits()->with('user')->latest()->get();

        return Inertia::render('Users/Show', [
            'user'   => $user,
            'audits' => $audits,
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load('roles');
        $roles = Role::orderBy('name')->get(['id', 'name']);
        $audits = $user->audits()->with('user')->latest()->get();

        return Inertia::render('Users/Edit', [
            'user'   => $user,
            'roles'  => $roles,
            'audits' => $audits,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $roleName = $data['role'] ?? null;
        unset($data['role']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if (isset($data['preferences']) && is_string($data['preferences']) && $data['preferences'] !== '') {
            $decoded = json_decode($data['preferences'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['preferences' => 'Preferences harus berupa JSON yang valid.'])->withInput();
            }
            $decoded['updated_at'] = now()->toIso8601String();
            $data['preferences'] = $decoded;
        }

        $user->update($data);

        if ($roleName) {
            $user->syncRoles([$roleName]);
        }

        session()->flash('success', 'User berhasil diperbarui.');

        return redirect()->route('users.show', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->update(['is_active' => false]);
        $user->delete();

        session()->flash('success', 'User berhasil dihapus.');

        return redirect()->route('users.index');
    }
}
