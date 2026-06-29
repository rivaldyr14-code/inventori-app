# Implementation Plan: Inventori App

## Overview

Task list ini mengimplementasikan aplikasi Manajemen Inventori menggunakan Laravel 11 + Vue.js 3 + Inertia.js + Bootstrap 5 secara inkremental. Setiap task membangun di atas task sebelumnya, dimulai dari setup proyek hingga fitur lengkap. Semua implementasi menggunakan bahasa PHP 8.2+ (backend) dan JavaScript/Vue 3 Composition API (frontend).

## Tasks

- [x] 1. Setup proyek Laravel 11 dan konfigurasi awal
  - Install Laravel 11 via Composer: `composer create-project laravel/laravel inventori-app`
  - Konfigurasi `.env`: DB_CONNECTION=mysql, QUEUE_CONNECTION=database
  - Install Inertia.js server-side adapter: `composer require inertiajs/inertia-laravel`
  - Install Vue 3 + Inertia.js client-side: `npm install @inertiajs/vue3 vue`
  - Install Vite plugin Vue: `npm install @vitejs/plugin-vue`
  - Konfigurasi `vite.config.js` untuk Vue + Inertia
  - Install Bootstrap 5: `npm install bootstrap @popperjs/core`
  - Install Tom Select: `npm install tom-select`
  - Setup `resources/js/app.js` dengan Inertia bootstrap dan Vue
  - Setup `resources/css/app.css` dengan Bootstrap 5 dan Tom Select CSS
  - Buat `resources/views/app.blade.php` sebagai root Inertia template
  - _Requirements: 16.1_

- [x] 2. Install dan konfigurasi packages backend
  - [x] 2.1 Install Spatie Laravel Permission
    - `composer require spatie/laravel-permission`
    - Publish dan jalankan migration Spatie
    - Tambahkan `HasRoles` trait ke model `User`
    - _Requirements: 5.1, 4.1_
  - [x] 2.2 Install owen-it/laravel-auditing
    - `composer require owen-it/laravel-auditing`
    - Publish config `config/audit.php`
    - Publish dan jalankan migration tabel `audits`
    - _Requirements: 9.1_
  - [x] 2.3 Install maatwebsite/laravel-excel
    - `composer require maatwebsite/laravel-excel`
    - Publish config `config/excel.php`
    - _Requirements: 13.1, 14.1_
  - [x] 2.4 Setup Laravel Queue database driver
    - `php artisan queue:table && php artisan migrate`
    - Buat tabel `failed_jobs`: `php artisan queue:failed-table`
    - _Requirements: 15.1, 15.2_

- [x] 3. Buat migrasi database dan model dasar
  - [x] 3.1 Modifikasi migrasi `users` dan buat `HasUuid` trait
    - Buat `app/Traits/HasUuid.php` dengan UUID auto-generation pada `creating` event
    - Modifikasi migration `create_users_table`: ubah `id` ke `CHAR(36)`, tambahkan kolom `is_active` (boolean, default: true) dan `deleted_at` (SoftDeletes)
    - Update model `User.php`: gunakan `HasUuid`, `HasRoles`, `SoftDeletes`, `Auditable`, set `$keyType = 'string'`, `$incrementing = false`
    - _Requirements: 16.1, 16.3, 5.6_
  - [x] 3.2 Buat migrasi dan model `Category`
    - Buat migration `create_categories_table` dengan semua kolom (uuid id, name, description, is_active, metadata json, attachment_path, timestamps, deleted_at)
    - Buat `app/Models/Category.php` dengan traits: `HasUuid`, `SoftDeletes`, `Auditable`
    - Tambahkan `$fillable`, `$casts` (metadata→array, is_active→boolean), relasi `products()`
    - _Requirements: 6.3, 16.1, 16.3, 16.4, 16.5_
  - [x] 3.3 Buat migrasi dan model `Product`
    - Buat migration `create_products_table` dengan semua kolom (uuid id, category_id FK, sku, name, description, price, current_stock, is_active, attributes json, attachment_path, timestamps, deleted_at)
    - Buat `app/Models/Product.php` dengan traits: `HasUuid`, `SoftDeletes`, `Auditable`
    - Tambahkan relasi `category()`, `stockTransactions()`
    - _Requirements: 7.3, 16.1, 16.3, 16.4, 16.5_
  - [x] 3.4 Buat migrasi dan model `StockTransaction`
    - Buat migration `create_stock_transactions_table` dengan semua kolom (uuid id, product_id FK, created_by FK, transaction_number unique, type enum, quantity, stock_before, stock_after, notes, metadata json, attachment_path, timestamps, deleted_at)
    - Buat `app/Models/StockTransaction.php` dengan traits: `HasUuid`, `SoftDeletes`, `Auditable`
    - Set `$auditExclude = []` (tidak exclude apapun saat create; immutability dihandle di request layer)
    - Tambahkan relasi `product()` (withTrashed), `createdBy()`
    - _Requirements: 8.3, 16.1, 16.3, 16.5_
  - [x] 3.5 Buat migrasi `export_import_logs` dan model
    - Buat migration dan `app/Models/ExportImportLog.php`
    - Relasi `user()` BelongsTo
    - _Requirements: 15.3_
  - [x] 3.6 Jalankan semua migrasi dan buat DatabaseSeeder
    - `php artisan migrate`
    - Buat `database/seeders/RolePermissionSeeder.php`: seed roles (Administrator, Staff) dan default users
    - Buat `database/seeders/DatabaseSeeder.php` yang memanggil semua seeder
    - `php artisan db:seed`
    - _Requirements: 5.1_

