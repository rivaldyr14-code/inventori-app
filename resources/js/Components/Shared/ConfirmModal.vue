<script setup>
import { watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi',
    },
    message: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['confirm', 'cancel'])

// Sync Bootstrap modal visibility with `show` prop
watch(
    () => props.show,
    (val) => {
        const el = document.getElementById('confirmModal')
        if (!el) return

        // Lazily import Bootstrap Modal to avoid SSR issues
        import('bootstrap').then(({ Modal }) => {
            const modal = Modal.getOrCreateInstance(el)
            if (val) {
                modal.show()
            } else {
                modal.hide()
            }
        })
    }
)

function onConfirm() {
    emit('confirm')
}

function onCancel() {
    emit('cancel')
}
</script>

<template>
    <!-- Bootstrap 5 Modal -->
    <div
        id="confirmModal"
        class="modal fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="confirmModalLabel"
        aria-modal="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="confirmModalLabel" class="modal-title">
                        {{ title }}
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Tutup"
                        @click="onCancel"
                    ></button>
                </div>

                <div class="modal-body">
                    <p class="mb-0">{{ message }}</p>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="onCancel"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="btn btn-danger"
                        @click="onConfirm"
                    >
                        Ya, Konfirmasi
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
