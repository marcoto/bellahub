<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const tenant = computed(() => page.props.tenant)

const props = defineProps({
    users: Array,
})

const showModal = ref(false)
const editingUser = ref(null)

const form = useForm({
    name:          '',
    email:         '',
    password:      '',
    role:          'worker',
    phone:         '',
    color:         '#8b5cf6',
    hire_date:     '',
    vacation_days: 22,
    is_active:     true,
})

function openCreate() {
    editingUser.value = null
    form.reset()
    form.role = 'worker'
    form.color = '#8b5cf6'
    form.vacation_days = 22
    form.is_active = true
    showModal.value = true
}

function openEdit(user) {
    editingUser.value = user
    form.name          = user.name
    form.email         = user.email
    form.password      = ''
    form.role          = user.role
    form.phone         = user.phone ?? ''
    form.color         = user.color ?? '#8b5cf6'
    form.hire_date     = user.hire_date_raw ?? ''
    form.vacation_days = user.vacation_days ?? 22
    form.is_active     = user.is_active
    showModal.value = true
}

function submitForm() {
    if (editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            onSuccess: () => { showModal.value = false },
        })
    } else {
        form.post(route('users.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

function deleteUser(id) {
    if (confirm('¿Eliminar este usuario?')) {
        router.delete(route('users.destroy', id))
    }
}
</script>

<template>
    <Head title="Usuarios" />
    <AppLayout>
        <div class="p-7">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Usuarios</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Administra el equipo de la peluquería</p>
                </div>
                <button
                    @click="openCreate"
                    class="text-sm px-4 py-2 rounded-lg text-white font-medium"
                    :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }"
                >
                    + Añadir usuario
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-wide border-b border-gray-50">
                            <th class="px-5 py-3 text-left font-medium">Usuario</th>
                            <th class="px-5 py-3 text-left font-medium">Rol</th>
                            <th class="px-5 py-3 text-left font-medium">Estado</th>
                            <th class="px-5 py-3 text-left font-medium">Hoy</th>
                            <th class="px-5 py-3 text-left font-medium">Vacaciones</th>
                            <th class="px-5 py-3 text-left font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0"
                                         :style="{ backgroundColor: user.color }">
                                        {{ user.name?.slice(0,2)?.toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ user.name }}</p>
                                        <p class="text-xs text-gray-400">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span :class="[
                                    'px-2.5 py-1 rounded-full text-xs font-medium',
                                    user.role === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-green-100 text-green-700'
                                ]">
                                    {{ user.role === 'admin' ? 'Admin' : 'Trabajador' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span :class="[
                                    'px-2.5 py-1 rounded-full text-xs font-medium',
                                    user.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'
                                ]">
                                    {{ user.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-gray-600">
                                {{ user.hours_today ? user.hours_today + 'h' : '0h' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full" :style="{
                                            width: Math.min(100, (user.vacation_used / user.vacation_days) * 100) + '%',
                                            backgroundColor: tenant?.primary_color || '#8b5cf6'
                                        }"></div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ user.vacation_used }}/{{ user.vacation_days }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex gap-2">
                                    <button @click="openEdit(user)" class="text-xs text-gray-500 hover:text-gray-800 border border-gray-200 px-2.5 py-1 rounded-lg hover:bg-gray-50 transition-colors">
                                        Editar
                                    </button>
                                    <button @click="deleteUser(user.id)" class="text-xs text-red-500 hover:text-red-700 border border-red-100 px-2.5 py-1 rounded-lg hover:bg-red-50 transition-colors">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-screen overflow-y-auto">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                        <div v-if="editingUser"
                             class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-semibold"
                             :style="{ backgroundColor: form.color }">
                            {{ editingUser.name?.slice(0,2)?.toUpperCase() }}
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-gray-800">{{ editingUser ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
                            <p v-if="editingUser" class="text-xs text-gray-400">{{ editingUser.name }}</p>
                        </div>
                        <button @click="showModal = false" class="ml-auto text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nombre *</label>
                            <input v-model="form.name" type="text" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email *</label>
                                <input v-model="form.email" type="email" required
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Rol *</label>
                                <select v-model="form.role"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                                    <option value="worker">Trabajador</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    {{ editingUser ? 'Nueva contraseña' : 'Contraseña *' }}
                                </label>
                                <input v-model="form.password" type="password"
                                    :placeholder="editingUser ? 'Dejar vacío para no cambiar' : ''"
                                    :required="!editingUser"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Confirmar contraseña</label>
                                <input type="password"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Teléfono</label>
                                <input v-model="form.phone" type="text" placeholder="+34 600 000 000"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Color</label>
                                <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2">
                                    <input v-model="form.color" type="color" class="w-6 h-6 rounded border-0 p-0 bg-transparent cursor-pointer" />
                                    <span class="text-sm text-gray-600 font-mono text-xs">{{ form.color }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de contratación</label>
                                <input v-model="form.hire_date" type="date"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Días de vacaciones / Año</label>
                                <input v-model="form.vacation_days" type="number" min="0"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" id="is_active"
                                class="w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500" />
                            <label for="is_active" class="text-sm text-gray-700">Usuario activo</label>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="px-5 py-2 text-sm text-white rounded-lg font-medium disabled:opacity-60"
                                :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }">
                                {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
