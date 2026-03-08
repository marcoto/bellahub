<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user  = $request->user();
        $today = today();

        // Stats
        $bookingsToday  = Booking::whereDate('date', $today)->count();
        $bookingsPending = Booking::where('status', 'pending')->count();
        $bookingsWeek   = Booking::whereBetween('date', [
            now()->startOfWeek(), now()->endOfWeek(),
        ])->count();
        $workersCount   = User::where('is_active', true)->count();

        // Today agenda bookings
        $todayBookings = Booking::with('worker')
            ->whereDate('date', $today)
            ->orderBy('time_start')
            ->get()
            ->map(fn ($b) => [
                'id'          => $b->id,
                'title'       => $b->client_name,
                'start'       => $today->format('Y-m-d') . ' ' . $b->time_start,
                'end'         => $today->format('Y-m-d') . ' ' . $b->time_end,
                'worker'      => $b->worker?->name,
                'worker_color'=> $b->worker?->color ?? '#8b5cf6',
                'status'      => $b->status,
                'service'     => $b->service,
            ]);

        // Upcoming bookings (next 7 days)
        $upcomingBookings = Booking::with('worker')
            ->whereDate('date', '>=', $today)
            ->orderBy('date')
            ->orderBy('time_start')
            ->take(5)
            ->get()
            ->map(fn ($b) => [
                'id'          => $b->id,
                'client_name' => $b->client_name,
                'date'        => $b->date->format('d/m/Y'),
                'time_start'  => substr($b->time_start, 0, 5),
                'worker'      => $b->worker?->name,
                'worker_color'=> $b->worker?->color ?? '#8b5cf6',
                'status'      => $b->status,
            ]);

        // All bookings for schedule-x (current month)
        $calendarEvents = Booking::with('worker')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->get()
            ->map(fn ($b) => [
                'id'          => (string) $b->id,
                'title'       => $b->client_name,
                'start'       => $b->date->format('Y-m-d') . ' ' . substr($b->time_start, 0, 5),
                'end'         => $b->date->format('Y-m-d') . ' ' . substr($b->time_end, 0, 5),
                'calendarId'  => (string) ($b->worker_id ?? 'unassigned'),
            ]);

        // Workers for calendar config
        $workers = User::where('is_active', true)->get()->map(fn ($w) => [
            'id'    => (string) $w->id,
            'name'  => $w->name,
            'color' => $w->color ?? '#8b5cf6',
        ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'bookingsToday'   => $bookingsToday,
                'bookingsPending' => $bookingsPending,
                'bookingsWeek'    => $bookingsWeek,
                'workersCount'    => $workersCount,
            ],
            'todayBookings'   => $todayBookings,
            'upcomingBookings'=> $upcomingBookings,
            'calendarEvents'  => $calendarEvents,
            'workers'         => $workers,
        ]);
    }
}
