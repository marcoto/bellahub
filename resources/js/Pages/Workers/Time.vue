<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const tenant = computed(() => page.props.tenant)
const authUser = computed(() => page.props.auth?.user)

const props = defineProps({
    records: Array,
    hoursToday: Number,
    daysWorked: Number,
    hoursYear: Number,
    isClockedIn: Boolean,
    openRecord: Object,
    currentMonth: Number,
    currentYear: Number,
    targetUserId: Number,
    workers: Array,
})

const month = ref(props.currentMonth)
const year  = ref(props.currentYear)
const selectedUser = ref(props.targetUserId)

function applyFilters() {
    router.get(route('workers.time'), {
        month: month.value,
        year: year.value,
        user_id: authUser.value?.role === 'admin' ? selectedUser.value : undefined,
    }, { preserveState: true, replace: true })
}

const clockForm = useForm({})

function clockIn() {
    clockForm.post(route('workers.clock-in'), { onSuccess: () => router.reload() })
}
function clockOut() {
    clockForm.post(route('workers.clock-out'), { onSuccess: () => router.reload() })
}

const months = [
    'Enero','Febrero','Marzo','Abril','Mayo','Junio',
    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
]
</script>

<template>
    <Head title="Fichaje" />
    <AppLayout>
        <div class="p-7">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Fichaje</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Registro de entradas y salidas</p>
                </div>
                <!-- Worker selector (admin only) -->
                <select v-if="authUser?.role === 'admin' && workers?.length"
                    v-model="selectedUser"
                    @change="applyFilters"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option v-for="w in workers" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
            </div>

            <!-- Stats + Clock in/out -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="w-9 h-9 bg-violet-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">{{ hoursToday ? hoursToday + 'h' : '0h' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Horas hoy</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">{{ daysWorked }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Días trabajados (mes)</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">{{ hoursYear ? hoursYear + 'h' : '0h' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Horas este año</p>
                </div>

                <!-- Clock in/out button -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-col items-center justify-center gap-3">
                    <div class="flex items-center gap-2">
                        <div :class="['w-3 h-3 rounded-full', isClockedIn ? 'bg-green-400' : 'bg-gray-300']"></div>
                        <span class="text-xs text-gray-500">{{ isClockedIn ? 'Fichado' : 'Sin fichar' }}</span>
                    </div>
                    <button
                        v-if="!isClockedIn"
                        @click="clockIn"
                        :disabled="clockForm.processing"
                        class="w-full py-2 rounded-lg text-white text-sm font-medium disabled:opacity-60"
                        :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }"
                    >
                        ▶ Fichar entrada
                    </button>
                    <button
                        v-else
                        @click="clockOut"
                        :disabled="clockForm.processing"
                        class="w-full py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-800 disabled:opacity-60"
                    >
                        ⏹ Fichar salida
                    </button>
                    <p v-if="openRecord" class="text-xs text-gray-400">Entrada: {{ openRecord.check_in }}</p>
                </div>
            </div>

            <!-- Month/Year filter -->
            <div class="flex items-center gap-3 mb-4">
                <select v-model="month" @change="applyFilters"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option v-for="(m, i) in months" :key="i+1" :value="i+1">{{ m }}</option>
                </select>
                <input v-model="year" type="number" @change="applyFilters"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 w-24" />
            </div>

            <!-- Records table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Registro de fichajes</h3>
                    <span class="text-xs text-gray-400">{{ records?.length ?? 0 }} registros</span>
                </div>

                <div v-if="!records?.length" class="p-10 text-center text-gray-400 text-sm">
                    No hay registros para el periodo seleccionado.
                </div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wide border-b border-gray-50">
                            <th class="px-5 py-3 text-left font-medium">Fecha</th>
                            <th class="px-5 py-3 text-left font-medium">Entrada</th>
                            <th class="px-5 py-3 text-left font-medium">Salida</th>
                            <th class="px-5 py-3 text-left font-medium">Duración</th>
                            <th class="px-5 py-3 text-left font-medium">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="r in records" :key="r.id" class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-5 py-3.5 text-sm text-gray-700">{{ r.date }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-700">{{ r.check_in }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span v-if="r.check_out" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-600">{{ r.check_out }}</span>
                                <span v-else class="text-xs text-gray-300">—</span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ r.duration || '0h 00m' }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-400">{{ r.notes || '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
