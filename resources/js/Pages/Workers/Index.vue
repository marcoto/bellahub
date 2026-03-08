<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const tenant = computed(() => page.props.tenant)

const props = defineProps({
    workers: Array,
})
</script>

<template>
    <Head title="Trabajadores" />
    <AppLayout>
        <div class="p-7">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Trabajadores</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Estado del equipo en tiempo real</p>
                </div>
                <Link
                    :href="route('users.index')"
                    class="text-sm px-4 py-2 rounded-lg text-white font-medium"
                    :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }"
                >
                    + Añadir trabajador
                </Link>
            </div>

            <!-- Workers grid -->
            <div v-if="!workers?.length" class="bg-white rounded-xl border border-gray-100 p-16 text-center text-gray-400 text-sm">
                No hay trabajadores activos.
            </div>

            <div v-else class="grid grid-cols-2 xl:grid-cols-3 gap-5">
                <div
                    v-for="worker in workers"
                    :key="worker.id"
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
                >
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-base font-bold"
                                     :style="{ backgroundColor: worker.color }">
                                    {{ worker.name?.slice(0,2)?.toUpperCase() }}
                                </div>
                                <div :class="[
                                    'absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white',
                                    worker.is_clocked_in ? 'bg-green-400' : 'bg-yellow-400'
                                ]"></div>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ worker.name }}</p>
                                <p class="text-xs text-gray-400">{{ worker.email }}</p>
                                <p class="text-xs mt-0.5" :class="worker.is_clocked_in ? 'text-green-600' : 'text-gray-400'">
                                    {{ worker.is_clocked_in ? 'Fichado' : 'Sin fichar' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="text-center">
                            <p class="text-xl font-bold text-gray-800">{{ worker.bookings_today }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Citas hoy</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xl font-bold text-gray-800">{{ worker.hours_today ? worker.hours_today + 'h' : '0h' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Hoy</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xl font-bold text-gray-800">{{ worker.hours_year ? worker.hours_year + 'h' : '0h' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Este año</p>
                        </div>
                    </div>

                    <!-- Vacaciones -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-500">Vacaciones</span>
                            <span class="text-xs text-gray-400">{{ worker.vacation_used }}/{{ worker.vacation_days }} días</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all"
                                :style="{
                                    width: Math.min(100, (worker.vacation_used / worker.vacation_days) * 100) + '%',
                                    backgroundColor: tenant?.primary_color || '#8b5cf6'
                                }"
                            ></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-3 gap-2">
                        <Link :href="route('workers.time') + '?user_id=' + worker.id"
                              class="text-center text-xs py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors font-medium">
                            Fichajes
                        </Link>
                        <Link :href="route('workers.vacations')"
                              class="text-center text-xs py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors font-medium">
                            Vacaciones
                        </Link>
                        <Link :href="route('users.index')"
                              class="text-center text-xs py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors font-medium">
                            Editar
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
