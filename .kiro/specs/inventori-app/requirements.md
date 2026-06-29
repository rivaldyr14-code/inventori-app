# Requirements Document

## Introduction

Aplikasi Manajemen Inventori berbasis web yang dibangun menggunakan Laravel 11, Vue.js 3, Inertia.js, dan Bootstrap 5. Sistem ini mencakup manajemen produk, kategori, dan transaksi stok beserta fitur autentikasi, CRUD dengan relasi, audit trail, soft-deletes, upload file, export/import Excel berbasis queue, dan role-based access control menggunakan Spatie Laravel Permission.

## Glossary

- **System**: Aplikasi Manajemen Inventori (Inventori App)
- **User**: Pengguna yang telah terdaftar dan login ke sistem
- **Administrator**: Peran (role) dengan akses penuh ke seluruh fitur sistem
- **Staff**: Peran (role) dengan akses terbatas ke fitur tertentu
- **Product**: Entitas produk dalam inventori dengan atribut UUID, nama, kategori, stok, dan metadata JSON
- **Category**: Entitas kategori produk yang menjadi referensi master data
- **StockTransaction**: Entitas transaksi stok yang mencatat perubahan jumlah stok produk
- **Audit**: Catatan historis perubahan data pada setiap entitas
- **AuditTrail**: Tampilan riwayat perubahan data pada form detail entitas
- **SoftDelete**: Mekanisme penghapusan logis yang menandai data sebagai terhapus tanpa menghapus dari database
- **Queue**: Antrian pekerjaan Laravel untuk memproses export/import Excel di background
- **Job**: Unit pekerjaan yang diproses oleh Queue
- **Attachment**: File PDF yang diunggah sebagai lampiran pada entitas tertentu
- **UUID**: Universally Unique Identifier sebagai primary key tabel
- **Select2/Autocomplete**: Komponen input dengan fitur pencarian dan autocomplete berbasis data dari database
- **ExportJob**: Job antrian untuk proses export data ke Excel
- **ImportJob**: Job antrian untuk proses import data dari Excel
- **DynamicField**: Kolom yang dapat dipilih secara dinamis oleh user saat proses export/import
- **Auditable**: Konsep bahwa data historis/transaksi yang sudah tersimpan tidak boleh berubah walau data induk diperbarui
- **Router**: Inertia.js router untuk navigasi SPA berbasis server-side rendering

---

## Requirements

### Requirement 1: Landing Page Publik

**User Story:** Sebagai pengunjung, saya ingin melihat landing page publik tanpa perlu login, sehingga saya dapat mengetahui informasi tentang aplikasi sebelum mendaftar atau masuk.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman landing page yang dapat diakses tanpa autentikasi pada route `/`
2. WHEN pengunjung mengakses route `/`, THE System SHALL menampilkan konten landing page berisi nama aplikasi, deskripsi singkat, dan tombol navigasi ke halaman login
3. WHEN pengguna yang sudah login mengakses route `/`, THE System SHALL tetap menampilkan landing page tanpa redirect otomatis
4. THE System SHALL merender landing page menggunakan Vue component `Pages/Landing.vue` melalui Inertia.js
5. THE System SHALL menampilkan landing page dengan layout Bootstrap 5 yang responsif

---

### Requirement 2: Autentikasi Pengguna

**User Story:** Sebagai pengguna terdaftar, saya ingin dapat login dan logout dari sistem, sehingga saya dapat mengakses fitur yang sesuai dengan peran saya.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman login pada route `/login` yang dapat diakses publik
2. WHEN pengguna memasukkan email dan password yang valid lalu menekan tombol login, THE System SHALL mengautentikasi pengguna dan me-redirect ke `/dashboard`
3. WHEN pengguna memasukkan email atau password yang tidak valid, THE System SHALL mencegah autentikasi dan menampilkan pesan kesalahan validasi tanpa mengungkap detail teknis
4. WHEN pengguna yang sudah login mencoba mengakses `/login`, THE System SHALL selalu me-redirect ke `/dashboard`
5. WHEN pengguna menekan tombol logout, THE System SHALL menghapus sesi autentikasi dan me-redirect ke `/`
6. THE System SHALL menggunakan Laravel built-in authentication dengan session-based approach melalui Inertia.js
7. THE System SHALL memproteksi semua route bertanda `auth` menggunakan middleware `auth`
8. IF pengguna yang belum login mengakses route yang dilindungi, THEN THE System SHALL me-redirect ke halaman `/login`

