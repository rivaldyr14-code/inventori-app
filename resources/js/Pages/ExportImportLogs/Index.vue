<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Pagination from '@/Components/Shared/Pagination.vue'

const props = defineProps({
    logs: Object,
})

function statusBadge(status) {
    const map = { pending: 'bg-warning text-dark', processing: 'bg-info', completed: 'bg-success', failed: 'bg-danger' }
    return map[status] || 'bg-secondary'
}

function jobTypeIcon(type) {
    return type === 'export' ? 'bi-upload' : 'bi-download'
}

function jobTypeLabel(type) {
    return type === 'export' ? 'Export' : 'Import'
}

function formatDate(dt) {
    if (!dt) return '-'
    return new Date(dt).toLocaleString('id-ID', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
        timeZone: 'Asia/Jakarta',
    })
}

function entityLabel(entity) {
    const map = {
        'categories': 'Kategori',
        'products': 'Produk',
        'stock-transactions': 'Transaksi Stok',
        'users': 'User',
        'roles': 'Role',
    }
    return map[entity] || entity
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Log Export/Import</h1>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col" style="width:4rem;">No</th>
                  <th scope="col">User</th>
                  <th scope="col">Tipe</th>
                  <th scope="col">Entitas</th>
                  <th scope="col">Status</th>
                  <th scope="col" class="text-center">Total</th>
                  <th scope="col" class="text-center">Sukses</th>
                  <th scope="col" class="text-center">Gagal</th>
                  <th scope="col">Waktu Mulai</th>
                  <th scope="col">Waktu Selesai</th>
                  <th scope="col">File</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!logs.data || logs.data.length === 0">
                  <td colspan="11" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                    Belum ada log export/import.
                  </td>
                </tr>
                <tr v-for="(log, index) in logs.data" :key="log.id">
                  <td class="text-muted">{{ (logs.current_page - 1) * logs.per_page + index + 1 }}</td>
                  <td>
                    <i class="bi bi-person-fill me-1 text-muted"></i>
                    {{ log.user?.name ?? 'Unknown' }}
                  </td>
                  <td>
                    <span class="badge" :class="log.job_type === 'export' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary'">
                      <i :class="['bi', jobTypeIcon(log.job_type)]" class="me-1"></i>
                      {{ jobTypeLabel(log.job_type) }}
                    </span>
                  </td>
                  <td>{{ entityLabel(log.entity) }}</td>
                  <td><span class="badge" :class="statusBadge(log.status)">{{ log.status }}</span></td>
                  <td class="text-center">{{ log.total_rows ?? '-' }}</td>
                  <td class="text-center">{{ log.success_rows ?? '-' }}</td>
                  <td class="text-center">{{ log.failed_rows ?? '-' }}</td>
                  <td>{{ formatDate(log.started_at) }}</td>
                  <td>{{ formatDate(log.completed_at) }}</td>
                  <td>
                    <a
                      v-if="log.job_type === 'export' && log.status === 'completed' && log.file_path"
                      :href="`/exports/${log.id}/download`"
                      class="btn btn-sm btn-outline-success"
                    >
                      <i class="bi bi-download me-1"></i>Download
                    </a>
                    <span v-else-if="log.status === 'failed'" class="text-danger small">
                      <i class="bi bi-exclamation-triangle me-1"></i>Gagal
                    </span>
                    <span v-else class="text-muted small">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-3 d-flex justify-content-end">
            <Pagination :links="logs.links" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
