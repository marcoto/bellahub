<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkerController extends Controller
{
    public function index(): Response
    {
        $workers = User::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id'              => $u->id,
                'name'            => $u->name,
                'email'           => $u->email,
                'color'           => $u->color ?? '#8b5cf6',
                'role'            => $u->role,
                'hire_date'       => $u->hire_date?->format('d/m/Y'),
                'vacation_days'   => $u->vacation_days,
                'vacation_used'   => $u->vacation_used,
                'hours_today'     => $u->hours_today,
                'hours_year'      => $u->hours_year,
                'is_clocked_in'   => $u->is_clocked_in,
                'bookings_today'  => Booking::where('worker_id', $u->id)
                    ->whereDate('date', today())
                    ->count(),
            ]);

        return Inertia::render('Workers/Index', [
            'workers' => $workers,
        ]);
    }
}
