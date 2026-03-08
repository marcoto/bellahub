<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const tenant = computed(() => page.props.tenant)
const user   = computed(() => page.props.auth?.user)

const props = defineProps({
    stats: Object,
    todayBookings: Array,
    upcomingBookings: Array,
    calendarEvents: Array,
    workers: Array,
})

// Greeting
const greeting = computed(() => {
    const h = new Date().getHours()
    if (h < 12) return 'Buenos días'
    if (h < 20) return 'Buenas tardes'
    return 'Buenas noches'
})

// Calendar
const calendarEl = ref(null)

onMounted(async () => {
    if (!calendarEl.value) return
    try {
        const { createCalendar, viewDay, viewWeek, viewMonthGrid } = await import('@schedule-x/calendar')
        const { createEventsServicePlugin } = await import('@schedule-x/events-service')
        await import('@schedule-x/theme-default/dist/index.css')

        const eventsPlugin = createEventsServicePlugin()
        const calendarsObj = {}

        if (props.workers) {
            props.workers.forEach(w => {
                calendarsObj[w.id] = {
                    colorName: String(w.id),
                    lightColors: {
                        main: w.color || '#8b5cf6',
                        container: (w.color || '#8b5cf6') + '30',
                        onContainer: w.color || '#8b5cf6',
                    },
                    darkColors: {
                        main: w.color || '#8b5cf6',
                        onContainer: '#ffffff',
                        container: w.color || '#8b5cf6',
                    },
                }
            })
        }

        const calendar = createCalendar(calendarEl.value, {
            views: [viewDay, viewWeek, viewMonthGrid],
            defaultView: viewDay.name,
            locale: 'es-ES',
            events: props.calendarEvents || [],
            plugins: [eventsPlugin],
            calendars: calendarsObj,
            selectedDate: new Date().toISOString().slice(0, 10),
        })
        calendar.render()
    } catch (e) {
        console.warn('schedule-x not available:', e)
    }
})

const statCards = computed(() => [
    { label: 'Reservas hoy', value: props.stats?.bookingsToday ?? 0, sub: 'hoy', bg: 'bg-violet-50', icon: 'calendar', iconBg: 'bg-violet-100', iconColor: '#7c3aed' },
    { label: 'Pendientes',   value: props.stats?.bookingsPending ?? 0, sub: 'hoy', bg: 'bg-orange-50', icon: 'clock', iconBg: 'bg-orange-100', iconColor: '#ea580c' },
    { label: 'Esta semana',  value: props.stats?.bookingsWeek ?? 0, sub: 'hoy', bg: 'bg-blue-50', icon: 'chart', iconBg: 'bg-blue-100', iconColor: '#2563eb' },
    { label: 'Trabajadores', value: props.stats?.workersCount ?? 0, sub: 'hoy', bg: 'bg-green-50', icon: 'team', iconBg: 'bg-green-100', iconColor: '#16a34a' },
])

const today = new Date().toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' })