---

### Requirement 3: Dashboard

**User Story:** Sebagai pengguna yang sudah login, saya ingin melihat halaman dashboard dengan ringkasan data inventori, sehingga saya dapat memantau kondisi stok secara cepat.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman dashboard pada route `/dashboard` yang hanya dapat diakses oleh pengguna yang sudah login
2. THE System SHALL menampilkan ringkasan statistik: total produk aktif, total kategori, total transaksi stok hari ini, dan total nilai stok
3. WHEN pengguna mengakses `/dashboard`, THE System SHALL menampilkan data statistik yang diambil secara real-time dari database
4. THE System SHALL menampilkan dashboard menggunakan Vue component `Pages/Dashboard.vue` melalui Inertia.js
5. THE System SHALL memperbolehkan semua role (Administrator dan Staff) mengakses halaman dashboard
6. THE System SHALL menampilkan nama pengguna yang sedang login pada navigation bar

---

### Requirement 4: Manajemen Role

**User Story:** Sebagai pengguna yang sudah login, saya ingin mengelola daftar role dalam sistem, sehingga saya dapat mendefinisikan peran-peran yang tersedia.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman CRUD role management pada route `/roles`; aksesibilitas halaman bergantung pada status login pengguna dan ketersediaan route
2. THE System SHALL memperbolehkan semua role (Administrator dan Staff) yang sudah login mengakses halaman manajemen role
3. WHEN pengguna mengakses `/roles`, THE System SHALL menampilkan daftar semua role yang tersedia dalam tabel dengan kolom: nama role, guard name, jumlah user, tanggal dibuat
4. WHEN pengguna mengisi form dan menekan tombol simpan role baru, THE System SHALL membuat record role baru menggunakan Spatie Permission dan menampilkan pesan sukses
5. WHEN pengguna memperbarui data role yang sudah ada, THE System SHALL memperbarui record role dan menampilkan pesan sukses
6. WHEN pengguna menghapus role, THE System SHALL menghapus record role menggunakan soft-delete dan menampilkan pesan konfirmasi
7. THE System SHALL mengimplementasikan searching dan filtering berdasarkan nama role
8. THE System SHALL mengimplementasikan sorting pada semua kolom tabel
9. THE System SHALL menampilkan audit trail perubahan data role pada halaman detail role

---

### Requirement 5: Manajemen User Account

**User Story:** Sebagai Administrator, saya ingin mengelola akun pengguna termasuk assignment role, sehingga saya dapat mengontrol siapa yang memiliki akses ke sistem dan dengan peran apa.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman CRUD user account pada route `/users` yang hanya dapat diakses oleh pengguna dengan role "Administrator"; aksesibilitas halaman bergantung pada role pengguna yang login
2. IF pengguna dengan role selain "Administrator" mencoba mengakses `/users`, THEN THE System SHALL menampilkan halaman 403 Forbidden
3. WHEN pengguna Administrator mengakses `/users`, THE System SHALL menampilkan daftar semua user dalam tabel dengan kolom: nama, email, role, status aktif, tanggal dibuat
4. WHEN Administrator membuat user baru, THE System SHALL membuat record user dengan field: name, email, password (hashed), role assignment, dan status boolean
5. WHEN Administrator memperbarui data user, THE System SHALL memperbarui record dan jika password dikosongkan maka password lama tetap dipertahankan
6. WHEN Administrator menghapus user, THE System SHALL melakukan soft-delete pada record user dan memperbarui status user menjadi tidak aktif
7. THE System SHALL mengimplementasikan autocomplete/select2 untuk field pemilihan role menggunakan data dari database
8. THE System SHALL mengimplementasikan searching, filtering berdasarkan nama, email, dan role, serta sorting pada semua kolom
9. THE System SHALL menampilkan audit trail perubahan data user pada halaman detail user