- [x] 4. Implementasi autentikasi dan layout
  - [x] 4.1 Buat Auth controllers dan routes
    - Buat `app/Http/Controllers/Auth/AuthenticatedSessionController.php` (create, store, destroy)
    - Buat `app/Http/Requests/Auth/LoginRequest.php` dengan validasi email dan password
    - Daftarkan route di `routes/web.php`: GET/POST /login, POST /logout
    - Middleware `guest` pada GET /login untuk redirect jika sudah login
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_
  - [x] 4.2 Buat Vue component untuk Login
    - Buat `resources/js/Pages/Auth/Login.vue`
    - Form dengan field email, password, tombol login menggunakan Bootstrap 5
    - Integrasikan Inertia `useForm()` untuk form submission
    - Tampilkan validation errors di bawah setiap field
    - _Requirements: 2.1, 2.3_
  - [x] 4.3 Buat AppLayout dan GuestLayout Vue component
    - Buat `resources/js/Components/Layout/GuestLayout.vue` (layout sederhana untuk landing dan login)
    - Buat `resources/js/Components/Layout/AppLayout.vue` dengan sidebar navigation, navbar (tampilkan nama user), dan flash message area
    - Sidebar mencantumkan link ke: Dashboard, Categories, Products, Stock Transactions, Roles, Users (conditional: hanya untuk Administrator), Export/Import Logs
    - _Requirements: 3.6, 5.1_
  - [x] 4.4 Buat Landing page
    - Buat `app/Http/Controllers/LandingController.php` → return `Inertia::render('Landing')`
    - Buat `resources/js/Pages/Landing.vue` dengan GuestLayout, konten: nama app, deskripsi, tombol Login
    - Daftarkan route `GET /` → LandingController
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_
  - [x] 4.5 Buat FlashMessage component dan Dashboard
    - Buat `resources/js/Components/Shared/FlashMessage.vue` yang membaca flash dari Inertia shared props
    - Share flash messages via `HandleInertiaRequests` middleware
    - Buat `app/Http/Controllers/DashboardController.php` yang menghitung stats dan return Inertia response
    - Buat `resources/js/Pages/Dashboard.vue` yang menampilkan stats cards dan flash messages
    - Daftarkan route `GET /dashboard` dengan middleware `auth`
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 16.7_
  - [ ]* 4.6 Tulis feature tests untuk autentikasi
    - Test login dengan valid credentials → redirect /dashboard
    - Test login dengan invalid credentials → validation error, tidak ter-autentikasi
    - Test redirect logged-in user dari /login → /dashboard
    - Test logout → redirect ke /
    - Test proteksi route /dashboard untuk guest → redirect /login
    - _Requirements: 2.2, 2.3, 2.4, 2.5, 2.8_

