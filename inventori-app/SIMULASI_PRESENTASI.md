# Simulasi Presentasi — Inventori App

---

## 1. Opening (1 menit)

> "Selamat pagi/siang, saya akan mempresentasikan hasil coding test untuk topik **Sistem Manajemen Inventori**. Aplikasi ini dibangun dengan **Laravel 11 + Vue 3 + Inertia.js** dan menggunakan **MySQL** sebagai database."

**Tech Stack:**
- Backend: Laravel 11, PHP 8.2+
- Frontend: Vue 3, Inertia.js, Bootstrap 5
- Database: MySQL
- Auth: Spatie Laravel Permission
- Audit: owen-it/laravel-auditing
- Export: maatwebsite/laravel-excel

---

## 2. Topik 1: Authentication (3 menit)

### 2.1 Landing Page
> "Ini adalah landing page yang bisa diakses tanpa login. Menampilkan data stok produk secara publik."

**Demo:**
- Buka `http://localhost:8000`
- Tampilkan landing page (tabel produk, fitur, tech stack)

### 2.2 Register
> "Pengguna baru bisa mendaftar. Setelah register, akun belum aktif — harus menunggu approval dari admin."

**Demo:**
1. Klik **Daftar** di landing page
2. Isi form: Nama, Email, Password, Konfirmasi Password
3. Submit → pesan "Registrasi berhasil, menunggu approval admin"
4. Coba login → **diblokir** karena akun belum aktif

### 2.3 Login
> "Saya login sebagai Administrator terlebih dahulu untuk approve registrasi."

**Demo:**
1. Login: `administrator@inventori.test` / `password`
2. Masuk ke Dashboard

### 2.4 Admin Approval
> "Admin bisa melihat daftar pengguna yang menunggu approval, lalu approve atau reject."

**Demo:**
1. Buka sidebar → **Pending Registrations**
2. Lihat user yang baru register (status: Menunggu)
3. Klik **Approve** → user sekarang aktif
4. Logout → login dengan akun yang baru di-approve → berhasil

### 2.5 Role-Based Access
> "Sistem memiliki 2 role: Administrator dan Staff. Staff hanya bisa melihat data, sedangkan Admin bisa CRUD penuh."

**Demo:**
1. Login sebagai Staff (`staff@inventori.test` / `password`)
2. Tampilkan sidebar — **Users** dan **Pending Registrations** tidak terlihat
3. Buka halaman Categories — tombol **Tambah**, **Edit**, **Hapus** tidak ada (hanya lihat)
4. Logout → login sebagai Admin

---

## 3. Topik 2: CRUD + Relationship (5 menit)

### 3.1 Categories (Kategori)
> "Kategori memiliki relasi ke Products. Setiap kategori punya nama, deskripsi, status aktif, metadata JSON, dan lampiran PDF."

**Demo Create:**
1. Klik **Categories** → **Tambah Kategori**
2. Isi: Nama = "Testing Kategori", Deskripsi = "Deskripsi testing"
3. Upload PDF lampiran (file 100-500 KB)
4. Submit → muncul di tabel

**Demo Search/Filter:**
1. Ketik "Testing" di kolom pencarian → filter otomatis
2. Filter Status: Aktif / Tidak Aktif

**Demo Edit:**
1. Klik **Edit** pada kategori
2. Ubah nama → Submit
3. Tampilkan bagian **History & Note** — audit trail tercatat

**Demo Show:**
1. Klik **Detail** → tampil semua field + download PDF

### 3.2 Products (Produk)
> "Produk punya relasi ke Kategori (belongs to). Setiap produk punya SKU, nama, harga, stok, extra_data JSON, dan lampiran PDF."

**Demo Create:**
1. Klik **Products** → **Tambah Produk**
2. Pilih Kategori pakai **autocomplete search** (ketik → muncul hasil dari database)
3. Isi: SKU, Nama, Harga, Stok Awal
4. Submit → produk muncul di tabel

**Demo Relationship:**
1. Di tabel Products, kolom **Kategori** menampilkan nama kategori (bukan ID)
2. Buka Detail Produk → tampilkan info kategori + **riwayat transaksi stok**

### 3.3 Stock Transactions (Transaksi Stok)
> "Transaksi stok punya relasi ke Produk dan User (siapa yang membuat). Sistem otomatis menghitung stok sebelum dan sesudah transaksi."

**Demo Create:**
1. Klik **Stock Transactions** → **Tambah Transaksi**
2. Pilih Produk (autocomplete)
3. Tipe: **Masuk** (in), Jumlah: 10
4. Submit → stok produk otomatis bertambah

