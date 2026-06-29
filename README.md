# Inventori App

Sistem manajemen inventaris berbasis web untuk Coding Test. Dibangun dengan **Laravel 11 + Vue 3 + Inertia.js** dan menggunakan **MySQL** sebagai database.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11, PHP 8.2+ |
| Frontend | Vue 3, Inertia.js, Bootstrap 5, Vite |
| Database | MySQL 8 |
| Auth & RBAC | Spatie Laravel Permission |
| Audit Trail | owen-it/laravel-auditing |
| Export/Import | Maatwebsite Laravel Excel |
| Route (JS) | Ziggy |
| Dropdown | TomSelect |

---

## Fitur Utama

### 1. Authentication & Authorization
- Login / Logout
- Register dengan **admin approval** (user baru belum aktif sampai admin approve)
- Role-Based Access Control (RBAC): **Administrator** (full akses) dan **Staff** (lihat data saja)
- Landing page publik (tidak perlu login)

### 2. CRUD dengan Relationship
- **Categories** → **Products** → **StockTransactions** (relasi FK)
- **Users** → **Roles** (Spatie many-to-many)
- Search, filter, sort, dan pagination di semua halaman
- TomSelect autocomplete untuk dropdown (Categories, Products, Roles)
- **UUID** sebagai primary key di semua tabel
- **Soft deletes** (data tidak benar-benar dihapus)

### 3. Audit Trail (History & Note)
- Setiap **Create**, **Update**, **Delete** tercatat otomatis
- Kolom: Date, Action (badge), User, Nilai Lama, Nilai Baru, Note
- Ditampilkan di semua halaman **Show** dan **Edit**

### 4. Excel Export & Import
- **Export**: pilih kolom yang ingin di-export ke Excel (.xlsx)
- **Import**: upload file Excel → preview → column mapping → import
- File Excel styled: header berwarna, auto-width, row numbers
- **Export/Import Logs** mencatat semua aktivitas
- Queue: `sync` (eksekusi langsung tanpa worker)

### 5. File Attachment (PDF)
- Upload lampiran PDF di Categories, Products, dan StockTransactions
- Validasi: MIME type PDF, ukuran 100–500 KB
- Download lampiran dari halaman Show/Edit
- File storage: `storage/app/private/attachments/`

---

## Database Schema

### Tabel Utama

| Tabel | UUID | Boolean | JSON | Datetime |
|-------|:----:|:-------:|:----:|:--------:|
| `categories` | `id` | `is_active` | `metadata` | `created_at`, `updated_at`, `deleted_at` |
| `products` | `id`, `category_id` | `is_active` | `extra_data` | `created_at`, `updated_at`, `deleted_at` |
| `stock_transactions` | `id`, `product_id`, `created_by` | `is_active` | `metadata` | `created_at`, `updated_at`, `deleted_at` |
| `users` | `id` | `is_active` | `preferences` | `created_at`, `updated_at`, `deleted_at` |
| `roles` | — | `is_active` | `settings` | `created_at`, `updated_at`, `deleted_at` |

### Relasi

```
Users ──< StockTransactions >── Products ──< Categories
  │                                      │
  └── Roles (many-to-many via Spatie)    └── products.category_id → categories.id
```

---

## Setup & Installation

### Prerequisites
- PHP 8.2+
- MySQL 8
- Node.js 18+
- Composer

### Steps

```bash
# 1. Clone repo
git clone https://github.com/rivaldyr14-code/inventori-app.git
cd inventori-app

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventori_app
DB_USERNAME=root
DB_PASSWORD=

# 5. Jalankan migration & seeder
php artisan migrate:fresh --seed

# 6. Build frontend
npm run build

# 7. Jalankan server
php artisan serve
```

Aplikasi berjalan di: **http://localhost:8000**

---

## Credential Login

| Role | Email | Password |
|------|-------|----------|
| Administrator | administrator@inventori.test | password |
| Staff | staff@inventori.test | password |

