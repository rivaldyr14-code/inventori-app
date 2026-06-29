<?php

namespace App\Http\Controllers;

use App\Jobs\ImportJob;
use App\Models\ExportImportLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    private const ALLOWED_ENTITIES = [
        'categories', 'products', 'stock-transactions', 'users', 'roles',
    ];

    public function dispatch(Request $request, string $entity): JsonResponse
    {
        if (! in_array($entity, self::ALLOWED_ENTITIES)) {
            return response()->json(['message' => 'Entity not supported.'], 422);
        }

        $validated = $request->validate([
            'column_mapping' => ['nullable', 'array'],
            'file_path'      => ['required', 'string'],
        ]);

        $log = ExportImportLog::create([
            'user_id'         => auth()->id(),
            'entity'          => $entity,
            'job_type'        => 'import',
            'status'          => 'pending',
            'selected_fields' => null,
        ]);

        $columnMapping = $validated['column_mapping'] ?? [];

        ImportJob::dispatch($log->id, $entity, $validated['file_path'], $columnMapping);

        return response()->json([
            'message' => 'Import sedang diproses di background.',
            'log_id'  => $log->id,
        ]);
    }

    public function preview(Request $request, string $entity): JsonResponse
    {
        if (! in_array($entity, self::ALLOWED_ENTITIES)) {
            return response()->json(['message' => 'Entity not supported.'], 422);
        }

        $request->validate([
            'file_path' => ['required', 'string'],
        ]);

        $path = $request->file_path;

        if (! Storage::disk('private')->exists($path)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        try {
            $rows = Excel::toArray(new \App\Imports\ImportPreview, $path, 'private');
            $headers = ! empty($rows[0][0]) ? array_keys($rows[0][0]) : [];

            return response()->json([
                'headers' => $headers,
                'preview' => $rows[0][0] ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membaca file: ' . $e->getMessage()], 422);
        }
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $path = $request->file('file')->store('imports', 'private');

        return response()->json([
            'file_path' => $path,
            'message'   => 'File berhasil diupload.',
        ]);
    }
}
