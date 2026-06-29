<?php

namespace App\Imports;

use App\Models\ExportImportLog;
use App\Models\Role;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RoleImport implements ToModel, WithHeadingRow, SkipsOnError
{
    public int $totalRows = 0;
    public int $successRows = 0;
    public int $failedRows = 0;
    private array $errors = [];

    public function __construct(
        private array $columnMapping,
        private string $logId,
    ) {}

    public function model(array $row)
    {
        $this->totalRows++;

        $data = [];
        foreach ($this->columnMapping as $excelCol => $dbField) {
            if ($dbField && isset($row[$excelCol])) {
                $data[$dbField] = $row[$excelCol];
            }
        }

        if (empty($data['name'])) {
            $this->failedRows++;
            $this->errors[] = "Baris {$this->totalRows}: Nama role wajib diisi.";
            ExportImportLog::where('id', $this->logId)->update([
                'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
            ]);
            return null;
        }

        $settings = null;
        if (isset($data['settings'])) {
            $settings = json_decode($data['settings'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->failedRows++;
                $this->errors[] = "Baris {$this->totalRows}: JSON settings tidak valid - " . json_last_error_msg();
                ExportImportLog::where('id', $this->logId)->update([
                    'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                    'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
                ]);
                return null;
            }
        }

        try {
            Role::create([
                'name'       => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
                'is_active'  => ($data['is_active'] ?? 1) == 1,
                'settings'   => $settings,
            ]);
            $this->successRows++;
        } catch (\Exception $e) {
            $this->failedRows++;
            $this->errors[] = "Baris {$this->totalRows}: {$e->getMessage()}";
        }

        ExportImportLog::where('id', $this->logId)->update([
            'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
            'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
        ]);

        return null;
    }

    public function onError(\Throwable $e)
    {
        $this->failedRows++;
        $this->errors[] = "Baris {$this->totalRows}: {$e->getMessage()}";
    }
}
