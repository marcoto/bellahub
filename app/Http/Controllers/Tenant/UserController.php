<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::withCount(['bookings as bookings_today' => fn ($q) => $q->whereDate('date', today())])
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => $this->mapUser($u));

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8',
            'role'          => 'required|in:admin,worker',
            'phone'         => 'nullable|string|max:30',
            'color'         => 'nullable|string|max:7',
            'hire_date'     => 'nullable|date',
            'vacation_days' => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        User::create([
            ...$request->only(['name', 'email', 'role', 'phone', 'color', 'hire_date', 'vacation_days', 'is_active']),
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'      => 'nullable|string|min:8',
            'role'          => 'required|in:admin,worker',
            'phone'         => 'nullable|string|max:30',
            'color'         => 'nullable|string|max:7',
            'hire_date'     => 'nullable|date',
            'vacation_days' => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        $data = $request->only(['name', 'email', 'role', 'phone', 'color', 'hire_date', 'vacation_days', 'is_active']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminarte a ti mismo.']);
        }
        $user->delete();
        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    private function mapUser(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'role'           => $user->role,
            'phone'          => $user->phone,
            'color'          => $user->color ?? '#8b5cf6',
            'hire_date'      => $user->hire_date?->format('d/m/Y'),
            'hire_date_raw'  => $user->hire_date?->format('Y-m-d'),
            'vacation_days'  => $user->vacation_days,
            'vacation_used'  => $user->vacation_used,
            'is_active'      => $user->is_active,
            'hours_today'    => $user->hours_today,
            'is_clocked_in'  => $user->is_clocked_in,
            'bookings_today' => $user->bookings_today ?? 0,
        ];
    }
}
