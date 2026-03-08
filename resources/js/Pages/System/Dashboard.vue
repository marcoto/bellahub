<script setup>
import { Head, Link } from '@inertiajs/vue3'
import SystemLayout from '@/Layouts/SystemLayout.vue'

defineProps({
    user: Object,
    stats: Object,
    tenants: Array,
})

const planLabel = (plan) => ({ basic: 'Básico', professional: 'Profesional', enterprise: 'Enterprise' }[plan] ?? 'Básico')
</script>

<template>
    <Head title="Dashboard - BellaHub" />
    <SystemLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
        </template>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="bg-[#1a1744] rounded-xl p-6">
                <p class="text-sm text-gray-400 mb-1">Total peluquerías</p>
                <p class="text-4xl font-bold text-white">{{ stats.total }}</p>
            </div>
            <div class="bg-[#1a1744] rounded-xl p-6">
                <p class="text-sm text-gray-400 mb-1">Activas</p>
                <p class="text-4xl font-bold text-yellow-400">{{ stats.active }}</p>
            </div>
            <div class="bg-[#1a1744] rounded-xl p-6">
                <p class="text-sm text-gray-400 mb-1">Nuevas este mes</p>
                <p class="text-4xl font-bold text-yellow-400">{{ stats.thisMonth }}</p>
            </div>
        </div>

        <!-- Recent tenants -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">Peluquerías recientes</h2>
                <Link
                    :href="route('system.tenants.create')"
                    class="bg-violet-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-violet-700 transition-colors font-medium"
                >
                    + Nueva
                </Link>
            </div>

            <div v-if="!tenants || tenants.length === 0" class="px-6 py-10 text-center text-gray-400 text-sm">
                No hay peluquerías registradas.
            </div>

            <table v-else class="w-full">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase tracking-wider border-b border-gray-50">
                        <th class="px-6 py-3 text-left font-medium">Peluquería</th>
                        <th class="px-6 py-3 text-left font-medium">Plan</th>
                        <th class="px-6 py-3 text-left font-medium">Estado</th>
                        <th class="px-6 py-3 text-left font-medium">Creada</th>
                        <th class="px-6 py-3 text-left font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="tenant in tenants" :key="tenant.id" class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0"
                                     :style="{ backgroundColor: (tenant.primary_color || '#8b5cf6') + '20' }">
                                    <img v-if="tenant.logo" :src="tenant.logo" class="w-full h-full object-cover" />
                                    <span v-else class="text-sm font-bold" :style="{ color: tenant.primary_color || '#8b5cf6' }">
                                        {{ tenant.name?.charAt(0)?.toUpperCase() }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ tenant.name }}</p>
                                    <p class="text-xs text-gray-400">{{ tenant.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-violet-100 text-violet-700">
                                {{ planLabel(tenant.plan) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="[
                                'px-2.5 py-1 rounded-full text-xs font-medium',
                                tenant.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                            ]">
                                {{ tenant.status === 'active' ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ tenant.created_at }}</td>
                        <td class="px-6 py-4">
                            <Link
                                :href="route('system.tenants.edit', tenant.id)"
                                class="text-sm text-violet-600 hover:text-violet-800 font-medium border border-violet-200 px-3 py-1 rounded-lg hover:bg-violet-50 transition-colors"
                            >
                                Gestionar
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SystemLayout>
</template>
