<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\ExportImportLog;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow, SkipsOnError
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

        if (empty($data['sku']) || empty($data['name'])) {
            $this->failedRows++;
            $this->errors[] = "Baris {$this->totalRows}: SKU dan Nama produk wajib diisi.";
            ExportImportLog::where('id', $this->logId)->update([
                'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
            ]);
            return null;
        }

        if (isset($data['category_name'])) {
            $category = Category::where('name', $data['category_name'])->first();
            if (! $category) {
                $this->failedRows++;
                $this->errors[] = "Baris {$this->totalRows}: Kategori '{$data['category_name']}' tidak ditemukan.";
                ExportImportLog::where('id', $this->logId)->update([
                    'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                    'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
                ]);
                return null;
            }
            $data['category_id'] = $category->id;
            unset($data['category_name']);
        }

        if (isset($data['extra_data'])) {
            $attrs = json_decode($data['extra_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->failedRows++;
                $this->errors[] = "Baris {$this->totalRows}: JSON extra_data tidak valid - " . json_last_error_msg();
                ExportImportLog::where('id', $this->logId)->update([
                    'total_rows' => $this->totalRows, 'success_rows' => $this->successRows,
                    'failed_rows' => $this->failedRows, 'error_log' => implode("\n", $this->errors),
                ]);
                return null;
            }
            $data['extra_data'] = $attrs;
        }

        $data['is_active'] = ($data['is_active'] ?? 1) == 1;

        try {
            Product::create($data);
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
