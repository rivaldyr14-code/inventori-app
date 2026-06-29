<script setup>
import { Link, router } from '@inertiajs/vue3'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'

const props = defineProps({
    products: Object,
    stats: Object,
})

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(price)
}

function stockStatus(stock) {
    if (stock <= 0) return { class: 'bg-danger', text: 'Habis' }
    if (stock <= 10) return { class: 'bg-warning text-dark', text: 'Rendah' }
    return { class: 'bg-success', text: 'Tersedia' }
}

function navigateToPage(url) {
    if (url) router.visit(url, { preserveState: true })
}
</script>

<template>
    <GuestLayout>
        <!-- Hero Section -->
        <div class="hero-section text-center">
            <div class="hero-badge mb-3">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">
                    Sistem Manajemen Inventori
                </span>
            </div>
            <h1 class="display-4 fw-bold text-body mb-3 hero-title">
                Inventori <span class="text-primary">App</span>
            </h1>
            <p class="lead text-secondary mb-4 mx-auto hero-subtitle">
                Kelola produk, kategori, dan transaksi stok secara efisien dan terpusat.
                Dilengkapi audit trail, export/import Excel, dan manajemen akses berbasis role.
            </p>
        </div>

        <!-- Features Section -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card text-center p-4 rounded-4 h-100">
                    <div class="feature-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-box-seam fs-3"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Produk & Kategori</h5>
                    <p class="text-secondary small mb-0">Kelola data produk dan kategori dengan mudah dan terstruktur</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card text-center p-4 rounded-4 h-100">
                    <div class="feature-icon bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-arrow-left-right fs-3"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Transaksi Stok</h5>
                    <p class="text-secondary small mb-0">Catat pergerakan stok masuk dan keluar secara real-time</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card text-center p-4 rounded-4 h-100">
                    <div class="feature-icon bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Audit Trail</h5>
                    <p class="text-secondary small mb-0">Riwayat lengkap setiap perubahan data yang tidak bisa diubah</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="feature-card text-center p-4 rounded-4 h-100">
                    <div class="feature-icon bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-file-earmark-excel fs-3"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Export & Import</h5>
                    <p class="text-secondary small mb-0">Proses data dalam jumlah besar via antrian (queue)</p>
                </div>
            </div>
        </div>

        <!-- Stock Overview Section -->
        <section class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="fw-bold text-body mb-1">
                        <i class="bi bi-box-seam me-2 text-primary"></i>Stok Produk
                    </h3>
                    <p class="text-secondary small mb-0">
                        Total {{ stats.total_products }} produk aktif dalam sistem
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 110px;">SKU</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th class="text-center" style="width: 90px;">Stok</th>
                                    <th class="text-end" style="width: 130px;">Harga</th>
                                    <th class="text-center" style="width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!products?.data?.length">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        Belum ada data produk.
                                    </td>
                                </tr>
                                <tr v-for="product in products.data" :key="product.id">
                                    <td><code class="text-secondary">{{ product.sku }}</code></td>
                                    <td class="fw-medium">{{ product.name }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ product.category?.name || '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold">
                                        {{ product.current_stock }}
                                    </td>
                                    <td class="text-end">{{ formatPrice(product.price) }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill"
                                              :class="stockStatus(product.current_stock).class">
                                            {{ stockStatus(product.current_stock).text }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="products?.links?.length > 3"
                     class="card-footer bg-white border-top d-flex justify-content-end">
                    <nav aria-label="Navigasi halaman stok">
                        <ul class="pagination pagination-sm mb-0">
                            <li v-for="(link, i) in products.links" :key="i"
                                class="page-item"
                                :class="{ active: link.active, disabled: !link.url }">
                                <button class="page-link"
                                        :disabled="!link.url"
                                        @click="navigateToPage(link.url)"
                                        v-html="link.label" />
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Tech Stack -->
        <div class="text-center mb-5">
            <h5 class="text-secondary fw-semibold mb-3">Dibangun dengan</h5>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Laravel 11</span>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Vue 3</span>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Inertia.js</span>
                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">MySQL</span>
                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">Bootstrap 5</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Spatie RBAC</span>
            </div>
        </div>

        <!-- Login Hint -->
        <div class="text-center mt-4 pt-4 border-top">
            <p class="text-muted small mb-2">
                <i class="bi bi-info-circle me-1"></i>
                Gunakan akun yang telah terdaftar dan disetujui untuk masuk ke aplikasi.
            </p>
            <div class="d-flex gap-3 justify-content-center">
                <Link href="/register" class="text-primary text-decoration-none fw-semibold">
                    <i class="bi bi-person-plus me-1"></i>Daftar Akun Baru
                </Link>
                <Link href="/login" class="text-secondary text-decoration-none fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
.hero-section {
    padding: 2rem 0 1rem;
}

.hero-title {
    font-size: 2.8rem;
    font-weight: 800;
    letter-spacing: -0.5px;
}

.hero-subtitle {
    max-width: 520px;
    font-size: 1.05rem;
    line-height: 1.7;
}

.feature-card {
    background: #fff;
    border: 1px solid #e9ecef;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}
</style>
