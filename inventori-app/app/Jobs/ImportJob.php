<?php

namespace App\Jobs;

use App\Models\ExportImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;

class ImportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public string $logId,
        public string $entity,
        public string $filePath,
        public array $columnMapping,
    ) {}

    public function handle(): void
    {
        $log = ExportImportLog::findOrFail($this->logId);
        $log->update(['status' => 'processing', 'started_at' => now()]);

        try {
            $importClass = $this->getImportClass();
            $import = new $importClass($this->columnMapping, $this->logId);

            Excel::import($import, $this->filePath, 'private');

            $log->refresh();
            $log->update([
                'status'       => 'completed',
                'total_rows'   => $import->totalRows ?? $log->total_rows,
                'success_rows' => $import->successRows ?? $log->success_rows,
                'failed_rows'  => $import->failedRows ?? $log->failed_rows,
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            $log->update([
                'status'    => 'failed',
                'error_log' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            throw $e;
        }
    }

    private function getImportClass(): string
    {
        return match ($this->entity) {
            'categories'          => \App\Imports\CategoryImport::class,
            'products'            => \App\Imports\ProductImport::class,
            'stock-transactions'  => \App\Imports\StockTransactionImport::class,
            'users'               => \App\Imports\UserImport::class,
            'roles'               => \App\Imports\RoleImport::class,
            default               => throw new \InvalidArgumentException("Unknown entity: {$this->entity}"),
        };
    }
}
