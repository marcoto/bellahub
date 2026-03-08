<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Select from 'primevue/select'
import MultiSelect from 'primevue/multiselect'
import DatePicker from 'primevue/datepicker'

const page = usePage()
const tenant = computed(() => page.props.tenant)

const props = defineProps({
    bookings: Object,
    workers: Array,
    serviceCategories: Array,
    filters: Object,
})

// Filters
const filterDateObj  = ref(props.filters?.date ? new Date(props.filters.date + 'T00:00:00') : null)
const filterWorker   = ref(props.filters?.worker_id ? Number(props.filters.worker_id) : null)
const filterStatus   = ref(props.filters?.status ?? null)

const statusOptions = [
    { label: 'Todos los estados', value: null },
    { label: 'Pendiente',  value: 'pending' },
    { label: 'Confirmada', value: 'confirmed' },
    { label: 'Cancelada',  value: 'cancelled' },
    { label: 'Completada', value: 'completed' },
]

function applyFilters() {
    router.get(route('bookings.index'), {
        date:      filterDateObj.value ? filterDateObj.value.toISOString().slice(0, 10) : undefined,
        worker_id: filterWorker.value || undefined,
        status:    filterStatus.value || undefined,
    }, { preserveState: true, replace: true })
}

function clearFilters() {
    filterDateObj.value = null
    filterWorker.value  = null
    filterStatus.value  = null
    router.get(route('bookings.index'))
}

// Create/Edit modal
const showModal = ref(false)
const editingBooking = ref(null)

const form = useForm({
    client_name:  '',
    client_phone: '',
    date:         new Date().toISOString().slice(0, 10),
    time_start:   '09:00',
    time_end:     '10:00',
    worker_id:    '',
    service_ids:  [],
    status:       'confirmed',
    notes:        '',
})

const formDateObj = computed({
    get: () => form.date ? new Date(form.date + 'T00:00:00') : null,
    set: (val) => { form.date = val ? val.toISOString().slice(0, 10) : '' },
})

const formStatusOptions = [
    { label: 'Pendiente',  value: 'pending' },
    { label: 'Confirmada', value: 'confirmed' },
    { label: 'Cancelada',  value: 'cancelled' },
    { label: 'Completada', value: 'completed' },
]

function openCreate() {
    editingBooking.value = null
    form.reset()
    form.date = new Date().toISOString().slice(0, 10)
    form.time_start = '09:00'
    form.time_end = '10:00'
    form.status = 'confirmed'
    form.service_ids = []
    showModal.value = true
}

function openEdit(booking) {
    editingBooking.value = booking
    form.client_name  = booking.client_name
    form.client_phone = booking.client_phone ?? ''
    form.date         = booking.date_raw
    form.time_start   = booking.time_start
    form.time_end     = booking.time_end
    form.worker_id    = booking.worker?.id ?? ''
    form.service_ids  = booking.services.map(s => s.id)
    form.status       = booking.status
    form.notes        = booking.notes ?? ''
    showModal.value = true
}

