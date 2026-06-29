<script setup>
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

// Local visibility state for each flash type
const showSuccess = ref(false)
const showError   = ref(false)
const showWarning = ref(false)

// Watch for flash prop changes (new Inertia page visits)
watch(
    () => page.props.flash,
    (flash) => {
        showSuccess.value = !!flash?.success
        showError.value   = !!flash?.error
        showWarning.value = !!flash?.warning
    },
    { immediate: true, deep: true }
)
</script>

<template>
    <div role="region" aria-label="Notifikasi flash">
        <!-- Success -->
        <div
            v-if="page.props.flash?.success && showSuccess"
            class="alert alert-success alert-dismissible fade show mb-2"
            role="alert"
        >
            <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
            {{ page.props.flash.success }}
            <button
                type="button"
                class="btn-close"
                aria-label="Tutup"
                @click="showSuccess = false"
            ></button>
        </div>

        <!-- Error -->
        <div
            v-if="page.props.flash?.error && showError"
            class="alert alert-danger alert-dismissible fade show mb-2"
            role="alert"
        >
            <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
            {{ page.props.flash.error }}
            <button
                type="button"
                class="btn-close"
                aria-label="Tutup"
                @click="showError = false"
            ></button>
        </div>

        <!-- Warning -->
        <div
            v-if="page.props.flash?.warning && showWarning"
            class="alert alert-warning alert-dismissible fade show mb-2"
            role="alert"
        >
            <i class="bi bi-exclamation-circle-fill me-2" aria-hidden="true"></i>
            {{ page.props.flash.warning }}
            <button
                type="button"
                class="btn-close"
                aria-label="Tutup"
                @click="showWarning = false"
            ></button>
        </div>
    </div>
</template>
