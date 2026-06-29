<?php

/**
 * Generate test files for upload and import testing.
 * Run: php generate-test-files.php
 */

$dir = __DIR__ . '/storage/app/test-files';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// ─── 1. Generate PDF files (valid: 100KB-500KB) ──────────────────────
function generatePdf(int $targetKB): string
{
    $header = '%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>
endobj
4 0 obj
<< /Length 44 >>
stream
BT
/F1 24 Tf
100 700 Td
(Test Document) Tj
ET
endstream
endobj
5 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
xref
0 6
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000266 00000 n 
0000000360 00000 n 
trailer
<< /Size 6 /Root 1 0 R >>
startxref
441
%%EOF';

    $currentKB = strlen($header) / 1024;
    $paddingLines = (int)(($targetKB - $currentKB) * 1024 / 80);
    $padding = str_repeat("\n% Padding line for reaching target file size in KB for upload validation\n", $paddingLines);

    return $header . $padding;
}

echo "=== PDF Files ===\n";
$pdfSizes = [150, 250, 400];
foreach ($pdfSizes as $kb) {
    $content = generatePdf($kb);
    $filename = "test-{$kb}kb.pdf";
    file_put_contents("{$dir}/{$filename}", $content);
    $actualKB = round(strlen($content) / 1024);
    echo "  {$filename}: {$actualKB} KB (valid for upload)\n";
}

// ─── 2. Generate XLSX files using PHPZip ──────────────────────────────
// Create minimal XLSX manually - Excel file is a ZIP with XML inside

function createXlsx(array $headers, array $rows): string
{
    $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
    $zip = new ZipArchive();
    $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // [Content_Types].xml
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>');

    // _rels/.rels
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

    // xl/workbook.xml
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

    // xl/_rels/workbook.xml.rels
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>');

    // xl/styles.xml
    $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="1"><font><sz val="11"/></font></fonts>
  <fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>
  <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
  <cellStyleXfs count="1"><xf/></cellStyleXfs>
  <cellXfs count="1"><xf/></cellXfs>
</styleSheet>');

    // xl/sharedStrings.xml
    $sharedStrings = [];
    foreach ($headers as $h) $sharedStrings[] = $h;
    foreach ($rows as $row) {
        foreach ($row as $cell) {
            if (!in_array($cell, $sharedStrings)) $sharedStrings[] = $cell;
        }
    }

    $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
    foreach ($sharedStrings as $s) {
        $ssXml .= '<si><t>' . htmlspecialchars($s) . '</t></si>';
    }
    $ssXml .= '</sst>';
    $zip->addFromString('xl/sharedStrings.xml', $ssXml);

    // xl/worksheets/sheet1.xml
    $letters = range('A', 'Z');
    $sheetData = '';
    $colCount = count($headers);

    // Header row
    $sheetData .= '<row>';
    for ($i = 0; $i < $colCount; $i++) {
        $col = $letters[$i];
        $idx = array_search($headers[$i], $sharedStrings);
        $sheetData .= "<c r=\"{$col}1\" t=\"s\"><v>{$idx}</v></c>";
    }
    $sheetData .= '</row>';

    // Data rows
    foreach ($rows as $r => $row) {
        $rowNum = $r + 2;
        $sheetData .= '<row>';
        for ($i = 0; $i < $colCount; $i++) {
            $col = $letters[$i];
            $idx = array_search($row[$i], $sharedStrings);
            $sheetData .= "<c r=\"{$col}{$rowNum}\" t=\"s\"><v>{$idx}</v></c>";
        }
        $sheetData .= '</row>';
    }

    $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>' . $sheetData . '</sheetData>
</worksheet>');

    $zip->close();
    $contents = file_get_contents($tmpFile);
    unlink($tmpFile);
    return $contents;
}

echo "\n=== XLSX Import Files ===\n";

// Categories
$xlsx = createXlsx(
    ['name', 'description', 'is_active', 'metadata'],
    [
        ['Kategori Import 1', str_repeat('Deskripsi kategori import 1. ', 10), '1', '{"type":"import"}'],
        ['Kategori Import 2', str_repeat('Deskripsi kategori import 2. ', 10), '1', '{"type":"import"}'],
        ['Kategori Import 3', str_repeat('Deskripsi kategori import 3. ', 10), '0', '{"type":"import"}'],
    ]
);
file_put_contents("{$dir}/import-categories.xlsx", $xlsx);
echo "  import-categories.xlsx: " . round(strlen($xlsx)/1024) . " KB\n";

// Products
$xlsx = createXlsx(
    ['sku', 'name', 'category_name', 'price', 'current_stock', 'is_active', 'description', 'attributes'],
    [
        ['IMP-001', 'Produk Import 1', 'Kantor Pusat', '150000', '50', '1', 'Deskripsi produk import 1', '{"brand":"ImportBrand"}'],
        ['IMP-002', 'Produk Import 2', 'Gudang', '250000', '100', '1', 'Deskripsi produk import 2', '{"brand":"ImportBrand"}'],
        ['IMP-003', 'Produk Import 3', 'Workshop', '350000', '75', '0', 'Deskripsi produk import 3', '{"brand":"ImportBrand"}'],
    ]
);
file_put_contents("{$dir}/import-products.xlsx", $xlsx);
echo "  import-products.xlsx: " . round(strlen($xlsx)/1024) . " KB\n";

// Stock Transactions
$xlsx = createXlsx(
    ['product_sku', 'type', 'quantity', 'is_active', 'notes'],
    [
        ['IMP-001', 'in', '25', '1', 'Pengadaan awal produk import 1'],
        ['IMP-002', 'in', '50', '1', 'Pengadaan awal produk import 2'],
        ['IMP-003', 'out', '10', '1', 'Distribusi produk import 3'],
    ]
);
file_put_contents("{$dir}/import-stock-transactions.xlsx", $xlsx);
echo "  import-stock-transactions.xlsx: " . round(strlen($xlsx)/1024) . " KB\n";

// Users
$xlsx = createXlsx(
    ['name', 'email', 'password', 'role_name', 'is_active'],
    [
        ['User Import 1', 'userimport1@test.com', 'password123', 'Staff', '1'],
        ['User Import 2', 'userimport2@test.com', 'password123', 'Staff', '1'],
        ['User Import 3', 'userimport3@test.com', 'password123', 'Staff', '0'],
    ]
);
file_put_contents("{$dir}/import-users.xlsx", $xlsx);
echo "  import-users.xlsx: " . round(strlen($xlsx)/1024) . " KB\n";

// Roles
$xlsx = createXlsx(
    ['name', 'guard_name', 'is_active', 'settings'],
    [
        ['Manager', 'web', '1', '{"can_export":true,"can_import":true}'],
        ['Supervisor', 'web', '1', '{"can_export":true,"can_import":false}'],
        ['Operator', 'web', '0', '{"can_export":false,"can_import":false}'],
    ]
);
file_put_contents("{$dir}/import-roles.xlsx", $xlsx);
echo "  import-roles.xlsx: " . round(strlen($xlsx)/1024) . " KB\n";

echo "\n=== Summary ===\n";
echo "PDF files: 3 (150KB, 250KB, 400KB) - all valid for upload\n";
echo "XLSX files: 5 (Categories, Products, StockTransactions, Users, Roles)\n";
echo "All files in: {$dir}\n";
