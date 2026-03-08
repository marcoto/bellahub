<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const tenant = computed(() => page.props.tenant)

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const showPassword = ref(false)

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}

const features = [
    'Calendario de citas por horas',
    'Gestión de trabajadores',
    'Fichaje de entrada y salida',
    'Solicitudes de vacaciones',
]
</script>

<template>
    <Head title="Iniciar sesión" />

    <div class="min-h-screen flex">
        <!-- Left side -->
        <div class="flex-1 bg-gray-100 flex flex-col justify-between p-12 relative overflow-hidden">
            <!-- Decorative circles -->
            <div class="absolute top-20 left-16 w-48 h-48 rounded-full bg-gray-200/60"></div>
            <div class="absolute bottom-32 right-8 w-64 h-64 rounded-full bg-gray-200/40"></div>

            <div class="relative z-10">
                <!-- Logo -->
                <div class="flex items-center gap-3 mb-16">
                    <div class="w-10 h-10 rounded-xl overflow-hidden flex items-center justify-center"
                         :style="{ backgroundColor: (tenant?.primary_color || '#8b5cf6') + '20' }">
                        <img v-if="tenant?.logo" :src="tenant.logo" class="w-full h-full object-cover" />
                        <span v-else class="text-lg font-bold" :style="{ color: tenant?.primary_color || '#8b5cf6' }">
                            {{ tenant?.name?.charAt(0)?.toUpperCase() ?? 'S' }}
                        </span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-base leading-none">{{ tenant?.name ?? 'Salón' }}</p>
                        <p class="text-gray-400 text-xs mt-0.5">Panel de gestión</p>
                    </div>
                </div>

                <!-- Heading -->
                <div class="mb-12">
                    <h1 class="text-4xl font-bold text-gray-800 leading-tight mb-3">
                        Gestiona tu<br>
                        <span :style="{ color: tenant?.primary_color || '#8b5cf6' }">peluquería</span><br>
                        fácilmente.
                    </h1>
                    <p class="text-gray-500 text-sm">Reservas, empleados, fichaje y<br>vacaciones desde un solo lugar.</p>
                </div>

                <!-- Features -->
                <ul class="space-y-3">
                    <li v-for="feature in features" :key="feature" class="flex items-center gap-2.5 text-sm text-gray-600">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                             :style="{ backgroundColor: (tenant?.primary_color || '#8b5cf6') + '20' }">
                            <svg class="w-3 h-3" :style="{ color: tenant?.primary_color || '#8b5cf6' }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        {{ feature }}
                    </li>
                </ul>
            </div>

            <p class="relative z-10 text-xs text-gray-400">Powered by BellaHub SaaS</p>
        </div>

        <!-- Right side (dark) -->
        <div class="w-[460px] bg-[#0f0d2e] flex items-center justify-center p-12">
            <div class="w-full max-w-sm">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-white mb-1">Bienvenido/a</h2>
                    <p class="text-gray-400 text-sm">Introduce tus credenciales para entrar</p>
                </div>

                <div v-if="status" class="mb-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg text-sm text-green-400">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-300 mb-1.5">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="tu@email.com"
                            required
                            autofocus
                            class="w-full bg-[#1a1744] border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-400">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-300 mb-1.5">Contraseña</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                required
                                class="w-full bg-[#1a1744] border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent pr-10"
                            />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-xs text-red-400">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="remember"
                            type="checkbox"
                            v-model="form.remember"
                            class="w-4 h-4 rounded border-white/20 bg-[#1a1744] text-violet-600 focus:ring-violet-500"
                        />
                        <label for="remember" class="text-sm text-gray-400">Recordarme</label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 rounded-lg text-sm font-semibold text-white transition-opacity disabled:opacity-60"
                        :style="{ background: `linear-gradient(to right, ${tenant?.primary_color || '#8b5cf6'}, ${tenant?.secondary_color || '#6d28d9'})` }"
                    >
                        {{ form.processing ? 'Entrando...' : 'Entrar' }}
                    </button>

                    <div v-if="canResetPassword" class="text-center">
                        <Link :href="route('password.request')" class="text-xs text-gray-500 hover:text-gray-300">
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
