<?php

namespace App\Http\Controllers;

use App\Jobs\ImportJob;
use App\Models\ExportImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
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
            'column_mapping' => ['nullable', 'array'],
            'file_path'      => ['required', 'string'],
        ]);

        $path = $validated['file_path'];
        if (! Storage::disk('private')->exists($path)) {
            return back()->withErrors(['message' => 'File tidak ditemukan.']);
        }

        try {
            $rows = Excel::toArray(new \App\Imports\ImportPreview, $path, 'private');
            $headers = ! empty($rows[0][0]) ? array_keys($rows[0][0]) : [];
            $allowedFields = self::ENTITY_FIELDS[$entity] ?? [];
            $normalizedHeaders = array_map(fn($h) => strtolower(trim($h)), $headers);
            $normalizedAllowed = array_map(fn($f) => strtolower(trim($f)), $allowedFields);
            $matching = array_intersect($normalizedHeaders, $normalizedAllowed);

            if (empty($matching)) {
                return back()->withErrors([
                    'message' => 'Kolom Excel tidak sesuai dengan entitas "' . $entity . '". Kolom yang diizinkan: ' . implode(', ', $allowedFields) . '.',
                ]);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Gagal memvalidasi file: ' . $e->getMessage()]);
        }

        $log = ExportImportLog::create([
            'user_id'         => auth()->id(),
            'entity'          => $entity,
            'job_type'        => 'import',
            'status'          => 'pending',
            'selected_fields' => null,
        ]);

        $columnMapping = $validated['column_mapping'] ?? [];

        ImportJob::dispatch($log->id, $entity, $validated['file_path'], $columnMapping);

        return back()->with('success', 'Import sedang diproses di background.');
    }

    public function preview(Request $request, string $entity)
    {
        if (! in_array($entity, self::ALLOWED_ENTITIES)) {
            return back()->withErrors(['message' => 'Entity not supported.']);
        }

        $request->validate([
            'file_path' => ['required', 'string'],
        ]);

        $path = $request->file_path;

        if (! Storage::disk('private')->exists($path)) {
            return back()->withErrors(['message' => 'File tidak ditemukan.']);
        }

        try {
            $rows = Excel::toArray(new \App\Imports\ImportPreview, $path, 'private');
            $headers = ! empty($rows[0][0]) ? array_keys($rows[0][0]) : [];

            $allowedFields = self::ENTITY_FIELDS[$entity] ?? [];
            $normalizedHeaders = array_map(fn($h) => strtolower(trim($h)), $headers);
            $normalizedAllowed = array_map(fn($f) => strtolower(trim($f)), $allowedFields);

            $unknown = array_diff($normalizedHeaders, $normalizedAllowed);
            if (! empty($unknown)) {
                return back()->withErrors([
                    'message' => 'Kolom Excel tidak sesuai dengan entitas "' . $entity . '". Kolom yang tidak dikenali: ' . implode(', ', $unknown) . '. Kolom yang diizinkan: ' . implode(', ', $allowedFields) . '.',
                ]);
            }

            if (empty($normalizedHeaders)) {
                return back()->withErrors([
                    'message' => 'Kolom Excel kosong.',
                ]);
            }

            return back()->with([
                'headers' => $headers,
                'preview' => $rows[0][0] ?? [],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Gagal membaca file: ' . $e->getMessage()]);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $path = $request->file('file')->store('imports', 'private');

        return back()->with([
            'file_path' => $path,
            'message'   => 'File berhasil diupload.',
        ]);
    }
}