// Mini calendar
const now = new Date()
const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).getDay() || 7
const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()
const calDays = computed(() => {
    const days = []
    for (let i = 1; i < firstDayOfMonth; i++) days.push(null)
    for (let i = 1; i <= daysInMonth; i++) days.push(i)
    return days
})
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div class="flex h-full">
            <!-- Main area -->
            <div class="flex-1 p-7 overflow-y-auto min-w-0">
                <!-- Welcome banner -->
                <div class="bg-gradient-to-r from-violet-50 to-indigo-50 border border-violet-100 rounded-xl p-6 mb-6 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-0.5">{{ greeting }}, {{ user?.name?.split(' ')[0] }} ✂️</p>
                        <h2 class="text-2xl font-bold text-gray-800">
                            Tienes {{ stats?.bookingsToday ?? 0 }} reservas hoy
                        </h2>
                        <Link
                            :href="route('bookings.index')"
                            class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-white px-4 py-2 rounded-lg transition-colors"
                            :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }"
                        >
                            + Nueva reserva
                        </Link>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div v-for="card in statCards" :key="card.label"
                         class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div :class="['w-9 h-9 rounded-lg flex items-center justify-center', card.iconBg]">
                                <svg v-if="card.icon === 'calendar'" class="w-4 h-4" :style="{ color: card.iconColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <svg v-if="card.icon === 'clock'" class="w-4 h-4" :style="{ color: card.iconColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg v-if="card.icon === 'chart'" class="w-4 h-4" :style="{ color: card.iconColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <svg v-if="card.icon === 'team'" class="w-4 h-4" :style="{ color: card.iconColor }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-400">{{ card.sub }}</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-800">{{ card.value }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ card.label }}</p>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 flex items-center justify-between border-b border-gray-50">
                        <h3 class="text-sm font-semibold text-gray-800">Agenda de hoy</h3>
                        <span class="text-xs text-gray-400 capitalize">{{ today }}</span>
                    </div>
                    <div ref="calendarEl" style="min-height: 400px;"></div>
                </div>
            </div>

            <!-- Right sidebar -->
            <aside class="w-72 bg-[#0f0d2e] flex flex-col p-5 overflow-y-auto flex-shrink-0">
                <!-- User -->
                <div class="text-center mb-6 pt-2">
                    <p class="text-gray-500 text-xs mb-3">{{ greeting }}</p>
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-2"
                         :style="{ backgroundColor: tenant?.primary_color || '#8b5cf6' }">
                        {{ user?.name?.charAt(0)?.toUpperCase() ?? 'U' }}
                    </div>
                    <p class="text-white font-semibold text-sm">{{ user?.name }}</p>
                    <p class="text-gray-400 text-xs capitalize mt-0.5">{{ user?.role ?? 'Admin' }}</p>
                </div>

                <!-- Stats quick -->
                <div class="bg-[#1a1744] rounded-xl p-4 mb-4">
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div>
                            <p class="text-2xl font-bold text-white">{{ stats?.bookingsToday ?? 0 }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">Hoy</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-white">{{ stats?.bookingsWeek ?? 0 }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">Semana</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-white">{{ stats?.workersCount ?? 0 }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">Equipo</p>
                        </div>
                    </div>
                </div>

                <!-- Mini calendar -->
                <div class="bg-[#1a1744] rounded-xl p-4 mb-4">
                    <p class="text-white text-sm font-medium mb-3 capitalize">
                        {{ new Date().toLocaleDateString('es-ES', { month: 'long', year: 'numeric' }) }}
                    </p>
                    <div class="grid grid-cols-7 mb-1">
                        <span v-for="d in ['Lu','Ma','Mi','Ju','Vi','Sa','Do']" :key="d"
                              class="text-center text-xs text-gray-500 py-0.5">{{ d }}</span>
                    </div>
                    <div class="grid grid-cols-7 gap-y-0.5">
                        <div v-for="(day, idx) in calDays" :key="idx"
                             class="text-center">
                            <span
                                v-if="day"
                                :class="[
                                    'inline-flex items-center justify-center w-6 h-6 text-xs rounded-full',
                                    day === now.getDate() ? 'text-white font-bold' : 'text-gray-400'
                                ]"
                                :style="day === now.getDate() ? { backgroundColor: tenant?.primary_color || '#8b5cf6' } : {}"
                            >{{ day }}</span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming -->
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Próximas reservas</h3>
                    <div v-if="!upcomingBookings?.length" class="text-xs text-gray-600 text-center py-4">
                        No hay reservas próximas.
                    </div>
                    <div v-else class="space-y-2">
                        <div v-for="b in upcomingBookings" :key="b.id"
                             class="bg-[#1a1744] rounded-lg p-3 flex items-center gap-2.5">
                            <div class="w-1 rounded-full flex-shrink-0 self-stretch"
                                 :style="{ backgroundColor: b.worker_color || '#8b5cf6' }"></div>
                            <div class="min-w-0">
                                <p class="text-white text-xs font-medium truncate">{{ b.client_name }}</p>
                                <p class="text-gray-500 text-xs">{{ b.date }} · {{ b.time_start }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>
