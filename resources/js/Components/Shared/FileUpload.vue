<script setup>
/**
 * FileUpload — PDF-only file input with client-side size validation.
 * Validates: Requirements 10.1, 11.6
 *
 * Constraints:
 *   - MIME type must be application/pdf
 *   - Size must be between 100 KB (102400 bytes) and 500 KB (512000 bytes)
 *
 * Usage:
 *   <FileUpload v-model="form.attachment" :error="form.errors.attachment" />
 */
import { ref, computed } from 'vue'

const MIN_SIZE = 102400  // 100 KB in bytes
const MAX_SIZE = 512000  // 500 KB in bytes

const props = defineProps({
    modelValue: {
        type: [File, null],
        default: null,
    },
    /** External server-side error message to display */
    error: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['update:modelValue'])

const localError = ref('')
const fileInputEl = ref(null)

const displayError = computed(() => localError.value || props.error)
const fileName = computed(() => props.modelValue?.name ?? '')

function handleChange(event) {
    const file = event.target.files[0] ?? null
    localError.value = ''

    if (!file) {
        emit('update:modelValue', null)
        return
    }

    // Validate MIME type
    if (file.type !== 'application/pdf') {
        localError.value = 'File harus berupa PDF'
        emit('update:modelValue', null)
        // Reset native input so the same file can be re-selected after fixing
        if (fileInputEl.value) fileInputEl.value.value = ''
        return
    }

    // Validate minimum size
    if (file.size < MIN_SIZE) {
        localError.value = 'Ukuran file minimum adalah 100 KB'
        emit('update:modelValue', null)
        if (fileInputEl.value) fileInputEl.value.value = ''
        return
    }

    // Validate maximum size
    if (file.size > MAX_SIZE) {
        localError.value = 'Ukuran file maksimum adalah 500 KB'
        emit('update:modelValue', null)
        if (fileInputEl.value) fileInputEl.value.value = ''
        return
    }

    // File passed all validations
    emit('update:modelValue', file)
}
</script>

<template>
    <div>
        <input
            ref="fileInputEl"
            type="file"
            accept="application/pdf"
            class="form-control"
            :class="{ 'is-invalid': displayError }"
            @change="handleChange"
        />

        <!-- Valid file preview -->
        <div v-if="fileName && !displayError" class="form-text text-success mt-1">
            <i class="bi bi-file-earmark-pdf me-1"></i>
            {{ fileName }}
        </div>

        <!-- Error message -->
        <div v-if="displayError" class="invalid-feedback d-block mt-1">
            {{ displayError }}
        </div>
    </div>
</template>
