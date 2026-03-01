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
});
