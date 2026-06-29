<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'

const form = useForm({
    name: '',
    is_active: true,
})

function submit() {
    form.transform((data) => ({
        ...data,
        settings: JSON.stringify({
            theme: 'light',
            notifications: true,
        }),
    })).post('/roles')
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Role</h1>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form @submit.prevent="submit" novalidate>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="name" class="form-label">Nama Role <span class="text-danger">*</span></label>
                <input id="name" v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" />
                <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
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
              <button type="button" class="btn btn-secondary" @click="router.get('/roles')">Batal</button>
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
