<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan di sistem.']);
        }

        $latestRequest = PasswordResetRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        if (! $latestRequest) {
            return back()->withErrors(['email' => 'Tidak ada permintaan reset password untuk email ini.']);
        }

        return Inertia::render('Auth/ForgotPassword', [
            'status'  => $latestRequest->status,
            'request' => $latestRequest->only(['id', 'status', 'admin_note', 'resolved_at']),
            'email'   => $validated['email'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'  => ['required', 'email', 'max:255'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan di sistem.']);
        }

        $hasActive = PasswordResetRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('new_password')
            ->exists();

        if ($hasActive) {
            return back()->withErrors(['email' => 'Anda sudah memiliki permintaan aktif yang belum selesai.']);
        }

        PasswordResetRequest::create([
            'user_id' => $user->id,
            'reason'  => $validated['reason'],
            'status'  => 'pending',
        ]);

        return back()->with('success', 'Permintaan reset password berhasil dikirim. Administrator akan meninjau permintaan Anda.');
    }

    public function resetPassword(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        if ($passwordResetRequest->status !== 'approved' || $passwordResetRequest->new_password) {
            return back()->withErrors(['message' => 'Permintaan ini tidak valid atau sudah digunakan.']);
        }

        $validated = $request->validate([
            'new_password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::findOrFail($passwordResetRequest->user_id);
        $user->update(['password' => Hash::make($validated['new_password'])]);

        $passwordResetRequest->update([
            'new_password' => $validated['new_password'],
        ]);

        return back()->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
    }
}
