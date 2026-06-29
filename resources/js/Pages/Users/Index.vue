<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import DataTable from '@/Components/Shared/DataTable.vue'
import SearchFilter from '@/Components/Shared/SearchFilter.vue'
import Pagination from '@/Components/Shared/Pagination.vue'
import FlashMessage from '@/Components/Shared/FlashMessage.vue'
import ConfirmModal from '@/Components/Shared/ConfirmModal.vue'
import ExportImportModal from '@/Components/Shared/ExportImportModal.vue'

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
})

const columns = [
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Role', sortable: false },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Tanggal Dibuat', sortable: true },
    { key: 'actions', label: 'Aksi', sortable: false },
]

const filterFields = [
    { key: 'search', label: 'Pencarian', type: 'text' },
    { key: 'filter_role', label: 'Role', type: 'select', options: props.roles.map(r => ({ value: r.name, label: r.name })) },
]

const showExportImport = ref(false)
const showConfirmDelete = ref(false)
const deleteTarget = ref(null)

const exportFields = ['name', 'email', 'role', 'is_active', 'preferences', 'created_at']

function handleFilterChange(filters) {
    router.get('/users', { ...filters, sort_by: props.filters.sort_by, sort_dir: props.filters.sort_dir }, { preserveState: true })
}

function handleSort(key) {
    const dir = props.filters.sort_by === key && props.filters.sort_dir === 'asc' ? 'desc' : 'asc'
    router.get('/users', { ...props.filters, sort_by: key, sort_dir: dir }, { preserveState: true })
}

function confirmDelete(user) {
    deleteTarget.value = user
    showConfirmDelete.value = true
}

function deleteMessage(user) {
    return `Yakin ingin menghapus user "${user?.name || ''}"?`
}

function deleteUser() {
    if (deleteTarget.value) {
        router.delete(`/users/${deleteTarget.value.id}`, {
            onSuccess: () => { showConfirmDelete.value = false; deleteTarget.value = null }
        })
    }
}

function statusBadge(isActive) {
    return isActive ? 'bg-success' : 'bg-secondary'
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>User</h1>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-success btn-sm" @click="showExportImport = true"><i class="bi bi-file-earmark-excel me-1"></i>Export/Import</button>
          <a href="/users/create" class="btn btn-primary btn-sm" @click.prevent="router.get('/users/create')"><i class="bi bi-plus-lg me-1"></i>Tambah User</a>
        </div>
      </div>
      <FlashMessage />
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <SearchFilter :filters="filters" :filterFields="filterFields" @filter-change="handleFilterChange" />
          <div class="mt-3">
            <DataTable :columns="columns" :data="users.data" :sort-by="filters.sort_by" :sort-direction="filters.sort_dir" @sort="handleSort">
              <template #row="{ row }">
                <tr>
                  <td>{{ row.name }}</td>
                  <td>{{ row.email }}</td>
                  <td>{{ row.roles?.map(r => r.name).join(', ') || '-' }}</td>
                  <td><span class="badge" :class="statusBadge(row.is_active)">{{ row.is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                  <td>{{ new Date(row.created_at).toLocaleDateString('id-ID') }}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a :href="`/users/${row.id}`" class="btn btn-outline-primary" @click.prevent="router.get(`/users/${row.id}`)"><i class="bi bi-eye"></i></a>
                      <a :href="`/users/${row.id}/edit`" class="btn btn-outline-warning" @click.prevent="router.get(`/users/${row.id}/edit`)"><i class="bi bi-pencil"></i></a>
                      <button class="btn btn-outline-danger" @click="confirmDelete(row)"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
              </template>
            </DataTable>
          </div>
          <div class="mt-3 d-flex justify-content-end"><Pagination :links="users.links" /></div>
        </div>
      </div>
      <ConfirmModal :show="showConfirmDelete" title="Hapus User" :message="deleteTarget ? deleteMessage(deleteTarget) : ''" @confirm="deleteUser" @cancel="showConfirmDelete = false" />
      <ExportImportModal v-if="showExportImport" entity="users" entity-label="User" :available-fields="exportFields" @close="showExportImport = false" />
    </div>
  </AppLayout>
</template>
