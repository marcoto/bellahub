<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
    services:   { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    workers:    { type: Array, default: () => [] },
})

const page    = usePage()
const primary = computed(() => page.props.tenant?.primary_color || '#8b5cf6')

// ── Flash ──────────────────────────────────────────────────────────────────────
const flash = computed(() => page.props.flash?.success)

// ── Search / filter ───────────────────────────────────────────────────────────
const search       = ref('')
const filterActive = ref('all') // 'all' | 'active' | 'inactive'

const filteredServices = computed(() => {
    return props.services.filter(s => {
        const matchSearch = !search.value || s.name.toLowerCase().includes(search.value.toLowerCase())
        const matchActive =
            filterActive.value === 'all' ||
            (filterActive.value === 'active' && s.is_active) ||
            (filterActive.value === 'inactive' && !s.is_active)
        return matchSearch && matchActive
    })
})

// ── Service CRUD ──────────────────────────────────────────────────────────────
const showServiceModal = ref(false)
const editingService   = ref(null)

const serviceForm = useForm({
    name:         '',
    description:  '',
    duration:     30,
    price:        0,
    is_active:    true,
    category_ids: [],
    worker_ids:   [],
})

function openCreate() {
    editingService.value = null
    serviceForm.reset()
    serviceForm.is_active    = true
    serviceForm.duration     = 30
    serviceForm.price        = 0
    serviceForm.category_ids = []
    serviceForm.worker_ids   = []
    showServiceModal.value   = true
}

function openEdit(service) {
    editingService.value         = service
    serviceForm.name             = service.name
    serviceForm.description      = service.description || ''
    serviceForm.duration         = service.duration
    serviceForm.price            = service.price
    serviceForm.is_active        = service.is_active
    serviceForm.category_ids     = service.categories.map(c => c.id)
    serviceForm.worker_ids       = service.workers.map(w => w.id)
    showServiceModal.value       = true
}

function closeServiceModal() {
    showServiceModal.value = false
    editingService.value   = null
    serviceForm.reset()
}

function submitService() {
    if (editingService.value) {
        serviceForm.put(route('services.update', editingService.value.id), {
            onSuccess: closeServiceModal,
        })
    } else {
        serviceForm.post(route('services.store'), {
            onSuccess: closeServiceModal,
        })
    }
}

function deleteService(service) {
    if (!confirm(`¿Eliminar el servicio "${service.name}"? Esta acción no se puede deshacer.`)) return
    useForm({}).delete(route('services.destroy', service.id))
}

// ── Toggle category selection ─────────────────────────────────────────────────
function toggleCategory(id) {
    const idx = serviceForm.category_ids.indexOf(id)
    if (idx === -1) serviceForm.category_ids.push(id)
    else serviceForm.category_ids.splice(idx, 1)
}

function toggleWorker(id) {
    const idx = serviceForm.worker_ids.indexOf(id)
    if (idx === -1) serviceForm.worker_ids.push(id)
    else serviceForm.worker_ids.splice(idx, 1)
}

// ── Categories Modal ───────────────────────────────────────────────────────────
const showCategoriesModal = ref(false)
const editingCategory     = ref(null)

const categoryForm = useForm({
    name:        '',
    description: '',
    color:       '#8b5cf6',
})

function openCategoryCreate() {
    editingCategory.value  = null
    categoryForm.reset()
    categoryForm.color = '#8b5cf6'
}

function openCategoryEdit(cat) {
    editingCategory.value    = cat
    categoryForm.name        = cat.name
    categoryForm.description = cat.description || ''
    categoryForm.color       = cat.color
}

function cancelCategoryEdit() {
    editingCategory.value = null
    categoryForm.reset()
    categoryForm.color = '#8b5cf6'
}

function submitCategory() {
    if (editingCategory.value) {
        categoryForm.put(route('service-categories.update', editingCategory.value.id), {
            onSuccess: cancelCategoryEdit,
        })
    } else {
        categoryForm.post(route('service-categories.store'), {
            onSuccess: cancelCategoryEdit,
        })
    }
}

function deleteCategory(cat) {
    if (!confirm(`¿Eliminar la categoría "${cat.name}"?`)) return
    useForm({}).delete(route('service-categories.destroy', cat.id))
}

// ── Duration helpers ──────────────────────────────────────────────────────────
const durationPresets = [15, 30, 45, 60, 90, 120]

