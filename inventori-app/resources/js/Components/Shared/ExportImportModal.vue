<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    entity: String,
    entityLabel: String,
    availableFields: Array,
})

const emit = defineEmits(['close'])

const activeTab = ref('export')
const selectedFields = ref([...props.availableFields])
const isProcessing = ref(false)
const resultMessage = ref('')
const resultType = ref('')
const exportLogId = ref(null)

// Export
function toggleAllFields() {
    if (selectedFields.value.length === props.availableFields.length) {
        selectedFields.value = []
    } else {
        selectedFields.value = [...props.availableFields]
    }
}

function doExport() {
    if (selectedFields.value.length === 0) {
        resultMessage.value = 'Pilih minimal 1 kolom.'
        resultType.value = 'danger'
        return
    }
    isProcessing.value = true
    resultMessage.value = ''

    fetch(`/export/${props.entity}`, {
        method: 'POST',
        body: JSON.stringify({ selected_fields: selectedFields.value }),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        exportLogId.value = data.log_id || null
        resultMessage.value = data.message || 'Export berhasil.'
        resultType.value = 'success'
        isProcessing.value = false
        if (data.log_id) {
            window.location.href = `/exports/${data.log_id}/download`
        }
    })
    .catch(() => {
        resultMessage.value = 'Gagal menjalankan export.'
        resultType.value = 'danger'
        isProcessing.value = false
    })
}

// Import
const importFile = ref(null)
const uploadProgress = ref(false)
const uploadedFilePath = ref('')
const fileHeaders = ref([])
const previewData = ref([])
const columnMapping = ref({})
const importStep = ref('upload')
const importResult = ref('')

function handleFileSelect(event) {
    importFile.value = event.target.files[0] || null
}

function uploadFile() {
    if (!importFile.value) {
        importResult.value = 'Pilih file terlebih dahulu.'
        return
    }
    uploadProgress.value = true
    importResult.value = ''
    const formData = new FormData()
    formData.append('file', importFile.value)

    fetch('/import/upload', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
    })
    .then(async r => {
        const data = await r.json().catch(() => ({}))
        if (!r.ok) {
            const msg = data.message || (data.errors?.file?.[0]) || `HTTP ${r.status}`
            throw new Error(msg)
        }
        return data
    })
    .then(data => {
        if (!data.file_path) {
            throw new Error(data.message || 'Upload gagal: file_path tidak ditemukan')
        }
        uploadedFilePath.value = data.file_path
        return fetch(`/import/${props.entity}/preview`, {
            method: 'POST',
            body: JSON.stringify({ file_path: data.file_path }),
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
        })
    })
    .then(async r => {
        const data = await r.json().catch(() => ({}))
        if (!r.ok) {
            throw new Error(data.message || `HTTP ${r.status}`)
        }
        return data
    })
    .then(data => {
        if (data.message && !data.headers) {
            throw new Error(data.message)
        }
        fileHeaders.value = data.headers || []
        previewData.value = data.preview || {}
        props.availableFields.forEach(f => {
            columnMapping.value[f] = fileHeaders.value.find(h => h.toLowerCase() === f.toLowerCase()) || ''
        })
        importStep.value = 'map'
        uploadProgress.value = false
    })
    .catch(err => {
        importResult.value = err.message || 'Gagal upload/preview file.'
        uploadProgress.value = false
    })
}

function doImport() {
    if (!uploadedFilePath.value) {
        importResult.value = 'File belum diupload. Silakan upload file terlebih dahulu.'
        return
    }
    isProcessing.value = true
    importResult.value = ''

    fetch(`/import/${props.entity}`, {
        method: 'POST',
        body: JSON.stringify({
            file_path: uploadedFilePath.value,
            column_mapping: columnMapping.value,
        }),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
        },
    })
    .then(r => {
        if (!r.ok) return r.json().then(err => { throw new Error(err.message || 'Import gagal') })
        return r.json()
    })
    .then(data => {
        importResult.value = data.message || 'Import sedang diproses. Cek halaman Log Export/Import untuk melihat hasil.'
        isProcessing.value = false
    })
    .catch(() => {
        importResult.value = 'Gagal menjalankan import.'
        isProcessing.value = false
    })
}
</script>

<template>
  <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);" @click.self="emit('close')">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-file-earmark-excel me-2 text-success"></i>
            Export / Import — {{ entityLabel }}
          </h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>
        <div class="modal-body">
          <!-- Tabs -->
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
              <button class="nav-link" :class="{ active: activeTab === 'export' }" @click="activeTab = 'export'">
                <i class="bi bi-upload me-1"></i>Export
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" :class="{ active: activeTab === 'import' }" @click="activeTab = 'import'">
                <i class="bi bi-download me-1"></i>Import
              </button>
            </li>
          </ul>

          <!-- EXPORT TAB -->
          <div v-if="activeTab === 'export'">
            <p class="text-muted small mb-2">Pilih kolom yang ingin di-export ke Excel:</p>
            <div class="mb-2">
              <button class="btn btn-sm btn-outline-secondary" @click="toggleAllFields">
                {{ selectedFields.length === availableFields.length ? 'Batal Pilih Semua' : 'Pilih Semua' }}
              </button>
            </div>
            <div class="row g-2 mb-3">
              <div v-for="field in availableFields" :key="field" class="col-6 col-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" :value="field" v-model="selectedFields" :id="`exp_${field}`" />
                  <label class="form-check-label small" :for="`exp_${field}`">{{ field }}</label>
                </div>
              </div>
            </div>
            <button class="btn btn-success" :disabled="isProcessing || selectedFields.length === 0" @click="doExport">
              <span v-if="isProcessing" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-download me-1"></i>
              {{ isProcessing ? 'Memproses...' : 'Export Excel' }}
            </button>
          </div>

          <!-- IMPORT TAB -->
          <div v-if="activeTab === 'import'">
            <!-- Step 1: Upload -->
            <div v-if="importStep === 'upload'">
              <p class="text-muted small mb-2">Upload file Excel (.xlsx, .xls, .csv) untuk diimport:</p>
              <div class="mb-3">
                <input type="file" accept=".xlsx,.xls,.csv" class="form-control" @input="handleFileSelect" />
              </div>
              <button class="btn btn-primary" :disabled="uploadProgress || !importFile" @click="uploadFile">
                <span v-if="uploadProgress" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-upload me-1"></i>
                {{ uploadProgress ? 'Mengupload...' : 'Upload & Preview' }}
              </button>
            </div>

            <!-- Step 2: Column Mapping -->
            <div v-if="importStep === 'map'">
              <p class="text-muted small mb-2">Mapping kolom Excel ke field aplikasi:</p>
              <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>Field Aplikasi</th>
                      <th>Kolom Excel</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="field in availableFields" :key="field">
                      <td class="fw-semibold">{{ field }}</td>
                      <td>
                        <select class="form-select form-select-sm" v-model="columnMapping[field]">
                          <option value="">— Kosong —</option>
                          <option v-for="header in fileHeaders" :key="header" :value="header">{{ header }}</option>
                        </select>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <button class="btn btn-success" :disabled="isProcessing" @click="doImport">
                <span v-if="isProcessing" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-download me-1"></i>
                {{ isProcessing ? 'Memproses...' : 'Import Data' }}
              </button>
            </div>
          </div>

          <!-- Result messages -->
          <div v-if="resultMessage" class="alert mt-3" :class="`alert-${resultType}`">{{ resultMessage }}</div>
          <div v-if="importResult" class="alert mt-3 alert-info">{{ importResult }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
