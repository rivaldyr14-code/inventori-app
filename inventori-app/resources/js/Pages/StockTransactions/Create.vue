<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import TomSelectInput from '@/Components/Shared/TomSelectInput.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'
import FileUpload from '@/Components/Shared/FileUpload.vue'

const props = defineProps({
    products: Array,
})

const form = useForm({
    product_id: '',
    type: 'in',
    quantity: 1,
    is_active: true,
    notes: '',
    attachment: null,
})

const selectedProduct = computed(() => {
    return props.products.find(p => p.id === form.product_id)
})

function submit() {
    form.post('/stock-transactions', { forceFormData: true })
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Transaksi Stok</h1>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form @submit.prevent="submit" novalidate>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="product_id" class="form-label">Produk <span class="text-danger">*</span></label>
                <TomSelectInput
                  url="/api/products/search"
                  v-model="form.product_id"
                  placeholder="Cari produk..."
                  :class="{ 'is-invalid': form.errors.product_id }"
                />
                <div v-if="form.errors.product_id" class="invalid-feedback">{{ form.errors.product_id }}</div>
                <div v-if="selectedProduct" class="mt-1 small text-muted">Stok saat ini: <strong>{{ selectedProduct.current_stock }}</strong></div>
              </div>
              <div class="col-12 col-md-3">
                <label for="type" class="form-label">Tipe <span class="text-danger">*</span></label>
                <select id="type" v-model="form.type" class="form-select" :class="{ 'is-invalid': form.errors.type }">
                  <option value="in">Masuk</option>
                  <option value="out">Keluar</option>
                </select>
                <div v-if="form.errors.type" class="invalid-feedback">{{ form.errors.type }}</div>
              </div>
              <div class="col-12 col-md-3">
                <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
                <input id="quantity" v-model.number="form.quantity" type="number" min="1" class="form-control" :class="{ 'is-invalid': form.errors.quantity }" />
                <div v-if="form.errors.quantity" class="invalid-feedback">{{ form.errors.quantity }}</div>
              </div>
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
                <label for="attachment" class="form-label">File Lampiran (PDF, 100-500 KB)</label>
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
          <AuditTrail :audits="[]" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