function submitForm() {
    if (editingBooking.value) {
        form.put(route('bookings.update', editingBooking.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('bookings.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

function deleteBooking(id) {
    if (confirm('¿Eliminar esta reserva?')) {
        router.delete(route('bookings.destroy', id))
    }
}

const statusColors = {
    confirmed: 'bg-green-100 text-green-700',
    pending:   'bg-yellow-100 text-yellow-700',
    cancelled: 'bg-red-100 text-red-700',
    completed: 'bg-gray-100 text-gray-600',
}
</script>

<template>
    <Head title="Reservas" />
    <AppLayout>
        <div class="p-7">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Reservas</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Gestiona las citas de la peluquería</p>
                </div>
                <div class="flex gap-2">
                    <button class="text-sm px-3 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 flex items-center gap-1.5">
                        📅 Calendario
                    </button>
                    <button
                        @click="openCreate"
                        class="text-sm px-4 py-2 rounded-lg text-white font-medium flex items-center gap-1.5"
                        :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }"
                    >
                        + Nueva reserva
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5 flex items-center gap-3 flex-wrap">
                <DatePicker
                    v-model="filterDateObj"
                    dateFormat="dd/mm/yy"
                    placeholder="Fecha"
                    showClear
                    class="h-[42px]"
                />
                <Select
                    v-model="filterWorker"
                    :options="workers"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Todos los trabajadores"
                    showClear
                    class="min-w-[180px]"
                />
                <Select
                    v-model="filterStatus"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Todos los estados"
                    showClear
                    class="min-w-[160px]"
                />
                <button @click="applyFilters" class="px-4 py-2 rounded-lg text-white text-sm font-medium" :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }">
                    Filtrar
                </button>
                <button @click="clearFilters" class="px-4 py-2 rounded-lg text-sm text-gray-500 hover:text-gray-700 border border-gray-200">
                    Limpiar
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div v-if="!bookings?.data?.length" class="p-12 text-center text-gray-400 text-sm">
                    No hay reservas que mostrar.
                </div>
                <table v-else class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wide border-b border-gray-50">
                            <th class="px-5 py-3 text-left font-medium">Cliente</th>
                            <th class="px-5 py-3 text-left font-medium">Fecha / Hora</th>
                            <th class="px-5 py-3 text-left font-medium">Trabajador</th>
                            <th class="px-5 py-3 text-left font-medium">Servicios</th>
                            <th class="px-5 py-3 text-left font-medium">Estado</th>
                            <th class="px-5 py-3 text-left font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="text-sm font-medium text-gray-800">{{ booking.client_name }}</p>
                                <p v-if="booking.client_phone" class="text-xs text-gray-400">{{ booking.client_phone }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-sm text-gray-700">{{ booking.date }}</p>
                                <p class="text-xs text-gray-400">{{ booking.time_start }} - {{ booking.time_end }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <div v-if="booking.worker" class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: booking.worker.color }"></div>
                                    <span class="text-sm text-gray-700">{{ booking.worker.name }}</span>
                                </div>
                                <span v-else class="text-sm text-gray-400">—</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div v-if="booking.services?.length" class="flex flex-wrap gap-1">
                                    <span
                                        v-for="s in booking.services"
                                        :key="s.id"
                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700"
                                    >
                                        {{ s.name }}
                                    </span>
                                </div>
                                <span v-else class="text-sm text-gray-400">—</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', statusColors[booking.status] || 'bg-gray-100 text-gray-500']">
                                    {{ booking.status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex gap-2">
                                    <button @click="openEdit(booking)" class="text-xs text-gray-500 hover:text-gray-800 border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50 transition-colors">
                                        Editar
                                    </button>
                                    <button @click="deleteBooking(booking.id)" class="text-xs text-red-500 hover:text-red-700 border border-red-100 px-2.5 py-1 rounded-lg hover:bg-red-50 transition-colors">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="bookings?.last_page > 1" class="flex justify-center gap-1 mt-4">
                <Link v-for="link in bookings.links" :key="link.label"
                      :href="link.url || '#'"
                      :class="[
                          'px-3 py-1.5 text-xs rounded-lg border',
                          link.active ? 'border-violet-500 text-violet-600 bg-violet-50' : 'border-gray-200 text-gray-500 hover:bg-gray-50',
                          !link.url ? 'opacity-40 cursor-not-allowed' : ''
                      ]"
                      v-html="link.label"
                />
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 bg-black/25 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                <div class="modal-panel bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-screen overflow-y-auto">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ editingBooking ? 'Editar reserva' : 'Nueva reserva' }}
                        </h2>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Nombre del cliente *</label>
                                <input v-model="form.client_name" type="text" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-violet-500" />
                                <p v-if="form.errors.client_name" class="text-xs text-red-500 mt-1">{{ form.errors.client_name }}</p>
                            </div>
                            <div>
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Teléfono</label>
                                <input v-model="form.client_phone" type="text"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                            <div>
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Fecha *</label>
                                <DatePicker
                                    v-model="formDateObj"
                                    dateFormat="dd/mm/yy"
                                    showClear
                                    class="w-full"
                                    inputClass="w-full border border-gray-200 rounded-lg px-3 py-2 text-base"
                                />
                                <p v-if="form.errors.date" class="text-xs text-red-500 mt-1">{{ form.errors.date }}</p>
                            </div>
                            <div>
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Hora inicio *</label>
                                <input v-model="form.time_start" type="time" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                            <div>
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Hora fin *</label>
                                <input v-model="form.time_end" type="time" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                             <div class="col-span-2">
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Servicios</label>
                                <MultiSelect
                                    v-model="form.service_ids"
                                    :options="serviceCategories"
                                    optionLabel="name"
                                    optionValue="id"
                                    optionGroupLabel="label"
                                    optionGroupChildren="items"
                                    filter
                                    filterPlaceholder="Buscar servicio..."
                                    placeholder="Seleccionar servicios"
                                    display="chip"
                                    class="w-full"
                                />
                                <p v-if="form.errors.service_ids" class="text-xs text-red-500 mt-1">{{ form.errors.service_ids }}</p>
                            </div>
                            <div>
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Trabajador</label>
                                <Select
                                    v-model="form.worker_id"
                                    :options="workers"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Sin asignar"
                                    showClear
                                    class="w-full"
                                />
                            </div>
                            <div>
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Estado</label>
                                <Select
                                    v-model="form.status"
                                    :options="formStatusOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    class="w-full"
                                />
                            </div>
                           
                            <div class="col-span-2">
                                <label class="block text-base font-medium text-gray-600 mb-1.5">Notas</label>
                                <textarea v-model="form.notes" rows="2"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-base focus:outline-none focus:ring-2 focus:ring-violet-500"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2.5 text-base text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="px-5 py-2.5 text-base text-white rounded-lg font-medium disabled:opacity-60"
                                :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }">
                                {{ form.processing ? 'Guardando...' : (editingBooking ? 'Guardar cambios' : 'Crear reserva') }}
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
