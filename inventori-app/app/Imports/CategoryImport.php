<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\ExportImportLog;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Validators\ValidationException;

class CategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
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
            $this->errors[] = "Baris {$this->totalRows}: Nama kategori wajib diisi.";
            ExportImportLog::where('id', $this->logId)->update([
                'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
            ]);
            return null;
        }

        if (isset($data['metadata'])) {
            $meta = json_decode($data['metadata'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->failedRows++;
                $this->errors[] = "Baris {$this->totalRows}: JSON metadata tidak valid - " . json_last_error_msg();
                ExportImportLog::where('id', $this->logId)->update([
                    'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                    'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
                ]);
                return null;
            }
            $data['metadata'] = $meta;
        }

        $data['is_active'] = ($data['is_active'] ?? 1) == 1;

        try {
            Category::create($data);
            $this->successRows++;
        } catch (\Exception $e) {
            $this->failedRows++;
            $this->errors[] = "Baris {$this->totalRows}: {$e->getMessage()}";
        }

        ExportImportLog::where('id', $this->logId)->update([
            'total_rows'   => $this->totalRows,
            'success_rows' => $this->successRows,
            'failed_rows'  => $this->failedRows,
            'error_log'    => implode("\n", $this->errors),
        ]);

        return null;
    }

    public function rules(): array
    {
        return [];
    }

    public function onError(\Throwable $e)
    {
        $this->failedRows++;
        $this->errors[] = "Baris {$this->totalRows}: {$e->getMessage()}";
    }
}
