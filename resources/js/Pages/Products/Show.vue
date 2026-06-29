<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'
import DataTable from '@/Components/Shared/DataTable.vue'
import Pagination from '@/Components/Shared/Pagination.vue'

const props = defineProps({
    product: Object,
    audits: Array,
    transactions: Object,
})

function statusBadge(isActive) {
    return isActive ? 'bg-success' : 'bg-secondary'
}

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)
}

const txnColumns = [
    { key: 'transaction_number', label: 'No. Transaksi', sortable: false },
    { key: 'type', label: 'Tipe', sortable: false },
    { key: 'quantity', label: 'Jumlah', sortable: false },
    { key: 'stock_before', label: 'Stok Sebelum', sortable: false },
    { key: 'stock_after', label: 'Stok Sesudah', sortable: false },
    { key: 'created_at', label: 'Tanggal', sortable: false },
]
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-box me-2 text-primary"></i>Detail Produk</h1>
        <div class="d-flex gap-2">
          <a :href="`/products/${product.id}/edit`" class="btn btn-warning btn-sm" @click.prevent="router.get(`/products/${product.id}/edit`)"><i class="bi bi-pencil me-1"></i>Edit</a>
          <a href="/products" class="btn btn-secondary btn-sm" @click.prevent="router.get('/products')"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <table class="table table-bordered mb-0">
            <tr><th class="bg-light" style="width:200px;">SKU</th><td>{{ product.sku }}</td></tr>
            <tr><th class="bg-light">Nama</th><td>{{ product.name }}</td></tr>
            <tr><th class="bg-light">Kategori</th><td>{{ product.category?.name || '-' }}</td></tr>
            <tr><th class="bg-light">Deskripsi</th><td>{{ product.description || '-' }}</td></tr>
            <tr><th class="bg-light">Harga</th><td>{{ formatPrice(product.price) }}</td></tr>
            <tr><th class="bg-light">Stok Saat Ini</th><td><strong>{{ product.current_stock }}</strong></td></tr>
            <tr><th class="bg-light">Status</th><td><span class="badge" :class="statusBadge(product.is_active)">{{ product.is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td></tr>
            <tr><th class="bg-light">Lampiran</th><td><a v-if="product.attachment_path" :href="`/attachments/products/${product.id}`" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-paperclip me-1"></i>Download</a><span v-else class="text-muted">Tidak ada</span></td></tr>
            <tr v-if="product.extra_data && Object.keys(product.extra_data).length">
              <th class="bg-light">Extra Data</th>
              <td>
                <table class="table table-sm table-borderless mb-0">
                  <tr v-for="(value, key) in product.extra_data" :key="key">
                    <td class="text-muted" style="width: 150px;">{{ key }}</td>
                    <td>{{ value }}</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr><th class="bg-light">Tanggal Dibuat</th><td>{{ new Date(product.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) }}</td></tr>
          </table>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <h5 class="mb-3"><i class="bi bi-arrow-left-right me-2"></i>Riwayat Transaksi Stok</h5>
          <DataTable :columns="txnColumns" :data="transactions?.data || []" :sort-by="null" :sort-direction="'desc'">
            <template #row="{ row }">
              <tr>
                <td>{{ row.transaction_number }}</td>
                <td><span class="badge" :class="row.type === 'in' ? 'bg-success' : 'bg-danger'">{{ row.type === 'in' ? 'Masuk' : 'Keluar' }}</span></td>
                <td>{{ row.quantity }}</td>
                <td>{{ row.stock_before }}</td>
                <td>{{ row.stock_after }}</td>
                <td>{{ new Date(row.created_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta' }) }}</td>
              </tr>
            </template>
          </DataTable>
          <div v-if="transactions" class="mt-2 d-flex justify-content-end">
            <Pagination :links="transactions.links" />
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <AuditTrail :audits="audits" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
