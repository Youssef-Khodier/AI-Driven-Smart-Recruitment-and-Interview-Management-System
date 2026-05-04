<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\CandidateRegistrationController;
use App\Http\Controllers\Candidate\ApplicationController as CandidateApplicationController;
use App\Http\Controllers\Candidate\AssessmentController as CandidateAssessmentController;
use App\Http\Controllers\Candidate\DashboardController as CandidateDashboardController;
use App\Http\Controllers\Candidate\JobController as CandidateJobController;
use App\Http\Controllers\Candidate\ProfileController as CandidateProfileController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Hr\DashboardController as HrDashboardController;
use App\Http\Controllers\Hr\ApplicationController as HrApplicationController;
use App\Http\Controllers\Hr\AssessmentController as HrAssessmentController;
use App\Http\Controllers\Hr\AssessmentQuestionController as HrAssessmentQuestionController;
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
        Route::post('/applications/{application}/assessments/{assessment}/start', [CandidateAssessmentController::class, 'start'])->name('assessments.start');
        Route::get('/assessments/{attempt}', [CandidateAssessmentController::class, 'show'])->name('assessments.show');
        Route::put('/assessments/{attempt}/answers/{attemptQuestion}', [CandidateAssessmentController::class, 'saveAnswer'])->name('assessments.answers.update');
        Route::post('/assessments/{attempt}/submit', [CandidateAssessmentController::class, 'submit'])->name('assessments.submit');
        Route::post('/assessments/{attempt}/focus-events', [CandidateAssessmentController::class, 'recordFocusEvent'])->name('assessments.focus-events.store');
        Route::get('/assessments/{attempt}/result', [CandidateAssessmentController::class, 'result'])->name('assessments.result');
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
        Route::get('/requisitions/{requisition}/assessments', [HrAssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/requisitions/{requisition}/assessments/create', [HrAssessmentController::class, 'create'])->name('assessments.create');
        Route::post('/requisitions/{requisition}/assessments', [HrAssessmentController::class, 'store'])->name('assessments.store');
        Route::get('/assessments/{assessment}', [HrAssessmentController::class, 'show'])->name('assessments.show');
        Route::get('/assessments/{assessment}/edit', [HrAssessmentController::class, 'edit'])->name('assessments.edit');
        Route::put('/assessments/{assessment}', [HrAssessmentController::class, 'update'])->name('assessments.update');
        Route::post('/assessments/{assessment}/deactivate', [HrAssessmentController::class, 'deactivate'])->name('assessments.deactivate');
        Route::get('/assessments/{assessment}/questions/create', [HrAssessmentQuestionController::class, 'create'])->name('assessment-questions.create');
        Route::post('/assessments/{assessment}/questions', [HrAssessmentQuestionController::class, 'store'])->name('assessment-questions.store');
        Route::get('/assessment-questions/{question}/edit', [HrAssessmentQuestionController::class, 'edit'])->name('assessment-questions.edit');
        Route::put('/assessment-questions/{question}', [HrAssessmentQuestionController::class, 'update'])->name('assessment-questions.update');
        Route::post('/assessment-questions/{question}/deactivate', [HrAssessmentQuestionController::class, 'deactivate'])->name('assessment-questions.deactivate');
        Route::get('/requisitions/{requisition}/assessment-results', [HrAssessmentController::class, 'results'])->name('assessment-results.index');
        Route::get('/candidate-assessments/{attempt}', [HrAssessmentController::class, 'attempt'])->name('candidate-assessments.show');
    });

    Route::get('/interviewer/dashboard', InterviewerDashboardController::class)
        ->middleware('role:INTERVIEWER')
        ->name('interviewer.dashboard');
});
