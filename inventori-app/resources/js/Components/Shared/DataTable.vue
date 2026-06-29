<script setup>
/**
 * DataTable component — reusable sortable table with named row slot.
 *
 * Props:
 *   columns       — Array of { key: string, label: string, sortable?: boolean }
 *   data          — Array of row objects
 *   sortBy        — Currently active sort column key (string)
 *   sortDirection — Current sort direction: 'asc' | 'desc'
 *
 * Emits:
 *   sort(key)     — emitted when a sortable header is clicked,
 *                   toggling asc → desc → asc; starts from 'asc' on first click
 *
 * Slot:
 *   #row="{ row }" — scoped slot rendered once per data row inside <tbody>
 */

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        // Each element: { key: string, label: string, sortable?: boolean }
    },
    data: {
        type: Array,
        required: true,
    },
    sortBy: {
        type: String,
        default: null,
    },
    sortDirection: {
        type: String,
        default: 'asc',
        validator: (v) => ['asc', 'desc'].includes(v),
    },
})

const emit = defineEmits(['sort'])

/**
 * Handle a header click. Always emits 'sort' with the column key;
 * the parent is responsible for toggling direction, but the icon
 * logic below also reflects current state.
 */
function handleSort(col) {
    if (!col.sortable) return
    emit('sort', col.key)
}

/**
 * Returns the sort icon character for a given column:
 *   ▲  — this column is active and ascending
 *   ▼  — this column is active and descending
 *   ⇅  — sortable but not currently active (neutral indicator)
 */
function sortIcon(col) {
    if (!col.sortable) return ''
    if (props.sortBy !== col.key) return '⇅'
    return props.sortDirection === 'asc' ? '▲' : '▼'
}

/**
 * Returns true when the column is the active sort column.
 * Used to apply a visual highlight class.
 */
function isActiveSort(col) {
    return col.sortable && props.sortBy === col.key
}
</script>

<template>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0">
            <!-- ── Table head ──────────────────────────────────────── -->
            <thead class="table-light">
                <tr>
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        :class="[
                            col.sortable ? 'user-select-none' : '',
                            isActiveSort(col) ? 'table-active' : '',
                        ]"
                        :style="col.sortable ? 'cursor: pointer; white-space: nowrap;' : 'white-space: nowrap;'"
                        :aria-sort="
                            isActiveSort(col)
                                ? sortDirection === 'asc'
                                    ? 'ascending'
                                    : 'descending'
                                : col.sortable
                                    ? 'none'
                                    : undefined
                        "
                        scope="col"
                        @click="handleSort(col)"
                    >
                        {{ col.label }}
                        <span
                            v-if="col.sortable"
                            class="ms-1 text-muted small"
                            aria-hidden="true"
                        >{{ sortIcon(col) }}</span>
                    </th>
                </tr>
            </thead>

            <!-- ── Table body ──────────────────────────────────────── -->
            <tbody>
                <!-- Empty state -->
                <tr v-if="!data || data.length === 0">
                    <td :colspan="columns.length" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-1" aria-hidden="true"></i>
                        Tidak ada data ditemukan.
                    </td>
                </tr>

                <!-- Data rows — consumer provides <td> cells via the scoped slot -->
                <template v-else>
                    <slot
                        v-for="row in data"
                        name="row"
                        :row="row"
                    />
                </template>
            </tbody>
        </table>
    </div>
</template>
