<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import FlashMessage from '@/Components/Shared/FlashMessage.vue'

const props = defineProps({
    request: Object,
})

const approveForm = useForm({
    admin_note: '',
})

const rejectForm = useForm({
    admin_note: '',
})

function approve() {
    approveForm.post(`/admin/password-resets/${props.request.id}/approve`, {
        onSuccess: () => { approveForm.reset() },
    })
}

function reject() {
    rejectForm.post(`/admin/password-resets/${props.request.id}/reject`, {
        onSuccess: () => { rejectForm.reset() },
    })
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
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-key me-2 text-warning"></i>Detail Reset Password</h1>
        <a href="/admin/password-resets" class="btn btn-sm btn-outline-secondary" @click.prevent="router.get('/admin/password-resets')"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
      </div>
      <FlashMessage />

      <div class="row">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
              <h6 class="mb-0 fw-semibold">Informasi Permintaan</h6>
            </div>
            <div class="card-body">
              <table class="table table-borderless mb-0">
                <tr><th class="text-muted" style="width:140px">User</th><td>{{ request.user?.name }}</td></tr>
                <tr><th class="text-muted">Email</th><td>{{ request.user?.email }}</td></tr>
                <tr><th class="text-muted">Alasan</th><td>{{ request.reason }}</td></tr>
                <tr><th class="text-muted">Status</th><td><span class="badge" :class="statusBadge(request.status)">{{ statusLabel(request.status) }}</span></td></tr>
                <tr><th class="text-muted">Diajukan</th><td>{{ new Date(request.created_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</td></tr>
                <tr v-if="request.approver"><th class="text-muted">Diproses Oleh</th><td>{{ request.approver?.name }}</td></tr>
                <tr v-if="request.admin_note"><th class="text-muted">Catatan Admin</th><td>{{ request.admin_note }}</td></tr>
                <tr v-if="request.resolved_at"><th class="text-muted">Diproses</th><td>{{ new Date(request.resolved_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</td></tr>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-6" v-if="request.status === 'pending'">
          <div class="card border-0 shadow-sm mb-4 border-start border-4 border-success">
            <div class="card-header bg-white">
              <h6 class="mb-0 fw-semibold text-success"><i class="bi bi-check-circle me-1"></i>Setujui Permintaan</h6>
            </div>
            <div class="card-body">
              <div class="alert alert-info small mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Jika disetujui, user dapat mengatur password baru sendiri.
              </div>
              <form @submit.prevent="approve">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Catatan (opsional)</label>
                  <textarea v-model="approveForm.admin_note" class="form-control" rows="2" placeholder="Catatan untuk user..."></textarea>
                </div>
                <button type="submit" class="btn btn-success" :disabled="approveForm.processing">
                  <span v-if="approveForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-check-lg me-1"></i>
                  {{ approveForm.processing ? 'Memproses...' : 'Setujui' }}
                </button>
              </form>
            </div>
          </div>

          <div class="card border-0 shadow-sm mb-4 border-start border-4 border-danger">
            <div class="card-header bg-white">
              <h6 class="mb-0 fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i>Tolak Permintaan</h6>
            </div>
            <div class="card-body">
              <form @submit.prevent="reject">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
                  <textarea v-model="rejectForm.admin_note" class="form-control" rows="2" placeholder="Jelaskan alasan penolakan..." required></textarea>
                  <div v-if="rejectForm.errors.admin_note" class="text-danger small mt-1">{{ rejectForm.errors.admin_note }}</div>
                </div>
                <button type="submit" class="btn btn-danger" :disabled="rejectForm.processing">
                  <span v-if="rejectForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-x-lg me-1"></i>
                  {{ rejectForm.processing ? 'Memproses...' : 'Tolak Permintaan' }}
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="col-md-6" v-else>
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
              <h6 class="mb-0 fw-semibold">Status</h6>
            </div>
            <div class="card-body">
              <div v-if="request.status === 'approved'" class="alert alert-success mb-0">
                <i class="bi bi-check-circle me-1"></i>
                Permintaan disetujui. User sedang mengatur password baru.
              </div>
              <div v-else class="alert alert-danger mb-0">
                <i class="bi bi-x-circle me-1"></i>
                Permintaan ditolak.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
