<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.user)

const navigation = [
    { name: 'Dashboard', href: route('system.dashboard'), icon: 'grid' },
    { name: 'Peluquerías', href: route('system.tenants.index'), icon: 'scissors' },
]

function isActive(href) {
    return window.location.pathname.startsWith(new URL(href).pathname)
}
</script>

<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-56 bg-[#0f0d2e] flex flex-col flex-shrink-0">
            <!-- Logo -->
            <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
                <div class="w-8 h-8 bg-violet-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white text-sm font-semibold leading-none">BellaHub</p>
                    <p class="text-violet-300 text-xs mt-0.5">Super Admin</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-1">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider px-2 mb-2">PANEL</p>
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                        isActive(item.href)
                            ? 'bg-violet-600 text-white'
                            : 'text-gray-400 hover:text-white hover:bg-white/5'
                    ]"
                >
                    <!-- Dashboard icon -->
                    <svg v-if="item.icon === 'grid'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <!-- Scissors icon -->
                    <svg v-if="item.icon === 'scissors'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/>
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User -->
            <div class="px-4 py-4 border-t border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-violet-600 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? 'A' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-white text-xs font-medium truncate">Admin</p>
                        <p class="text-gray-500 text-xs truncate">Super Admin</p>
                    </div>
                </div>
                <Link
                    :href="route('system.logout')"
                    method="post"
                    as="button"
                    class="text-gray-500 hover:text-white transition-colors"
                    title="Cerrar sesión"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between flex-shrink-0">
                <slot name="header">
                    <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
                </slot>
                <p class="text-sm text-gray-400">{{ new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
