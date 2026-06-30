<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPasswordResetController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status', 'pending');

        $query = PasswordResetRequest::with('user')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/PasswordResets/Index', [
            'requests' => $requests,
            'status'   => $status,
        ]);
    }

    public function show(PasswordResetRequest $passwordResetRequest): Response
    {
        $passwordResetRequest->load('user', 'approver');

        return Inertia::render('Admin/PasswordResets/Show', [
            'request' => $passwordResetRequest,
        ]);
    }

    public function approve(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        if ($passwordResetRequest->status !== 'pending') {
            return back()->withErrors(['message' => 'Permintaan ini sudah diproses.']);
        }

        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $passwordResetRequest->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'admin_note'  => $validated['admin_note'] ?? null,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Permintaan reset password disetujui. User dapat mengatur password baru.');
    }

    public function reject(Request $request, PasswordResetRequest $passwordResetRequest)
    {
        if ($passwordResetRequest->status !== 'pending') {
            return back()->withErrors(['message' => 'Permintaan ini sudah diproses.']);
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        $passwordResetRequest->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'admin_note'  => $validated['admin_note'],
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Permintaan reset password ditolak.');
    }
}
