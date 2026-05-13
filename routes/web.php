<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\TenantAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\TenantSwitchController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// clear cache
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');

    return 'Cache cleared successfully.';
})->name('cache.clear');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tenants', TenantController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Domain management routes
    Route::prefix('api/domains')->group(function () {
        Route::get('/', [DomainController::class, 'index'])->name('domains.index');
        Route::post('/', [DomainController::class, 'store'])->name('domains.store');
        Route::get('/{domain}', [DomainController::class, 'show'])->name('domains.show');
        Route::patch('/{domain}', [DomainController::class, 'update'])->name('domains.update');
        Route::delete('/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');
        Route::post('/{domain}/test-ftp', [DomainController::class, 'testFtp'])->name('domains.testFtp');
    });

    // Tenant switching routes
    Route::prefix('api/tenant')->group(function () {
        Route::get('/available', [TenantSwitchController::class, 'getAvailableTenants'])->name('tenant.available');
        Route::post('/switch', [TenantSwitchController::class, 'switchTenant'])->name('tenant.switch');
        Route::get('/current', [TenantSwitchController::class, 'getCurrentTenant'])->name('tenant.current');
        Route::post('/initialize/{tenantId}', [TenantSwitchController::class, 'initializeTenant'])->name('tenant.initialize');
    });

    // Upload routes
    Route::prefix('api/uploads')->group(function () {
        Route::post('/domain/{domain}', [UploadController::class, 'uploadFile'])->name('uploads.upload');
        Route::post('/domain/{domain}/multiple', [UploadController::class, 'uploadMultiple'])->name('uploads.uploadMultiple');
        Route::get('/domain/{domain}/config', [UploadController::class, 'getUploadConfig'])->name('uploads.config');
        Route::delete('/domain/{domain}/file', [UploadController::class, 'deleteFile'])->name('uploads.delete');
    });
});

Route::middleware(['tenant', 'tenant.guest'])->group(function () {
    Route::get('/tenant/login', [TenantAuthenticatedSessionController::class, 'create'])->name('tenant.login');
    Route::post('/tenant/login', [TenantAuthenticatedSessionController::class, 'store']);
});

Route::middleware(['tenant', 'tenant.auth'])->group(function () {
    Route::get('/tenant/dashboard', function () {
        return view('tenant.dashboard');
    })->name('tenant.dashboard');

    Route::get('/tenant/billing', [App\Http\Controllers\TenantBillingController::class, 'index'])
        ->name('tenant.billing.index');
    Route::post('/tenant/billing/subscribe', [App\Http\Controllers\TenantBillingController::class, 'subscribe'])
        ->name('tenant.billing.subscribe');

    Route::post('/tenant/logout', [TenantAuthenticatedSessionController::class, 'destroy'])->name('tenant.logout');
});

Route::middleware(['tenant', 'auth'])->group(function () {
    Route::resource('posts', PostController::class);
});

require __DIR__.'/auth.php';
