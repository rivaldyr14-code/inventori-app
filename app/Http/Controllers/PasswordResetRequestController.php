<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetRequestController extends Controller
{
    public function index(): Response
    {
        $requests = PasswordResetRequest::where('user_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('PasswordReset/Index', [
            'requests' => $requests,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $hasActive = PasswordResetRequest::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->whereNull('new_password')
            ->exists();

        if ($hasActive) {
            return back()->withErrors(['reason' => 'Anda sudah memiliki permintaan aktif yang belum selesai.']);
        }

        PasswordResetRequest::create([
            'user_id' => auth()->id(),
            'reason'  => $validated['reason'],
            'status'  => 'pending',
        ]);

        return back()->with('success', 'Permintaan reset password berhasil dikirim. Menunggu persetujuan administrator.');
    }

    public function resetPassword(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        if ($passwordResetRequest->user_id !== auth()->id()) {
            abort(403);
        }

        if ($passwordResetRequest->status !== 'approved') {
            return back()->withErrors(['message' => 'Permintaan ini belum disetujui.']);
        }

        $validated = $request->validate([
            'new_password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update(['password' => Hash::make($validated['new_password'])]);

        $passwordResetRequest->update([
            'new_password' => $validated['new_password'],
        ]);

        return back()->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
    }
}
