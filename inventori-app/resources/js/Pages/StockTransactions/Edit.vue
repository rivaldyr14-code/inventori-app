<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'
import FileUpload from '@/Components/Shared/FileUpload.vue'

const props = defineProps({
    stockTransaction: Object,
    audits: Array,
})

const form = useForm({
    notes: props.stockTransaction.notes || '',
    is_active: props.stockTransaction.is_active,
    attachment: null,
})

function submit() {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/stock-transactions/${props.stockTransaction.id}`, { forceFormData: true })
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Transaksi Stok</h1>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Hanya catatan yang dapat diubah. Data transaksi inti (produk, tipe, jumlah, stok) tidak dapat diubah setelah tersimpan.
          </div>

          <table class="table table-bordered mb-4">
            <tr><th class="bg-light" style="width:200px;">No. Transaksi</th><td>{{ stockTransaction.transaction_number }}</td></tr>
            <tr><th class="bg-light">Produk</th><td>{{ stockTransaction.product?.name || '(dihapus)' }}</td></tr>
            <tr><th class="bg-light">Tipe</th><td><span class="badge" :class="stockTransaction.type === 'in' ? 'bg-success' : 'bg-danger'">{{ stockTransaction.type === 'in' ? 'Masuk' : 'Keluar' }}</span></td></tr>
            <tr><th class="bg-light">Jumlah</th><td>{{ stockTransaction.quantity }}</td></tr>
            <tr><th class="bg-light">Stok Sebelum</th><td>{{ stockTransaction.stock_before }}</td></tr>
            <tr><th class="bg-light">Stok Sesudah</th><td>{{ stockTransaction.stock_after }}</td></tr>
          </table>

          <form @submit.prevent="submit" novalidate>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="is_active" class="form-label">Status</label>
                <select id="is_active" v-model="form.is_active" class="form-select" :class="{ 'is-invalid': form.errors.is_active }">
                  <option :value="true">Aktif</option>
                  <option :value="false">Tidak Aktif</option>
                </select>
                <div v-if="form.errors.is_active" class="invalid-feedback">{{ form.errors.is_active }}</div>
              </div>
              <div class="col-12">
                <label for="notes" class="form-label">Catatan</label>
                <textarea id="notes" v-model="form.notes" class="form-control" rows="3" :class="{ 'is-invalid': form.errors.notes }"></textarea>
                <div v-if="form.errors.notes" class="invalid-feedback">{{ form.errors.notes }}</div>
              </div>
              <div class="col-12">
                <label v-if="stockTransaction.attachment_path" class="form-label">File Saat Ini</label>
                <div v-if="stockTransaction.attachment_path" class="mb-2">
                  <a :href="`/attachments/stock-transactions/${stockTransaction.id}`" class="btn btn-sm btn-outline-info" target="_blank">
                    <i class="bi bi-paperclip me-1"></i>Download Lampiran
                  </a>
                </div>
                <label for="attachment" class="form-label">Ganti File (PDF, 100-500 KB)</label>
                <FileUpload v-model="form.attachment" :error="form.errors.attachment" />
              </div>
            </div>
            <div class="mt-4 d-flex gap-2">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>{{ form.processing ? 'Menyimpan...' : 'Simpan' }}
              </button>
              <button type="button" class="btn btn-secondary" @click="router.get('/stock-transactions')">Batal</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
          <AuditTrail :audits="audits" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
