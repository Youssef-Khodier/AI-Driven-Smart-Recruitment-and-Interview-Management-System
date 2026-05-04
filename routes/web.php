<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CandidateRegistrationController;
use App\Http\Controllers\Candidate\ApplicationController as CandidateApplicationController;
use App\Http\Controllers\Candidate\DashboardController as CandidateDashboardController;
use App\Http\Controllers\Candidate\JobController as CandidateJobController;
use App\Http\Controllers\Candidate\ProfileController as CandidateProfileController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Hr\DashboardController as HrDashboardController;
use App\Http\Controllers\Hr\ApplicationController as HrApplicationController;
use App\Http\Controllers\Hr\JobRequisitionController as HrJobRequisitionController;
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
    Route::put('/candidate/profile', [CandidateProfileController::class, 'update'])
        ->middleware('role:CANDIDATE')
        ->name('candidate.profile.update');
    Route::middleware('role:CANDIDATE')->prefix('candidate')->name('candidate.')->group(function (): void {
        Route::get('/jobs', [CandidateJobController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/{requisition}', [CandidateJobController::class, 'show'])->name('jobs.show');
        Route::get('/applications', [CandidateApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [CandidateApplicationController::class, 'show'])->name('applications.show');
        Route::post('/jobs/{requisition}/applications', [CandidateApplicationController::class, 'store'])->name('applications.store');
    });

    Route::middleware('role:HR_ADMIN')->prefix('hr')->name('hr.')->group(function (): void {
        Route::get('/dashboard', HrDashboardController::class)->name('dashboard');
        Route::get('/users', [HrUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [HrUserController::class, 'create'])->name('users.create');
        Route::post('/users', [HrUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/access', [HrUserAccessController::class, 'edit'])->name('users.access.edit');
        Route::put('/users/{user}/access', [HrUserAccessController::class, 'update'])->name('users.access.update');
        Route::get('/requisitions', [HrJobRequisitionController::class, 'index'])->name('requisitions.index');
        Route::get('/requisitions/create', [HrJobRequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('/requisitions', [HrJobRequisitionController::class, 'store'])->name('requisitions.store');
        Route::get('/requisitions/{requisition}', [HrJobRequisitionController::class, 'show'])->name('requisitions.show');
        Route::get('/requisitions/{requisition}/edit', [HrJobRequisitionController::class, 'edit'])->name('requisitions.edit');
        Route::put('/requisitions/{requisition}', [HrJobRequisitionController::class, 'update'])->name('requisitions.update');
        Route::post('/requisitions/{requisition}/submit', [HrJobRequisitionController::class, 'submit'])->name('requisitions.submit');
        Route::post('/requisitions/{requisition}/approve', [HrJobRequisitionController::class, 'approve'])->name('requisitions.approve');
        Route::post('/requisitions/{requisition}/open', [HrJobRequisitionController::class, 'open'])->name('requisitions.open');
        Route::post('/requisitions/{requisition}/close', [HrJobRequisitionController::class, 'close'])->name('requisitions.close');
        Route::get('/requisitions/{requisition}/applications', [HrApplicationController::class, 'index'])->name('applications.index');
        Route::put('/applications/{application}', [HrApplicationController::class, 'update'])->name('applications.update');
    });

    Route::get('/interviewer/dashboard', InterviewerDashboardController::class)
        ->middleware('role:INTERVIEWER')
        ->name('interviewer.dashboard');
});
