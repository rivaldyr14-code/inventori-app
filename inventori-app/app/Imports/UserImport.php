<?php

namespace App\Imports;

use App\Models\ExportImportLog;
use App\Models\Role;
use App\Models\User;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow, SkipsOnError
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

        if (empty($data['name']) || empty($data['email'])) {
            $this->failedRows++;
            $this->errors[] = "Baris {$this->totalRows}: Nama dan Email wajib diisi.";
            ExportImportLog::where('id', $this->logId)->update([
                'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
            ]);
            return null;
        }

        $roleName = $data['role_name'] ?? 'Staff';
        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            $this->failedRows++;
            $this->errors[] = "Baris {$this->totalRows}: Role '{$roleName}' tidak ditemukan.";
            ExportImportLog::where('id', $this->logId)->update([
                'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
            ]);
            return null;
        }

        unset($data['role_name']);

        if (empty($data['password'])) {
            $data['password'] = 'password';
        }

        $data['password'] = bcrypt($data['password']);
        $data['is_active'] = ($data['is_active'] ?? 1) == 1;

        if (isset($data['preferences'])) {
            $prefs = json_decode($data['preferences'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->failedRows++;
                $this->errors[] = "Baris {$this->totalRows}: JSON preferences tidak valid - " . json_last_error_msg();
                ExportImportLog::where('id', $this->logId)->update([
                    'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                    'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
                ]);
                return null;
            }
            $data['preferences'] = $prefs;
        }

        try {
            $user = User::create($data);
            $user->assignRole($roleName);
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
