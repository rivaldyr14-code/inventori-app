<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockTransactionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private array $headers;
    private int $rowNumber = 1;

    public function __construct(
        private Builder $query,
        private array $selectedFields,
    ) {
        $fieldLabels = [
            'transaction_number' => 'No. Transaksi',
            'product'            => 'Produk',
            'type'               => 'Tipe',
            'quantity'           => 'Jumlah',
            'stock_before'       => 'Stok Sebelum',
            'stock_after'        => 'Stok Sesudah',
            'is_active'          => 'Status Aktif',
            'notes'              => 'Catatan',
            'created_by'         => 'Dibuat Oleh',
            'created_at'         => 'Tanggal Transaksi',
        ];
        $this->headers = array_map(fn($f) => $fieldLabels[$f] ?? $f, $this->selectedFields);
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return array_merge(['No'], $this->headers);
    }

    public function map($row): array
    {
        $data = [$this->rowNumber++];
        foreach ($this->selectedFields as $field) {
            $data[] = match ($field) {
                'product'    => $row->product?->name ?? '',
                'type'       => $row->type === 'in' ? 'Masuk' : 'Keluar',
                'is_active'  => $row->is_active ? 'Aktif' : 'Tidak Aktif',
                'created_by' => $row->createdBy?->name ?? '',
                'created_at' => $row->created_at?->format('d/m/Y H:i'),
                default      => $row->$field ?? '',
            };
        }
        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => 'solid', 'color' => ['rgb' => 'E65100']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders'   => [
                    'allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CCCCCC']],
                ],
            ],
        ];
    }
}