- [x] 5. Checkpoint — Pastikan semua tes lulus dan aplikasi dapat login
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Buat shared components untuk tabel, search, filter, dan pagination
  - [x] 6.1 Buat DataTable component
    - Buat `resources/js/Components/Shared/DataTable.vue`
    - Props: columns (array of {key, label, sortable}), data, sortBy, sortDirection
    - Emit: `sort` event saat header diklik (toggle asc/desc, dimulai dari asc)
    - Render slot per-row untuk fleksibilitas
    - _Requirements: 12.4_
  - [x] 6.2 Buat SearchFilter component
    - Buat `resources/js/Components/Shared/SearchFilter.vue`
    - Props: filters (object), filterFields (array of {key, label, type: text/select/date})
    - Emit: `filter-change` event yang dihandle oleh parent untuk Inertia visit
    - _Requirements: 12.1, 12.2, 12.3_
  - [x] 6.3 Buat Pagination component
    - Buat `resources/js/Components/Shared/Pagination.vue`
    - Terima prop `links` dari Laravel paginator
    - Gunakan Inertia.router.visit untuk navigasi halaman
    - _Requirements: 12.6_
  - [x] 6.4 Buat ConfirmModal, TomSelectInput, FileUpload components
    - Buat `ConfirmModal.vue`: modal konfirmasi delete dengan props message dan emit confirm/cancel
    - Buat `TomSelectInput.vue`: wrapper Tom Select dengan props url (untuk remote search), value, label; emit `update:modelValue`
    - Buat `FileUpload.vue`: input file PDF dengan client-side validasi MIME dan ukuran (100-500 KB), tampilkan preview nama file
    - _Requirements: 10.1, 11.1, 11.6_
  - [x] 6.5 Buat AuditTrail component
    - Buat `resources/js/Components/Shared/AuditTrail.vue`
    - Props: audits (array)
    - Tampilkan tabel: event, user, old_values vs new_values (diff), IP, timestamp
    - _Requirements: 9.3, 9.4_

- [ ] 7. Implementasi API autocomplete endpoints
  - Buat `app/Http/Controllers/Api/CategorySearchController.php`
    - `GET /api/categories/search?q={query}` → return JSON max 10 categories aktif yang cocok dengan query pada `name`
  - Buat `app/Http/Controllers/Api/ProductSearchController.php`
    - `GET /api/products/search?q={query}` → return JSON max 10 products aktif
  - Buat `app/Http/Controllers/Api/RoleSearchController.php`
    - `GET /api/roles/search?q={query}` → return JSON max 10 roles
  - Daftarkan semua API route di `routes/api.php` dengan middleware `auth`
  - _Requirements: 11.2, 11.3, 11.4, 11.5, 11.7_

  - [ ]* 7.1 Tulis property test untuk autocomplete API
    - **Property 4: Autocomplete Returns Only Active Records**
    - For any search query ke `/api/categories/search` dan `/api/products/search`, semua item yang dikembalikan harus `is_active = true` dan tidak soft-deleted
    - **Validates: Requirements 11.2, 11.3, 11.4**

