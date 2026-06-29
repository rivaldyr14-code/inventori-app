<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'

const props = defineProps({
    category: Object,
    audits: Array,
})

function statusBadge(isActive) {
    return isActive ? 'bg-success' : 'bg-secondary'
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0">
          <i class="bi bi-tag me-2 text-primary" aria-hidden="true"></i>
          Detail Kategori
        </h1>
        <div class="d-flex gap-2">
          <a :href="`/categories/${category.id}/edit`" class="btn btn-warning btn-sm" @click.prevent="router.get(`/categories/${category.id}/edit`)">
            <i class="bi bi-pencil me-1"></i>Edit
          </a>
          <a :href="`/categories`" class="btn btn-secondary btn-sm" @click.prevent="router.get('/categories')">
            <i class="bi bi-arrow-left me-1"></i>Kembali
          </a>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <table class="table table-bordered mb-0">
            <tr>
              <th class="bg-light" style="width: 200px;">Nama</th>
              <td>{{ category.name }}</td>
            </tr>
            <tr>
              <th class="bg-light">Deskripsi</th>
              <td>{{ category.description || '-' }}</td>
            </tr>
            <tr>
              <th class="bg-light">Status</th>
              <td><span class="badge" :class="statusBadge(category.is_active)">{{ category.is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
            </tr>
            <tr>
              <th class="bg-light">Jumlah Produk</th>
              <td>{{ category.products_count ?? '-' }}</td>
            </tr>
            <tr>
              <th class="bg-light">Lampiran</th>
              <td>
                <a v-if="category.attachment_path" :href="`/attachments/categories/${category.id}`" target="_blank" class="btn btn-sm btn-outline-info">
                  <i class="bi bi-paperclip me-1"></i>Download
                </a>
                <span v-else class="text-muted">Tidak ada lampiran</span>
              </td>
            </tr>
            <tr v-if="category.metadata && Object.keys(category.metadata).length">
              <th class="bg-light">Metadata</th>
              <td>
                <table class="table table-sm table-borderless mb-0">
                  <tr v-for="(value, key) in category.metadata" :key="key">
                    <td class="text-muted" style="width: 150px;">{{ key }}</td>
                    <td>{{ value }}</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <th class="bg-light">Tanggal Dibuat</th>
              <td>{{ new Date(category.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) }}</td>
            </tr>
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
