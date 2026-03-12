<?php

use App\Http\Controllers\Tenant\CandidateController;
use App\Http\Controllers\Tenant\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('dashboard', 'Tenant/Dashboard')->name('dashboard');

Route::resource('users', UserController::class)
    ->except(['show']);

Route::resource('candidates', CandidateController::class)
    ->except(['show']);
