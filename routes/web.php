<?php

use App\Http\Controllers\Tenant\ImageUploadController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::prefix('t/{tenant}')
    ->name('tenant.')
    ->group(function () {
        Route::get('images/{image}', [ImageUploadController::class, 'show'])
            ->name('images.show');
    });

Route::middleware(['auth', 'verified', 'tenant.redirect'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// Landlord admin routes
Route::middleware(['auth', 'verified', 'tenant.redirect'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::inertia('dashboard', 'Admin/Dashboard')->name('dashboard');
    });

// Tenant routes
Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('t/{tenant}')
    ->name('tenant.')
    ->group(base_path('routes/tenant.php'));

require __DIR__.'/settings.php';
