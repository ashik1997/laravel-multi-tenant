<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\TenantAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TenantController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tenants', TenantController::class)->except(['destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('tenant.guest')->group(function () {
    Route::get('/tenant/login', [TenantAuthenticatedSessionController::class, 'create'])->name('tenant.login');
    Route::post('/tenant/login', [TenantAuthenticatedSessionController::class, 'store']);
});

Route::middleware('tenant.auth')->group(function () {
    Route::get('/tenant/dashboard', function () {
        return view('tenant.dashboard');
    })->name('tenant.dashboard');

    Route::post('/tenant/logout', [TenantAuthenticatedSessionController::class, 'destroy'])->name('tenant.logout');
});

Route::middleware(['tenant', 'auth'])->group(function () {
    Route::resource('posts', PostController::class);
});

require __DIR__.'/auth.php';
