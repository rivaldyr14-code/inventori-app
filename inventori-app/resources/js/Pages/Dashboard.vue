<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue'
import FlashMessage from '@/Components/Shared/FlashMessage.vue'

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalProducts: 0,
            totalCategories: 0,
            totalTransactionsToday: 0,
            totalStockValue: 0,
        }),
    },
})

/**
 * Format a number as Indonesian Rupiah currency.
 */
function formatRupiah(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value)
}
</script>

<template>
    <AppLayout>
        <div class="container-fluid">
            <!-- Page heading -->
            <div class="d-flex align-items-center mb-4">
                <h1 class="h4 fw-bold mb-0">
                    <i class="bi bi-speedometer2 me-2 text-primary" aria-hidden="true"></i>
                    Dashboard
                </h1>
            </div>

            <!-- Flash messages (standalone reusable component) -->
            <FlashMessage />

            <!-- Stats cards grid -->
            <div class="row g-3 mb-4">

                <!-- Total Produk Aktif -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div
                                class="stats-icon rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                aria-hidden="true"
                            >
                                <i class="bi bi-box-seam fs-3 text-primary"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-body-secondary small mb-1">Total Produk Aktif</p>
                                <p class="h3 fw-bold mb-0 text-body">
                                    {{ stats.totalProducts.toLocaleString('id-ID') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Kategori Aktif -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div
                                class="stats-icon rounded-3 bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                aria-hidden="true"
                            >
                                <i class="bi bi-tags fs-3 text-success"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-body-secondary small mb-1">Total Kategori Aktif</p>
                                <p class="h3 fw-bold mb-0 text-body">
                                    {{ stats.totalCategories.toLocaleString('id-ID') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaksi Stok Hari Ini -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div
                                class="stats-icon rounded-3 bg-warning bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                aria-hidden="true"
                            >
                                <i class="bi bi-arrow-left-right fs-3 text-warning"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-body-secondary small mb-1">Transaksi Stok Hari Ini</p>
                                <p class="h3 fw-bold mb-0 text-body">
                                    {{ stats.totalTransactionsToday.toLocaleString('id-ID') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Nilai Stok -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div
                                class="stats-icon rounded-3 bg-info bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                aria-hidden="true"
                            >
                                <i class="bi bi-currency-dollar fs-3 text-info"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-body-secondary small mb-1">Total Nilai Stok</p>
                                <p class="h4 fw-bold mb-0 text-body text-truncate">
                                    {{ formatRupiah(stats.totalStockValue) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Quick info / welcome card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-1">Selamat Datang di Inventori App</h5>
                    <p class="card-text text-body-secondary mb-0">
                        Gunakan menu di sebelah kiri untuk mengelola kategori, produk, transaksi stok, role, dan pengguna.
                        Statistik di atas menampilkan data real-time dari database.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.stats-icon {
    width: 56px;
    height: 56px;
}
</style>
