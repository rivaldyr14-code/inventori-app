<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

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

    router.post(`/export/${props.entity}`, {
        selected_fields: selectedFields.value,
    }, {
        preserveState: true,
        onFinish: () => { isProcessing.value = false },
        onSuccess: (page) => {
            const flash = page.props.flash
            resultMessage.value = flash?.success || 'Export berhasil.'
            resultType.value = 'success'
            if (flash?.log_id) {
                exportLogId.value = flash.log_id
                resultMessage.value = flash.success || 'Export berhasil. File akan didownload.'
                window.location.href = `/exports/${flash.log_id}/download`
            }
        },
        onError: (errors) => {
            resultMessage.value = errors.message || 'Gagal menjalankan export.'
            resultType.value = 'danger'
        },
    })
}

const importFile = ref(null)
const uploadProgress = ref(false)
const uploadedFilePath = ref('')
const fileHeaders = ref([])
const previewData = ref({})
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

    router.post('/import/upload', formData, {
        forceFormData: true,
        preserveState: true,
        onFinish: () => { uploadProgress.value = false },
        onSuccess: (page) => {
            const flash = page.props.flash
            if (!flash?.file_path) {
                importResult.value = flash?.message || 'Upload gagal.'
                return
            }
            uploadedFilePath.value = flash.file_path
            doPreview(flash.file_path)
        },
        onError: (errors) => {
            importResult.value = errors.file || errors.message || 'Gagal upload file.'
        },
    })
}

function doPreview(filePath) {
    router.post(`/import/${props.entity}/preview`, { file_path: filePath }, {
        preserveState: true,
        onSuccess: (page) => {
            const flash = page.props.flash
            fileHeaders.value = flash?.headers || []
            previewData.value = flash?.preview || {}
            props.availableFields.forEach(f => {
                columnMapping.value[f] = fileHeaders.value.find(h => h.toLowerCase() === f.toLowerCase()) || ''
            })
            importStep.value = 'map'
        },
        onError: (err) => {
            importResult.value = err.message || 'Gagal preview file.'
        },
    })
}

function doImport() {
    if (!uploadedFilePath.value) {
        importResult.value = 'File belum diupload.'
        return
    }
    isProcessing.value = true
    importResult.value = ''

    router.post(`/import/${props.entity}`, {
        file_path: uploadedFilePath.value,
        column_mapping: columnMapping.value,
    }, {
        preserveState: true,
        onFinish: () => { isProcessing.value = false },
        onSuccess: (page) => {
            importResult.value = page.props.flash?.success || 'Import sedang diproses.'
        },
        onError: (errors) => {
            importResult.value = errors.message || 'Gagal menjalankan import.'
        },
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

          <div v-if="activeTab === 'import'">
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

          <div v-if="resultMessage" class="alert mt-3" :class="`alert-${resultType}`">{{ resultMessage }}</div>
          <div v-if="importResult" class="alert mt-3 alert-info">{{ importResult }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
