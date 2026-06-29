<script setup>
/**
 * SearchFilter component
 *
 * Props:
 *   filters      — object with current filter values, keyed by filterField.key
 *   filterFields — array of { key, label, type: 'text'|'select'|'date', options?: [{value, label}] }
 *
 * Emits:
 *   filter-change — emitted with the updated filters object when any field changes
 *
 * Requirements: 12.1, 12.2, 12.3
 */
import { ref, watch } from 'vue'

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({}),
    },
    filterFields: {
        type: Array,
        default: () => [],
        // Expected shape: [{ key: string, label: string, type: 'text'|'select'|'date', options?: [{value, label}] }]
    },
})

const emit = defineEmits(['filter-change'])

// Internal reactive copy of filter values
const localFilters = ref({ ...props.filters })

// Sync internal state when parent-provided filters prop changes
// (e.g. on Inertia navigation with preserved state)
watch(
    () => props.filters,
    (incoming) => {
        // Only update keys that differ to avoid circular updates
        for (const key of Object.keys(incoming)) {
            if (localFilters.value[key] !== incoming[key]) {
                localFilters.value[key] = incoming[key]
            }
        }
    },
    { deep: true }
)

// Debounce timer map (per field) — used for text inputs only
const debounceTimers = {}

/**
 * Called immediately when a select or date field changes.
 */
function onImmediateChange(key, value) {
    localFilters.value[key] = value
    emit('filter-change', { ...localFilters.value })
}

/**
 * Called on every keystroke for text inputs — debounced by 300 ms.
 */
function onTextInput(key, event) {
    const value = event.target.value
    localFilters.value[key] = value

    clearTimeout(debounceTimers[key])
    debounceTimers[key] = setTimeout(() => {
        emit('filter-change', { ...localFilters.value })
    }, 300)
}

/**
 * Reset all filters to empty values and emit.
 */
function resetFilters() {
    for (const field of props.filterFields) {
        localFilters.value[field.key] = ''
    }
    emit('filter-change', { ...localFilters.value })
}

/**
 * Returns true if at least one filter has a non-empty value.
 */
function hasActiveFilters() {
    return Object.values(localFilters.value).some((v) => v !== '' && v !== null && v !== undefined)
}
</script>

<template>
    <div class="search-filter-bar">
        <div class="row g-2 align-items-end">
            <!-- Render each filter field -->
            <div
                v-for="field in filterFields"
                :key="field.key"
                class="col-12 col-sm-6 col-md-4 col-lg-3"
            >
                <label :for="`filter-${field.key}`" class="form-label form-label-sm mb-1">
                    {{ field.label }}
                </label>

                <!-- Text input with debounce -->
                <input
                    v-if="field.type === 'text'"
                    :id="`filter-${field.key}`"
                    type="text"
                    class="form-control form-control-sm"
                    :value="localFilters[field.key] ?? ''"
                    :placeholder="`Cari ${field.label}…`"
                    :aria-label="`Filter ${field.label}`"
                    @input="onTextInput(field.key, $event)"
                />

                <!-- Select / dropdown -->
                <select
                    v-else-if="field.type === 'select'"
                    :id="`filter-${field.key}`"
                    class="form-select form-select-sm"
                    :value="localFilters[field.key] ?? ''"
                    :aria-label="`Filter ${field.label}`"
                    @change="onImmediateChange(field.key, $event.target.value)"
                >
                    <option value="">— Semua —</option>
                    <option
                        v-for="option in field.options"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>

                <!-- Date input -->
                <input
                    v-else-if="field.type === 'date'"
                    :id="`filter-${field.key}`"
                    type="date"
                    class="form-control form-control-sm"
                    :value="localFilters[field.key] ?? ''"
                    :aria-label="`Filter ${field.label}`"
                    @change="onImmediateChange(field.key, $event.target.value)"
                />
            </div>

            <!-- Reset button — only shown when at least one filter is active -->
            <div v-if="hasActiveFilters()" class="col-auto">
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm"
                    aria-label="Reset semua filter"
                    @click="resetFilters"
                >
                    <span aria-hidden="true">×</span>
                    Reset
                </button>
            </div>
        </div>
    </div>
</template>
