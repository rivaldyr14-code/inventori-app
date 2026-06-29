<script setup>
import { router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import DataTable from '@/Components/Shared/DataTable.vue'
import SearchFilter from '@/Components/Shared/SearchFilter.vue'
import Pagination from '@/Components/Shared/Pagination.vue'
import FlashMessage from '@/Components/Shared/FlashMessage.vue'
import ConfirmModal from '@/Components/Shared/ConfirmModal.vue'
import ExportImportModal from '@/Components/Shared/ExportImportModal.vue'

const page = usePage()
const isAdmin = computed(() =>
    Array.isArray(page.props.auth?.user?.roles) && page.props.auth.user.roles.includes('Administrator')
)

const props = defineProps({
    roles: Object,
    filters: Object,
})

const columns = [
    { key: 'name', label: 'Nama Role', sortable: true },
    { key: 'guard_name', label: 'Guard', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'users_count', label: 'Jumlah User', sortable: false },
    { key: 'created_at', label: 'Tanggal Dibuat', sortable: true },
    { key: 'actions', label: 'Aksi', sortable: false },
]

const filterFields = [
    { key: 'search', label: 'Pencarian', type: 'text' },
]

const showExportImport = ref(false)
const showConfirmDelete = ref(false)
const deleteTarget = ref(null)

const exportFields = ['name', 'guard_name', 'is_active', 'settings', 'created_at']

function handleFilterChange(filters) {
    router.get('/roles', { ...filters, sort_by: props.filters.sort_by, sort_dir: props.filters.sort_dir }, { preserveState: true })
}

function handleSort(key) {
    const dir = props.filters.sort_by === key && props.filters.sort_dir === 'asc' ? 'desc' : 'asc'
    router.get('/roles', { ...props.filters, sort_by: key, sort_dir: dir }, { preserveState: true })
}

function confirmDelete(role) {
    deleteTarget.value = role
    showConfirmDelete.value = true
}

function deleteMessage(role) {
    return `Yakin ingin menghapus role "${role?.name || ''}"?`
}

function deleteRole() {
    if (deleteTarget.value) {
        router.delete(`/roles/${deleteTarget.value.id}`, {
            onSuccess: () => { showConfirmDelete.value = false; deleteTarget.value = null }
        })
    }
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Role</h1>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-success btn-sm" @click="showExportImport = true"><i class="bi bi-file-earmark-excel me-1"></i>Export/Import</button>
          <a v-if="isAdmin" href="/roles/create" class="btn btn-primary btn-sm" @click.prevent="router.get('/roles/create')"><i class="bi bi-plus-lg me-1"></i>Tambah Role</a>
        </div>
      </div>
      <FlashMessage />
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <SearchFilter :filters="filters" :filterFields="filterFields" @filter-change="handleFilterChange" />
          <div class="mt-3">
            <DataTable :columns="columns" :data="roles.data" :sort-by="filters.sort_by" :sort-direction="filters.sort_dir" @sort="handleSort">
              <template #row="{ row }">
                <tr>
                  <td>{{ row.name }}</td>
                  <td>{{ row.guard_name }}</td>
                  <td><span class="badge" :class="row.is_active ? 'bg-success' : 'bg-secondary'">{{ row.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                  <td>{{ row.users_count }}</td>
                  <td>{{ new Date(row.created_at).toLocaleDateString('id-ID') }}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a :href="`/roles/${row.id}`" class="btn btn-outline-primary" @click.prevent="router.get(`/roles/${row.id}`)"><i class="bi bi-eye"></i></a>
                      <template v-if="isAdmin">
                        <a :href="`/roles/${row.id}/edit`" class="btn btn-outline-warning" @click.prevent="router.get(`/roles/${row.id}/edit`)"><i class="bi bi-pencil"></i></a>
                        <button class="btn btn-outline-danger" @click="confirmDelete(row)"><i class="bi bi-trash"></i></button>
                      </template>
                    </div>
                  </td>
                </tr>
              </template>
            </DataTable>
          </div>
          <div class="mt-3 d-flex justify-content-end"><Pagination :links="roles.links" /></div>
        </div>
      </div>
      <ConfirmModal :show="showConfirmDelete" title="Hapus Role" :message="deleteTarget ? deleteMessage(deleteTarget) : ''" @confirm="deleteRole" @cancel="showConfirmDelete = false" />
      <ExportImportModal v-if="showExportImport" entity="roles" entity-label="Role" :available-fields="exportFields" @close="showExportImport = false" />
    </div>
  </AppLayout>
</template>
