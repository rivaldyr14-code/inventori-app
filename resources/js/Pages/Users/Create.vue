<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import TomSelectInput from '@/Components/Shared/TomSelectInput.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'

const props = defineProps({
    roles: Array,
})

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    is_active: true,
})

function submit() {
    form.transform((data) => ({
        ...data,
        preferences: JSON.stringify({
            language: 'id',
            theme: 'light',
        }),
    })).post('/users')
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Tambah User</h1>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form @submit.prevent="submit" novalidate>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                <input id="name" v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" />
                <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input id="email" v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.email }" />
                <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <input id="password" v-model="form.password" type="password" class="form-control" :class="{ 'is-invalid': form.errors.password }" />
                <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                <TomSelectInput
                  url="/api/roles/search"
                  v-model="form.role"
                  placeholder="Cari role..."
                  value-field="name"
                  :class="{ 'is-invalid': form.errors.role }"
                />
                <div v-if="form.errors.role" class="invalid-feedback">{{ form.errors.role }}</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="is_active" class="form-label">Status</label>
                <select id="is_active" v-model="form.is_active" class="form-select" :class="{ 'is-invalid': form.errors.is_active }">
                  <option :value="true">Aktif</option>
                  <option :value="false">Tidak Aktif</option>
                </select>
                <div v-if="form.errors.is_active" class="invalid-feedback">{{ form.errors.is_active }}</div>
              </div>
            </div>
            <div class="mt-4 d-flex gap-2">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>{{ form.processing ? 'Menyimpan...' : 'Simpan' }}
              </button>
              <button type="button" class="btn btn-secondary" @click="router.get('/users')">Batal</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
          <AuditTrail :audits="[]" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