function formatDuration(min) {
    const h = Math.floor(min / 60)
    const m = min % 60
    if (h > 0 && m > 0) return `${h}h ${m}min`
    if (h > 0) return `${h}h`
    return `${m}min`
}

// ── Initials ──────────────────────────────────────────────────────────────────
function initials(name) {
    return name?.split(' ').map(p => p[0]).join('').slice(0, 2).toUpperCase() || '?'
}
</script>

<template>
    <AppLayout>
        <div class="p-6 space-y-6">

            <!-- ── Header ─────────────────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Servicios</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ props.services.length }} servicio{{ props.services.length !== 1 ? 's' : '' }} registrado{{ props.services.length !== 1 ? 's' : '' }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <!-- Categorías button -->
                    <button
                        @click="showCategoriesModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-600 text-sm font-medium hover:bg-gray-50 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Categorías
                    </button>
                    <!-- Nuevo Servicio -->
                    <button
                        @click="openCreate"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium transition-colors"
                        :style="{ backgroundColor: primary }"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo servicio
                    </button>
                </div>
            </div>

            <!-- ── Flash ──────────────────────────────────────────────────── -->
            <div v-if="flash" class="bg-green-50 text-green-700 border border-green-100 rounded-lg px-4 py-2.5 text-sm">
                {{ flash }}
            </div>

            <!-- ── Filters ────────────────────────────────────────────────── -->
            <div class="flex items-center gap-3">
                <!-- Search -->
                <div class="relative flex-1 max-w-xs">
                    <svg class="w-4 h-4 text-gray-300 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar servicios..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500/30 bg-white"
                    />
                </div>
                <!-- Status filter -->
                <div class="flex rounded-lg border border-gray-200 overflow-hidden bg-white text-sm">
                    <button
                        @click="filterActive = 'all'"
                        class="px-3 py-2 transition-colors"
                        :class="filterActive === 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50'"
                    >Todos</button>
                    <button
                        @click="filterActive = 'active'"
                        class="px-3 py-2 border-l border-gray-200 transition-colors"
                        :class="filterActive === 'active' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50'"
                    >Activos</button>
                    <button
                        @click="filterActive = 'inactive'"
                        class="px-3 py-2 border-l border-gray-200 transition-colors"
                        :class="filterActive === 'inactive' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50'"
                    >Inactivos</button>
                </div>
            </div>

            <!-- ── Services table ─────────────────────────────────────────── -->
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Servicio</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Categorías</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Empleados</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Duración</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Precio</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                            <th class="w-px px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredServices.length === 0">
                            <td colspan="7" class="text-center py-12 text-gray-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                <p class="text-sm">No hay servicios</p>
                            </td>
                        </tr>
                        <tr
                            v-for="service in filteredServices"
                            :key="service.id"
                            class="border-b border-gray-50 hover:bg-gray-50/60 transition-colors"
                        >
                            <!-- Name + description -->
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ service.name }}</p>
                                <p v-if="service.description" class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ service.description }}</p>
                            </td>

                            <!-- Categories -->
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="cat in service.categories"
                                        :key="cat.id"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium text-white"
                                        :style="{ backgroundColor: cat.color }"
                                    >{{ cat.name }}</span>
                                    <span v-if="!service.categories.length" class="text-xs text-gray-300">—</span>
                                </div>
                            </td>

                            <!-- Workers avatars -->
                            <td class="px-5 py-4">
                                <div class="flex -space-x-1">
                                    <div
                                        v-for="w in service.workers.slice(0,5)"
                                        :key="w.id"
                                        class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center text-white text-[9px] font-bold"
                                        :style="{ backgroundColor: w.color }"
                                        :title="w.name"
                                    >{{ initials(w.name) }}</div>
                                    <div
                                        v-if="service.workers.length > 5"
                                        class="w-6 h-6 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center text-gray-600 text-[9px] font-bold"
                                    >+{{ service.workers.length - 5 }}</div>
                                    <span v-if="!service.workers.length" class="text-xs text-gray-300">—</span>
                                </div>
                            </td>

                            <!-- Duration -->
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 text-gray-600">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ service.duration_label }}
                                </span>
                            </td>

                            <!-- Price -->
                            <td class="px-5 py-4 font-medium text-gray-800">
                                {{ service.price.toFixed(2) }} €
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="service.is_active
                                        ? 'bg-green-50 text-green-700'
                                        : 'bg-gray-100 text-gray-500'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="service.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                                    {{ service.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        @click="openEdit(service)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                                        title="Editar"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteService(service)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                        title="Eliminar"
                                    >
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

        <!-- ════════════════════════════════════════════════════════════════
             Service Create/Edit Modal
        ════════════════════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <div
                v-if="showServiceModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeServiceModal" />

                <!-- Panel -->
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
                        <h2 class="text-base font-semibold text-gray-900">
                            {{ editingService ? 'Editar servicio' : 'Nuevo servicio' }}
                        </h2>
                        <button @click="closeServiceModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitService" class="p-6 space-y-5">

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre del servicio *</label>
                            <input
                                v-model="serviceForm.name"
                                type="text"
                                required
                                placeholder="Ej: Corte de cabello"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500/30"
                            />
                            <p v-if="serviceForm.errors.name" class="text-red-500 text-xs mt-1">{{ serviceForm.errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción</label>
                            <textarea
                                v-model="serviceForm.description"
                                rows="2"
                                placeholder="Descripción breve del servicio..."
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500/30 resize-none"
                            />
                        </div>

                        <!-- Duration + Price -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Duración (minutos) *</label>
                                <!-- Quick presets -->
                                <div class="flex flex-wrap gap-1.5 mb-2">
                                    <button
                                        v-for="preset in durationPresets"
                                        :key="preset"
                                        type="button"
                                        @click="serviceForm.duration = preset"
                                        class="px-2.5 py-1 text-xs rounded-md border transition-colors"
                                        :class="serviceForm.duration === preset
                                            ? 'border-transparent text-white'
                                            : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                                        :style="serviceForm.duration === preset ? { backgroundColor: primary } : {}"
                                    >{{ formatDuration(preset) }}</button>
                                </div>
                                <input
                                    v-model.number="serviceForm.duration"
                                    type="number"
                                    min="1"
                                    max="480"
                                    required
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500/30"
                                />
                                <p v-if="serviceForm.errors.duration" class="text-red-500 text-xs mt-1">{{ serviceForm.errors.duration }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Precio (€) *</label>
                                <input
                                    v-model.number="serviceForm.price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                    placeholder="0.00"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500/30"
                                />
                                <p v-if="serviceForm.errors.price" class="text-red-500 text-xs mt-1">{{ serviceForm.errors.price }}</p>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categorías</label>
                            <div v-if="props.categories.length" class="flex flex-wrap gap-2">
                                <button
                                    v-for="cat in props.categories"
                                    :key="cat.id"
                                    type="button"
                                    @click="toggleCategory(cat.id)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border-2 transition-all"
                                    :style="serviceForm.category_ids.includes(cat.id)
                                        ? { backgroundColor: cat.color, borderColor: cat.color, color: '#fff' }
                                        : { backgroundColor: 'transparent', borderColor: cat.color, color: cat.color }"
                                >
                                    <svg v-if="serviceForm.category_ids.includes(cat.id)" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ cat.name }}
                                </button>
                            </div>
                            <p v-else class="text-xs text-gray-400">No hay categorías. Créalas desde el botón "Categorías".</p>
                        </div>

                        <!-- Workers -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Empleados que realizan este servicio</label>
                            <div v-if="props.workers.length" class="flex flex-wrap gap-2">
                                <button
                                    v-for="w in props.workers"
                                    :key="w.id"
                                    type="button"
                                    @click="toggleWorker(w.id)"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border-2 transition-all"
                                    :class="serviceForm.worker_ids.includes(w.id)
                                        ? 'text-white'
                                        : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'"
                                    :style="serviceForm.worker_ids.includes(w.id)
                                        ? { backgroundColor: w.color, borderColor: w.color }
                                        : {}"
                                >
                                    <span
                                        class="w-4 h-4 rounded-full flex items-center justify-center text-[8px] font-bold flex-shrink-0"
                                        :style="{ backgroundColor: serviceForm.worker_ids.includes(w.id) ? 'rgba(255,255,255,0.3)' : w.color, color: '#fff' }"
                                    >{{ initials(w.name) }}</span>
                                    {{ w.name }}
                                </button>
                            </div>
                            <p v-else class="text-xs text-gray-400">No hay empleados activos.</p>
                        </div>

                        <!-- Active status -->
                        <div class="flex items-center gap-3 pt-1">
                            <button
                                type="button"
                                @click="serviceForm.is_active = !serviceForm.is_active"
                                class="relative inline-flex h-5 w-9 rounded-full transition-colors duration-200 focus:outline-none"
                                :style="{ backgroundColor: serviceForm.is_active ? primary : '#d1d5db' }"
                            >
                                <span
                                    class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform duration-200 mt-0.5"
                                    :class="serviceForm.is_active ? 'translate-x-4' : 'translate-x-0.5'"
                                />
                            </button>
                            <span class="text-sm text-gray-700">Servicio activo</span>
                        </div>

                        <!-- Footer buttons -->
                        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                            <button
                                type="button"
                                @click="closeServiceModal"
                                class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                            >Cancelar</button>
                            <button
                                type="submit"
                                :disabled="serviceForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors disabled:opacity-50"
                                :style="{ backgroundColor: primary }"
                            >
                                {{ editingService ? 'Guardar cambios' : 'Crear servicio' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- ════════════════════════════════════════════════════════════════
             Categories Modal
        ════════════════════════════════════════════════════════════════════ -->
        <Teleport to="body">
            <div
                v-if="showCategoriesModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showCategoriesModal = false; cancelCategoryEdit()" />

                <!-- Panel -->
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white rounded-t-2xl z-10">
                        <h2 class="text-base font-semibold text-gray-900">Gestionar categorías</h2>
                        <button
                            @click="showCategoriesModal = false; cancelCategoryEdit()"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-5">

                        <!-- Category form (create/edit) -->
                        <form @submit.prevent="submitCategory" class="bg-gray-50 rounded-xl p-4 space-y-3 border border-gray-100">
                            <h3 class="text-sm font-medium text-gray-700">
                                {{ editingCategory ? `Editando: ${editingCategory.name}` : 'Nueva categoría' }}
                            </h3>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre *</label>
                                <input
                                    v-model="categoryForm.name"
                                    type="text"
                                    required
                                    placeholder="Ej: Coloración"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500/30 bg-white"
                                />
                                <p v-if="categoryForm.errors.name" class="text-red-500 text-xs mt-1">{{ categoryForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                                <input
                                    v-model="categoryForm.description"
                                    type="text"
                                    placeholder="Descripción opcional"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500/30 bg-white"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                <div class="flex items-center gap-3">
                                    <input
                                        v-model="categoryForm.color"
                                        type="color"
                                        class="w-10 h-10 rounded-lg cursor-pointer border border-gray-200 p-0.5 bg-white"
                                    />
                                    <div class="flex gap-1.5 flex-wrap">
                                        <button
                                            v-for="c in ['#8b5cf6','#3b82f6','#10b981','#f59e0b','#ef4444','#ec4899','#14b8a6','#f97316']"
                                            :key="c"
                                            type="button"
                                            @click="categoryForm.color = c"
                                            class="w-6 h-6 rounded-full border-2 transition-transform hover:scale-110"
                                            :style="{ backgroundColor: c, borderColor: categoryForm.color === c ? '#1f2937' : 'transparent' }"
                                        />
                                    </div>
                                </div>
                            </div>
                            <!-- Preview badge -->
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400">Vista previa:</span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                    :style="{ backgroundColor: categoryForm.color }"
                                >{{ categoryForm.name || 'Nombre' }}</span>
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button
                                    v-if="editingCategory"
                                    type="button"
                                    @click="cancelCategoryEdit"
                                    class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                                >Cancelar</button>
                                <button
                                    type="submit"
                                    :disabled="categoryForm.processing"
                                    class="px-3 py-1.5 text-xs font-medium text-white rounded-lg transition-colors disabled:opacity-50"
                                    :style="{ backgroundColor: primary }"
                                >
                                    {{ editingCategory ? 'Guardar cambios' : 'Crear categoría' }}
                                </button>
                            </div>
                        </form>

                        <!-- Category list -->
                        <div class="space-y-2">
                            <h3 class="text-sm font-medium text-gray-700">
                                Categorías existentes ({{ props.categories.length }})
                            </h3>
                            <div v-if="!props.categories.length" class="text-xs text-gray-400 py-4 text-center">
                                No hay categorías. Crea la primera arriba.
                            </div>
                            <div
                                v-for="cat in props.categories"
                                :key="cat.id"
                                class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 bg-white transition-colors"
                            >
                                <!-- Color dot -->
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0" :style="{ backgroundColor: cat.color }">
                                    {{ cat.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ cat.name }}</p>
                                    <p v-if="cat.description" class="text-xs text-gray-400 truncate">{{ cat.description }}</p>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button
                                        @click="openCategoryEdit(cat)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                                        title="Editar"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteCategory(cat)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                                        title="Eliminar"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
