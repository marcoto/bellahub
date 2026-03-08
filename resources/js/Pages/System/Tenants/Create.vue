<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import SystemLayout from '@/Layouts/SystemLayout.vue'

defineProps({ user: Object })

const form = useForm({
    name: '',
    slug: '',
    email: '',
    phone: '',
    city: '',
    address: '',
    logo: null,
    primary_color: '#8b5cf6',
    secondary_color: '#6d28d9',
    plan: 'basic',
    admin_name: '',
    admin_email: '',
    admin_password: '',
})

const logoPreview = ref(null)

function onLogoChange(e) {
    const file = e.target.files[0]
    form.logo = file
    if (file) logoPreview.value = URL.createObjectURL(file)
}

function autoSlug() {
    form.slug = form.name
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '')
}

function submit() {
    form.post(route('system.tenants.store'), {
        forceFormData: true,
        onSuccess: () => form.reset(),
    })
}

const plans = [
    { value: 'basic', label: 'Básico', description: 'Hasta 2 trabajadores' },
    { value: 'professional', label: 'Profesional', description: 'Hasta 10 trabajadores' },
    { value: 'enterprise', label: 'Enterprise', description: 'Sin límite' },
]

const appDomain = 'bellahub.test'
</script>

<template>
    <Head title="Nueva peluquería - BellaHub" />
    <SystemLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-800">Nueva peluquería</h1>
        </template>

        <div class="max-w-2xl">
            <!-- Back -->
            <Link :href="route('system.tenants.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Nueva peluquería
            </Link>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- Datos del salón -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
                    <h2 class="text-xs font-semibold text-violet-600 uppercase tracking-wider">Datos del salón</h2>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Nombre del salón *</label>
                        <input
                            v-model="form.name"
                            @blur="!form.slug && autoSlug()"
                            type="text"
                            placeholder="Ej: Peluquería Carmela"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Subdominio (Slug) *</label>
                            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-violet-500">
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="carmela"
                                    class="flex-1 px-3.5 py-2.5 text-sm focus:outline-none min-w-0"
                                    pattern="[a-z0-9-]+"
                                    required
                                />
                                <span class="bg-gray-100 border-l border-gray-200 px-2.5 py-2.5 text-xs text-gray-400 select-none whitespace-nowrap">.{{ appDomain }}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Solo letras minúsculas, números y guiones</p>
                            <p v-if="form.errors.slug" class="mt-1 text-xs text-red-500">{{ form.errors.slug }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Email *</label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="salon@email.com"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                                required
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Teléfono</label>
                            <input v-model="form.phone" type="text" placeholder="+34 600 000 000"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Ciudad</label>
                            <input v-model="form.city" type="text" placeholder="Madrid"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Dirección</label>
                        <input v-model="form.address" type="text" placeholder="Calle Mayor, 1"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent" />
                    </div>
                </div>

                <!-- Admin -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-5">
                    <h2 class="text-xs font-semibold text-violet-600 uppercase tracking-wider">Propietario / Admin inicial</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Nombre *</label>
                            <input v-model="form.admin_name" type="text" placeholder="Nombre completo"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent" required />
                            <p v-if="form.errors.admin_name" class="mt-1 text-xs text-red-500">{{ form.errors.admin_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Email del admin *</label>
                            <input v-model="form.admin_email" type="email" placeholder="admin@salon.com"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent" required />
                            <p v-if="form.errors.admin_email" class="mt-1 text-xs text-red-500">{{ form.errors.admin_email }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5 uppercase tracking-wide">Contraseña inicial *</label>
                        <input v-model="form.admin_password" type="password" placeholder="Mínimo 8 caracteres"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent" required minlength="8" />
                        <p class="mt-1 text-xs text-gray-400">Se usará para el primer acceso. El admin podrá cambiarla después.</p>
                        <p v-if="form.errors.admin_password" class="mt-1 text-xs text-red-500">{{ form.errors.admin_password }}</p>
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
                        <label
                            v-for="p in plans"
                            :key="p.value"
                            :class="[
                                'cursor-pointer border-2 rounded-lg p-4 text-sm transition-colors',
                                form.plan === p.value
                                    ? 'border-violet-600 bg-violet-50'
                                    : 'border-gray-200 hover:border-violet-300'
                            ]"
                        >
                            <input type="radio" v-model="form.plan" :value="p.value" class="hidden" />
                            <p class="font-semibold text-gray-800">{{ p.label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ p.description }}</p>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-violet-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-violet-700 disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? 'Creando...' : '✨ Crear peluquería' }}
                    </button>
                    <Link :href="route('system.tenants.index')" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </SystemLayout>
</template>
