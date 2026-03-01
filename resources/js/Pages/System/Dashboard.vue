<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
    tenants: Array,
});

const form = useForm({
    name: '',
    domain: '',
    logo: null,
});

const logoPreview = ref(null);

const onLogoChange = (e) => {
    const file = e.target.files[0];
    form.logo = file;
    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('system.tenants.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            logoPreview.value = null;
        },
    });
};
</script>

<template>
    <Head title="Panel del Sistema" />

    <div class="min-h-screen bg-gray-100">
        <!-- Navbar -->
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

        <main class="max-w-5xl mx-auto py-10 px-6 space-y-10">

            <!-- Formulario nuevo tenant -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-6">Nuevo Tenant</h2>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del tenant</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Ej: Clínica Bella"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Dominio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del dominio</label>
                        <div class="flex items-center border border-gray-300 rounded-md overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                            <input
                                v-model="form.domain"
                                type="text"
                                placeholder="clinicabella"
                                class="flex-1 px-3 py-2 text-sm focus:outline-none"
                                pattern="[a-z0-9-]+"
                                title="Solo letras minúsculas, números y guiones"
                                required
                            />
                            <span class="bg-gray-100 border-l border-gray-300 px-3 py-2 text-sm text-gray-500 select-none">.localhost</span>
                        </div>
                        <p v-if="form.errors.domain" class="mt-1 text-xs text-red-600">{{ form.errors.domain }}</p>
                    </div>

                    <!-- Logo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        <div class="flex items-center gap-4">
                            <img
                                v-if="logoPreview"
                                :src="logoPreview"
                                class="h-16 w-16 rounded-lg object-cover border border-gray-200"
                            />
                            <div
                                v-else
                                class="h-16 w-16 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-xs"
                            >
                                Sin logo
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                @change="onLogoChange"
                                class="text-sm text-gray-600"
                            />
                        </div>
                        <p v-if="form.errors.logo" class="mt-1 text-xs text-red-600">{{ form.errors.logo }}</p>
                    </div>

                    <!-- Info usuario admin -->
                    <div class="bg-indigo-50 border border-indigo-100 rounded-md px-4 py-3 text-sm text-indigo-700">
                        Se creará automáticamente el usuario
                        <strong>admin@{{ form.domain || 'dominio' }}.localhost</strong>
                        con contraseña <strong>123456</strong>.
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-indigo-600 text-white px-5 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 transition"
                        >
                            {{ form.processing ? 'Creando...' : 'Crear Tenant' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Lista de tenants -->
            <section class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Tenants existentes</h2>

                <div v-if="tenants.length === 0" class="text-sm text-gray-400">
                    No hay tenants creados todavía.
                </div>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-500">
                            <th class="pb-2 font-medium">Logo</th>
                            <th class="pb-2 font-medium">Nombre</th>
                            <th class="pb-2 font-medium">ID</th>
                            <th class="pb-2 font-medium">Dominio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="tenant in tenants" :key="tenant.id" class="py-2">
                            <td class="py-2 pr-4">
                                <img
                                    v-if="tenant.logo"
                                    :src="tenant.logo"
                                    class="h-10 w-10 rounded object-cover"
                                />
                                <div
                                    v-else
                                    class="h-10 w-10 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs"
                                >
                                    —
                                </div>
                            </td>
                            <td class="py-2 pr-4 font-medium text-gray-800">{{ tenant.name ?? '—' }}</td>
                            <td class="py-2 pr-4 text-gray-500 font-mono">{{ tenant.id }}</td>
                            <td class="py-2 text-gray-500">
                                <a
                                    v-for="d in tenant.domains"
                                    :key="d"
                                    :href="'http://' + d"
                                    target="_blank"
                                    class="text-indigo-600 hover:underline"
                                >{{ d }}</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

        </main>
    </div>
</template>
