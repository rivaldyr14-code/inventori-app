<script setup>
/**
 * TomSelectInput — wrapper around Tom Select with remote search.
 * Validates: Requirements 11.1, 11.2
 *
 * Usage:
 *   <TomSelectInput
 *     url="/api/categories/search"
 *     v-model="form.category_id"
 *     placeholder="Cari kategori..."
 *     label-field="name"
 *     value-field="id"
 *   />
 */
import { ref, watch, onMounted, onUnmounted } from 'vue'
import TomSelect from 'tom-select'

const props = defineProps({
    /** API endpoint for remote search, expects ?q={query} */
    url: {
        type: String,
        required: true,
    },
    modelValue: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Cari...',
    },
    labelField: {
        type: String,
        default: 'name',
    },
    valueField: {
        type: String,
        default: 'id',
    },
    /** Optional preloaded options for edit forms: [{id: '...', name: '...'}] */
    preloadOptions: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['update:modelValue'])

const selectEl = ref(null)
let tomSelect = null

onMounted(() => {
    tomSelect = new TomSelect(selectEl.value, {
        valueField: props.valueField,
        labelField: props.labelField,
        searchField: [props.labelField],
        placeholder: props.placeholder,
        maxOptions: 10,
        preload: false,
        // Show "no results" message only after an active search
        shouldLoad(query) {
            return query.length > 0
        },
        load(query, callback) {
            fetch(`${props.url}?q=${encodeURIComponent(query)}`)
                .then((res) => res.json())
                .then((data) => callback(data))
                .catch(() => callback())
        },
        render: {
            no_results() {
                return '<div class="no-results">Tidak ada data ditemukan</div>'
            },
        },
        onChange(value) {
            emit('update:modelValue', value || null)
        },
    })

    // Preload options for edit forms (show label instead of raw UUID)
    if (props.preloadOptions.length) {
        props.preloadOptions.forEach(opt => {
            tomSelect.addOption({ [props.valueField]: opt[props.valueField], [props.labelField]: opt[props.labelField] })
        })
    }

    // Set initial value when modelValue is already set
    if (props.modelValue) {
        tomSelect.setValue(props.modelValue, true)
    }
})

// Keep Tom Select in sync when v-model changes externally
watch(
    () => props.modelValue,
    (val) => {
        if (!tomSelect) return
        const current = tomSelect.getValue()
        if (current !== (val ?? '')) {
            tomSelect.setValue(val ?? '', true)
        }
    }
)

onUnmounted(() => {
    if (tomSelect) {
        tomSelect.destroy()
        tomSelect = null
    }
})
</script>

<template>
    <select ref="selectEl"></select>
</template>
