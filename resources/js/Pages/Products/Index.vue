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
    products: Object,
    categories: Array,
    filters: Object,
})

const columns = [
    { key: 'sku', label: 'SKU', sortable: true },
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'category', label: 'Kategori', sortable: false },
    { key: 'current_stock', label: 'Stok', sortable: true },
    { key: 'price', label: 'Harga', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Tanggal Dibuat', sortable: true },
    { key: 'actions', label: 'Aksi', sortable: false },
]

const filterFields = [
    { key: 'search', label: 'Pencarian', type: 'text' },
    { key: 'filter_category_id', label: 'Kategori', type: 'select', options: props.categories.map(c => ({ value: c.id, label: c.name })) },
    { key: 'filter_is_active', label: 'Status Aktif', type: 'select', options: [{ value: '1', label: 'Aktif' }, { value: '0', label: 'Tidak Aktif' }] },
]

const showExportImport = ref(false)
const showConfirmDelete = ref(false)
const deleteTarget = ref(null)

const exportFields = ['sku', 'name', 'category', 'price', 'current_stock', 'is_active', 'extra_data', 'description', 'created_at']

function handleFilterChange(filters) {
    router.get('/products', { ...filters, sort_by: props.filters.sort_by, sort_dir: props.filters.sort_dir }, { preserveState: true })
}

function handleSort(key) {
    const dir = props.filters.sort_by === key && props.filters.sort_dir === 'asc' ? 'desc' : 'asc'
    router.get('/products', { ...props.filters, sort_by: key, sort_dir: dir }, { preserveState: true })
}

function confirmDelete(product) {
    deleteTarget.value = product
    showConfirmDelete.value = true
}

function deleteProduct() {
    if (deleteTarget.value) {
        router.delete(`/products/${deleteTarget.value.id}`, {
            onSuccess: () => { showConfirmDelete.value = false; deleteTarget.value = null }
        })
    }
}

function statusBadge(isActive) {
    return isActive ? 'bg-success' : 'bg-secondary'
}

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price)
}

function deleteMessage(item) {
    return `Yakin ingin menghapus produk "${item?.name || ''}"?`
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0">
          <i class="bi bi-box-seam me-2 text-primary" aria-hidden="true"></i>
          Produk
        </h1>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-success btn-sm" @click="showExportImport = true"><i class="bi bi-file-earmark-excel me-1"></i>Export/Import</button>
          <a href="/products/create" class="btn btn-primary btn-sm" @click.prevent="router.get('/products/create')">
            <i class="bi bi-plus-lg me-1"></i>Tambah Produk
          </a>
        </div>
      </div>
      <FlashMessage />
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <SearchFilter :filters="filters" :filterFields="filterFields" @filter-change="handleFilterChange" />
          <div class="mt-3">
            <DataTable :columns="columns" :data="products.data" :sort-by="filters.sort_by" :sort-direction="filters.sort_dir" @sort="handleSort">
              <template #row="{ row }">
                <tr>
                  <td>{{ row.sku }}</td>
                  <td>{{ row.name }}</td>
                  <td>{{ row.category?.name || '-' }}</td>
                  <td>{{ row.current_stock }}</td>
                  <td>{{ formatPrice(row.price) }}</td>
                  <td><span class="badge" :class="statusBadge(row.is_active)">{{ row.is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                  <td>{{ new Date(row.created_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta' }) }}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a :href="`/products/${row.id}`" class="btn btn-outline-primary" @click.prevent="router.get(`/products/${row.id}`)"><i class="bi bi-eye"></i></a>
                      <a :href="`/products/${row.id}/edit`" class="btn btn-outline-warning" @click.prevent="router.get(`/products/${row.id}/edit`)"><i class="bi bi-pencil"></i></a>
                      <button class="btn btn-outline-danger" @click="confirmDelete(row)"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
              </template>
            </DataTable>
          </div>
          <div class="mt-3 d-flex justify-content-end">
            <Pagination :links="products.links" />
          </div>
        </div>
      </div>
      <ConfirmModal :show="showConfirmDelete" title="Hapus Produk" :message="deleteTarget ? deleteMessage(deleteTarget) : ''" @confirm="deleteProduct" @cancel="showConfirmDelete = false" />
      <ExportImportModal v-if="showExportImport" entity="products" entity-label="Produk" :available-fields="exportFields" @close="showExportImport = false" />
    </div>
  </AppLayout>
</template>
