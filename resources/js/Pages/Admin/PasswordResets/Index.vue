<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import FlashMessage from '@/Components/Shared/FlashMessage.vue'
import Pagination from '@/Components/Shared/Pagination.vue'

const props = defineProps({
    requests: Object,
    status: String,
})

function filterStatus(status) {
    router.get('/admin/password-resets', { status }, { preserveState: true })
}

function statusBadge(status) {
    return {
        pending: 'bg-warning text-dark',
        approved: 'bg-success',
        rejected: 'bg-danger',
    }[status] || 'bg-secondary'
}

function statusLabel(status) {
    return { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' }[status] || status
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <h1 class="h4 fw-bold mb-3"><i class="bi bi-key me-2 text-warning"></i>Reset Password Requests</h1>
      <FlashMessage />

      <div class="d-flex gap-2 mb-3">
        <button class="btn btn-sm" :class="status === 'pending' ? 'btn-warning' : 'btn-outline-warning'" @click="filterStatus('pending')">Menunggu</button>
        <button class="btn btn-sm" :class="status === 'approved' ? 'btn-success' : 'btn-outline-success'" @click="filterStatus('approved')">Disetujui</button>
        <button class="btn btn-sm" :class="status === 'rejected' ? 'btn-danger' : 'btn-outline-danger'" @click="filterStatus('rejected')">Ditolak</button>
        <button class="btn btn-sm" :class="status === 'all' ? 'btn-secondary' : 'btn-outline-secondary'" @click="filterStatus('all')">Semua</button>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>User</th>
                <th>Email</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="requests.data.length === 0">
                <td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td>
              </tr>
              <tr v-for="req in requests.data" :key="req.id">
                <td>{{ req.user?.name }}</td>
                <td>{{ req.user?.email }}</td>
                <td>{{ req.reason }}</td>
                <td><span class="badge" :class="statusBadge(req.status)">{{ statusLabel(req.status) }}</span></td>
                <td>{{ new Date(req.created_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</td>
                <td>
                  <a :href="`/admin/password-resets/${req.id}`" class="btn btn-sm btn-outline-primary" @click.prevent="router.get(`/admin/password-resets/${req.id}`)">
                    <i class="bi bi-eye"></i>
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="mt-3 d-flex justify-content-end"><Pagination :links="requests.links" /></div>
    </div>
  </AppLayout>
</template>
