<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'

const props = defineProps({
    stockTransaction: Object,
    audits: Array,
})
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>Detail Transaksi Stok</h1>
        <div class="d-flex gap-2">
          <a :href="`/stock-transactions/${stockTransaction.id}/edit`" class="btn btn-warning btn-sm" @click.prevent="router.get(`/stock-transactions/${stockTransaction.id}/edit`)"><i class="bi bi-pencil me-1"></i>Edit</a>
          <a href="/stock-transactions" class="btn btn-secondary btn-sm" @click.prevent="router.get('/stock-transactions')"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <table class="table table-bordered mb-0">
            <tr><th class="bg-light" style="width:200px;">No. Transaksi</th><td>{{ stockTransaction.transaction_number }}</td></tr>
            <tr><th class="bg-light">Produk</th><td>{{ stockTransaction.product?.name || '(dihapus)' }}</td></tr>
            <tr><th class="bg-light">Tipe</th><td><span class="badge" :class="stockTransaction.type === 'in' ? 'bg-success' : 'bg-danger'">{{ stockTransaction.type === 'in' ? 'Masuk' : 'Keluar' }}</span></td></tr>
            <tr><th class="bg-light">Jumlah</th><td>{{ stockTransaction.quantity }}</td></tr>
            <tr><th class="bg-light">Status</th><td><span class="badge" :class="stockTransaction.is_active ? 'bg-success' : 'bg-secondary'">{{ stockTransaction.is_active ? 'Aktif' : 'Nonaktif' }}</span></td></tr>
            <tr><th class="bg-light">Stok Sebelum</th><td>{{ stockTransaction.stock_before }}</td></tr>
            <tr><th class="bg-light">Stok Sesudah</th><td>{{ stockTransaction.stock_after }}</td></tr>
            <tr><th class="bg-light">Catatan</th><td>{{ stockTransaction.notes || '-' }}</td></tr>
            <tr><th class="bg-light">Dibuat Oleh</th><td>{{ stockTransaction.createdBy?.name || '-' }}</td></tr>
            <tr><th class="bg-light">Lampiran</th><td><a v-if="stockTransaction.attachment_path" :href="`/attachments/stock-transactions/${stockTransaction.id}`" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-paperclip me-1"></i>Download</a><span v-else class="text-muted">Tidak ada</span></td></tr>
            <tr><th class="bg-light">Tanggal Transaksi</th><td>{{ new Date(stockTransaction.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) }}</td></tr>
          </table>
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