---

### Requirement 6: Manajemen Kategori Produk

**User Story:** Sebagai pengguna yang sudah login, saya ingin mengelola kategori produk, sehingga produk dapat diorganisir dengan baik dalam kelompok-kelompok yang relevan.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman CRUD kategori pada route `/categories` yang hanya dapat diakses oleh pengguna yang sudah login
2. WHEN pengguna mengakses `/categories`, THE System SHALL menampilkan daftar kategori dalam tabel dengan kolom: nama, deskripsi, status aktif (boolean), jumlah produk, tanggal dibuat
3. WHEN pengguna membuat kategori baru, THE System SHALL menyimpan record dengan field: `id` (UUID), `name` (string), `description` (text), `is_active` (boolean), `metadata` (JSON), `created_at`, `updated_at`, `deleted_at`
4. WHEN pengguna memperbarui kategori, THE System SHALL memperbarui record dan mencatat perubahan di tabel audit
5. WHEN pengguna menghapus kategori, THE System SHALL melakukan soft-delete pada record kategori
6. THE System SHALL mengimplementasikan searching berdasarkan nama dan deskripsi kategori
7. THE System SHALL mengimplementasikan filtering berdasarkan status aktif
8. THE System SHALL mengimplementasikan sorting pada semua kolom tabel
9. THE System SHALL menampilkan audit trail perubahan data kategori pada halaman detail
10. THE System SHALL mengimplementasikan fitur upload file PDF sebagai attachment kategori dengan ukuran antara 100 KB - 500 KB
11. IF file yang diupload bukan PDF atau ukurannya di luar rentang 100 KB - 500 KB, THEN THE System SHALL menolak upload dan menampilkan pesan kesalahan yang spesifik
12. THE System SHALL menyediakan fitur export data kategori ke Excel dengan pemilihan kolom dinamis
13. THE System SHALL menyediakan fitur import data kategori dari Excel dengan pemilihan kolom dinamis
14. WHEN proses export atau import dipicu, THE System SHALL menjalankan proses tersebut melalui Laravel Queue dan menampilkan status progress kepada pengguna

---

### Requirement 7: Manajemen Produk

**User Story:** Sebagai pengguna yang sudah login, saya ingin mengelola data produk dalam inventori, sehingga saya dapat melacak semua item yang tersedia beserta informasi detailnya.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman CRUD produk pada route `/products`; akses bersifat kondisional — hanya pengguna yang sudah login yang dapat mengakses halaman ini
2. WHEN pengguna mengakses `/products`, THE System SHALL menampilkan daftar produk dalam tabel dengan kolom: SKU, nama, kategori, stok saat ini, harga, status aktif, tanggal dibuat
3. WHEN pengguna membuat produk baru, THE System SHALL menyimpan record dengan field: `id` (UUID), `category_id` (UUID, FK), `sku` (string, unique), `name` (string), `description` (text), `price` (decimal), `current_stock` (integer), `is_active` (boolean), `attributes` (JSON), `created_at`, `updated_at`, `deleted_at`
4. THE System SHALL mengimplementasikan autocomplete/select2 untuk field pemilihan kategori menggunakan data dari database
5. WHEN pengguna memperbarui produk, THE System SHALL memperbarui record dan mencatat perubahan di tabel audit
6. WHEN pengguna menghapus produk, THE System SHALL melakukan soft-delete pada record produk
7. THE System SHALL menampilkan relasi kategori produk pada form dan tabel daftar produk
8. THE System SHALL mengimplementasikan searching berdasarkan SKU dan nama produk
9. THE System SHALL mengimplementasikan filtering berdasarkan kategori dan status aktif
10. THE System SHALL mengimplementasikan sorting pada semua kolom tabel
11. THE System SHALL menampilkan audit trail perubahan data produk pada halaman detail
12. THE System SHALL mengimplementasikan fitur upload file PDF sebagai attachment produk dengan ukuran antara 100 KB - 500 KB
13. IF file yang diupload bukan PDF atau ukurannya di luar rentang 100 KB - 500 KB, THEN THE System SHALL menolak upload dan menampilkan pesan kesalahan yang spesifik
14. THE System SHALL menyediakan fitur export data produk ke Excel dengan pemilihan kolom dinamis
15. THE System SHALL menyediakan fitur import data produk dari Excel dengan pemilihan kolom dinamis
16. WHEN proses export atau import dipicu, THE System SHALL menjalankan proses tersebut melalui Laravel Queue

