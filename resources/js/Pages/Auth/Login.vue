<script setup>
import { useForm, usePage, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'

const page = usePage()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <GuestLayout title="Login">
        <!-- Flash messages -->
        <div
            v-if="page.props.flash?.success"
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >
            {{ page.props.flash.success }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
        <div
            v-if="page.props.flash?.error"
            class="alert alert-danger alert-dismissible fade show"
            role="alert"
        >
            {{ page.props.flash.error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>

        <form @submit.prevent="submit" novalidate>
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">
                    Email <span class="text-danger">*</span>
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.email }"
                    placeholder="nama@contoh.com"
                    autocomplete="email"
                    required
                    autofocus
                />
                <div v-if="form.errors.email" class="invalid-feedback">
                    {{ form.errors.email }}
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">
                    Password <span class="text-danger">*</span>
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.password }"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                />
                <div v-if="form.errors.password" class="invalid-feedback">
                    {{ form.errors.password }}
                </div>
            </div>

            <!-- Remember me -->
            <div class="mb-4 form-check">
                <input
                    id="remember"
                    v-model="form.remember"
                    type="checkbox"
                    class="form-check-input"
                />
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>

            <!-- Submit -->
            <div class="d-grid">
                <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="form.processing"
                >
                    <span
                        v-if="form.processing"
                        class="spinner-border spinner-border-sm me-2"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    {{ form.processing ? 'Memproses...' : 'Login' }}
                </button>
            </div>
        </form>

        <!-- Register link -->
        <div class="text-center mt-3">
            <span class="text-secondary small">Belum punya akun?</span>
            <Link href="/register" class="text-primary text-decoration-none fw-semibold small ms-1">
                Daftar
            </Link>
        </div>
    </GuestLayout>
</template>
