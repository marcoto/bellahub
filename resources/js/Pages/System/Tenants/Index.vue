<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import SystemLayout from '@/Layouts/SystemLayout.vue'

const props = defineProps({
    user: Object,
    tenants: Array,
})

const planLabel = (plan) => ({ basic: 'Básico', professional: 'Profesional', enterprise: 'Enterprise' }[plan] ?? 'Básico')

const confirmDelete = ref(null)

function deleteTenant(id) {
    if (confirmDelete.value === id) {
        router.delete(route('system.tenants.destroy', id))
        confirmDelete.value = null
    } else {
        confirmDelete.value = id
    }
}
</script>

<template>
    <Head title="Peluquerías - BellaHub" />
    <SystemLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800">Peluquerías</h1>
        </template>

        <!-- Header row -->
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-gray-500">{{ tenants.length }} peluquerías registradas</p>
            <Link
                :href="route('system.tenants.create')"
                class="bg-violet-600 text-white text-sm px-4 py-2.5 rounded-lg hover:bg-violet-700 transition-colors font-medium"
            >
                + Nueva peluquería
            </Link>
        </div>

        <!-- Empty state -->
        <div v-if="tenants.length === 0" class="bg-white rounded-xl p-16 text-center border border-gray-100">
            <div class="w-16 h-16 bg-violet-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <p class="text-gray-500 text-sm">No hay peluquerías registradas.</p>
            <Link :href="route('system.tenants.create')" class="mt-4 inline-block text-violet-600 text-sm hover:underline font-medium">
                Crea la primera peluquería →
            </Link>
        </div>

        <!-- Cards grid -->
        <div v-else class="grid grid-cols-3 gap-5">
            <div
                v-for="tenant in tenants"
                :key="tenant.id"
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
            >
                <!-- Top color bar -->
                <div class="h-1.5 w-full" :style="{ background: `linear-gradient(to right, ${tenant.primary_color || '#8b5cf6'}, ${tenant.secondary_color || '#6d28d9'})` }"></div>

                <div class="p-5">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0"
                                 :style="{ backgroundColor: (tenant.primary_color || '#8b5cf6') + '15' }">
                                <img v-if="tenant.logo" :src="tenant.logo" class="w-full h-full object-cover" />
                                <span v-else class="text-base font-bold" :style="{ color: tenant.primary_color || '#8b5cf6' }">
                                    {{ tenant.name?.charAt(0)?.toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 text-sm">{{ tenant.name }}</p>
                                <p class="text-xs text-gray-400">{{ tenant.email }}</p>
                            </div>
                        </div>
                        <span :class="[
                            'px-2 py-0.5 rounded-full text-xs font-medium',
                            tenant.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                        ]">
                            {{ tenant.status === 'active' ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>

                    <!-- Info -->
                    <div class="space-y-1.5 mb-4">
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ tenant.id }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                            </svg>
                            <a v-if="tenant.domains?.[0]" :href="'http://' + tenant.domains[0]" target="_blank"
                               class="text-violet-600 hover:underline">
                                {{ tenant.domains[0] }}
                            </a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-violet-100 text-violet-700">
                            {{ planLabel(tenant.plan) }}
                        </span>
                        <span class="text-xs text-gray-400">{{ tenant.created_at }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <a v-if="tenant.domains?.[0]"
                           :href="'http://' + tenant.domains[0]"
                           target="_blank"
                           class="flex-1 text-center text-xs py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium transition-colors"
                        >
                            Abrir ↗
                        </a>
                        <Link
                            :href="route('system.tenants.edit', tenant.id)"
                            class="flex-1 text-center text-xs py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 font-medium transition-colors"
                        >
                            Editar
                        </Link>
                        <button
                            @click="deleteTenant(tenant.id)"
                            :class="[
                                'flex-1 text-xs py-2 rounded-lg font-medium transition-colors',
                                confirmDelete === tenant.id
                                    ? 'bg-red-600 text-white border border-red-600'
                                    : 'border border-red-200 text-red-500 hover:bg-red-50'
                            ]"
                        >
                            {{ confirmDelete === tenant.id ? '¿Confirmar?' : 'Eliminar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </SystemLayout>
</template>
