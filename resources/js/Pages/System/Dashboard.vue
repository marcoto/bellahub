<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    user: Object,
});
</script>

<template>
    <Head title="Panel del Sistema" />

    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <span class="font-semibold text-gray-800">BellaHub · Sistema</span>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ user?.email }}</span>
                <Link
                    :href="route('system.logout')"
                    method="post"
                    as="button"
                    class="text-sm text-red-600 hover:underline"
                >
                    Cerrar sesión
                </Link>
            </div>
        </nav>

        <main class="max-w-5xl mx-auto py-12 px-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-8">Configuración del Sistema</h1>

            <div class="bg-white rounded-lg shadow p-6 text-gray-500">
                El formulario de configuración irá aquí.
            </div>
        </main>
    </div>
</template>
