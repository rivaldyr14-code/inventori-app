<?php

namespace App\Imports;

use App\Models\ExportImportLog;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StockTransactionImport implements ToModel, WithHeadingRow, SkipsOnError
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

        try {
            DB::transaction(function () use ($data) {
                $productSku = $data['product_sku'] ?? null;
                if (! $productSku) {
                    throw new \Exception("SKU produk wajib diisi.");
                }

                $product = Product::where('sku', $productSku)->first();
                if (! $product) {
                    throw new \Exception("Produk dengan SKU '{$productSku}' tidak ditemukan.");
                }

                $type = $data['type'] ?? 'in';
                $quantity = (int) ($data['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new \Exception("Jumlah harus lebih dari 0.");
                }

                $stockBefore = $product->current_stock;
                $stockAfter = $type === 'in' ? $stockBefore + $quantity : $stockBefore - $quantity;

                if ($stockAfter < 0) {
                    throw new \Exception("Stok tidak mencukupi. Stok saat ini: {$stockBefore}, diminta: {$quantity}.");
                }

                $product->update(['current_stock' => $stockAfter]);

                $txnNumber = 'TXN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

                StockTransaction::create([
                    'product_id'         => $product->id,
                    'created_by'         => auth()->id(),
                    'transaction_number' => $txnNumber,
                    'type'               => $type,
                    'quantity'           => $quantity,
                    'is_active'          => true,
                    'stock_before'       => $stockBefore,
                    'stock_after'        => $stockAfter,
                    'notes'              => $data['notes'] ?? 'Import data',
                ]);

                $this->successRows++;
            });
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
