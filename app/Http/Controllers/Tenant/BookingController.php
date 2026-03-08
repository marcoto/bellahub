<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Booking::with('worker', 'services')->orderByDesc('date')->orderByDesc('time_start');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(20)->through(fn ($b) => [
            'id'           => $b->id,
            'client_name'  => $b->client_name,
            'client_phone' => $b->client_phone,
            'date'         => $b->date->format('d/m/Y'),
            'date_raw'     => $b->date->format('Y-m-d'),
            'time_start'   => substr($b->time_start, 0, 5),
            'time_end'     => substr($b->time_end, 0, 5),
            'worker'       => $b->worker ? ['id' => $b->worker->id, 'name' => $b->worker->name, 'color' => $b->worker->color] : null,
            'services'     => $b->services->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
            'status'       => $b->status,
            'status_label' => $b->status_label,
            'notes'        => $b->notes,
        ]);

        $workers = User::where('is_active', true)->get(['id', 'name', 'color']);

        $serviceCategories = ServiceCategory::with(['services' => fn ($q) => $q->where('is_active', true)->select('services.id', 'services.name')])
            ->get()
            ->map(fn ($c) => [
                'label' => $c->name,
                'items' => $c->services->values()->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->all(),
            ])
            ->filter(fn ($c) => count($c['items']) > 0)
            ->values()
            ->all();

        return Inertia::render('Bookings/Index', [
            'bookings'          => $bookings,
            'workers'           => $workers,
            'serviceCategories' => $serviceCategories,
            'filters'           => $request->only(['date', 'worker_id', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'client_name'  => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:30',
            'date'         => 'required|date',
            'time_start'   => 'required|date_format:H:i',
            'time_end'     => 'required|date_format:H:i|after:time_start',
            'worker_id'    => 'nullable|exists:users,id',
            'service_ids'  => 'nullable|array',
            'service_ids.*'=> 'exists:services,id',
            'status'       => 'required|in:pending,confirmed,cancelled,completed',
            'notes'        => 'nullable|string',
        ]);

        $booking = Booking::create($request->except('service_ids'));
        $booking->services()->sync($request->service_ids ?? []);

        return back()->with('success', 'Reserva creada correctamente.');
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate([
            'client_name'  => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:30',
            'date'         => 'required|date',
            'time_start'   => 'required|date_format:H:i',
            'time_end'     => 'required|date_format:H:i|after:time_start',
            'worker_id'    => 'nullable|exists:users,id',
            'service_ids'  => 'nullable|array',
            'service_ids.*'=> 'exists:services,id',
            'status'       => 'required|in:pending,confirmed,cancelled,completed',
            'notes'        => 'nullable|string',
        ]);

        $booking->update($request->except('service_ids'));
        $booking->services()->sync($request->service_ids ?? []);

        return back()->with('success', 'Reserva actualizada correctamente.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();
        return back()->with('success', 'Reserva eliminada correctamente.');
    }
}
