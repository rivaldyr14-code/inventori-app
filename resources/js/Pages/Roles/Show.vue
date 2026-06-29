<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'

const page = usePage()
const isAdmin = computed(() =>
    Array.isArray(page.props.auth?.user?.roles) && page.props.auth.user.roles.includes('Administrator')
)

const props = defineProps({
    role: Object,
    audits: Array,
    usersCount: Number,
})
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-shield me-2 text-primary"></i>Detail Role</h1>
        <div class="d-flex gap-2">
          <a v-if="isAdmin" :href="`/roles/${role.id}/edit`" class="btn btn-warning btn-sm" @click.prevent="router.get(`/roles/${role.id}/edit`)"><i class="bi bi-pencil me-1"></i>Edit</a>
          <a href="/roles" class="btn btn-secondary btn-sm" @click.prevent="router.get('/roles')"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
      </div>
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <table class="table table-bordered mb-0">
            <tr><th class="bg-light" style="width:200px;">Nama Role</th><td>{{ role.name }}</td></tr>
            <tr><th class="bg-light">Guard</th><td>{{ role.guard_name }}</td></tr>
            <tr><th class="bg-light">Status</th><td><span class="badge" :class="role.is_active ? 'bg-success' : 'bg-secondary'">{{ role.is_active ? 'Aktif' : 'Nonaktif' }}</span></td></tr>
            <tr><th class="bg-light">Jumlah User</th><td>{{ usersCount }}</td></tr>
            <tr v-if="role.settings && Object.keys(role.settings).length">
              <th class="bg-light">Settings</th>
              <td>
                <table class="table table-sm table-borderless mb-0">
                  <tr v-for="(value, key) in role.settings" :key="key">
                    <td class="text-muted" style="width: 150px;">{{ key }}</td>
                    <td>{{ value }}</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr><th class="bg-light">Tanggal Dibuat</th><td>{{ new Date(role.created_at).toLocaleString('id-ID') }}</td></tr>
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