---

### Requirement 8: Manajemen Transaksi Stok

**User Story:** Sebagai pengguna yang sudah login, saya ingin mencatat transaksi perubahan stok produk, sehingga saya dapat melacak semua pergerakan stok masuk dan keluar.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman CRUD transaksi stok pada route `/stock-transactions`
2. WHEN pengguna mengakses halaman `/stock-transactions`, aksesnya bersifat kondisional — hanya pengguna yang sudah login yang dapat mengakses; kontrol akses ditangani oleh middleware terpisah
2. WHEN pengguna mengakses `/stock-transactions`, THE System SHALL menampilkan daftar transaksi dalam tabel dengan kolom: nomor transaksi, produk, tipe (masuk/keluar), jumlah, stok sebelum, stok sesudah, catatan, tanggal transaksi
3. WHEN pengguna membuat transaksi stok baru, THE System SHALL menyimpan record dengan field: `id` (UUID), `product_id` (UUID, FK), `transaction_number` (string, unique, auto-generated), `type` (enum: `in`/`out`), `quantity` (integer), `stock_before` (integer), `stock_after` (integer), `notes` (text), `metadata` (JSON), `attachment_path` (string, nullable), `created_by` (UUID, FK ke users), `created_at`, `updated_at`, `deleted_at`
4. WHEN transaksi stok baru dibuat dengan tipe `in`, THE System SHALL menambahkan jumlah `quantity` ke `current_stock` produk terkait
5. WHEN transaksi stok baru dibuat dengan tipe `out`, THE System SHALL mengurangi jumlah `quantity` dari `current_stock` produk terkait
6. IF transaksi tipe `out` menyebabkan `current_stock` menjadi negatif, THEN THE System SHALL menolak transaksi dan menampilkan pesan kesalahan
7. THE System SHALL mengimplementasikan konsep Auditable: setelah transaksi stok tersimpan, field `stock_before`, `stock_after`, `quantity`, `type`, dan `product_id` TIDAK BOLEH diubah
8. THE System SHALL mengimplementasikan autocomplete/select2 untuk field pemilihan produk menggunakan data dari database
9. THE System SHALL menampilkan relasi produk pada form dan tabel daftar transaksi
10. THE System SHALL mengimplementasikan searching berdasarkan nomor transaksi dan nama produk
11. THE System SHALL mengimplementasikan filtering berdasarkan tipe transaksi, produk, dan rentang tanggal
12. THE System SHALL mengimplementasikan sorting pada semua kolom tabel
13. THE System SHALL menampilkan audit trail perubahan data transaksi pada halaman detail
14. THE System SHALL mengimplementasikan fitur upload file PDF sebagai attachment transaksi dengan ukuran antara 100 KB - 500 KB
15. IF file yang diupload bukan PDF atau ukurannya di luar rentang 100 KB - 500 KB, THEN THE System SHALL menolak upload dan menampilkan pesan kesalahan yang spesifik
16. THE System SHALL menyediakan fitur export data transaksi ke Excel dengan pemilihan kolom dinamis
17. THE System SHALL menyediakan fitur import data transaksi dari Excel dengan pemilihan kolom dinamis
18. WHEN proses export atau import dipicu, THE System SHALL menjalankan proses tersebut melalui Laravel Queue

---

### Requirement 9: Audit Trail

**User Story:** Sebagai pengguna yang sudah login, saya ingin melihat riwayat perubahan data pada setiap entitas, sehingga saya dapat melacak siapa mengubah apa dan kapan.

#### Acceptance Criteria

