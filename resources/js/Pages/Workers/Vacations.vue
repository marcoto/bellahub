<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import DatePicker from 'primevue/datepicker'

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

const fromDateObj = computed({
    get: () => form.from_date ? new Date(form.from_date + 'T00:00:00') : null,
    set: (val) => { form.from_date = val ? val.toISOString().slice(0, 10) : '' },
})

const toDateObj = computed({
    get: () => form.to_date ? new Date(form.to_date + 'T00:00:00') : null,
    set: (val) => { form.to_date = val ? val.toISOString().slice(0, 10) : '' },
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
                    <h1 class="text-2xl font-bold text-gray-800">Vacaciones</h1>
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
                            <th class="w-px px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Acciones</th>
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
                                <div class="flex items-center gap-1">
                                    <template v-if="req.can_approve">
                                        <button @click="approve(req.id)"
                                            class="p-1.5 rounded-lg transition-colors"
                                            :style="{ color: tenant?.primary_color || '#8b5cf6' }"
                                            title="Aprobar"
                                            @mouseenter="e => e.currentTarget.style.backgroundColor = (tenant?.primary_color || '#8b5cf6') + '18'"
                                            @mouseleave="e => e.currentTarget.style.backgroundColor = ''">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                        <button @click="reject(req.id)"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                            title="Rechazar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </template>
                                    <button @click="deleteRequest(req.id)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                        title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
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
            <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 bg-black/25 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                <div class="modal-panel bg-white rounded-xl shadow-xl w-full max-w-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-800">Solicitar vacaciones</h2>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Fecha inicio *</label>
                                <DatePicker
                                    v-model="fromDateObj"
                                    dateFormat="dd/mm/yy"
                                    showClear
                                    class="w-full"
                                />
                                <p v-if="form.errors.from_date" class="text-xs text-red-500 mt-1">{{ form.errors.from_date }}</p>
                            </div>
                            <div>
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Fecha fin *</label>
                                <DatePicker
                                    v-model="toDateObj"
                                    dateFormat="dd/mm/yy"
                                    showClear
                                    class="w-full"
                                />
                                <p v-if="form.errors.to_date" class="text-xs text-red-500 mt-1">{{ form.errors.to_date }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-base font-medium text-gray-600 mb-1.5">Notas</label>
                            <textarea v-model="form.notes" rows="3" placeholder="Motivo o comentarios opcionales..."
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-violet-500"></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2.5 text-base text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="px-5 py-2.5 text-base text-white rounded-lg font-medium disabled:opacity-60"
                                :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }">
                                {{ form.processing ? 'Enviando...' : 'Enviar solicitud' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-active .modal-panel,
.modal-leave-active .modal-panel {
    transition: transform 0.25s ease, opacity 0.25s ease;
}
.modal-enter-from .modal-panel,
.modal-leave-to .modal-panel {
    transform: scale(0.95) translateY(10px);
    opacity: 0;
}
</style>
