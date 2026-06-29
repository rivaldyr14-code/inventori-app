<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PendingRegistrationController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::where('is_active', false)
            ->with('roles:name')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/PendingRegistrations', [
            'users' => $users,
        ]);
    }

    public function approve(string $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => true]);

        return back()->with('success', "Akun {$user->name} berhasil disetujui.");
    }

    public function reject(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', "Akun {$user->name} ditolak dan dihapus.");
    }
}
