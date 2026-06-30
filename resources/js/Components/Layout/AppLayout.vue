<script setup>
import { computed, ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

// ─── Auth & flash ──────────────────────────────────────────────────
const page = usePage()

const user = computed(() => page.props.auth?.user ?? null)
const flash = computed(() => page.props.flash ?? {})

const isAdministrator = computed(() =>
    Array.isArray(user.value?.roles) && user.value.roles.includes('Administrator')
)

// ─── Flash dismiss ─────────────────────────────────────────────────
const flashSuccess = ref(true)
const flashError   = ref(true)
const flashWarning = ref(true)

// Reset visibility whenever flash props change (new page visit)
watch(() => page.props.flash, () => {
    flashSuccess.value = true
    flashError.value   = true
    flashWarning.value = true
})

// ─── Sidebar toggle (mobile) ───────────────────────────────────────
const sidebarOpen = ref(false)

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
}

function closeSidebar() {
    sidebarOpen.value = false
}

// ─── Logout ────────────────────────────────────────────────────────
function logout() {
    router.post('/logout')
}

// ─── Navigation items ──────────────────────────────────────────────
const navItems = computed(() => {
    const items = [
        { label: 'Dashboard',          href: '/dashboard',          icon: 'bi-speedometer2' },
        { label: 'Categories',          href: '/categories',          icon: 'bi-tags' },
        { label: 'Products',            href: '/products',            icon: 'bi-box-seam' },
        { label: 'Stock Transactions',  href: '/stock-transactions',  icon: 'bi-arrow-left-right' },
        { label: 'Roles',               href: '/roles',               icon: 'bi-shield-check' },
    ]

    if (isAdministrator.value) {
        items.push({ label: 'Users', href: '/users', icon: 'bi-people' })
        items.push({ label: 'Pending Registrations', href: '/admin/pending-registrations', icon: 'bi-person-check' })
        items.push({ label: 'Reset Password Requests', href: '/admin/password-resets', icon: 'bi-key' })
    }

    items.push({ label: 'Reset Password', href: '/password-reset-requests', icon: 'bi-key' })
    items.push({ label: 'Export/Import Logs', href: '/export-import-logs', icon: 'bi-clock-history' })

    return items
})

// Determine if a nav link is "current" (simple prefix check)
function isActive(href) {
    const currentPath = page.url.split('?')[0]
    if (href === '/dashboard') return currentPath === '/dashboard'
    return currentPath.startsWith(href)
}
</script>

<template>
    <div class="d-flex min-vh-100">

        <!-- ── Sidebar overlay (mobile) ─────────────────────────── -->
        <div
            v-if="sidebarOpen"
            class="sidebar-overlay d-lg-none"
            @click="closeSidebar"
        ></div>

        <!-- ── Sidebar ──────────────────────────────────────────── -->
        <nav
            id="sidebar"
            class="sidebar bg-dark text-white d-flex flex-column flex-shrink-0"
            :class="{ 'sidebar--open': sidebarOpen }"
            aria-label="Navigasi utama"
        >
            <!-- Brand -->
            <div class="sidebar__brand d-flex align-items-center px-3 py-3 border-bottom border-secondary">
                <span class="fs-5 fw-bold text-white text-decoration-none">
                    📦 Inventori App
                </span>
                <!-- Close button (mobile) -->
                <button
                    type="button"
                    class="btn-close btn-close-white ms-auto d-lg-none"
                    aria-label="Tutup menu"
                    @click="closeSidebar"
                ></button>
            </div>

            <!-- Nav links -->
            <ul class="nav nav-pills flex-column mb-auto px-2 pt-2">
                <li
                    v-for="item in navItems"
                    :key="item.href"
                    class="nav-item"
                >
                    <Link
                        :href="item.href"
                        class="nav-link text-white d-flex align-items-center gap-2 rounded"
                        :class="{ active: isActive(item.href) }"
                        @click="closeSidebar"
                    >
                        <i :class="['bi', item.icon]" aria-hidden="true"></i>
                        {{ item.label }}
                    </Link>
                </li>
            </ul>

            <!-- Logged-in user (bottom of sidebar) -->
            <div class="sidebar__footer px-3 py-3 border-top border-secondary mt-auto">
                <div class="d-flex align-items-center gap-2 text-white-50 small">
                    <i class="bi bi-person-circle fs-5 text-white-50" aria-hidden="true"></i>
                    <span class="text-truncate">{{ user?.name ?? '—' }}</span>
                </div>
            </div>
        </nav>

        <!-- ── Main area ─────────────────────────────────────────── -->
        <div class="flex-grow-1 d-flex flex-column overflow-hidden">

            <!-- Navbar -->
            <header class="navbar navbar-expand bg-white border-bottom shadow-sm px-3 py-2 sticky-top">
                <!-- Hamburger (mobile) -->
                <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm me-3 d-lg-none"
                    aria-label="Buka menu"
                    @click="toggleSidebar"
                >
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>

                <!-- App name (desktop) -->
                <span class="navbar-brand fw-semibold text-body d-none d-lg-inline-block">
                    Inventori App
                </span>

                <!-- Right side: user + logout -->
                <div class="ms-auto d-flex align-items-center gap-2">
                    <span class="text-body-secondary small d-none d-sm-inline">
                        <i class="bi bi-person-fill me-1" aria-hidden="true"></i>
                        {{ user?.name ?? '—' }}
                    </span>

                    <div class="vr d-none d-sm-block"></div>

                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger"
                        @click="logout"
                    >
                        <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>
                        Logout
                    </button>
                </div>
            </header>

            <!-- Flash messages -->
            <div class="px-3 pt-3" role="region" aria-label="Notifikasi">
                <div
                    v-if="flash.success && flashSuccess"
                    class="alert alert-success alert-dismissible fade show mb-2"
                    role="alert"
                >
                    <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
                    {{ flash.success }}
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Tutup"
                        @click="flashSuccess = false"
                    ></button>
                </div>

                <div
                    v-if="flash.error && flashError"
                    class="alert alert-danger alert-dismissible fade show mb-2"
                    role="alert"
                >
                    <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
                    {{ flash.error }}
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Tutup"
                        @click="flashError = false"
                    ></button>
                </div>

                <div
                    v-if="flash.warning && flashWarning"
                    class="alert alert-warning alert-dismissible fade show mb-2"
                    role="alert"
                >
                    <i class="bi bi-exclamation-circle-fill me-2" aria-hidden="true"></i>
                    {{ flash.warning }}
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Tutup"
                        @click="flashWarning = false"
                    ></button>
                </div>
            </div>

            <!-- Page content slot -->
            <main class="flex-grow-1 p-3 p-md-4 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* ── Sidebar dimensions ─────────────────────────────────────── */
.sidebar {
    width: 260px;
    min-height: 100vh;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 1030;
    transition: transform 0.25s ease-in-out;
}

/* ── Mobile: hide sidebar off-canvas ────────────────────────── */
@media (max-width: 991.98px) {
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        transform: translateX(-100%);
    }

    .sidebar--open {
        transform: translateX(0);
    }
}

/* ── Overlay for mobile sidebar ────────────────────────────── */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1029;
}

/* ── Active nav item ────────────────────────────────────────── */
.nav-link.active {
    background-color: rgba(255, 255, 255, 0.15) !important;
    font-weight: 600;
}

.nav-link:not(.active):hover {
    background-color: rgba(255, 255, 255, 0.08);
}

.sidebar__brand {
    min-height: 56px;
}

.sidebar__footer {
    min-height: 56px;
}
</style>