---

## Halaman Aplikasi

| Halaman | URL | Akses |
|---------|-----|-------|
| Landing Page | `/` | Publik |
| Login | `/login` | Guest |
| Register | `/register` | Guest |
| Dashboard | `/dashboard` | Semua role |
| Categories | `/categories` | Semua role (Admin: CRUD, Staff: lihat) |
| Products | `/products` | Semua role (Admin: CRUD, Staff: lihat) |
| Stock Transactions | `/stock-transactions` | Semua role (Admin: CRUD, Staff: lihat) |
| Users | `/users` | Admin only |
| Roles | `/roles` | Admin: CRUD, Staff: lihat |
| Pending Registrations | `/admin/pending-registrations` | Admin only |
| Export/Import Logs | `/export-import-logs` | Semua role |

---

## Cara Penggunaan

### Register & Approval
1. Buka `/register` → isi form → Submit
2. Login akan **diblokir** karena akun belum aktif
3. Login sebagai **Admin** → buka **Pending Registrations**
4. Klik **Approve** → akun user baru aktif

### CRUD Categories
1. Buka **Categories** → **Tambah Kategori**
2. Isi nama, deskripsi, status, upload PDF (opsional)
3. Submit → data muncul di tabel
4. Klik **Edit** → ubah data → submit → audit trail tercatat
5. Klik **Detail** → lihat semua info + download PDF

### CRUD Products
1. Buka **Products** → **Tambah Produk**
2. Pilih kategori pakai **autocomplete search**
3. Isi SKU, nama, harga, stok awal → Submit
4. Stok bisa bertambah/kurang lewat **Stock Transactions**

### Stock Transactions
1. Buka **Stock Transactions** → **Tambah Transaksi**
2. Pilih produk (autocomplete), tipe Masuk/Keluar, jumlah
3. Submit → stok produk **otomatis terupdate**
4. Coba keluarkan melebihi stok → **error** "Stok tidak mencukupi"
5. Edit transaksi: hanya **Catatan** dan **Status** yang bisa diubah

### Export/Import Excel
1. Buka halaman entity (Categories/Products/etc)
2. Klik tombol **Export/Import**
3. **Export**: pilih kolom → klik Export Excel → file ter-download
4. **Import**: upload .xlsx → preview → mapping kolom → Import Data
5. Lihat log di **Export/Import Logs**

---

## Struktur Project

```
├── app/
│   ├── Exports/              # Export classes (styled Excel)
│   ├── Http/Controllers/     # Controllers (CRUD, Auth, Export, Import)
│   ├── Imports/              # Import classes
│   ├── Jobs/                 # ExportJob, ImportJob (queue)
│   ├── Models/               # Eloquent models (UUID, SoftDeletes, Auditable)
│   └── Traits/               # HasUuid trait
├── config/                   # app.php (timezone: Asia/Jakarta), permission.php, audit.php
├── database/
│   ├── migrations/           # Semua migration (UUID PKs, boolean, json, timestamps)
│   └── seeders/              # DummyDataSeeder, RolePermissionSeeder
├── resources/
│   └── js/
│       ├── Components/
│       │   ├── Layout/       # AppLayout (sidebar), GuestLayout
│       │   └── Shared/       # AuditTrail, ExportImportModal, FileUpload, TomSelectInput
│       └── Pages/            # Vue pages (Categories, Products, Users, Roles, etc.)
├── routes/web.php            # Semua route
└── storage/app/private/      # File uploads (PDF, exports, imports)
```

---

## Konfigurasi Penting

| File | Setting | Value |
|------|---------|-------|
| `config/app.php` | `timezone` | `Asia/Jakarta` (WIB) |
| `.env` | `QUEUE_CONNECTION` | `sync` |
| `config/filesystems.php` | `private` disk | `storage/app/private` |
| `config/permission.php` | `cache.store` | `database` |

---

## License

MIT
