<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post('/register')
}
</script>

<template>
    <GuestLayout title="Daftar Akun Baru">
        <div class="text-center mb-4">
            <p class="text-secondary small">
                Buat akun untuk mengakses sistem inventori.
                <br>Akun akan aktif setelah disetujui oleh administrator.
            </p>
        </div>

        <form @submit.prevent="submit">
            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.name }"
                    placeholder="Masukkan nama lengkap"
                    autofocus
                />
                <div v-if="form.errors.name" class="invalid-feedback">
                    {{ form.errors.name }}
                </div>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.email }"
                    placeholder="Masukkan email"
                />
                <div v-if="form.errors.email" class="invalid-feedback">
                    {{ form.errors.email }}
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.password }"
                    placeholder="Minimal 8 karakter"
                />
                <div v-if="form.errors.password" class="invalid-feedback">
                    {{ form.errors.password }}
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="form-control"
                    placeholder="Ulangi password"
                />
            </div>

            <!-- Info -->
            <div class="alert alert-info small mb-4">
                <i class="bi bi-info-circle me-1"></i>
                Setelah mendaftar, akun Anda akan berstatus <strong>menunggu persetujuan</strong>.
                Hubungi administrator untuk mengaktifkan akun.
            </div>

            <!-- Submit -->
            <button
                type="submit"
                class="btn btn-primary w-100 mb-3"
                :disabled="form.processing"
            >
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                {{ form.processing ? 'Mendaftar...' : 'Daftar' }}
            </button>
        </form>

        <!-- Login link -->
        <div class="text-center">
            <span class="text-secondary small">Sudah punya akun?</span>
            <Link href="/login" class="text-primary text-decoration-none fw-semibold small ms-1">
                Masuk
            </Link>
        </div>
    </GuestLayout>
</template>