- [ ] 8. Implementasi CRUD Category
  - [-] 8.1 Buat CategoryController dengan semua method CRUD
    - `app/Http/Controllers/CategoryController.php`
    - `index()`: query dengan search (name, description), filter (is_active), sort, paginate(15); return `Inertia::render('Categories/Index', [...])`
    - `create()`: return `Inertia::render('Categories/Create')`
    - `store()`: validasi via `StoreCategoryRequest`, simpan record, handle file upload, redirect dengan flash success
    - `show()`: load category dengan audits; return `Inertia::render('Categories/Show', [...])`
    - `edit()`: return `Inertia::render('Categories/Edit', [...])`
    - `update()`: validasi via `UpdateCategoryRequest`, update record, handle file upload baru jika ada
    - `destroy()`: soft-delete, redirect dengan flash success
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8, 6.9_
  - [-] 8.2 Buat StoreCategoryRequest dan UpdateCategoryRequest
    - Validasi: `name` required|string|max:255, `description` nullable|string, `is_active` boolean, `metadata` nullable|json, `attachment` nullable|mimes:pdf|min:102|max:512
    - _Requirements: 6.3, 10.2, 10.3_
  - [~] 8.3 Buat Vue pages untuk Category (Index, Create, Edit, Show)
    - `Categories/Index.vue`: DataTable + SearchFilter + Pagination + tombol Create/Export/Import + per-row actions (Show, Edit, Delete)
    - `Categories/Create.vue`: form dengan field name, description, is_active toggle, metadata JSON textarea, file upload; submit via Inertia useForm
    - `Categories/Edit.vue`: sama dengan Create tapi pre-filled, tampilkan link attachment jika ada
    - `Categories/Show.vue`: tampilkan detail category + AuditTrail component
    - _Requirements: 6.1, 6.2, 6.9, 9.3, 9.6_
  - [ ]* 8.4 Tulis feature tests untuk Category CRUD
    - Test create, update, soft-delete, search, filter, sort
    - Test file upload validation (valid PDF 100-500KB, reject non-PDF, reject out-of-range)
    - **Property 7: Soft Delete Preserves Data Integrity** — for any soft-deleted category, data harus ada di DB tapi tidak di index default
    - **Validates: Requirements 6.3, 6.5, 10.2, 10.3, 16.3**
  - [ ]* 8.5 Tulis property test untuk file upload validation
    - **Property 1: File Upload Validation Enforces Size Range**
    - For any file di luar rentang [100KB, 500KB] atau non-PDF, upload harus ditolak dan attachment_path tidak berubah
    - **Validates: Requirements 10.2, 10.3, 10.4, 10.5, 10.6**

- [ ] 9. Implementasi file attachment download route
  - Buat `app/Http/Controllers/AttachmentController.php` dengan method `download(string $entity, string $id)`
  - Validasi bahwa user authenticated, entity dan id valid, file ada di storage
  - Return `Storage::download($path)` dengan original filename
  - Daftarkan route `GET /attachments/{entity}/{id}` dengan middleware `auth`
  - Tampilkan link download di Show pages hanya jika `attachment_path` tidak null
  - _Requirements: 9.1, 10.8, 10.9, 10.10_

- [~] 10. Checkpoint — Pastikan CRUD Category berjalan lengkap
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Implementasi CRUD Product
  - [~] 11.1 Buat ProductController dengan semua method CRUD
    - `index()`: query dengan search (sku, name), filter (category_id, is_active), sort, paginate(15); eager load `category`
    - `store()`: validasi via `StoreProductRequest`, auto-generate sku jika kosong, simpan, handle upload
    - `show()`: load product dengan category, stockTransactions (paginated), audits
    - `update()`: validasi via `UpdateProductRequest`, update, handle upload
    - `destroy()`: soft-delete
    - _Requirements: 7.1, 7.2, 7.3, 7.5, 7.6, 7.7, 7.8, 7.9, 7.10, 7.11_
  - [~] 11.2 Buat StoreProductRequest dan UpdateProductRequest
    - `category_id` required|exists:categories,id, `sku` required|unique:products,sku (ignore on update), `name` required, `price` numeric|min:0, `current_stock` integer|min:0, `is_active` boolean, `attributes` nullable|json, `attachment` nullable|mimes:pdf|min:102|max:512
    - _Requirements: 7.3, 10.2, 10.3_
  - [~] 11.3 Buat Vue pages untuk Product (Index, Create, Edit, Show)
    - `Products/Index.vue`: tabel dengan relasi category, tombol filter by category menggunakan TomSelectInput
    - `Products/Create.vue`: form dengan TomSelectInput untuk category (remote search ke `/api/categories/search`), field lainnya
    - `Products/Edit.vue`: pre-filled form
    - `Products/Show.vue`: detail + daftar StockTransactions terbaru + AuditTrail
    - _Requirements: 7.2, 7.4, 7.7, 9.6_
  - [ ]* 11.4 Tulis feature tests untuk Product CRUD
    - Test create dengan valid category, update, soft-delete
    - Test autocomplete category search (Property 4 — sudah dicakup di task 7.1)
    - Test search by sku dan name, filter by category dan is_active
    - **Validates: Requirements 7.3, 7.8, 7.9_

