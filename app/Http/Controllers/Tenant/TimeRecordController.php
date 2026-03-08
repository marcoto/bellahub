<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TimeRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TimeRecordController extends Controller
{
    public function index(Request $request): Response
    {
        $user  = $request->user();
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        // Admin sees selector of workers; worker sees own records
        $targetUserId = $user->isAdmin() && $request->filled('user_id')
            ? (int) $request->user_id
            : $user->id;

        $targetUser = User::findOrFail($targetUserId);

        $records = TimeRecord::where('user_id', $targetUserId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn ($r) => [
                'id'                 => $r->id,
                'date'               => $r->date->format('d/m/Y'),
                'check_in'           => substr($r->check_in, 0, 5),
                'check_out'          => $r->check_out ? substr($r->check_out, 0, 5) : null,
                'duration'           => $r->formatted_duration,
                'duration_raw'       => $r->duration,
                'notes'              => $r->notes,
            ]);

        $hoursToday  = $targetUser->hours_today;
        $daysWorked  = TimeRecord::where('user_id', $targetUserId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->distinct('date')
            ->count('date');
        $hoursYear   = $targetUser->hours_year;
        $isClockedIn = $targetUser->is_clocked_in;

        $openRecord = TimeRecord::where('user_id', $user->id)
            ->whereDate('date', today())
            ->whereNull('check_out')
            ->first();

        $workers = $user->isAdmin()
            ? User::where('is_active', true)->get(['id', 'name'])
            : collect();

        return Inertia::render('Workers/Time', [
            'records'       => $records,
            'hoursToday'    => $hoursToday,
            'daysWorked'    => $daysWorked,
            'hoursYear'     => $hoursYear,
            'isClockedIn'   => $isClockedIn,
            'openRecord'    => $openRecord ? ['id' => $openRecord->id, 'check_in' => substr($openRecord->check_in, 0, 5)] : null,
            'currentMonth'  => $month,
            'currentYear'   => $year,
            'targetUserId'  => $targetUserId,
            'workers'       => $workers,
        ]);
    }

    public function clockIn(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Prevent double clock-in
        $existing = TimeRecord::where('user_id', $user->id)
            ->whereDate('date', today())
            ->whereNull('check_out')
            ->first();

        if ($existing) {
            return back()->withErrors(['error' => 'Ya has fichado la entrada hoy.']);
        }

        TimeRecord::create([
            'user_id'  => $user->id,
            'date'     => today(),
            'check_in' => now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Entrada fichada correctamente.');
    }

    public function clockOut(Request $request): RedirectResponse
    {
        $user = $request->user();

        $record = TimeRecord::where('user_id', $user->id)
            ->whereDate('date', today())
            ->whereNull('check_out')
            ->first();

        if (! $record) {
            return back()->withErrors(['error' => 'No hay entrada abierta.']);
        }

        $record->check_out = now()->format('H:i:s');
        $record->calculateDuration();
        $record->save();

        return back()->with('success', 'Salida fichada correctamente.');
    }
}
