<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const tenant = computed(() => page.props.tenant)

const props = defineProps({
    requests: Array,
    totalDays: Number,
    usedDays: Number,
    pendingDays: Number,
    availableDays: Number,
    isAdmin: Boolean,
})

const showModal = ref(false)
const form = useForm({
    from_date: '',
    to_date:   '',
    notes:     '',
})

function submit() {
    form.post(route('workers.vacations.store'), {
        onSuccess: () => { showModal.value = false; form.reset() },
    })
}

function approve(id) {
    router.post(route('workers.vacations.approve', id))
}

function reject(id) {
    router.post(route('workers.vacations.reject', id))
}

function deleteRequest(id) {
    if (confirm('¿Eliminar esta solicitud?')) {
        router.delete(route('workers.vacations.destroy', id))
    }
}

const statusColors = {
    pending:  'bg-yellow-100 text-yellow-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-600',
}

const usedPercent = computed(() => Math.min(100, (props.usedDays / props.totalDays) * 100))
</script>

<template>
    <Head title="Vacaciones" />
    <AppLayout>
        <div class="p-7">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Vacaciones</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Gestiona las solicitudes de días libres</p>
                </div>
                <button
                    @click="showModal = true"
                    class="text-sm px-4 py-2 rounded-lg text-white font-medium"
                    :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }"
                >
                    + Solicitar vacaciones
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                    <p class="text-3xl font-bold text-gray-800">{{ totalDays }}</p>
                    <p class="text-xs text-gray-400 mt-1">Días totales</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                    <p class="text-3xl font-bold text-green-500">{{ availableDays }}</p>
                    <p class="text-xs text-gray-400 mt-1">Disponibles</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                    <p class="text-3xl font-bold text-yellow-500">{{ pendingDays }}</p>
                    <p class="text-xs text-gray-400 mt-1">Pendientes</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                    <p class="text-3xl font-bold text-gray-700">{{ usedDays }}</p>
                    <p class="text-xs text-gray-400 mt-1">Usados</p>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-700">Uso de vacaciones este año</p>
                    <p class="text-sm text-gray-400">{{ usedDays }}/{{ totalDays }} días</p>
                </div>
                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all"
                        :style="{ width: usedPercent + '%', backgroundColor: tenant?.primary_color || '#8b5cf6' }"
                    ></div>
                </div>
            </div>

            <!-- Requests -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50">
                    <h3 class="text-sm font-semibold text-gray-800">Solicitudes</h3>
                </div>

                <div v-if="!requests?.length" class="p-10 text-center text-gray-400 text-sm">
                    No hay solicitudes de vacaciones.
                </div>

                <table v-else class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wide border-b border-gray-50">
                            <th class="px-5 py-3 text-left font-medium">Trabajador</th>
                            <th class="px-5 py-3 text-left font-medium">Desde</th>
                            <th class="px-5 py-3 text-left font-medium">Hasta</th>
                            <th class="px-5 py-3 text-left font-medium">Días</th>
                            <th class="px-5 py-3 text-left font-medium">Estado</th>
                            <th class="px-5 py-3 text-left font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="req in requests" :key="req.id" class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-5 py-3.5 text-sm text-gray-700">{{ req.user }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ req.from_date }}</td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">{{ req.to_date }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ req.days }} días
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', statusColors[req.status] || 'bg-gray-100 text-gray-500']">
                                    {{ req.status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex gap-2">
                                    <template v-if="req.can_approve">
                                        <button @click="approve(req.id)"
                                            class="text-xs text-white px-2.5 py-1 rounded-lg font-medium"
                                            :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }">
                                            Aprobar
                                        </button>
                                        <button @click="reject(req.id)"
                                            class="text-xs text-red-600 border border-red-200 px-2.5 py-1 rounded-lg hover:bg-red-50 transition-colors">
                                            Rechazar
                                        </button>
                                    </template>
                                    <button @click="deleteRequest(req.id)"
                                        class="text-xs text-gray-400 hover:text-gray-600 border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50 transition-colors">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Request Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h2 class="text-base font-semibold text-gray-800">Solicitar vacaciones</h2>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha inicio *</label>
                                <input v-model="form.from_date" type="date" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                                <p v-if="form.errors.from_date" class="text-xs text-red-500 mt-1">{{ form.errors.from_date }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha fin *</label>
                                <input v-model="form.to_date" type="date" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                                <p v-if="form.errors.to_date" class="text-xs text-red-500 mt-1">{{ form.errors.to_date }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                            <textarea v-model="form.notes" rows="3" placeholder="Motivo o comentarios opcionales..."
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="px-5 py-2 text-sm text-white rounded-lg font-medium disabled:opacity-60"
                                :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }">
                                {{ form.processing ? 'Enviando...' : 'Enviar solicitud' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
