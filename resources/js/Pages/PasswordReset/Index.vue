<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import FlashMessage from '@/Components/Shared/FlashMessage.vue'

const props = defineProps({
    requests: Array,
})

const form = useForm({
    reason: '',
})

const passwordForm = useForm({
    new_password: '',
    new_password_confirmation: '',
})

function submitRequest() {
    form.post('/password-reset-requests', {
        onSuccess: () => { form.reset() },
    })
}

function resetPassword(requestId) {
    passwordForm.post(`/password-reset-requests/${requestId}/reset`, {
        onSuccess: () => { passwordForm.reset() },
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

const approvedRequest = props.requests.find(r => r.status === 'approved' && !r.new_password)
const pendingRequest = props.requests.find(r => r.status === 'pending')
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <h1 class="h4 fw-bold mb-3"><i class="bi bi-key me-2 text-warning"></i>Reset Password</h1>
      <FlashMessage />

      <!-- 1. Ada approved → tampilkan form reset password -->
      <div v-if="approvedRequest" class="card border-0 shadow-sm mb-4 border-start border-4 border-success">
        <div class="card-header bg-white">
          <h6 class="mb-0 fw-semibold text-success"><i class="bi bi-check-circle me-1"></i>Permintaan Disetujui — Atur Password Baru</h6>
        </div>
        <div class="card-body">
          <div class="alert alert-success small mb-3">
            <i class="bi bi-info-circle me-1"></i>
            Administrator telah menyetujui permintaan Anda. Silakan masukkan password baru di bawah ini.
          </div>
          <form @submit.prevent="resetPassword(approvedRequest.id)">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                <input v-model="passwordForm.new_password" type="password" class="form-control" :class="{ 'is-invalid': passwordForm.errors.new_password }" placeholder="Minimal 8 karakter" required minlength="8" />
                <div v-if="passwordForm.errors.new_password" class="invalid-feedback">{{ passwordForm.errors.new_password }}</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                <input v-model="passwordForm.new_password_confirmation" type="password" class="form-control" placeholder="Ulangi password baru" required />
              </div>
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-success" :disabled="passwordForm.processing">
                <span v-if="passwordForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check-lg me-1"></i>
                {{ passwordForm.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- 2. Ada pending → tampilkan info menunggu -->
      <div v-else-if="pendingRequest" class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
        <div class="card-header bg-white">
          <h6 class="mb-0 fw-semibold text-warning"><i class="bi bi-hourglass-split me-1"></i>Permintaan Menunggu Persetujuan</h6>
        </div>
        <div class="card-body">
          <div class="alert alert-warning mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Permintaan Anda sedang menunggu persetujuan administrator. Mohon tunggu.
          </div>
        </div>
      </div>

      <!-- 3. Tidak ada aktif → tampilkan form permintaan baru -->
      <div v-else class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
          <h6 class="mb-0 fw-semibold">Ajukan Permintaan Reset Password</h6>
        </div>
        <div class="card-body">
          <p class="text-muted small">Jika Anda lupa password, ajukan permintaan ini. Administrator akan meninjau dan menyetujui atau menolak.</p>
          <form @submit.prevent="submitRequest">
            <div class="mb-3">
              <label class="form-label fw-semibold">Alasan Permintaan <span class="text-danger">*</span></label>
              <textarea v-model="form.reason" class="form-control" :class="{ 'is-invalid': form.errors.reason }" rows="3" placeholder="Jelaskan alasan Anda meminta reset password..." required></textarea>
              <div v-if="form.errors.reason" class="invalid-feedback">{{ form.errors.reason }}</div>
            </div>
            <button type="submit" class="btn btn-warning" :disabled="form.processing">
              <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-send me-1"></i>
              {{ form.processing ? 'Mengirim...' : 'Kirim Permintaan' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Riwayat -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
          <h6 class="mb-0 fw-semibold">Riwayat Permintaan</h6>
        </div>
        <div class="card-body p-0">
          <div v-if="requests.length === 0" class="text-center text-muted py-4">Belum ada permintaan.</div>
          <table v-else class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Catatan Admin</th>
                <th>Diproses</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="req in requests" :key="req.id">
                <td>{{ new Date(req.created_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</td>
                <td>{{ req.reason }}</td>
                <td><span class="badge" :class="statusBadge(req.status)">{{ statusLabel(req.status) }}</span></td>
                <td>{{ req.admin_note || '-' }}</td>
                <td>{{ req.resolved_at ? new Date(req.resolved_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short', year: 'numeric' }) : '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
