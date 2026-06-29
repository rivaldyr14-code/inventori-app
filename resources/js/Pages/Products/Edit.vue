<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import TomSelectInput from '@/Components/Shared/TomSelectInput.vue'
import AuditTrail from '@/Components/Shared/AuditTrail.vue'
import FileUpload from '@/Components/Shared/FileUpload.vue'

const props = defineProps({
    product: Object,
    categories: Array,
    audits: Array,
})

const form = useForm({
    category_id: props.product.category_id,
    sku: props.product.sku,
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price,
    current_stock: props.product.current_stock,
    is_active: props.product.is_active,
    attachment: null,
})

function submit() {
    form.transform((data) => ({
        ...data,
        extra_data: JSON.stringify({
            sku: data.sku,
            price: data.price,
            status: data.is_active ? 'active' : 'inactive',
        }),
    })).post(`/products/${props.product.id}`, {
        forceFormData: true,
        _method: 'PUT',
    })
}
</script>

<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="d-flex align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Produk</h1>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form @submit.prevent="submit" novalidate>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                <TomSelectInput
                  url="/api/categories/search"
                  v-model="form.category_id"
                  placeholder="Cari kategori..."
                  :preload-options="categories"
                  :class="{ 'is-invalid': form.errors.category_id }"
                />
                <div v-if="form.errors.category_id" class="invalid-feedback">{{ form.errors.category_id }}</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                <input id="sku" v-model="form.sku" type="text" class="form-control" :class="{ 'is-invalid': form.errors.sku }" />
                <div v-if="form.errors.sku" class="invalid-feedback">{{ form.errors.sku }}</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                <input id="name" v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" />
                <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="price" class="form-label">Harga <span class="text-danger">*</span></label>
                <input id="price" v-model="form.price" type="number" step="0.01" class="form-control" :class="{ 'is-invalid': form.errors.price }" />
                <div v-if="form.errors.price" class="invalid-feedback">{{ form.errors.price }}</div>
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
                <label class="form-label">Stok Saat Ini: <strong>{{ product.current_stock }}</strong></label>
              </div>
              <div class="col-12">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea id="description" v-model="form.description" class="form-control" rows="3" :class="{ 'is-invalid': form.errors.description }"></textarea>
                <div v-if="form.errors.description" class="invalid-feedback">{{ form.errors.description }}</div>
              </div>
              <div class="col-12">
                <label v-if="product.attachment_path" class="form-label">File Saat Ini</label>
                <div v-if="product.attachment_path" class="mb-2">
                  <a :href="`/attachments/products/${product.id}`" class="btn btn-sm btn-outline-info" target="_blank">
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
              <button type="button" class="btn btn-secondary" @click="router.get('/products')">Batal</button>
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
