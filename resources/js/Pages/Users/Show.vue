<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'

const props = defineProps({
    user: Object,
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
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-person me-2 text-primary"></i>Detail User</h1>
        <div class="d-flex gap-2">
          <a :href="`/users/${user.id}/edit`" class="btn btn-warning btn-sm" @click.prevent="router.get(`/users/${user.id}/edit`)"><i class="bi bi-pencil me-1"></i>Edit</a>
          <a href="/users" class="btn btn-secondary btn-sm" @click.prevent="router.get('/users')"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
      </div>
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <table class="table table-bordered mb-0">
            <tr><th class="bg-light" style="width:200px;">Nama</th><td>{{ user.name }}</td></tr>
            <tr><th class="bg-light">Email</th><td>{{ user.email }}</td></tr>
            <tr><th class="bg-light">Role</th><td>{{ user.roles?.map(r => r.name).join(', ') || '-' }}</td></tr>
            <tr><th class="bg-light">Status</th><td><span class="badge" :class="statusBadge(user.is_active)">{{ user.is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td></tr>
            <tr v-if="user.preferences && Object.keys(user.preferences).length">
              <th class="bg-light">Preferences</th>
              <td>
                <table class="table table-sm table-borderless mb-0">
                  <tr v-for="(value, key) in user.preferences" :key="key">
                    <td class="text-muted" style="width: 150px;">{{ key }}</td>
                    <td>{{ value }}</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr><th class="bg-light">Tanggal Dibuat</th><td>{{ new Date(user.created_at).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' }) }}</td></tr>
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