1. THE System SHALL merekam semua operasi create, update, dan delete pada entitas: Category, Product, StockTransaction, User, dan Role menggunakan `owen-it/laravel-auditing`
2. WHEN data entitas berubah, THE System SHALL menyimpan record audit; IF sistem audit gagal saat menyimpan StockTransaction, THEN THE System SHALL melakukan rollback transaksi keseluruhan
3. WHEN pengguna mengakses halaman detail sebuah entitas, THE System SHALL menampilkan daftar audit trail yang terkait dengan entitas tersebut
4. THE System SHALL menampilkan audit trail dalam format tabel dengan kolom: event, user, perubahan (old vs new), IP address, waktu
5. THE System SHALL mengimplementasikan konsep Auditable untuk StockTransaction: WHEN StockTransaction sudah tersimpan, THE System SHALL TIDAK merekam perubahan pada field `stock_before`, `stock_after`, `quantity`, `type`, `product_id` karena field tersebut tidak boleh diubah
6. THE System SHALL menampilkan audit trail pada setiap halaman detail Category, Product, StockTransaction, User, dan Role

---

### Requirement 10: Upload File PDF

**User Story:** Sebagai pengguna yang sudah login, saya ingin mengunggah file PDF sebagai lampiran pada entitas Category, Product, dan StockTransaction, sehingga saya dapat menyimpan dokumen pendukung.

#### Acceptance Criteria

1. THE System SHALL menyediakan input file upload pada form Category, Product, dan StockTransaction
2. WHEN pengguna memilih file untuk diupload, THE System SHALL memvalidasi bahwa file bertipe PDF (MIME type: `application/pdf`)
3. WHEN pengguna memilih file untuk diupload, THE System SHALL memvalidasi bahwa ukuran file antara 100 KB (102.400 bytes) dan 500 KB (512.000 bytes)
4. IF file tidak bertipe PDF, THEN THE System SHALL menolak upload dan menampilkan pesan: "File harus berupa PDF"
5. IF ukuran file kurang dari 100 KB, THEN THE System SHALL menolak upload dan menampilkan pesan: "Ukuran file minimum adalah 100 KB"
6. IF ukuran file lebih dari 500 KB, THEN THE System SHALL menolak upload dan menampilkan pesan: "Ukuran file maksimum adalah 500 KB"
7. WHEN file lolos validasi, THE System SHALL menyimpan file di direktori `storage/app/private/attachments/{entity_type}/{year}/{month}/`
8. THE System SHALL menyimpan path relatif file di field `attachment_path` pada record entitas terkait
9. THE System SHALL menampilkan link download untuk file attachment hanya ketika file berhasil diunggah dan tersimpan; THE System SHALL TIDAK menampilkan link download untuk entitas yang file uploadnya gagal atau ditolak validasi
10. THE System SHALL menyediakan route download yang terautentikasi untuk mengakses file attachment

---

### Requirement 11: Select2 / Autocomplete

**User Story:** Sebagai pengguna, saya ingin menggunakan input autocomplete saat memilih referensi seperti kategori atau produk, sehingga proses pengisian form menjadi lebih cepat dan akurat.

#### Acceptance Criteria

1. THE System SHALL mengimplementasikan komponen autocomplete menggunakan `Tom Select` pada semua field yang datasource-nya dari database
2. WHEN pengguna mengetikkan teks pada field autocomplete, THE System SHALL mengirimkan request ke endpoint API dan menampilkan hasil yang sesuai dalam daftar dropdown
3. THE System SHALL menyediakan API endpoint `GET /api/categories/search?q={query}` yang mengembalikan daftar kategori aktif yang cocok dengan query
4. THE System SHALL menyediakan API endpoint `GET /api/products/search?q={query}` yang mengembalikan daftar produk aktif yang cocok dengan query
5. THE System SHALL menyediakan API endpoint `GET /api/roles/search?q={query}` yang mengembalikan daftar role yang cocok dengan query
6. WHEN pengguna mengakses autocomplete dan hasil pencarian kosong setelah pencarian aktif dilakukan, THE System SHALL menampilkan pesan "Tidak ada data ditemukan" dalam dropdown; pesan ini TIDAK ditampilkan sebelum pengguna melakukan pencarian atau saat terjadi error teknis
7. THE System SHALL membatasi hasil autocomplete maksimum 10 item per request untuk performa optimal