- [ ] 12. Implementasi CRUD StockTransaction
  - [~] 12.1 Buat StockTransactionController
    - `store()`: wrap dalam `DB::transaction()` — (1) hitung stock_before dari `product->current_stock`, (2) hitung stock_after, (3) validasi tidak negatif, (4) update `product->current_stock`, (5) simpan transaksi, (6) jika ada exception (termasuk audit failure) rollback
    - `update()`: hanya perbolehkan update field `notes` dan `attachment_path`; field immutable ditolak
    - Semua method lain serupa dengan CategoryController
    - Generate `transaction_number` format `TXN-YYYYMMDD-NNNN` sebelum save
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.9, 8.10, 8.11, 8.12_
  - [~] 12.2 Buat StoreStockTransactionRequest dan UpdateStockTransactionRequest
    - `StoreStockTransactionRequest`: `product_id` required|exists, `type` required|in:in,out, `quantity` required|integer|min:1, `notes` nullable, `attachment` nullable|mimes:pdf|min:102|max:512; custom rule: jika type=out, pastikan `product->current_stock >= quantity`
    - `UpdateStockTransactionRequest`: hanya `notes` dan `attachment` yang boleh diubah; reject jika ada field immutable dalam request
    - _Requirements: 8.6, 8.7, 10.2, 10.3_
  - [~] 12.3 Buat Vue pages untuk StockTransaction (Index, Create, Edit, Show)
    - `StockTransactions/Index.vue`: filter by type, product (TomSelectInput), date range
    - `StockTransactions/Create.vue`: TomSelectInput untuk product, field type (select), quantity, notes, file upload; tampilkan current stock product yang dipilih secara realtime
    - `StockTransactions/Edit.vue`: hanya field notes dan attachment yang editable; field lain read-only/disabled
    - `StockTransactions/Show.vue`: detail lengkap + AuditTrail
    - _Requirements: 8.2, 8.3, 8.8, 8.9, 9.6_
  - [ ]* 12.4 Tulis property test untuk Stock Calculation
    - **Property 3: Stock Level Consistency After Transaction**
    - For any initial stock S dan quantity Q (tipe in): current_stock harus = S + Q, stock_after = S + Q, stock_before = S
    - For any initial stock S dan quantity Q ≤ S (tipe out): current_stock = S - Q
    - For any Q > current_stock dengan tipe out: transaksi ditolak
    - **Validates: Requirements 8.4, 8.5, 8.6**
  - [ ]* 12.5 Tulis property test untuk Transaction Immutability
    - **Property 2: Stock Transaction Immutability of Core Fields**
    - For any saved StockTransaction, attempt update pada stock_before/stock_after/quantity/type/product_id harus ditolak (422) dan nilai tidak berubah
    - **Validates: Requirements 8.7, 9.5**

- [~] 13. Checkpoint — Pastikan CRUD tiga entitas (Category, Product, StockTransaction) berjalan
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 14. Implementasi CRUD Role Management
  - [~] 14.1 Buat RoleController
    - CRUD standar menggunakan Spatie Permission `Role` model
    - `index()`: paginate dengan search (name), sort
    - `store()`: buat role baru via `Role::create(['name' => $request->name])`
    - `update()`: rename role
    - `destroy()`: soft-delete role (extend Spatie Role model atau custom)
    - Semua route dengan middleware `auth`
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8_
  - [~] 14.2 Extend Spatie Role model untuk Auditable dan SoftDeletes
    - Buat `app/Models/Role.php` yang extends `Spatie\Permission\Models\Role`
    - Tambahkan traits `SoftDeletes` dan `Auditable`
    - Update config `config/permission.php` untuk menggunakan custom Role model
    - _Requirements: 4.6, 9.1_
  - [~] 14.3 Buat Vue pages untuk Role (Index, Create, Edit, Show)
    - `Roles/Index.vue`: tabel dengan search dan sort
    - `Roles/Create.vue`, `Roles/Edit.vue`: form dengan field name
    - `Roles/Show.vue`: detail + jumlah user dengan role ini + AuditTrail
    - _Requirements: 4.3, 4.9_

