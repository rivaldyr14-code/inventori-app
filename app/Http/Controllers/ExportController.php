<?php

namespace App\Http\Controllers;

use App\Jobs\ExportJob;
use App\Models\ExportImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private const ALLOWED_ENTITIES = [
        'categories', 'products', 'stock-transactions', 'users', 'roles',
    ];

    private const ENTITY_FIELDS = [
        'categories'         => ['name', 'description', 'is_active', 'metadata', 'created_at'],
        'products'           => ['sku', 'name', 'category', 'price', 'current_stock', 'is_active', 'extra_data', 'description', 'created_at'],
        'stock-transactions' => ['transaction_number', 'product', 'type', 'quantity', 'is_active', 'stock_before', 'stock_after', 'notes', 'created_by', 'created_at'],
        'users'              => ['name', 'email', 'role', 'is_active', 'preferences', 'created_at'],
        'roles'              => ['name', 'guard_name', 'is_active', 'settings', 'created_at'],
    ];

    public function dispatch(Request $request, string $entity)
    {
        if (! in_array($entity, self::ALLOWED_ENTITIES)) {
            return back()->withErrors(['message' => 'Entity not supported.']);
        }

        $validated = $request->validate([
            'selected_fields' => ['required', 'array'],
            'selected_fields.*' => ['string', 'in:' . implode(',', self::ENTITY_FIELDS[$entity])],
        ]);

        $log = ExportImportLog::create([
            'user_id'         => auth()->id(),
            'entity'          => $entity,
            'job_type'        => 'export',
            'status'          => 'pending',
            'selected_fields' => $validated['selected_fields'],
        ]);

        $filters = $request->except(['selected_fields', '_token', '_method']);

        ExportJob::dispatch($log->id, $entity, $filters, $validated['selected_fields'], auth()->id());

        return back()->with([
            'success' => 'Export sedang diproses di background.',
            'log_id'  => $log->id,
        ]);
    }

    public function download(string $logId): StreamedResponse
    {
        $log = ExportImportLog::findOrFail($logId);

        if ($log->user_id !== auth()->id()) {
            abort(403);
        }

        if ($log->status !== 'completed' || ! $log->file_path) {
            abort(404, 'File belum tersedia.');
        }

        if (! Storage::disk('private')->exists($log->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('private')->download($log->file_path, basename($log->file_path));
    }
}