---

### Requirement 12: Searching, Filtering, dan Sorting

**User Story:** Sebagai pengguna, saya ingin dapat mencari, memfilter, dan mengurutkan data dalam setiap tabel daftar, sehingga saya dapat menemukan data yang dibutuhkan dengan cepat.

#### Acceptance Criteria

1. THE System SHALL mengimplementasikan fitur pencarian teks bebas pada semua halaman daftar (Categories, Products, StockTransactions, Users, Roles)
2. WHEN pengguna mengetikkan teks di input pencarian, THE System SHALL memfilter data yang ditampilkan berdasarkan kolom yang relevan
3. THE System SHALL mengimplementasikan filter berdasarkan field tertentu pada setiap halaman daftar sesuai spesifikasi per-requirement
4. THE System SHALL mengimplementasikan sorting dengan klik pada header kolom tabel; klik pertama SELALU menghasilkan urutan ascending, klik kedua menghasilkan descending; namun WHEN halaman diakses melalui URL yang sudah mengandung parameter sort descending (misalnya dari bookmark), THE System SHALL menerapkan sorting sesuai parameter URL tersebut
5. THE System SHALL mempertahankan state pencarian, filter, dan sorting pada URL query string agar bisa di-bookmark atau di-share
6. THE System SHALL mengimplementasikan pagination dengan jumlah item per halaman yang dapat dikonfigurasi (default: 15 item per halaman)
7. WHEN filter atau pencarian diterapkan, THE System SHALL mereset halaman ke halaman pertama

---

### Requirement 13: Export Excel dengan Dynamic Fields

**User Story:** Sebagai pengguna, saya ingin mengekspor data ke file Excel dengan memilih kolom yang diinginkan, sehingga saya mendapatkan laporan yang sesuai kebutuhan tanpa kolom yang tidak diperlukan.

#### Acceptance Criteria

1. THE System SHALL menyediakan tombol "Export Excel" pada halaman daftar semua entitas (Categories, Products, StockTransactions, Users, Roles)
2. WHEN pengguna menekan tombol Export, THE System SHALL menampilkan modal/dialog pemilihan kolom (dynamic fields)
3. WHEN pengguna memilih kolom dan mengkonfirmasi export, THE System SHALL mendispatch ExportJob ke Laravel Queue dan menampilkan notifikasi bahwa proses sedang berjalan di background
4. THE ExportJob SHALL memproses data sesuai filter yang aktif dan kolom yang dipilih menggunakan `maatwebsite/laravel-excel`
3. WHEN ExportJob selesai, THE System SHALL mendispatch notifikasi kepada pengguna bahwa file siap diunduh; THE System SHALL memproses data saat filter diterapkan ATAU kolom dipilih (salah satu kondisi sudah cukup)
6. THE System SHALL menyimpan file Excel hasil export di `storage/app/private/exports/` dengan nama file yang mengandung timestamp dan nama entitas
7. THE System SHALL menyediakan halaman/endpoint untuk melihat daftar file export yang tersedia dan mengunduhnya
8. THE System SHALL mengimplementasikan export menggunakan `maatwebsite/laravel-excel` dengan class Export yang terpisah per entitas

---

### Requirement 14: Import Excel dengan Dynamic Fields

**User Story:** Sebagai pengguna, saya ingin mengimpor data dari file Excel dengan kemampuan mapping kolom secara dinamis, sehingga saya dapat menginput data dalam jumlah besar dengan efisien.

#### Acceptance Criteria

