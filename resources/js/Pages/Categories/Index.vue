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
    categories: Object,
    filters: Object,
})

const columns = [
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'description', label: 'Deskripsi', sortable: true },
    { key: 'products_count', label: 'Jumlah Produk', sortable: false },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Tanggal Dibuat', sortable: true },
    { key: 'actions', label: 'Aksi', sortable: false },
]

const filterFields = [
    { key: 'search', label: 'Pencarian', type: 'text' },
    { key: 'filter_is_active', label: 'Status Aktif', type: 'select', options: [
        { value: '1', label: 'Aktif' },
        { value: '0', label: 'Tidak Aktif' },
    ]},
]

const showExportImport = ref(false)
const showConfirmDelete = ref(false)
const deleteTarget = ref(null)

const exportFields = ['name', 'description', 'is_active', 'metadata', 'created_at']

function handleFilterChange(filters) {
    router.get('/categories', {
        ...filters,
        sort_by: props.filters.sort_by,
        sort_dir: props.filters.sort_dir,
    }, { preserveState: true })
}

function handleSort(key) {
    const dir = props.filters.sort_by === key && props.filters.sort_dir === 'asc' ? 'desc' : 'asc'
    router.get('/categories', {
        ...props.filters,
        sort_by: key,
        sort_dir: dir,
    }, { preserveState: true })
}

function confirmDelete(category) {
    deleteTarget.value = category
    showConfirmDelete.value = true
}

function deleteCategory() {
    if (deleteTarget.value) {
        router.delete(`/categories/${deleteTarget.value.id}`, {
            onSuccess: () => {
                showConfirmDelete.value = false
                deleteTarget.value = null
            }
        })
    }
}

function statusBadge(isActive) {
    return isActive ? 'bg-success' : 'bg-secondary'
}

function deleteMessage(category) {
    return `Yakin ingin menghapus kategori "${category?.name || ''}"?`
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0">
          <i class="bi bi-tags me-2 text-primary" aria-hidden="true"></i>
          Kategori
        </h1>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-success btn-sm" @click="showExportImport = true"><i class="bi bi-file-earmark-excel me-1"></i>Export/Import</button>
          <a :href="route('categories.create')" class="btn btn-primary btn-sm" @click.prevent="router.get('/categories/create')">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
            Tambah Kategori
          </a>
        </div>
      </div>

      <FlashMessage />

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <SearchFilter :filters="props.filters" :filterFields="filterFields" @filter-change="handleFilterChange" />

          <div class="mt-3">
            <DataTable
              :columns="columns"
              :data="categories.data"
              :sort-by="filters.sort_by"
              :sort-direction="filters.sort_dir"
              @sort="handleSort"
            >
              <template #row="{ row }">
                <tr>
                  <td>{{ row.name }}</td>
                  <td>{{ row.description || '-' }}</td>
                  <td>{{ row.products_count }}</td>
                  <td><span class="badge" :class="statusBadge(row.is_active)">{{ row.is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                  <td>{{ new Date(row.created_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta' }) }}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a :href="route('categories.show', row.id)" class="btn btn-outline-primary" @click.prevent="router.get(`/categories/${row.id}`)">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a :href="route('categories.edit', row.id)" class="btn btn-outline-warning" @click.prevent="router.get(`/categories/${row.id}/edit`)">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <button class="btn btn-outline-danger" @click="confirmDelete(row)">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </DataTable>
          </div>

          <div class="mt-3 d-flex justify-content-end">
            <Pagination :links="categories.links" />
          </div>
        </div>
      </div>

      <ConfirmModal
        :show="showConfirmDelete"
        title="Hapus Kategori"
        :message="deleteTarget ? deleteMessage(deleteTarget) : ''"
        @confirm="deleteCategory"
        @cancel="showConfirmDelete = false"
      />
      <ExportImportModal v-if="showExportImport" entity="categories" entity-label="Kategori" :available-fields="exportFields" @close="showExportImport = false" />
    </div>
  </AppLayout>
</template>
