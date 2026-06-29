<script setup>
import { computed } from 'vue'

const props = defineProps({
    audits: {
        type: Array,
        default: () => [],
    },
})

/**
 * Format an old_values / new_values object into readable "key: value" lines.
 * Returns an empty string when the value is null / not an object.
 */
function formatValues(values) {
    if (!values || typeof values !== 'object' || Array.isArray(values)) {
        return ''
    }
    return Object.entries(values)
        .map(([k, v]) => {
            const display = v === null ? '—'
                : v === true ? 'Aktif'
                : v === false ? 'Nonaktif'
                : typeof v === 'object' ? JSON.stringify(v)
                : String(v)
            return `${k}: ${display}`
        })
        .join('\n')
}

/**
 * Generate a human-readable note describing what the user did.
 */
function generateNote(audit) {
    const userName = audit.user?.name ?? 'System'

    if (audit.event === 'created') {
        const fields = audit.new_values ? Object.keys(audit.new_values) : []
        return `${userName} menambahkan data baru${fields.length ? ` dengan kolom: ${fields.join(', ')}` : ''}`
    }
    if (audit.event === 'deleted') {
        return `${userName} menghapus data`
    }
    if (audit.event === 'restored') {
        return `${userName} memulihkan data yang sudah dihapus`
    }
    if (audit.event === 'updated' && audit.old_values && audit.new_values) {
        const changedFields = []
        for (const key of Object.keys(audit.new_values)) {
            const oldVal = audit.old_values[key]
            const newVal = audit.new_values[key]
            if (oldVal !== newVal) {
                changedFields.push(key.replace(/_/g, ' '))
            }
        }
        return changedFields.length
            ? `${userName} memperbarui kolom: ${changedFields.join(', ')}`
            : `${userName} memperbarui data`
    }
    return `${userName} melakukan aksi pada data`
}

/**
 * Map owen-it/laravel-auditing event names to Bootstrap badge classes.
 */
function eventBadgeClass(event) {
    const map = {
        created: 'bg-success',
        updated: 'bg-primary',
        deleted: 'bg-danger',
        restored: 'bg-warning text-dark',
    }
    return map[event] ?? 'bg-secondary'
}

/**
 * Format ISO timestamp to a localised Indonesian date string.
 */
function formatDate(ts) {
    if (!ts) return '-'
    const d = new Date(ts)
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    })
}

/**
 * Format ISO timestamp to a localised Indonesian time string (WIB).
 */
function formatTime(ts) {
    if (!ts) return ''
    const d = new Date(ts)
    return d.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Jakarta',
    }) + ' WIB'
}

/**
 * Map event name to readable action label.
 */
function actionLabel(event) {
    const map = {
        created: 'Create',
        updated: 'Update',
        deleted: 'Delete',
        restored: 'Restore',
    }
    return map[event] ?? event
}
</script>

<template>
    <div class="audit-trail">
        <h5 class="mb-3">
            <i class="bi bi-clock-history me-2" aria-hidden="true"></i>
            History & Note
        </h5>

        <!-- Empty state -->
        <p v-if="!audits || audits.length === 0" class="text-muted fst-italic">
            Tidak ada riwayat perubahan.
        </p>

        <!-- Audit table -->
        <div v-else class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-top mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 10rem;">Date</th>
                        <th scope="col" style="width: 8rem;">Action</th>
                        <th scope="col" style="width: 12rem;">User</th>
                        <th scope="col" style="width: 20rem;">Nilai Lama</th>
                        <th scope="col" style="width: 20rem;">Nilai Baru</th>
                        <th scope="col">Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="audit in audits" :key="audit.id">
                        <!-- Date -->
                        <td>
                            <div>{{ formatDate(audit.created_at) }}</div>
                            <small class="text-muted">{{ formatTime(audit.created_at) }}</small>
                        </td>

                        <!-- Action badge -->
                        <td>
                            <span
                                class="badge"
                                :class="eventBadgeClass(audit.event)"
                            >
                                {{ actionLabel(audit.event) }}
                            </span>
                        </td>

                        <!-- User -->
                        <td>
                            <span v-if="audit.user?.name">
                                {{ audit.user.name }}
                            </span>
                            <span v-else class="text-muted fst-italic">System</span>
                        </td>

                        <!-- Old values -->
                        <td>
                            <pre
                                v-if="audit.old_values && Object.keys(audit.old_values).length"
                                class="audit-values mb-0"
                            >{{ formatValues(audit.old_values) }}</pre>
                            <span v-else class="text-muted">—</span>
                        </td>

                        <!-- New values -->
                        <td>
                            <pre
                                v-if="audit.new_values && Object.keys(audit.new_values).length"
                                class="audit-values mb-0"
                            >{{ formatValues(audit.new_values) }}</pre>
                            <span v-else class="text-muted">—</span>
                        </td>

                        <!-- Note (description of what user did) -->
                        <td>
                            <small>{{ generateNote(audit) }}</small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.audit-values {
    font-size: 0.78rem;
    white-space: pre-wrap;
    word-break: break-word;
    background: transparent;
    border: none;
    padding: 0;
    margin: 0;
    font-family: var(--bs-font-monospace);
}
</style>
