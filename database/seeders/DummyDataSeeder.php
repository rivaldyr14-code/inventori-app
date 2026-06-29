<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'administrator@inventori.test')->first();
        $staff = User::where('email', 'staff@inventori.test')->first();

        if (! $admin || ! $staff) {
            $this->command->error('Jalankan RolePermissionSeeder terlebih dahulu.');
            return;
        }

        $users = [$admin, $staff];

        // ── Categories ──────────────────────────────────────────
        $categoriesData = [
            ['name' => 'Elektronik',       'description' => 'Perangkat elektronik seperti HP, laptop, aksesoris', 'is_active' => true,  'metadata' => ['type' => 'hardware', 'icon' => 'laptop']],
            ['name' => 'Furniture',        'description' => 'Meja, kursi, lemari, dan perlengkapan kantor',       'is_active' => true,  'metadata' => ['type' => 'furniture', 'icon' => 'chair']],
            ['name' => 'ATK',              'description' => 'Alat tulis kantor: pensil, pulpen, kertas, dll',     'is_active' => true,  'metadata' => ['type' => 'stationery', 'icon' => 'pen']],
            ['name' => 'Makanan & Minuman','description' => 'Snack, kopi, minuman ringan untuk pantry',            'is_active' => true,  'metadata' => ['type' => 'consumable', 'icon' => 'cup']],
            ['name' => 'Cleaning Service', 'description' => 'Perlengkapan kebersihan: sapu, pel, sabun, dll',     'is_active' => true,  'metadata' => ['type' => 'consumable', 'icon' => 'broom']],
            ['name' => 'Software',         'description' => 'Lisensi software dan tools digital',                  'is_active' => false, 'metadata' => ['type' => 'digital', 'icon' => 'code']],
            ['name' => 'Spare Part',       'description' => 'Suku cadang untuk perangkat elektronik',              'is_active' => true,  'metadata' => ['type' => 'hardware', 'icon' => 'gear']],
            ['name' => 'Pakaian Dinas',    'description' => 'Seragam kerja, name tag, aksesoris kantor',           'is_active' => true,  'metadata' => ['type' => 'apparel', 'icon' => 'person']],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[] = Category::create($cat);
        }

        // ── Products ────────────────────────────────────────────
        $productsData = [
            // Elektronik
            ['category' => 0, 'sku' => 'ELG-001', 'name' => 'Laptop ASUS VivoBook 14',   'price' => 8500000,  'stock' => 12, 'attributes' => ['brand' => 'ASUS', 'ram' => '8GB', 'storage' => '512GB SSD']],
            ['category' => 0, 'sku' => 'ELG-002', 'name' => 'Mouse Logitech M331',        'price' => 185000,   'stock' => 45, 'attributes' => ['brand' => 'Logitech', 'type' => 'wireless']],
            ['category' => 0, 'sku' => 'ELG-003', 'name' => 'Keyboard Mechanical Keychron','price' => 750000,  'stock' => 20, 'attributes' => ['brand' => 'Keychron', 'switch' => 'Brown']],
            ['category' => 0, 'sku' => 'ELG-004', 'name' => 'Monitor LG 24 inch',         'price' => 2200000,  'stock' => 8,  'attributes' => ['brand' => 'LG', 'resolution' => '1080p', 'panel' => 'IPS']],
            ['category' => 0, 'sku' => 'ELG-005', 'name' => 'Webcam Logitech C920',       'price' => 950000,   'stock' => 15, 'attributes' => ['brand' => 'Logitech', 'resolution' => '1080p']],

            // Furniture
            ['category' => 1, 'sku' => 'FUR-001', 'name' => 'Meja Kerja Ergonomis',       'price' => 1200000,  'stock' => 25, 'attributes' => ['material' => 'Engineered Wood', 'size' => '120x60cm']],
            ['category' => 1, 'sku' => 'FUR-002', 'name' => 'Kursi Putar Ergonomis',      'price' => 1850000,  'stock' => 18, 'attributes' => ['material' => 'Mesh', 'adjustable' => 'height']],
            ['category' => 1, 'sku' => 'FUR-003', 'name' => 'Lemari Arsip 4 Laci',        'price' => 2500000,  'stock' => 6,  'attributes' => ['material' => 'Steel', 'laci' => 4]],
            ['category' => 1, 'sku' => 'FUR-004', 'name' => 'Rak Buku 3 Tingkat',         'price' => 750000,   'stock' => 10, 'attributes' => ['material' => 'Wood', 'tingkat' => 3]],

            // ATK
            ['category' => 2, 'sku' => 'ATK-001', 'name' => 'Pilot Pen 0.5mm (Box 12)',   'price' => 48000,    'stock' => 100, 'attributes' => ['type' => 'gel pen', 'ukuran' => '0.5mm']],
            ['category' => 2, 'sku' => 'ATK-002', 'name' => 'Kertas A4 70g (Pack 500)',   'price' => 42000,    'stock' => 200, 'attributes' => ['ukuran' => 'A4', 'gramasi' => '70gsm']],
            ['category' => 2, 'sku' => 'ATK-003', 'name' => 'Map L Doff (Isi 100)',       'price' => 35000,    'stock' => 150, 'attributes' => ['warna' => 'Merah, Biru, Hijau', 'isi' => 100]],
            ['category' => 2, 'sku' => 'ATK-004', 'name' => 'Stabilo Boss Highlighter',   'price' => 85000,    'stock' => 80,  'attributes' => ['warna' => 'Kuning, Hijau, Pink']],

            // Makanan & Minuman
            ['category' => 3, 'sku' => 'MNM-001', 'name' => 'Kopi Kapal Api (Box 20)',    'price' => 65000,    'stock' => 50,  'attributes' => ['brand' => 'Kapal Api', 'isi' => '20 sachet']],
            ['category' => 3, 'sku' => 'MNM-002', 'name' => 'Teh Botol Sosro (Karton)',   'price' => 120000,   'stock' => 30,  'attributes' => ['brand' => 'Sosro', 'volume' => '350ml']],
            ['category' => 3, 'sku' => 'MNM-003', 'name' => 'Indomie Goreng (Box 40)',    'price' => 145000,   'stock' => 25,  'attributes' => ['brand' => 'Indomie', 'isi' => '40 bungkus']],
            ['category' => 3, 'sku' => 'MNM-004', 'name' => 'Aqua 600ml (Dus 24)',        'price' => 52000,    'stock' => 40,  'attributes' => ['brand' => 'Aqua', 'volume' => '600ml']],

            // Cleaning Service
            ['category' => 4, 'sku' => 'CLN-001', 'name' => 'Sabun Lantai Wipol (5L)',    'price' => 45000,    'stock' => 35,  'attributes' => ['brand' => 'Wipol', 'volume' => '5L']],
            ['category' => 4, 'sku' => 'CLN-002', 'name' => 'Sapu Lantai Bulu Ayam',      'price' => 35000,    'stock' => 20,  'attributes' => ['material' => 'Bulu Ayam']],
            ['category' => 4, 'sku' => 'CLN-003', 'name' => 'Kain Lap Microfiber (10pc)', 'price' => 55000,    'stock' => 40,  'attributes' => ['material' => 'Microfiber', 'isi' => 10]],

            // Spare Part
            ['category' => 6, 'sku' => 'SPT-001', 'name' => 'RAM DDR4 8GB Kingston',     'price' => 420000,   'stock' => 15,  'attributes' => ['brand' => 'Kingston', 'type' => 'DDR4', 'kapasitas' => '8GB']],
            ['category' => 6, 'sku' => 'SPT-002', 'name' => 'SSD NVMe 512GB Samsung',     'price' => 850000,   'stock' => 10,  'attributes' => ['brand' => 'Samsung', 'type' => 'NVMe', 'kapasitas' => '512GB']],
            ['category' => 6, 'sku' => 'SPT-003', 'name' => 'Baterai Laptop ASUS',        'price' => 380000,   'stock' => 8,   'attributes' => ['brand' => 'ASUS', 'kapasitas' => '42Wh']],
        ];

        $products = [];
        foreach ($productsData as $pdata) {
            $catIndex = $pdata['category'];
            $products[] = Product::create([
                'category_id'   => $categories[$catIndex]->id,
                'sku'           => $pdata['sku'],
                'name'          => $pdata['name'],
                'description'   => 'Deskripsi untuk ' . $pdata['name'],
                'price'         => $pdata['price'],
                'current_stock' => $pdata['stock'],
                'is_active'     => true,
                'extra_data'    => $pdata['attributes'],
            ]);
        }

        // ── Stock Transactions ──────────────────────────────────
        $txnDate = now();
        $txnSeq = 1;

        $txnData = [
            // ELG-001: 2 stock in
            [0, 'in', 10, 'Pengadaan awal laptop ASUS'],
            [0, 'in', 5,  'Restock laptop ASUS dari supplier'],

            // ELG-002: 2 in, 1 out
            [1, 'in',  30, 'Pengadaan mouse Logitech'],
            [1, 'out', 5,  'Distribusi ke divisi HR'],
            [1, 'out', 3,  'Distribusi ke divisi IT'],

            // ELG-003: 1 in, 1 out
            [2, 'in',  15, 'Pengadaan keyboard Keychron'],
            [2, 'out', 2,  'Distribusi ke tim development'],

            // ELG-004: 1 in
            [3, 'in',  8,  'Pengadaan monitor LG'],

            // ELG-005: 1 in, 1 out
            [4, 'in',  10, 'Pengadaan webcam Logitech'],
            [4, 'out', 3,  'Distribusi ke divisi Marketing'],

            // FUR-001: 1 in, 1 out
            [5, 'in',  20, 'Pengadaan meja kerja'],
            [5, 'out', 5,  'Pemasangan di area kerja baru'],

            // FUR-002: 1 in, 1 out
            [6, 'in',  15, 'Pengadaan kursi putar'],
            [6, 'out', 3,  'Pemasangan di ruang meeting'],

            // FUR-003: 1 in
            [7, 'in',  6,  'Pengadaan lemari arsip'],

            // ATK-001: 2 in, 2 out
            [9,  'in',  50, 'Pengadaan pulpen Pilot'],
            [9,  'out', 20, 'Distribusi ke seluruh divisi'],
            [9,  'out', 10, 'Restock ATK lantai 2'],

            // ATK-002: 2 in, 1 out
            [10, 'in',  100, 'Pengadaan kertas A4'],
            [10, 'out', 30,  'Distribusi ke printer lantai 1'],

            // MNM-001: 1 in, 1 out
            [13, 'in',  20, 'Pengadaan kopi Kapal Api'],
            [13, 'out', 5,  'Isi pantry lantai 1'],

            // MNM-003: 1 in, 1 out
            [15, 'in',  10, 'Pengadaan Indomie'],
            [15, 'out', 3,  'Isi pantry lantai 2'],

            // CLN-001: 1 in, 1 out
            [17, 'in',  20, 'Pengadaan sabun lantai'],
            [17, 'out', 5,  'Penggunaan cleaning service'],

            // SPT-001: 1 in, 1 out
            [20, 'in',  10, 'Pengadaan RAM DDR4'],
            [20, 'out', 2,  'Upgrade laptop divisi development'],

            // SPT-002: 1 in
            [21, 'in',  8,  'Pengadaan SSD Samsung'],
        ];

        foreach ($txnData as [$prodIdx, $type, $qty, $notes]) {
            $product = $products[$prodIdx];
            $user = $users[array_rand($users)];

            $stockBefore = $product->current_stock;
            $stockAfter = $type === 'in'
                ? $stockBefore + $qty
                : $stockBefore - $qty;

            StockTransaction::create([
                'product_id'         => $product->id,
                'created_by'         => $user->id,
                'transaction_number' => 'TXN-' . $txnDate->copy()->subDays(rand(0, 30))->format('Ymd') . '-' . str_pad($txnSeq++, 4, '0', STR_PAD_LEFT),
                'type'               => $type,
                'quantity'           => $qty,
                'is_active'          => true,
                'stock_before'       => $stockBefore,
                'stock_after'        => $stockAfter,
                'notes'              => $notes,
                'metadata'           => ['source' => 'seeder', 'batch' => 1],
            ]);
        }

        // ── Sample PDFs ────────────────────────────────────────
        $samplePdfs = [
            ['title' => 'Surat Pengadaan Elektronik',   'body' => 'Dokumen pengadaan perangkat elektronik untuk kebutuhan kantor. Berisi daftar barang, spesifikasi teknis, dan estimasi biaya pengadaan tahun berjalan. Disetujui oleh Kepala Bagian Umum.'],
            ['title' => 'Surat Pengadaan Furniture',     'body' => 'Dokumen pengadaan furniture kantor termasuk meja kerja ergonomis, kursi putar, dan lemari arsip. Sesuai standar ergonomic dan budget yang telah ditetapkan.'],
            ['title' => 'Surat Pengadaan ATK',            'body' => 'Surat permintaan alat tulis kantor untuk periode Q3. Meliputi pulpen, kertas A4, map, dan highlighter untuk kebutuhan seluruh divisi.'],
            ['title' => 'Surat Pengadaan Makanan Pantry', 'body' => 'Daftar pengadaan makanan dan minuman untuk pantry kantor. Termasuk kopi, teh, snack, dan air mineral untuk konsumsi karyawan.'],
            ['title' => 'Surat Pengadaan Cleaning Service','body' => 'Dokumen pengadaan perlengkapan kebersihan. Meliputi sabun lantai, sapu, kain lap, dan bahan kimia pembersih sesuai standar kebersihan kantor.'],
            ['title' => 'Bukti Transaksi Stok Masuk',     'body' => 'Bukti penerimaan barang dari supplier. Berisi detail barang, jumlah, harga satuan, dan total yang diterima gudang. Ditandatangani oleh Warehouse Manager.'],
            ['title' => 'Bukti Transaksi Stok Keluar',    'body' => 'Bukti pengeluaran barang dari gudang untuk distribusi ke divisi. Berisi detail barang, jumlah, tujuan distribusi, dan persetujuan dari Bagian Umum.'],
            ['title' => 'Laporan Inventaris Q3',          'body' => 'Laporan inventaris kuartal ketiga. Berisi rekapitulasi seluruh stok barang, pergerakan stok masuk dan keluar, serta rekomendasi pengadaan selanjutnya.'],
        ];

        $year = now()->format('Y');
        $month = now()->format('m');

        foreach ($samplePdfs as $i => $pdf) {
            $content = $this->generatePdf($pdf['title'], $pdf['body']);
            $filename = 'sample_' . ($i + 1) . '_' . Str::slug($pdf['title']) . '.pdf';
            $path = "attachments/categories/{$year}/{$month}/{$filename}";
            Storage::disk('private')->put($path, $content);

            if (isset($categories[$i])) {
                $categories[$i]->update(['attachment_path' => $path]);
            }
        }

        $productPdfs = [
            ['title' => 'Spesifikasi Laptop ASUS VivoBook', 'body' => 'Spesifikasi lengkap laptop ASUS VivoBook 14: Processor Intel Core i5, RAM 8GB DDR4, Storage 512GB SSD NVMe, Display 14 inch FHD IPS, Battery 42Wh. Garansi resmi 2 tahun.'],
            ['title' => 'Manual Book Mouse Logitech M331',  'body' => 'Panduan penggunaan mouse Logitech M331 Silent. Koneksi wireless 2.4GHz, battery hingga 24 bulan, sensor 1000 DPI, silent click technology.'],
            ['title' => 'Katalog Meja Kerja Ergonomis',     'body' => 'Katalog meja kerja ergonomis dengan material Engineered Wood. Ukuran 120x60cm, tinggi dapat diatur 70-85cm,载重 maks 50kg. Tersedia dalam warna walnut dan white.'],
            ['title' => 'Surat Jalan Kopi Kapal Api',       'body' => 'Surat jalan pengiriman kopi Kapal Api Box 20 sachet. Dikirim dari gudang pusat ke kantor cabang. Diterima dalam kondisi baik tanpa kerusakan.'],
        ];

        foreach ($productPdfs as $i => $pdf) {
            $content = $this->generatePdf($pdf['title'], $pdf['body']);
            $filename = 'sample_' . ($i + 1) . '_' . Str::slug($pdf['title']) . '.pdf';
            $path = "attachments/products/{$year}/{$month}/{$filename}";
            Storage::disk('private')->put($path, $content);

            if (isset($products[$i])) {
                $products[$i]->update(['attachment_path' => $path]);
            }
        }

        $this->command->info('Sample PDFs created and attached.');
        $this->command->info('Dummy data seeded successfully.');
        $this->command->info('  - ' . count($categories) . ' categories');
        $this->command->info('  - ' . count($products) . ' products');
        $this->command->info('  - ' . count($txnData) . ' stock transactions');
    }

    private function generatePdf(string $title, string $body, int $minSize = 102400): string
    {
        $padding = str_repeat(' ', max(0, $minSize - 500));
        $text = wordwrap($body, 80, "\n");
        $lines = explode("\n", $text);
        $stream = "BT\n/F1 14 Tf\n50 750 Td\n({$title}) Tj\n/F1 11 Tf\n";

        $y = 720;
        foreach ($lines as $line) {
            $escaped = addcslashes($line, '()\\');
            $stream .= "50 {$y} Td\n({$escaped}) Tj\n";
            $y -= 16;
            if ($y < 50) break;
        }
        $stream .= "ET\n{$padding}";

        $obj1 = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj";
        $obj2 = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj";
        $obj3 = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj";
        $obj4 = "4 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n{$stream}\nendstream\nendobj";
        $obj5 = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj";

        $objects = "{$obj1}\n{$obj2}\n{$obj3}\n{$obj4}\n{$obj5}\n";
        $xrefOffset = strlen($objects) + 9;

        $xref = "xref\n0 6\n";
        $xref .= "0000000000 65535 f \n";
        $offset = 9;
        foreach (explode("\nendobj", $objects) as $obj) {
            $xref .= str_pad($offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
            $offset += strlen($obj) + 8;
        }

        $pdf = "%PDF-1.4\n{$objects}xref\n{$xref}trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
