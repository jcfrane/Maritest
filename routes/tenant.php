<?php

use App\Http\Controllers\Tenant\CandidateController;
use App\Http\Controllers\Tenant\ExamSetController;
use App\Http\Controllers\Tenant\ImageUploadController;
use App\Http\Controllers\Tenant\QuestionnaireController;
use App\Http\Controllers\Tenant\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('dashboard', 'Tenant/Dashboard')->name('dashboard');

Route::resource('users', UserController::class)
    ->except(['show']);

Route::resource('candidates', CandidateController::class)
    ->except(['show']);

Route::post('images', [ImageUploadController::class, 'store'])
    ->name('images.store');

Route::resource('questionnaires', QuestionnaireController::class)
    ->except(['show']);

Route::resource('exam-sets', ExamSetController::class)
    ->except(['show']);
