<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VacationRequest;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VacationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $requests = $user->isAdmin()
            ? VacationRequest::with('user')->orderByDesc('created_at')->get()
            : VacationRequest::where('user_id', $user->id)->orderByDesc('created_at')->get();

        $mappedRequests = $requests->map(fn ($r) => [
            'id'         => $r->id,
            'user'       => $r->user->name,
            'from_date'  => $r->from_date->format('d/m/Y'),
            'to_date'    => $r->to_date->format('d/m/Y'),
            'days'       => $r->days,
            'status'     => $r->status,
            'status_label' => $r->status_label,
            'notes'      => $r->notes,
            'can_approve'=> $user->isAdmin() && $r->status === 'pending',
        ]);

        $totalDays     = $user->vacation_days;
        $usedDays      = $user->vacation_used;
        $pendingDays   = $user->vacationRequests()
            ->where('status', 'pending')
            ->whereYear('from_date', now()->year)
            ->sum('days');
        $availableDays = max(0, $totalDays - $usedDays - $pendingDays);

        return Inertia::render('Workers/Vacations', [
            'requests'      => $mappedRequests,
            'totalDays'     => $totalDays,
            'usedDays'      => $usedDays,
            'pendingDays'   => (int) $pendingDays,
            'availableDays' => $availableDays,
            'isAdmin'       => $user->isAdmin(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'notes'     => 'nullable|string|max:500',
        ]);

        // Calculate business days
        $days = $this->countBusinessDays($request->from_date, $request->to_date);

        VacationRequest::create([
            'user_id'   => $user->id,
            'from_date' => $request->from_date,
            'to_date'   => $request->to_date,
            'days'      => $days,
            'status'    => 'pending',
            'notes'     => $request->notes,
        ]);

        return back()->with('success', 'Solicitud de vacaciones enviada correctamente.');
    }

    public function approve(VacationRequest $vacationRequest, Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $vacationRequest->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Vacaciones aprobadas.');
    }

    public function reject(VacationRequest $vacationRequest, Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403);
        }

        $vacationRequest->update(['status' => 'rejected']);

        return back()->with('success', 'Vacaciones rechazadas.');
    }

    public function destroy(VacationRequest $vacationRequest, Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->isAdmin() && $vacationRequest->user_id !== $user->id) {
            abort(403);
        }
        $vacationRequest->delete();
        return back()->with('success', 'Solicitud eliminada.');
    }

    private function countBusinessDays(string $from, string $to): int
    {
        $period = CarbonPeriod::create($from, $to);
        $count  = 0;
        foreach ($period as $date) {
            if (! $date->isWeekend()) {
                $count++;
            }
        }
        return $count;
    }
}
