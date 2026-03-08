<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SystemController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('System/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        return redirect()->route('system.dashboard');
    }

    public function dashboard(): Response
    {
        $tenants   = Tenant::with('domains')->orderByDesc('created_at')->get();
        $total     = $tenants->count();
        $active    = $tenants->where('status', 'active')->count();
        $thisMonth = $tenants->filter(fn ($t) => $t->created_at?->isCurrentMonth())->count();

        return Inertia::render('System/Dashboard', [
            'user'    => Auth::user(),
            'stats'   => compact('total', 'active', 'thisMonth'),
            'tenants' => $tenants->take(10)->map(fn ($t) => $this->mapTenant($t))->values(),
        ]);
    }

    public function tenantsIndex(): Response
    {
        $tenants = Tenant::with('domains')->orderByDesc('created_at')->get();
        return Inertia::render('System/Tenants/Index', [
            'user'    => Auth::user(),
            'tenants' => $tenants->map(fn ($t) => $this->mapTenant($t))->values(),
        ]);
    }

    public function tenantsCreate(): Response
    {
        return Inertia::render('System/Tenants/Create', [
            'user' => Auth::user(),
        ]);
    }

    public function storeTenant(Request $request): RedirectResponse
    {
        $appDomain = config('app.domain', 'localhost');

        $request->validate([
            'name'            => 'required|string|max:255',
            'slug'            => 'required|string|max:63|regex:/^[a-z0-9-]+$/|unique:tenants,id',
            'email'           => 'required|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'city'            => 'nullable|string|max:100',
            'address'         => 'nullable|string|max:255',
            'logo'            => 'nullable|image|max:2048',
            'primary_color'   => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'plan'            => 'required|in:basic,professional,enterprise',
            'admin_name'      => 'required|string|max:255',
            'admin_email'     => 'required|email|max:255',
            'admin_password'  => 'required|string|min:8',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $tenant = Tenant::create([
            'id'              => $request->slug,
            'name'            => $request->name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'city'            => $request->city,
            'address'         => $request->address,
            'logo'            => $logoPath,
            'primary_color'   => $request->primary_color ?? '#8b5cf6',
            'secondary_color' => $request->secondary_color ?? '#6d28d9',
            'plan'            => $request->plan,
            'status'          => 'active',
            'admin_name'      => $request->admin_name,
        ]);

        $tenant->domains()->create([
            'domain' => $request->slug . '.' . $appDomain,
        ]);

        tenancy()->initialize($tenant);

        User::create([
            'name'     => $request->admin_name,
            'email'    => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'role'     => 'admin',
        ]);

        tenancy()->end();

        return redirect()->route('system.tenants.index')
            ->with('success', 'Peluquería "' . $request->name . '" creada correctamente.');
    }

    public function tenantsEdit(string $id): Response
    {
        $tenant = Tenant::with('domains')->findOrFail($id);
        return Inertia::render('System/Tenants/Edit', [
            'user'   => Auth::user(),
            'tenant' => $this->mapTenant($tenant),
        ]);
    }

    public function tenantsUpdate(Request $request, string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'city'            => 'nullable|string|max:100',
            'address'         => 'nullable|string|max:255',
            'logo'            => 'nullable|image|max:2048',
            'primary_color'   => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'plan'            => 'required|in:basic,professional,enterprise',
            'status'          => 'required|in:active,inactive',
        ]);

        $data = $request->only([
            'name', 'email', 'phone', 'city', 'address',
            'primary_color', 'secondary_color', 'plan', 'status',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $tenant->update($data);

        return redirect()->route('system.tenants.index')
            ->with('success', 'Peluquería actualizada correctamente.');
    }

    public function tenantsDestroy(string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return redirect()->route('system.tenants.index')
            ->with('success', 'Peluquería eliminada correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('system.login');
    }

    private function mapTenant(Tenant $tenant): array
    {
        return [
            'id'              => $tenant->id,
            'name'            => $tenant->name,
            'email'           => $tenant->email,
            'phone'           => $tenant->phone,
            'city'            => $tenant->city,
            'address'         => $tenant->address,
            'logo'            => $tenant->logo ? asset('storage/' . $tenant->logo) : null,
            'primary_color'   => $tenant->primary_color ?? '#8b5cf6',
            'secondary_color' => $tenant->secondary_color ?? '#6d28d9',
            'plan'            => $tenant->plan ?? 'basic',
            'plan_label'      => $tenant->plan_label,
            'status'          => $tenant->status ?? 'active',
            'admin_name'      => $tenant->admin_name,
            'domains'         => $tenant->domains->pluck('domain'),
            'created_at'      => $tenant->created_at?->format('d/m/Y'),
        ];
    }
}
