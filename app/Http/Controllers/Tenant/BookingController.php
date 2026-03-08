<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Booking::with('worker')->orderByDesc('date')->orderByDesc('time_start');

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
            'service'      => $b->service,
            'status'       => $b->status,
            'status_label' => $b->status_label,
            'notes'        => $b->notes,
        ]);

        $workers = User::where('is_active', true)->get(['id', 'name', 'color']);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'workers'  => $workers,
            'filters'  => $request->only(['date', 'worker_id', 'status']),
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
            'service'      => 'nullable|string|max:255',
            'status'       => 'required|in:pending,confirmed,cancelled,completed',
            'notes'        => 'nullable|string',
        ]);

        Booking::create($request->all());

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
            'service'      => 'nullable|string|max:255',
            'status'       => 'required|in:pending,confirmed,cancelled,completed',
            'notes'        => 'nullable|string',
        ]);

        $booking->update($request->all());

        return back()->with('success', 'Reserva actualizada correctamente.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();
        return back()->with('success', 'Reserva eliminada correctamente.');
    }
}
