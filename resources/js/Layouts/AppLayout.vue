<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const tenant = computed(() => page.props.tenant)
const primaryColor = computed(() => tenant.value?.primary_color || '#8b5cf6')

const collapsed = ref(Boolean(page.props.sidebar_collapsed))

function toggleSidebar() {
    collapsed.value = !collapsed.value
    router.post('/sidebar/toggle', { collapsed: collapsed.value }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const navSections = [
    {
        label: 'General',
        items: [
            { name: 'Dashboard', route: 'dashboard', icon: 'dashboard' },
            { name: 'Reservas', route: 'bookings.index', icon: 'calendar' },
        ],
    },
    {
        label: 'Administración',
        items: [
            { name: 'Usuarios', route: 'users.index', icon: 'users' },
            { name: 'Trabajadores', route: 'workers.index', icon: 'team' },
        ],
    },
    {
        label: 'Mi zona',
        items: [
            { name: 'Fichaje', route: 'workers.time', icon: 'clock' },
            { name: 'Vacaciones', route: 'workers.vacations', icon: 'vacation' },
        ],
    },
]

function isActive(routeName) {
    if (routeName === 'dashboard') return page.url === '/dashboard'
    if (routeName === 'bookings.index') return page.url.startsWith('/bookings')
    if (routeName === 'users.index') return page.url.startsWith('/users')
    if (routeName === 'workers.index') return page.url === '/workers'
    if (routeName === 'workers.time') return page.url.startsWith('/workers/time')
    if (routeName === 'workers.vacations') return page.url.startsWith('/workers/vacations')
    return false
}
</script>

<template>
    <div class="relative flex h-screen bg-gray-50 overflow-hidden">

        <!-- ── Floating toggle button (sits on the sidebar/topbar border line) ── -->
        <button
            @click="toggleSidebar"
            class="absolute top-[30px] z-50 -translate-y-1/2 w-6 h-6 bg-white border border-gray-200 rounded-lg shadow-md flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-[left] duration-300 ease-in-out"
            :class="collapsed ? 'left-[52px]' : 'left-[208px]'"
            :title="collapsed ? 'Expandir menú' : 'Colapsar menú'"
        >
            <!-- << collapse -->
            <svg v-if="!collapsed" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <!-- > expand -->
            <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <!-- ── Sidebar ── -->
        <aside
            class="bg-white flex flex-col flex-shrink-0 border-r border-gray-100 transition-[width] duration-300 ease-in-out overflow-hidden"
            :class="collapsed ? 'w-[64px]' : 'w-[220px]'"
        >
            <!-- Header: logo + name -->
            <div
                class="flex items-center h-[60px] border-b border-gray-100 flex-shrink-0 px-3 gap-2"
            >
                <!-- Tenant avatar -->
                <div
                    class="w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0"
                    :style="{ backgroundColor: primaryColor + '18' }"
                >
                    <img v-if="tenant?.logo" :src="tenant.logo" class="w-full h-full object-cover" />
                    <span v-else class="text-xs font-bold" :style="{ color: primaryColor }">
                        {{ tenant?.name?.charAt(0)?.toUpperCase() ?? 'S' }}
                    </span>
                </div>

                <!-- Name + subtitle (hidden when collapsed) -->
                <div
                    class="min-w-0 flex-1 overflow-hidden transition-[opacity] duration-200"
                    :class="collapsed ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                >
                    <p class="text-gray-900 text-sm font-semibold leading-none truncate whitespace-nowrap">
                        {{ tenant?.name ?? 'Salón' }}
                    </p>
                    <p class="text-gray-400 text-[11px] mt-0.5 whitespace-nowrap">Panel de gestión</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-3 overflow-y-auto overflow-x-hidden">
                <div
                    v-for="(section, index) in navSections"
                    :key="section.label"
                    :class="index > 0 ? 'mt-4' : ''"
                >
                    <!-- Section label (hidden when collapsed) -->
                    <p
                        v-if="!collapsed"
                        class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider px-4 mb-1 whitespace-nowrap"
                    >
                        {{ section.label }}
                    </p>
                    <!-- Divider when collapsed (except first section) -->
                    <div
                        v-if="collapsed && index > 0"
                        class="h-px bg-gray-100 mx-3 mb-3"
                    />

                    <!-- Nav items -->
                    <div class="space-y-0.5 px-2">
                        <Link
                            v-for="item in section.items"
                            :key="item.name"
                            :href="route(item.route)"
                            :title="collapsed ? item.name : ''"
                            class="group relative w-full flex items-center rounded-lg py-2 px-2 transition-colors duration-150"
                            :class="[
                                collapsed ? 'justify-center' : 'gap-2.5',
                                isActive(item.route)
                                    ? 'text-gray-900 bg-gray-100'
                                    : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50',
                            ]"
                        >
                            <!-- Dashboard -->
                            <svg v-if="item.icon === 'dashboard'" class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <!-- Calendar -->
                            <svg v-if="item.icon === 'calendar'" class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <!-- Users -->
                            <svg v-if="item.icon === 'users'" class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <!-- Team -->
                            <svg v-if="item.icon === 'team'" class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <!-- Clock -->
                            <svg v-if="item.icon === 'clock'" class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <!-- Vacation -->
                            <svg v-if="item.icon === 'vacation'" class="w-[18px] h-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                            </svg>

                            <!-- Item label -->
                            <span v-if="!collapsed" class="text-sm font-medium truncate">
                                {{ item.name }}
                            </span>

                            <!-- Right indicator bar -->
                            <span
                                class="absolute right-0 top-1/2 -translate-y-1/2 w-[3px] rounded-l-full transition-all duration-200 ease-out"
                                :class="
                                    isActive(item.route)
                                        ? 'h-5 opacity-100'
                                        : 'h-0 opacity-0 group-hover:h-4 group-hover:opacity-100'
                                "
                                :style="{ backgroundColor: primaryColor }"
                            />
                        </Link>
                    </div>
                </div>
            </nav>

            <!-- User footer -->
            <div class="border-t border-gray-100 px-3 py-3 flex-shrink-0">
                <div
                    class="flex items-center gap-2"
                    :class="collapsed ? 'justify-center' : ''"
                >
                    <!-- User avatar -->
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0 select-none"
                        :style="{ backgroundColor: primaryColor }"
                    >
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                    </div>

                    <!-- User name + role (hidden when collapsed) -->
                    <div
                        v-if="!collapsed"
                        class="flex-1 min-w-0"
                    >
                        <p class="text-gray-900 text-xs font-medium truncate">{{ user?.name }}</p>
                        <p class="text-gray-400 text-[11px] truncate capitalize">{{ user?.role ?? 'Admin' }}</p>
                    </div>

                    <!-- Logout button (hidden when collapsed) -->
                    <Link
                        v-if="!collapsed"
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors p-1 rounded hover:bg-gray-100"
                        title="Cerrar sesión"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ── Main ── -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top bar -->
            <header class="bg-white border-b border-gray-100 px-6 h-[60px] flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3 flex-1">
                    <div class="relative flex-1 max-w-xs">
                        <svg class="w-4 h-4 text-gray-300 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            placeholder="Buscar..."
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-500/30 bg-gray-50"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400">
                        {{ new Date().toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'short' }) }}
                    </span>
                    <button class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold select-none"
                        :style="{ backgroundColor: primaryColor }"
                    >
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
