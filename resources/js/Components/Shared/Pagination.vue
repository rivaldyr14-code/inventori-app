<script setup>
import { router } from '@inertiajs/vue3'

/**
 * Props:
 *  links — array dari Laravel paginator, setiap item:
 *    { url: string|null, label: string, active: boolean }
 *
 * Laravel mengirim label "Previous" dan "Next" sebagai entitas HTML
 * (&laquo; &raquo;), sehingga kita render dengan v-html dan menyediakan
 * aria-label yang bersih dari tag HTML.
 */
const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
})

/**
 * Navigasi ke URL halaman yang dipilih menggunakan Inertia router.
 * preserveState: true mempertahankan state pencarian/filter/sort di URL.
 */
function navigate(url) {
    if (!url) return
    router.visit(url, { preserveState: true })
}

/**
 * Hapus tag HTML dari label untuk digunakan sebagai aria-label yang bersih.
 * Contoh: "&laquo; Previous" → "« Previous"
 */
function ariaLabel(html) {
    const div = document.createElement('div')
    div.innerHTML = html
    return div.textContent || div.innerText || html
}
</script>

<template>
    <!-- Hanya render jika ada lebih dari 3 link (prev + halaman + next) -->
    <nav v-if="links && links.length > 3" aria-label="Navigasi halaman">
        <ul class="pagination pagination-sm mb-0 flex-wrap">
            <li
                v-for="(link, index) in links"
                :key="index"
                class="page-item"
                :class="{
                    active: link.active,
                    disabled: !link.url,
                }"
            >
                <button
                    type="button"
                    class="page-link"
                    :aria-label="ariaLabel(link.label)"
                    :aria-current="link.active ? 'page' : undefined"
                    :disabled="!link.url"
                    @click="navigate(link.url)"
                    v-html="link.label"
                />
            </li>
        </ul>
    </nav>
</template>
