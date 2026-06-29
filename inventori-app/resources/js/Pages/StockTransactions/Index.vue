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
    transactions: Object,
    products: Array,
    filters: Object,
})

const columns = [
    { key: 'transaction_number', label: 'No. Transaksi', sortable: true },
    { key: 'product', label: 'Produk', sortable: false },
    { key: 'type', label: 'Tipe', sortable: true },
    { key: 'quantity', label: 'Jumlah', sortable: true },
    { key: 'stock_before', label: 'Stok Sebelum', sortable: true },
    { key: 'stock_after', label: 'Stok Sesudah', sortable: true },
    { key: 'created_at', label: 'Tanggal', sortable: true },
    { key: 'actions', label: 'Aksi', sortable: false },
]

const filterFields = [
    { key: 'search', label: 'Pencarian', type: 'text' },
    { key: 'filter_type', label: 'Tipe', type: 'select', options: [{ value: 'in', label: 'Masuk' }, { value: 'out', label: 'Keluar' }] },
    { key: 'filter_product_id', label: 'Produk', type: 'select', options: props.products.map(p => ({ value: p.id, label: `${p.name} (${p.sku})` })) },
    { key: 'filter_date_from', label: 'Tanggal Awal', type: 'date' },
    { key: 'filter_date_to', label: 'Tanggal Akhir', type: 'date' },
]

const showExportImport = ref(false)
const showConfirmDelete = ref(false)
const deleteTarget = ref(null)

const exportFields = ['transaction_number', 'product', 'type', 'quantity', 'is_active', 'stock_before', 'stock_after', 'notes', 'created_by', 'created_at']

function handleFilterChange(filters) {
    router.get('/stock-transactions', { ...filters, sort_by: props.filters.sort_by, sort_dir: props.filters.sort_dir }, { preserveState: true })
}

function handleSort(key) {
    const dir = props.filters.sort_by === key && props.filters.sort_dir === 'asc' ? 'desc' : 'asc'
    router.get('/stock-transactions', { ...props.filters, sort_by: key, sort_dir: dir }, { preserveState: true })
}

function confirmDelete(txn) {
    deleteTarget.value = txn
    showConfirmDelete.value = true
}

function deleteTransaction() {
    if (deleteTarget.value) {
        router.delete(`/stock-transactions/${deleteTarget.value.id}`, {
            onSuccess: () => { showConfirmDelete.value = false; deleteTarget.value = null }
        })
    }
}

function typeBadge(type) {
    return type === 'in' ? 'bg-success' : 'bg-danger'
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Transaksi Stok</h1>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-success btn-sm" @click="showExportImport = true"><i class="bi bi-file-earmark-excel me-1"></i>Export/Import</button>
          <a href="/stock-transactions/create" class="btn btn-primary btn-sm" @click.prevent="router.get('/stock-transactions/create')"><i class="bi bi-plus-lg me-1"></i>Tambah Transaksi</a>
        </div>
      </div>
      <FlashMessage />
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <SearchFilter :filters="filters" :filterFields="filterFields" @filter-change="handleFilterChange" />
          <div class="mt-3">
            <DataTable :columns="columns" :data="transactions.data" :sort-by="filters.sort_by" :sort-direction="filters.sort_dir" @sort="handleSort">
              <template #row="{ row }">
                <tr>
                  <td>{{ row.transaction_number }}</td>
                  <td>{{ row.product?.name || '(dihapus)' }}</td>
                  <td><span class="badge" :class="typeBadge(row.type)">{{ row.type === 'in' ? 'Masuk' : 'Keluar' }}</span></td>
                  <td>{{ row.quantity }}</td>
                  <td>{{ row.stock_before }}</td>
                  <td>{{ row.stock_after }}</td>
                  <td>{{ new Date(row.created_at).toLocaleDateString('id-ID') }}</td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a :href="`/stock-transactions/${row.id}`" class="btn btn-outline-primary" @click.prevent="router.get(`/stock-transactions/${row.id}`)"><i class="bi bi-eye"></i></a>
                      <a :href="`/stock-transactions/${row.id}/edit`" class="btn btn-outline-warning" @click.prevent="router.get(`/stock-transactions/${row.id}/edit`)"><i class="bi bi-pencil"></i></a>
                      <button class="btn btn-outline-danger" @click="confirmDelete(row)"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
              </template>
            </DataTable>
          </div>
          <div class="mt-3 d-flex justify-content-end"><Pagination :links="transactions.links" /></div>
        </div>
      </div>
      <ConfirmModal :show="showConfirmDelete" title="Hapus Transaksi" :message="`Yakin ingin menghapus transaksi ${deleteTarget?.transaction_number || ''}?`" @confirm="deleteTransaction" @cancel="showConfirmDelete = false" />
      <ExportImportModal v-if="showExportImport" entity="stock-transactions" entity-label="Transaksi Stok" :available-fields="exportFields" @close="showExportImport = false" />
    </div>
  </AppLayout>
</template>
