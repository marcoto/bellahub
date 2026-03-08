<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import SystemLayout from '@/Layouts/SystemLayout.vue'

const props = defineProps({ user: Object, tenant: Object })

const form = useForm({
    name: props.tenant.name ?? '',
    email: props.tenant.email ?? '',
    phone: props.tenant.phone ?? '',
    city: props.tenant.city ?? '',
    address: props.tenant.address ?? '',
    logo: null,
    primary_color: props.tenant.primary_color ?? '#8b5cf6',
    secondary_color: props.tenant.secondary_color ?? '#6d28d9',
    plan: props.tenant.plan ?? 'basic',
    status: props.tenant.status ?? 'active',
    _method: 'PUT',
})

const logoPreview = ref(props.tenant.logo)

function onLogoChange(e) {
    const file = e.target.files[0]
    form.logo = file
    if (file) logoPreview.value = URL.createObjectURL(file)
}

function submit() {
    form.post(route('system.tenants.update', props.tenant.id), {
        forceFormData: true,
    })
}

const plans = [
    { value: 'basic', label: 'Básico', description: 'Hasta 2 trabajadores' },
    { value: 'professional', label: 'Profesional', description: 'Hasta 10 trabajadores' },
    { value: 'enterprise', label: 'Enterprise', description: 'Sin límite' },
]
</script>

<template>
    <Head title="Editar peluquería - BellaHub" />
    <SystemLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800">Editar peluquería</h1>
        </template>

        <div class="max-w-2xl">
            <Link :href="route('system.tenants.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Editar: {{ tenant.name }}
            </Link>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- Datos del salón -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
                    <h2 class="text-xs font-semibold text-violet-600 uppercase tracking-wider">Datos del salón</h2>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Nombre del salón *</label>
                        <input v-model="form.name" type="text" placeholder="Ej: Peluquería Carmela"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" required />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Subdominio</label>
                        <div class="flex items-center border border-gray-100 rounded-lg overflow-hidden bg-gray-50 px-3.5 py-2.5 text-sm text-gray-400">
                            <span>{{ tenant.id }}</span>
                            <span class="ml-1 text-gray-300">. (no editable)</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Email *</label>
                            <input v-model="form.email" type="email"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" required />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Teléfono</label>
                            <input v-model="form.phone" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Ciudad</label>
                            <input v-model="form.city" type="text"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Estado</label>
                            <select v-model="form.status"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500">
                                <option value="active">Activa</option>
                                <option value="inactive">Inactiva</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Dirección</label>
                        <input v-model="form.address" type="text"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500" />
                    </div>
                </div>

                <!-- Branding -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
                    <h2 class="text-xs font-semibold text-violet-600 uppercase tracking-wider">Branding y apariencia</h2>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Logo</label>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-lg border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50">
                                <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                                <svg v-else class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input type="file" accept="image/*" @change="onLogoChange" class="text-sm text-gray-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Color primario</label>
                            <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3.5 py-2.5">
                                <input v-model="form.primary_color" type="color" class="w-6 h-6 rounded cursor-pointer border-0 p-0 bg-transparent" />
                                <span class="text-sm text-gray-600 font-mono">{{ form.primary_color }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Color secundario</label>
                            <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-3.5 py-2.5">
                                <input v-model="form.secondary_color" type="color" class="w-6 h-6 rounded cursor-pointer border-0 p-0 bg-transparent" />
                                <span class="text-sm text-gray-600 font-mono">{{ form.secondary_color }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plan -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-xs font-semibold text-violet-600 uppercase tracking-wider mb-4">Plan</h2>
                    <div class="grid grid-cols-3 gap-3">
                        <label v-for="p in plans" :key="p.value" :class="[
                            'cursor-pointer border-2 rounded-lg p-4 text-sm transition-colors',
                            form.plan === p.value ? 'border-violet-600 bg-violet-50' : 'border-gray-200 hover:border-violet-300'
                        ]">
                            <input type="radio" v-model="form.plan" :value="p.value" class="hidden" />
                            <p class="font-semibold text-gray-800">{{ p.label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ p.description }}</p>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="bg-violet-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-violet-700 disabled:opacity-50 transition-colors">
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                    <Link :href="route('system.tenants.index')" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </SystemLayout>
</template>
