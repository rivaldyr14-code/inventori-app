<script setup>
import { useForm, usePage, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'

const props = defineProps({
    status: String,
    request: Object,
    email: String,
})

const page = usePage()

const step = ref(props.request ? 'status' : 'check')
const activeEmail = ref(props.email || '')

const checkForm = useForm({
    email: '',
})

const requestForm = useForm({
    email: '',
    reason: '',
})

const resetForm = useForm({
    new_password: '',
    new_password_confirmation: '',
})

function checkStatus() {
    checkForm.post('/forgot-password/check', {
        onFinish: () => {
            if (props.request) {
                activeEmail.value = checkForm.email || props.email
                step.value = 'status'
            }
        },
    })
}

function submitRequest() {
    requestForm.email = activeEmail.value
    requestForm.post('/forgot-password')
}

function doReset() {
    resetForm.post(`/forgot-password/${props.request.id}/reset`)
}
</script>

<template>
    <GuestLayout title="Lupa Password">
        <!-- Flash -->
        <div v-if="page.props.flash?.success" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ page.props.flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
        <div v-if="page.props.flash?.error" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ page.props.flash.error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>

        <!-- STEP 1: Cek Status (default) -->
        <div v-if="step === 'check'">
            <div class="alert alert-info small">
                <i class="bi bi-info-circle me-1"></i>
                Masukkan email akun Anda untuk mengecek status permintaan reset password.
            </div>

            <form @submit.prevent="checkStatus" novalidate>
                <div class="mb-4">
                    <label for="email" class="form-label">
                        Email Akun <span class="text-danger">*</span>
                    </label>
                    <input
                        id="email"
                        v-model="checkForm.email"
                        type="email"
                        class="form-control"
                        :class="{ 'is-invalid': checkForm.errors.email }"
                        placeholder="nama@contoh.com"
                        required
                        autofocus
                    />
                    <div v-if="checkForm.errors.email" class="invalid-feedback">
                        {{ checkForm.errors.email }}
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary" :disabled="checkForm.processing">
                        <span v-if="checkForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                        {{ checkForm.processing ? 'Mengecek...' : 'Cek Status' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- STEP 2: Status ditemukan -->
        <div v-if="step === 'status' && request">
            <!-- Pending -->
            <div v-if="status === 'pending'" class="card border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body">
                    <h6 class="fw-semibold text-warning"><i class="bi bi-hourglass-split me-1"></i>Menunggu Persetujuan</h6>
                    <p class="text-muted small mb-0">Permintaan reset password Anda sedang menunggu persetujuan administrator. Mohon tunggu.</p>
                </div>
            </div>

            <!-- Rejected -->
            <div v-if="status === 'rejected'" class="card border-0 shadow-sm border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="fw-semibold text-danger"><i class="bi bi-x-circle me-1"></i>Permintaan Ditolak</h6>
                    <p class="text-muted small mb-2">Alasan: {{ request.admin_note }}</p>
                    <button class="btn btn-sm btn-outline-primary" @click="step = 'new-request'">
                        <i class="bi bi-plus-lg me-1"></i>Ajukan Permintaan Baru
                    </button>
                </div>
            </div>

            <!-- Approved — belum reset -->
            <div v-if="status === 'approved' && !request.new_password" class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i>Permintaan Disetujui</h6>
                    <p class="text-muted small mb-3">Silakan atur password baru Anda.</p>
                    <form @submit.prevent="doReset">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                            <input v-model="resetForm.new_password" type="password" class="form-control" :class="{ 'is-invalid': resetForm.errors.new_password }" placeholder="Minimal 8 karakter" required minlength="8" />
                            <div v-if="resetForm.errors.new_password" class="invalid-feedback">{{ resetForm.errors.new_password }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input v-model="resetForm.new_password_confirmation" type="password" class="form-control" placeholder="Ulangi password baru" required />
                        </div>
                        <button type="submit" class="btn btn-success" :disabled="resetForm.processing">
                            <span v-if="resetForm.processing" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-check-lg me-1"></i>
                            {{ resetForm.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Approved — sudah reset -->
            <div v-if="status === 'approved' && request.new_password" class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-body">
                    <h6 class="fw-semibold text-success"><i class="bi bi-check-circle me-1"></i>Password Berhasil Diubah</h6>
                    <p class="text-muted small mb-3">Silakan login dengan password baru Anda.</p>
                    <Link href="/login" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login Sekarang
                    </Link>
                </div>
            </div>
        </div>

        <!-- STEP 3: Ajukan permintaan baru -->
        <div v-if="step === 'new-request'">
            <div class="alert alert-info small">
                <i class="bi bi-info-circle me-1"></i>
                Ajukan permintaan reset password baru untuk email <strong>{{ activeEmail }}</strong>.
            </div>

            <form @submit.prevent="submitRequest" novalidate>
                <div class="mb-3">
                    <label for="reason" class="form-label">
                        Alasan Permintaan <span class="text-danger">*</span>
                    </label>
                    <textarea
                        id="reason"
                        v-model="requestForm.reason"
                        class="form-control"
                        :class="{ 'is-invalid': requestForm.errors.reason }"
                        rows="3"
                        placeholder="Jelaskan alasan Anda meminta reset password..."
                        required
                    ></textarea>
                    <div v-if="requestForm.errors.reason" class="invalid-feedback">
                        {{ requestForm.errors.reason }}
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-warning" :disabled="requestForm.processing">
                        <span v-if="requestForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                        {{ requestForm.processing ? 'Mengirim...' : 'Kirim Permintaan' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="text-center mt-3">
            <Link href="/login" class="text-primary text-decoration-none fw-semibold small">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
            </Link>
        </div>
    </GuestLayout>
</template>
