<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CandidateRegistrationController;
use App\Http\Controllers\Candidate\DashboardController as CandidateDashboardController;
use App\Http\Controllers\Candidate\ProfileController as CandidateProfileController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Hr\DashboardController as HrDashboardController;
use App\Http\Controllers\Hr\UserAccessController as HrUserAccessController;
use App\Http\Controllers\Hr\UserController as HrUserController;
use App\Http\Controllers\Interviewer\DashboardController as InterviewerDashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [CandidateRegistrationController::class, 'create'])->name('register');
    Route::post('/register', [CandidateRegistrationController::class, 'store'])->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/candidate/dashboard', CandidateDashboardController::class)
        ->middleware('role:CANDIDATE')
        ->name('candidate.dashboard');
    Route::get('/candidate/profile', CandidateProfileController::class)
        ->middleware('role:CANDIDATE')
        ->name('candidate.profile');

    Route::middleware('role:HR_ADMIN')->prefix('hr')->name('hr.')->group(function (): void {
        Route::get('/dashboard', HrDashboardController::class)->name('dashboard');
        Route::get('/users', [HrUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [HrUserController::class, 'create'])->name('users.create');
        Route::post('/users', [HrUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/access', [HrUserAccessController::class, 'edit'])->name('users.access.edit');
        Route::put('/users/{user}/access', [HrUserAccessController::class, 'update'])->name('users.access.update');
    });

    Route::get('/interviewer/dashboard', InterviewerDashboardController::class)
        ->middleware('role:INTERVIEWER')
        ->name('interviewer.dashboard');
});
