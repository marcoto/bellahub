<?php

use App\Http\Controllers\System\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('system.login');
});

Route::middleware('guest')->group(function () {
    Route::get('system/login', [SystemController::class, 'showLogin'])->name('system.login');
    Route::post('system/login', [SystemController::class, 'login'])->name('system.login.post');
});

Route::middleware('auth')->group(function () {
    Route::get('system/dashboard', [SystemController::class, 'dashboard'])->name('system.dashboard');
    Route::post('system/logout', [SystemController::class, 'logout'])->name('system.logout');

    // Tenants CRUD
    Route::get('system/tenants', [SystemController::class, 'tenantsIndex'])->name('system.tenants.index');
    Route::get('system/tenants/create', [SystemController::class, 'tenantsCreate'])->name('system.tenants.create');
    Route::post('system/tenants', [SystemController::class, 'storeTenant'])->name('system.tenants.store');
    Route::get('system/tenants/{id}/edit', [SystemController::class, 'tenantsEdit'])->name('system.tenants.edit');
    Route::put('system/tenants/{id}', [SystemController::class, 'tenantsUpdate'])->name('system.tenants.update');
    Route::delete('system/tenants/{id}', [SystemController::class, 'tenantsDestroy'])->name('system.tenants.destroy');
});
