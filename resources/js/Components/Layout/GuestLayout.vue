<script setup>
defineProps({
    title: {
        type: String,
        default: '',
    },
})
</script>

<template>
    <div class="min-vh-100 bg-light">
        <!-- Content -->
        <div class="container py-4">
            <!-- Title (for login/register pages) -->
            <div v-if="title" class="row justify-content-center mb-4">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 p-md-5">
                            <h2 class="h5 fw-semibold text-center mb-4 text-body">
                                {{ title }}
                            </h2>
                            <slot />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slot (for landing page) -->
            <slot v-else />
        </div>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 mt-auto">
            <div class="container text-center">
                <small class="text-muted">
                    &copy; {{ new Date().getFullYear() }} Inventori App &middot; Sistem Manajemen Inventori
                </small>
            </div>
        </footer>
    </div>
</template>
