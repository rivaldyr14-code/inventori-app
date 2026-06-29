<script setup>
import { computed } from 'vue'
import GuestLayout from '@/Components/Layout/GuestLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    status: {
        type: Number,
        default: 404,
    },
})

const errorData = computed(() => {
    const map = {
        403: { icon: 'bi-shield-exclamation', title: '403 — Akses Ditolak', message: 'Anda tidak memiliki izin untuk mengakses halaman ini.' },
        404: { icon: 'bi-emoji-frown', title: '404 — Halaman Tidak Ditemukan', message: 'Halaman yang Anda cari tidak tersedia.' },
        500: { icon: 'bi-exclamation-triangle', title: '500 — Kesalahan Server', message: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.' },
    }
    return map[props.status] || map[500]
})
</script>

<template>
  <GuestLayout>
    <div class="text-center py-4">
      <i :class="['bi', errorData.icon, 'text-muted', 'display-1']" aria-hidden="true"></i>
      <h1 class="h4 fw-bold mt-3 mb-2">{{ errorData.title }}</h1>
      <p class="text-muted mb-4">{{ errorData.message }}</p>
      <Link href="/" class="btn btn-primary">Kembali ke Beranda</Link>
    </div>
  </GuestLayout>
</template>