1. THE System SHALL menyediakan tombol "Import Excel" pada halaman daftar semua entitas (Categories, Products, StockTransactions, Users, Roles)
2. WHEN pengguna menekan tombol Import, THE System SHALL menampilkan modal/dialog upload file Excel beserta opsi mapping kolom
3. WHEN pengguna mengupload file Excel, THE System SHALL menampilkan preview baris pertama (header) dan memungkinkan pengguna melakukan mapping antara kolom Excel dengan field database
5. WHEN pengguna mengkonfirmasi import, THE System SHALL mendispatch ImportJob ke Laravel Queue; THE System SHALL menampilkan notifikasi bahwa proses sedang berjalan di background setelah konfirmasi diterima (notifikasi dapat dikirim sebelum job benar-benar terdaftar di queue)
5. THE ImportJob SHALL memvalidasi setiap baris data dan mencatat baris yang gagal validasi ke dalam import error log
6. WHEN ImportJob selesai, THE System SHALL membuat notifikasi kepada pengguna beserta ringkasan: jumlah baris berhasil, jumlah baris gagal, dan link ke error log jika ada
7. THE System SHALL mengimplementasikan import menggunakan `maatwebsite/laravel-excel` dengan class Import yang terpisah per entitas
8. THE System SHALL menyimpan file Excel yang diupload di `storage/app/private/imports/` sebagai arsip

---

### Requirement 15: Queue Management

**User Story:** Sebagai pengguna, saya ingin proses export dan import Excel dijalankan di background, sehingga saya tidak perlu menunggu dan dapat melanjutkan aktivitas lain.

#### Acceptance Criteria

1. THE System SHALL menggunakan Laravel Queue dengan driver `database` untuk memproses semua ExportJob dan ImportJob
2. THE System SHALL menyimpan daftar jobs dalam tabel `jobs` di database
3. WHEN job didispatch, THE System SHALL menyimpan record di tabel `export_import_logs` dengan status `pending`
4. WHEN job mulai diproses, THE System SHALL memperbarui status record di `export_import_logs` menjadi `processing`
5. WHEN job berhasil selesai, THE System SHALL memperbarui status menjadi `completed` dan menyimpan path file hasil
6. WHEN job gagal, THE System SHALL memperbarui status menjadi `failed` dan menyimpan pesan error
7. THE System SHALL menyediakan halaman `/export-import-logs` yang menampilkan riwayat semua job export/import beserta statusnya
8. WHEN pengguna mengakses halaman log, THE System SHALL menampilkan kolom: tipe (export/import), entitas, status, total baris, baris sukses, baris gagal, waktu mulai, waktu selesai, link download (untuk export)

---

### Requirement 16: Non-Functional Requirements

**User Story:** Sebagai stakeholder, saya ingin sistem memenuhi standar kualitas teknis tertentu, sehingga sistem berjalan andal, aman, dan dapat dipelihara.

#### Acceptance Criteria

1. THE System SHALL menggunakan UUID (v4) sebagai primary key pada semua tabel utama (categories, products, stock_transactions, users)
2. THE System SHALL menggunakan field `created_at` dan `updated_at` (datetime) pada semua tabel
3. THE System SHALL mengimplementasikan soft-deletes dengan field `deleted_at` pada semua tabel utama
4. THE System SHALL menggunakan field `is_active` (boolean) untuk menandai status aktif/nonaktif pada tabel categories dan products
5. THE System SHALL menggunakan field bertipe JSON pada semua tabel utama: `metadata` di categories, `attributes` di products, `metadata` di stock_transactions
6. THE System SHALL mengimplementasikan database indexing pada kolom yang sering digunakan untuk searching dan filtering
7. THE System SHALL menampilkan pesan flash notification (success/error/warning) untuk setiap operasi CRUD
8. THE System SHALL mengimplementasikan validasi input di sisi server (Laravel FormRequest) dan sisi klien (Vue.js)
9. THE System SHALL menggunakan database transactions untuk operasi yang melibatkan multiple tabel (misalnya: pembuatan StockTransaction yang juga memperbarui current_stock produk)
10. THE System SHALL mengimplementasikan authorization menggunakan Spatie Laravel Permission (Gates/Policies) untuk memproteksi endpoint yang sensitif
11. THE System SHALL menampilkan error handling yang user-friendly untuk error 403, 404, dan 500
12. THE System SHALL mendukung resolusi layar minimal 1280px (desktop) dengan layout Bootstrap 5 yang responsif
