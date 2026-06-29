<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue'
import Pagination from '@/Components/Shared/Pagination.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    users: Object,
})

function approve(id) {
    if (confirm('Setujui akun ini?')) {
        router.post(`/admin/pending-registrations/${id}/approve`)
    }
}

function reject(id) {
    if (confirm('Tolak dan hapus akun ini?')) {
        router.post(`/admin/pending-registrations/${id}/reject`)
    }
}
</script>

<template>
    <AppLayout title="Persetujuan Registrasi">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">Persetujuan Registrasi</h4>
                <p class="text-secondary small mb-0">Kelola akun yang menunggu persetujuan</p>
            </div>
        </div>

        <!-- Info -->
        <div class="alert alert-info small mb-4">
            <i class="bi bi-info-circle me-1"></i>
            Akun baru akan muncul di sini dengan status <strong>belum aktif</strong>.
            Setujui untuk mengaktifkan akun, atau tolak untuk menghapusnya.
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Tanggal Daftar</th>
                                <th class="text-center" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="!users?.data?.length">
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                                    Tidak ada akun yang menunggu persetujuan.
                                </td>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id">
                                <td class="fw-medium">{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.name"
                                        class="badge bg-light text-dark border me-1"
                                    >
                                        {{ role.name }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">{{ user.created_at }}</small>
                                </td>
                                <td class="text-center">
                                    <button
                                        class="btn btn-success btn-sm me-1"
                                        @click="approve(user.id)"
                                    >
                                        <i class="bi bi-check-lg"></i> Setujui
                                    </button>
                                    <button
                                        class="btn btn-danger btn-sm"
                                        @click="reject(user.id)"
                                    >
                                        <i class="bi bi-x-lg"></i> Tolak
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="users?.links?.length > 3" class="card-footer bg-white border-top">
                <Pagination :links="users.links" />
            </div>
        </div>
    </AppLayout>
</template>
