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
        return Inertia::render('System/Dashboard', [
            'user'    => Auth::user(),
            'tenants' => Tenant::with('domains')->get()->map(fn ($t) => [
                'id'      => $t->id,
                'name'    => $t->name,
                'logo'    => $t->logo ? asset('storage/' . $t->logo) : null,
                'domains' => $t->domains->pluck('domain'),
            ]),
        ]);
    }

    public function storeTenant(Request $request): RedirectResponse
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'domain' => 'required|string|max:63|regex:/^[a-z0-9-]+$/|unique:tenants,id',
            'logo'   => 'nullable|image|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $tenant = Tenant::create([
            'id'   => $request->domain,
            'name' => $request->name,
            'logo' => $logoPath,
        ]);

        $tenant->domains()->create([
            'domain' => $request->domain . '.localhost',
        ]);

        // Crear usuario admin en la base de datos del tenant
        tenancy()->initialize($tenant);

        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@' . $request->domain . '.localhost',
            'password' => Hash::make('123456'),
        ]);

        tenancy()->end();

        return redirect()->route('system.dashboard')
            ->with('success', 'Tenant "' . $request->name . '" creado correctamente.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('system.login');
    }
}