- [ ] 15. Implementasi CRUD User Account
  - [~] 15.1 Buat UserController dengan authorization middleware
    - Semua method dilindungi middleware `role:Administrator` (Spatie)
    - `index()`: paginate dengan search (name, email), filter (role), sort
    - `store()`: buat user + assign role, hash password
    - `update()`: update user; jika password kosong, pertahankan password lama; update role assignment
    - `destroy()`: soft-delete + set `is_active = false`
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6_
  - [~] 15.2 Buat StoreUserRequest dan UpdateUserRequest
    - `StoreUserRequest`: name required, email required|unique, password required|min:8, role required|exists:roles,name, is_active boolean
    - `UpdateUserRequest`: sama tapi password nullable|min:8|sometimes
    - _Requirements: 5.4, 5.5_
  - [~] 15.3 Buat Vue pages untuk User (Index, Create, Edit, Show)
    - `Users/Index.vue`: tabel dengan filter by role, search by name/email
    - `Users/Create.vue` dan `Users/Edit.vue`: form dengan TomSelectInput untuk role (remote search ke `/api/roles/search`)
    - `Users/Show.vue`: detail + AuditTrail
    - Sembunyikan menu Users dari navigation jika bukan Administrator
    - _Requirements: 5.3, 5.7, 5.8, 5.9_
  - [ ]* 15.4 Tulis feature tests untuk User authorization
    - Test bahwa non-Administrator di-redirect 403 dari semua /users routes
    - Test create user, update (tanpa dan dengan password baru), soft-delete
    - **Validates: Requirements 5.1, 5.2, 5.6**

- [ ] 16. Implementasi Audit Trail display
  - Pastikan semua model (Category, Product, StockTransaction, User, Role) menggunakan trait `Auditable`
  - Di setiap `show()` controller method, load audits: `$entity->audits()->with('user')->latest()->get()`
  - Pass audit data ke Inertia response sebagai prop `audits`
  - `AuditTrail.vue` component sudah dibuat di task 6.5; integrasikan ke semua Show pages
  - Verifikasi bahwa `StockTransaction` mencatat audit saat create (event: created)
  - Verifikasi bahwa update pada StockTransaction mencatat perubahan hanya untuk fields yang diizinkan (notes, attachment_path)
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6_

  - [ ]* 16.1 Tulis property test untuk Audit Creation
    - **Property 8: Audit Record Created for Every Mutation**
    - For any create/update/delete pada Category, Product, StockTransaction, User, Role → minimal 1 audit record baru harus ada dengan auditable_type dan auditable_id yang cocok
    - Test juga bahwa rollback terjadi jika audit gagal pada StockTransaction
    - **Validates: Requirements 9.1, 9.2**

- [ ] 17. Implementasi searching, filtering, sorting dengan URL state persistence
  - Buat helper `app/Http/Traits/HasSearchFilterSort.php` untuk reuse logic di semua controllers
  - Trait ini mengambil query params (search, filter_*, sort_by, sort_dir, per_page) dari request dan membangun Eloquent query
  - Di setiap Index Vue page, implementasikan reactive state untuk search/filter/sort yang ter-sync dengan URL menggunakan Inertia `router.get()` dengan `preserveState: true`
  - Pastikan URL selalu merefleksikan state filter saat ini sehingga reload menghasilkan view yang identik
  - Reset ke halaman 1 saat filter/search berubah
  - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6, 12.7_

  - [ ]* 17.1 Tulis property test untuk URL State Preservation
    - **Property 5: Search/Filter State Preserved in URL**
    - For any kombinasi valid parameter search/filter/sort, URL harus merefleksikan state dan reload URL yang sama menghasilkan data yang identik
    - **Validates: Requirements 12.5**

