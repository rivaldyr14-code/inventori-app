<?php

namespace App\Http\Controllers;

use App\Models\ExportImportLog;
use Inertia\Inertia;
use Inertia\Response;

class ExportImportLogController extends Controller
{
    public function index(): Response
    {
        $logs = ExportImportLog::where('user_id', auth()->id())
            ->with('user:id,name')
            ->select([
                'id', 'user_id', 'entity', 'job_type', 'status',
                'selected_fields', 'file_path',
                'total_rows', 'success_rows', 'failed_rows',
                'started_at', 'completed_at',
            ])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('ExportImportLogs/Index', [
            'logs' => $logs,
        ]);
    }
}