**Demo Stock Logic:**
1. Buka Detail Produk yang tadi → stok sudah bertambah
2. Buat transaksi **Keluar** (out) sejumlah 5 → stok berkurang
3. Coba keluarkan melebihi stok → **error** "Stok tidak mencukupi"

**Demo Data Immutability:**
1. Buka Edit Transaksi
2. Hanya field **Catatan** dan **Status** yang bisa diubah
3. Field produk, tipe, jumlah — tidak bisa diubah (ditampilkan read-only)

---

## 4. Topik 3: Audits / Audit Trail (2 menit)

> "Setiap perubahan data tercatat di audit trail menggunakan owen-it/laravel-auditing."

**Demo:**
1. Buka **Edit Kategori** → ubah nama → submit
2. Gulir ke bawah ke bagian **History & Note**
3. Tampilkan kolom:
   - **Date**: tanggal/waktu perubahan
   - **Action**: badge Create/Update/Delete
   - **User**: siapa yang melakukan
   - **Nilai Lama**: data sebelum diubah
   - **Nilai Baru**: data sesudah diubah
   - **Note**: deskripsi otomatis (misal: "Budi Santoso memperbarui kolom: nama")

4. Buka **Create Produk** → audit trail kosong (belum ada riwayat)
5. Buka **Edit Produk** → audit trail terisi

> "Semua perubahan — create, update, delete — tercatat lengkap dengan siapa, kapan, dan apa yang berubah."

---

## 5. Topik 4: Excel Export/Import (3 menit)

### 5.1 Export
> "Data bisa di-export ke Excel dengan memilih kolom yang diinginkan."

**Demo:**
1. Buka halaman **Products**
2. Klik tombol **Export/Import**
3. Tab **Export** — muncul daftar kolom dengan checkbox
4. Pilih kolom: SKU, Nama, Harga, Stok
5. Klik **Export Excel**
6. File Excel langsung di-download
7. Buka file Excel — ada **kolom No**, **header berwarna**, **auto-width**

### 5.2 Import
> "Data juga bisa di-import dari file Excel dengan mapping kolom."

**Demo:**
1. Klik **Export/Import** → tab **Import**
2. Upload file Excel (.xlsx)
3. Preview: tampilkan header kolom dari file
4. Mapping: pilih kolom Excel → field aplikasi
5. Klik **Import Data**

### 5.3 Log Export/Import
> "Semua aktivitas export dan import tercatat di halaman Log."

**Demo:**
1. Buka sidebar → **Export/Import Logs**
2. Tampilkan tabel: User, Tipe, Entitas, Status, Waktu, Tombol Download

---

## 6. Fitur Tambahan (1 menit)

### PDF Upload/Download
> "Kategori, Produk, dan Transaksi Stok mendukung upload file PDF sebagai lampiran."

**Demo:**
1. Buka Detail Kategori → klik **Download Lampiran** → file PDF ter-download
2. Upload PDF baru di halaman Edit → validasi 100-500 KB

### UUID Primary Keys
> "Semua tabel menggunakan UUID sebagai primary key, bukan auto-increment integer."

### Soft Deletes
> "Data yang dihapus tidak benar-benar hilang dari database, hanya ditandai sebagai terhapus."

---

## 7. Closing (30 detik)

> "Aplikasi ini sudah memenuhi semua 4 topik requirement:
> 1. **Authentication** — login, register, approval, role-based access
> 2. **CRUD + Relationship** — Categories, Products, StockTransactions dengan relasi FK
> 3. **Audits** — audit trail lengkap untuk semua perubahan data
> 4. **Excel Export/Import** — export styled Excel, import dengan column mapping, log aktivitas
>
> Terima kasih."

---

## Credential Login

| Role | Email | Password |
|---|---|---|
| Administrator | administrator@inventori.test | password |
| Staff | staff@inventori.test | password |

## URL Aplikasi

| Halaman | URL |
|---|---|
| Landing Page | http://localhost:8000 |
| Login | http://localhost:8000/login |
| Register | http://localhost:8000/register |
| Dashboard | http://localhost:8000/dashboard |
| Categories | http://localhost:8000/categories |
| Products | http://localhost:8000/products |
| Stock Transactions | http://localhost:8000/stock-transactions |
| Users | http://localhost:8000/users |
| Roles | http://localhost:8000/roles |
| Pending Registrations | http://localhost:8000/admin/pending-registrations |
| Export/Import Logs | http://localhost:8000/export-import-logs |