- [~] 18. Checkpoint — Pastikan semua CRUD, Audit, Search/Filter/Sort berjalan
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 19. Implementasi Export Excel dengan Dynamic Fields dan Queue
  - [~] 19.1 Buat class Export per entitas
    - Buat `app/Exports/CategoryExport.php` implements `FromCollection`, `WithHeadings`, `WithMapping`
    - Constructor menerima: `$query` (Eloquent Builder), `$selectedFields` (array kolom yang dipilih)
    - `headings()`: return array label kolom sesuai `$selectedFields`
    - `map($row)`: return array value sesuai `$selectedFields`
    - Buat class Export serupa untuk Product, StockTransaction, User, Role
    - _Requirements: 13.4, 13.8_
  - [~] 19.2 Buat ExportJob
    - `app/Jobs/ExportJob.php` implements `ShouldQueue`
    - Constructor: `$logId`, `$entity`, `$filters`, `$selectedFields`, `$userId`
    - `handle()`: (1) update log status → processing, (2) build query dengan filters, (3) buat Export class, (4) save ke `storage/app/private/exports/{entity}_{timestamp}.xlsx`, (5) update log status → completed + file_path
    - Failure: update log status → failed + error_log
    - _Requirements: 13.3, 13.6, 15.3, 15.4, 15.5, 15.6_
  - [~] 19.3 Buat ExportController
    - `POST /export/{entity}`: validasi input (selected_fields array), buat ExportImportLog record (status: pending), dispatch ExportJob, return JSON response
    - `GET /exports/{log}/download`: stream file download jika status completed
    - _Requirements: 13.1, 13.2, 13.3, 13.5, 13.7_
  - [~] 19.4 Buat ExportModal Vue component
    - `resources/js/Components/Shared/ExportModal.vue`
    - Props: `availableFields` (array of {key, label}), `entity`
    - Tampilkan checklist kolom yang tersedia (semua dicentang by default)
    - Tombol "Export" mengirim POST ke `/export/{entity}` dengan selectedFields dan filter saat ini
    - Tampilkan notifikasi "Export sedang diproses di background"
    - _Requirements: 13.1, 13.2, 13.3_
  - [ ]* 19.5 Tulis property test untuk Export Dynamic Fields
    - **Property 6: Export Field Selection Produces Correct Columns**
    - For any non-empty subset kolom yang dipilih, file Excel yang dihasilkan harus mengandung tepat kolom-kolom tersebut
    - **Validates: Requirements 13.2, 13.4**

- [ ] 20. Implementasi Import Excel dengan Dynamic Fields dan Queue
  - [~] 20.1 Buat class Import per entitas
    - Buat `app/Imports/CategoryImport.php` implements `ToModel`, `WithHeadingRow`, `WithValidation`, `SkipsOnError`
    - Constructor: `$columnMapping` (array map kolom Excel → field DB), `$logId`
    - `model($row)`: buat model dari row menggunakan column mapping
    - Tracking success/failed rows, tulis ke log
    - Buat class Import serupa untuk Product, StockTransaction, User, Role
    - _Requirements: 14.4, 14.5, 14.7_
  - [~] 20.2 Buat ImportJob
    - `app/Jobs/ImportJob.php` implements `ShouldQueue`
    - Constructor: `$logId`, `$entity`, `$filePath`, `$columnMapping`
    - `handle()`: (1) update log → processing, (2) jalankan Import class, (3) update log → completed + summary
    - _Requirements: 14.4, 14.5, 14.6, 15.3, 15.4, 15.5, 15.6_
  - [~] 20.3 Buat ImportController
    - `POST /import/{entity}`: terima file upload, simpan ke `storage/app/private/imports/`, buat Log record (pending), dispatch ImportJob, return response
    - `GET /import/{entity}/preview` (API): terima path file yang sudah diupload, parse header row Excel, return daftar kolom
    - _Requirements: 14.1, 14.2, 14.3, 14.8_
  - [~] 20.4 Buat ImportModal Vue component
    - `resources/js/Components/Shared/ImportModal.vue`
    - Step 1: Upload file Excel
    - Step 2: Setelah upload, fetch preview header dari API → tampilkan column mapping UI (dropdown untuk tiap kolom Excel → field DB)
    - Step 3: Konfirmasi → POST ke `/import/{entity}` dengan file path dan column mapping
    - Tampilkan notifikasi "Import sedang diproses di background"
    - _Requirements: 14.1, 14.2, 14.3, 14.5_

- [~] 21. Implementasi halaman Export/Import Logs
  - Buat `app/Http/Controllers/ExportImportLogController.php`
  - `index()`: tampilkan semua logs milik user yang sedang login, dengan kolom sesuai Requirement 15.8
  - Daftarkan route `GET /export-import-logs` dengan middleware `auth`
  - Buat `resources/js/Pages/ExportImportLogs/Index.vue` dengan tabel dan link download untuk logs yang completed
  - _Requirements: 15.7, 15.8_

