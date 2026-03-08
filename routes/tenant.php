<?php

declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tenant\BookingController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\SidebarController;
use App\Http\Controllers\Tenant\TimeRecordController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\VacationController;
use App\Http\Controllers\Tenant\WorkerController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // Public: redirect to login
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // Authenticated routes
    Route::middleware(['auth', 'verified'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Bookings (Reservas)
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

        // Users (admin only)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Workers overview (admin)
        Route::get('/workers', [WorkerController::class, 'index'])->name('workers.index');

        // Time records (Fichaje)
        Route::get('/workers/time', [TimeRecordController::class, 'index'])->name('workers.time');
        Route::post('/workers/clock-in', [TimeRecordController::class, 'clockIn'])->name('workers.clock-in');
        Route::post('/workers/clock-out', [TimeRecordController::class, 'clockOut'])->name('workers.clock-out');

        // Vacations (Vacaciones)
        Route::get('/workers/vacations', [VacationController::class, 'index'])->name('workers.vacations');
        Route::post('/workers/vacations', [VacationController::class, 'store'])->name('workers.vacations.store');
        Route::post('/workers/vacations/{vacationRequest}/approve', [VacationController::class, 'approve'])->name('workers.vacations.approve');
        Route::post('/workers/vacations/{vacationRequest}/reject', [VacationController::class, 'reject'])->name('workers.vacations.reject');
        Route::delete('/workers/vacations/{vacationRequest}', [VacationController::class, 'destroy'])->name('workers.vacations.destroy');

        // Sidebar state
        Route::post('/sidebar/toggle', [SidebarController::class, 'toggle'])->name('sidebar.toggle');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__ . '/auth.php';
});
