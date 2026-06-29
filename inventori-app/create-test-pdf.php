<?php

// Create a more realistic PDF that looks like a category document
$content = '%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>
endobj
4 0 obj
<< /Length 320 >>
stream
BT
/F1 18 Tf
50 750 Td
(DOKUMEN KATEGORI PRODUK) Tj
/F2 12 Tf
50 720 Td
(Dikeluarkan oleh: Divisi Inventori) Tj
50 700 Td
(Tanggal: 28 Juni 2026) Tj
/F1 14 Tf
50 660 Td
(Daftar Kategori Aktif:) Tj
/F2 11 Tf
50 635 Td
(1. Elektronik - Perangkat elektronik seperti HP, laptop) Tj
50 618 Td
(2. Furniture - Meja, kursi, lemari kantor) Tj
50 601 Td
(3. ATK - Alat tulis kantor) Tj
50 584 Td
(4. Makanan & Minuman - Snack, kopi untuk pantry) Tj
50 567 Td
(5. ATK - Alat tulis kantor) Tj
50 540 Td
(Dokumen ini dicetak secara otomatis oleh sistem) Tj
ET
endstream
endobj
5 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>
endobj
6 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
xref
0 7
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000266 00000 n 
0000000638 00000 n 
0000000705 00000 n 
trailer
<< /Size 7 /Root 1 0 R >>
startxref
772
%%EOF';

// Pad to ~200KB
$padding = str_repeat("\n% Inventori App - Dokumen Kategori Produk\n", 2500);
$content .= $padding;

file_put_contents(__DIR__ . '/test-upload.pdf', $content);
echo "Created: test-upload.pdf (" . round(strlen($content) / 1024) . " KB)\n";