- [~] 22. Integrasikan Export/Import modal ke semua Index pages
  - Tambahkan `ExportModal` dan `ImportModal` ke: `Categories/Index.vue`, `Products/Index.vue`, `StockTransactions/Index.vue`, `Roles/Index.vue`, `Users/Index.vue`
  - Setiap page mendefinisikan `availableFields` yang spesifik untuk entitas tersebut
  - Pass filter aktif saat ini ke ExportModal agar export mengikuti filter yang sedang diterapkan
  - Tambahkan link "Export/Import Logs" di navigation sidebar
  - _Requirements: 13.1, 14.1_

- [~] 23. Checkpoint — Pastikan Export/Import dengan Queue berjalan
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 24. Implementasi error pages dan hardening akhir
  - [~] 24.1 Buat custom error pages Inertia
    - Buat `resources/js/Pages/Error.vue` yang menerima prop `status` (403, 404, 500) dan menampilkan pesan yang sesuai
    - Update exception handler di `bootstrap/app.php` / `app/Exceptions/Handler.php` untuk render `Error.vue` via Inertia untuk request Inertia
    - _Requirements: 16.11_
  - [~] 24.2 Tambahkan database indexes
    - Pastikan semua index yang didefinisikan di schema sudah ada di migrasi
    - Jalankan `php artisan migrate:fresh --seed` untuk verifikasi
    - _Requirements: 16.6_
  - [~] 24.3 Tambahkan validasi server-side yang komprehensif
    - Review semua FormRequest: pastikan semua field memiliki validasi yang lengkap
    - Tambahkan client-side validation di Vue component untuk UX yang lebih baik (tidak bergantung hanya pada server)
    - _Requirements: 16.8_
  - [~] 24.4 Buat DatabaseTransaction wrapper untuk StockTransaction
    - Pastikan `StockTransactionController::store()` membungkus semua operasi dalam `DB::transaction()`
    - Pastikan rollback terjadi jika ada error termasuk audit failure
    - _Requirements: 16.9, 9.2_

- [~] 25. Checkpoint final — Jalankan seluruh test suite
  - Ensure all tests pass, ask the user if questions arise.
  - Jalankan `php artisan test` untuk memastikan semua tests hijau
  - Verifikasi manual semua alur: login, CRUD 3 entitas, audit trail, upload PDF, autocomplete, search/filter/sort, export, import
  - Verifikasi bahwa queue worker dapat dijalankan dengan `php artisan queue:work`

---

## Notes

- Task yang berakhiran `*` adalah optional (test tasks) dan dapat dilewati untuk implementasi MVP yang lebih cepat
- Setiap task merujuk ke requirements spesifik untuk traceability
- Queue worker harus dijalankan secara terpisah: `php artisan queue:work --queue=default`
- Untuk development, gunakan `php artisan queue:listen` agar queue auto-restart saat ada perubahan kode
- File storage menggunakan Laravel private disk; pastikan `php artisan storage:link` dijalankan untuk public assets
- UUID digunakan sebagai primary key di semua tabel utama; Spatie Permission menggunakan integer ID untuk tabel roles/permissions (default behavior yang tidak diubah)
- Setiap property test dianotasikan dengan: `Feature: inventori-app, Property N: <property_text>`
- Import untuk StockTransaction harus hati-hati: setiap row import akan memicu perhitungan stok dan update product; gunakan DB::transaction di ImportJob

## Task Dependency Graph

```json
{
  "waves": [
    { "wave": 1, "tasks": ["1", "2"] },
    { "wave": 2, "tasks": ["3"] },
    { "wave": 3, "tasks": ["4"] },
    { "wave": 4, "tasks": ["5"] },
    { "wave": 5, "tasks": ["6", "7"] },
    { "wave": 6, "tasks": ["8", "9"] },
    { "wave": 7, "tasks": ["10"] },
    { "wave": 8, "tasks": ["11"] },
    { "wave": 9, "tasks": ["12"] },
    { "wave": 10, "tasks": ["13"] },
    { "wave": 11, "tasks": ["14", "15"] },
    { "wave": 12, "tasks": ["16", "17"] },
    { "wave": 13, "tasks": ["18"] },
    { "wave": 14, "tasks": ["19", "20"] },
    { "wave": 15, "tasks": ["21", "22"] },
    { "wave": 16, "tasks": ["23"] },
    { "wave": 17, "tasks": ["24"] },
    { "wave": 18, "tasks": ["25"] }
  ]
}
```
