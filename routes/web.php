<?php

use App\Controllers\AssessmentController;
use App\Controllers\AuthController;
use App\Controllers\CandidateController;
use App\Controllers\DashboardController;
use App\Controllers\HrController;
use App\Controllers\HrAuditLogController;
use App\Controllers\HrComplianceCheckController;
use App\Controllers\HrDataRetentionController;
use App\Controllers\HrReportController;
use App\Controllers\HrInterviewController;
use App\Controllers\InterviewerInterviewController;
use App\Controllers\NotificationController;
use App\Core\Response;
use App\Enums\JobRequisitionStatus;

$router->get('/', fn () => Response::view('welcome', ['title' => 'Welcome']), 'home');

$router->get('/register', [AuthController::class, 'register'], 'register');
$router->post('/register', [AuthController::class, 'storeRegistration'], 'register.store');
$router->get('/login', [AuthController::class, 'login'], 'login');
$router->post('/login', [AuthController::class, 'authenticate'], 'login.store');
$router->post('/logout', [AuthController::class, 'logout'], 'logout');

$router->get('/dashboard', [DashboardController::class, 'redirectToDashboard'], 'dashboard');
$router->get('/candidate/dashboard', [DashboardController::class, 'candidate'], 'candidate.dashboard');
$router->get('/hr/dashboard', [DashboardController::class, 'hr'], 'hr.dashboard');
$router->get('/interviewer/dashboard', [DashboardController::class, 'interviewer'], 'interviewer.dashboard');

$router->get('/notifications', [NotificationController::class, 'index'], 'notifications.index');
$router->post('/notifications/{id}/read', [NotificationController::class, 'markRead'], 'notifications.read');
$router->post('/notifications/read-all', [NotificationController::class, 'markAllRead'], 'notifications.read-all');

$router->get('/candidate/profile', [CandidateController::class, 'profile'], 'candidate.profile');
$router->put('/candidate/profile', [CandidateController::class, 'updateProfile'], 'candidate.profile.update');
$router->get('/candidate/jobs', [CandidateController::class, 'jobs'], 'candidate.jobs.index');
$router->get('/candidate/jobs/{id}', [CandidateController::class, 'job'], 'candidate.jobs.show');
$router->post('/candidate/jobs/{id}/applications', [CandidateController::class, 'apply'], 'candidate.applications.store');
$router->get('/candidate/applications', [CandidateController::class, 'applications'], 'candidate.applications.index');
$router->get('/candidate/applications/{id}', [CandidateController::class, 'application'], 'candidate.applications.show');
$router->post('/candidate/applications/{application}/assessments/{assessment}/start', [AssessmentController::class, 'startCandidate'], 'candidate.assessments.start');
$router->get('/candidate/assessments/{id}', [AssessmentController::class, 'showCandidate'], 'candidate.assessments.show');
$router->put('/candidate/assessments/{id}/answers/{question}', [AssessmentController::class, 'saveAnswer'], 'candidate.assessments.answers.update');
$router->post('/candidate/assessments/{id}/heartbeat', [AssessmentController::class, 'heartbeat'], 'candidate.assessments.heartbeat');
$router->get('/candidate/assessments/{id}/result', [AssessmentController::class, 'resultCandidate'], 'candidate.assessments.result');

$router->get('/candidate/interviews/{id}', [\App\Controllers\CandidateInterviewController::class, 'show'], 'candidate.interviews.show');
$router->get('/candidate/interviews/{id}/workspace', [\App\Controllers\CandidateInterviewController::class, 'workspace'], 'candidate.interviews.workspace');
$router->post('/candidate/interviews/{id}/workspace', [\App\Controllers\CandidateInterviewController::class, 'saveWorkspace'], 'candidate.interviews.workspace.save');


$router->get('/candidate/offers/{id}', [\App\Controllers\CandidateOfferController::class, 'show'], 'candidate.offers.show');
$router->post('/candidate/offers/{id}/accept', [\App\Controllers\CandidateOfferController::class, 'accept'], 'candidate.offers.accept');
$router->post('/candidate/offers/{id}/reject', [\App\Controllers\CandidateOfferController::class, 'reject'], 'candidate.offers.reject');

$router->get('/hr/users', [HrController::class, 'users'], 'hr.users.index');
$router->get('/hr/users/create', [HrController::class, 'createUser'], 'hr.users.create');
$router->post('/hr/users', [HrController::class, 'storeUser'], 'hr.users.store');
$router->get('/hr/users/{id}/access', [HrController::class, 'editAccess'], 'hr.users.access.edit');
$router->put('/hr/users/{id}/access', [HrController::class, 'updateAccess'], 'hr.users.access.update');

$router->post('/hr/checks/run', [HrComplianceCheckController::class, 'run'], 'hr.checks.run');
$router->get('/hr/reports/pipeline', [HrReportController::class, 'pipeline'], 'hr.reports.pipeline');
$router->get('/hr/reports/time-to-hire', [HrReportController::class, 'timeToHire'], 'hr.reports.time-to-hire');
$router->get('/hr/audit-log', [HrAuditLogController::class, 'index'], 'hr.audit-log.index');
$router->get('/hr/data-retention', [HrDataRetentionController::class, 'index'], 'hr.data-retention.index');
$router->post('/hr/data-retention/{candidate}/anonymize', [HrDataRetentionController::class, 'anonymize'], 'hr.data-retention.anonymize');
$router->post('/hr/data-retention/{candidate}/delete', [HrDataRetentionController::class, 'delete'], 'hr.data-retention.delete');

$router->get('/hr/requisitions', [HrController::class, 'requisitions'], 'hr.requisitions.index');
$router->get('/hr/requisitions/create', [HrController::class, 'createRequisition'], 'hr.requisitions.create');
$router->post('/hr/requisitions', [HrController::class, 'storeRequisition'], 'hr.requisitions.store');
$router->get('/hr/requisitions/{id}', [HrController::class, 'showRequisition'], 'hr.requisitions.show');
$router->get('/hr/requisitions/{id}/edit', [HrController::class, 'editRequisition'], 'hr.requisitions.edit');
$router->put('/hr/requisitions/{id}', [HrController::class, 'updateRequisition'], 'hr.requisitions.update');
$router->post('/hr/requisitions/{id}/submit', fn ($request, $id) => (new HrController())->transitionRequisition($request, $id, JobRequisitionStatus::PENDING->value), 'hr.requisitions.submit');
$router->get('/hr/approvals', [HrGovernanceController::class, 'approvalQueue'], 'hr.approvals.index');
$router->get('/hr/requisitions/{id}/review', [HrGovernanceController::class, 'approveForm'], 'hr.approvals.form');
$router->post('/hr/requisitions/{id}/approve', [HrGovernanceController::class, 'approveRequisition'], 'hr.requisitions.approve');
$router->post('/hr/requisitions/{id}/reject', [HrGovernanceController::class, 'rejectRequisition'], 'hr.requisitions.reject');

$router->get('/hr/requisitions/{id}/versions', [HrGovernanceController::class, 'versionHistory'], 'hr.requisitions.versions.index');
$router->get('/hr/requisitions/{id}/versions/compare', [HrGovernanceController::class, 'compareVersions'], 'hr.requisitions.versions.compare');
$router->get('/hr/requisitions/{id}/versions/{versionId}', [HrGovernanceController::class, 'showVersion'], 'hr.requisitions.versions.show');

$router->get('/hr/requisitions/{id}/publish', [HrGovernanceController::class, 'publishForm'], 'hr.requisitions.publish.form');
$router->post('/hr/requisitions/{id}/publish', [HrGovernanceController::class, 'publishRequisition'], 'hr.requisitions.publish.store');
$router->post('/hr/requisitions/{id}/unpublish', [HrGovernanceController::class, 'unpublishRequisition'], 'hr.requisitions.publish.unpublish');
$router->get('/hr/requisitions/{id}/sync-history', [HrGovernanceController::class, 'syncHistory'], 'hr.requisitions.sync-history');

$router->get('/hr/requisitions/{id}/governance-audit', [HrGovernanceController::class, 'governanceAudit'], 'hr.requisitions.governance-audit');

$router->get('/hr/department-heads', [HrGovernanceController::class, 'departmentHeads'], 'hr.department-heads.index');
$router->post('/hr/department-heads', [HrGovernanceController::class, 'assignDepartmentHead'], 'hr.department-heads.store');
$router->post('/hr/department-heads/{id}/remove', [HrGovernanceController::class, 'removeDepartmentHead'], 'hr.department-heads.destroy');

$router->post('/hr/requisitions/{id}/open', fn ($request, $id) => (new HrController())->transitionRequisition($request, $id, JobRequisitionStatus::OPEN->value), 'hr.requisitions.open');
$router->post('/hr/requisitions/{id}/close', fn ($request, $id) => (new HrController())->transitionRequisition($request, $id, JobRequisitionStatus::CLOSED->value), 'hr.requisitions.close');
$router->get('/hr/requisitions/{id}/applications', [HrController::class, 'applications'], 'hr.applications.index');
$router->put('/hr/applications/{id}', [HrController::class, 'updateApplication'], 'hr.applications.update');

$router->get('/hr/requisitions/{id}/screening', [HrScreeningController::class, 'config'], 'hr.screening.config');
$router->post('/hr/requisitions/{id}/screening', [HrScreeningController::class, 'storeConfig'], 'hr.screening.config.store');
$router->post('/hr/requisitions/{id}/screening/recalculate', [HrScreeningController::class, 'recalculate'], 'hr.screening.recalculate');
$router->get('/hr/requisitions/{id}/shortlist', [HrScreeningController::class, 'shortlist'], 'hr.screening.shortlist');
$router->get('/hr/requisitions/{id}/triage', [HrScreeningController::class, 'triagePreview'], 'hr.screening.triage');
$router->post('/hr/requisitions/{id}/triage', [HrScreeningController::class, 'executeTriage'], 'hr.screening.triage.execute');
$router->get('/hr/requisitions/{id}/duplicates', [HrScreeningController::class, 'duplicates'], 'hr.screening.duplicates');
$router->get('/hr/requisitions/{id}/duplicates/resolve', [HrScreeningController::class, 'resolveDuplicate'], 'hr.screening.duplicates.resolve.form');
$router->post('/hr/requisitions/{id}/duplicates/resolve', [HrScreeningController::class, 'resolveDuplicate'], 'hr.screening.duplicates.resolve');
$router->get('/hr/requisitions/{id}/screening/audit', [HrScreeningController::class, 'audit'], 'hr.screening.audit');

$router->get('/hr/requisitions/{id}/assessments', [AssessmentController::class, 'index'], 'hr.assessments.index');
$router->get('/hr/requisitions/{id}/assessments/create', [AssessmentController::class, 'create'], 'hr.assessments.create');
$router->post('/hr/requisitions/{id}/assessments', [AssessmentController::class, 'store'], 'hr.assessments.store');
$router->get('/hr/assessments/{id}', [AssessmentController::class, 'show'], 'hr.assessments.show');
$router->get('/hr/assessments/{id}/edit', [AssessmentController::class, 'edit'], 'hr.assessments.edit');
$router->put('/hr/assessments/{id}', [AssessmentController::class, 'update'], 'hr.assessments.update');
$router->post('/hr/assessments/{id}/deactivate', [AssessmentController::class, 'deactivate'], 'hr.assessments.deactivate');
$router->get('/hr/assessments/{id}/questions/create', [AssessmentController::class, 'createQuestion'], 'hr.assessment-questions.create');
$router->post('/hr/assessments/{id}/questions', [AssessmentController::class, 'storeQuestion'], 'hr.assessment-questions.store');
$router->get('/hr/assessment-questions/{id}/edit', [AssessmentController::class, 'editQuestion'], 'hr.assessment-questions.edit');
$router->put('/hr/assessment-questions/{id}', [AssessmentController::class, 'updateQuestion'], 'hr.assessment-questions.update');
$router->post('/hr/assessment-questions/{id}/deactivate', [AssessmentController::class, 'deactivateQuestion'], 'hr.assessment-questions.deactivate');
$router->get('/hr/requisitions/{id}/assessment-results', [AssessmentController::class, 'results'], 'hr.assessment-results.index');
$router->get('/hr/candidate-assessments/{id}', [AssessmentController::class, 'reviewAttempt'], 'hr.candidate-assessments.show');

$router->get('/hr/applications/{id}/interviews/create', [HrInterviewController::class, 'create'], 'hr.interviews.create');
$router->post('/hr/applications/{id}/interviews/recommendations', [HrInterviewController::class, 'recommendPanel'], 'hr.interviews.recommendations');
$router->post('/hr/applications/{id}/interviews', [HrInterviewController::class, 'store'], 'hr.interviews.store');
$router->get('/hr/interviews/{id}', [HrInterviewController::class, 'show'], 'hr.interviews.show');
$router->get('/hr/interviews/{id}/edit', [HrInterviewController::class, 'edit'], 'hr.interviews.edit');
$router->put('/hr/interviews/{id}', [HrInterviewController::class, 'update'], 'hr.interviews.update');
$router->post('/hr/interviews/{id}/cancel', [HrInterviewController::class, 'cancel'], 'hr.interviews.cancel');
$router->post('/hr/interviews/{id}/complete', [HrInterviewController::class, 'complete'], 'hr.interviews.complete');
$router->post('/hr/interviews/{id}/briefing/refresh', [HrInterviewController::class, 'refreshBriefing'], 'hr.interviews.briefing.refresh');
$router->get('/hr/interviews/{id}/workspace', [HrInterviewController::class, 'workspace'], 'hr.interviews.workspace');
$router->post('/hr/interviews/{id}/workspace', [HrInterviewController::class, 'saveWorkspace'], 'hr.interviews.workspace.save');
$router->get('/hr/interviews/{id}/extensions/{request}', [HrInterviewController::class, 'showExtension'], 'hr.interviews.extensions.show');
$router->post('/hr/interviews/{id}/extensions/{request}/approve', [HrInterviewController::class, 'approveExtension'], 'hr.interviews.extensions.approve');
$router->post('/hr/interviews/{id}/extensions/{request}/deny', [HrInterviewController::class, 'denyExtension'], 'hr.interviews.extensions.deny');
$router->get('/hr/interviews/{id}/audit', [HrInterviewController::class, 'audit'], 'hr.interviews.audit');

$router->get('/hr/applications/{id}/final-evaluation', [\App\Controllers\HrFinalEvaluationController::class, 'show'], 'hr.evaluations.show');
$router->post('/hr/applications/{id}/final-evaluation', [\App\Controllers\HrFinalEvaluationController::class, 'store'], 'hr.evaluations.store');

$router->get('/hr/offers', [\App\Controllers\HrOfferController::class, 'index'], 'hr.offers.index');
$router->get('/hr/applications/{id}/offers/create', [\App\Controllers\HrOfferController::class, 'create'], 'hr.offers.create');
$router->post('/hr/applications/{id}/offers', [\App\Controllers\HrOfferController::class, 'store'], 'hr.offers.store');
$router->get('/hr/offers/{id}', [\App\Controllers\HrOfferController::class, 'show'], 'hr.offers.show');
$router->post('/hr/offers/{id}/send', [\App\Controllers\HrOfferController::class, 'send'], 'hr.offers.send');

$router->get('/hr/onboarding', [\App\Controllers\HrOnboardingController::class, 'index'], 'hr.onboarding.index');
$router->get('/hr/offers/{id}/onboarding/create', [\App\Controllers\HrOnboardingController::class, 'create'], 'hr.onboarding.create');
$router->post('/hr/offers/{id}/onboarding', [\App\Controllers\HrOnboardingController::class, 'store'], 'hr.onboarding.store');
$router->get('/hr/onboarding/{id}', [\App\Controllers\HrOnboardingController::class, 'show'], 'hr.onboarding.show');
$router->put('/hr/onboarding/{id}', [\App\Controllers\HrOnboardingController::class, 'update'], 'hr.onboarding.update');

$router->get('/interviewer/interviews', [InterviewerInterviewController::class, 'index'], 'interviewer.interviews.index');
$router->get('/interviewer/interviews/{id}', [InterviewerInterviewController::class, 'show'], 'interviewer.interviews.show');
$router->get('/interviewer/interviews/{id}/workspace', [InterviewerInterviewController::class, 'workspace'], 'interviewer.interviews.workspace');
$router->post('/interviewer/interviews/{id}/workspace', [InterviewerInterviewController::class, 'saveWorkspace'], 'interviewer.interviews.workspace.save');
$router->post('/interviewer/interviews/{id}/extensions', [InterviewerInterviewController::class, 'requestExtension'], 'interviewer.interviews.extensions.store');
$router->post('/interviewer/interviews/{id}/extensions/{request}/cancel', [InterviewerInterviewController::class, 'cancelExtension'], 'interviewer.interviews.extensions.cancel');
$router->get('/interviewer/interviews/{id}/feedback', [InterviewerInterviewController::class, 'feedback'], 'interviewer.interviews.feedback.create');
$router->post('/interviewer/interviews/{id}/feedback', [InterviewerInterviewController::class, 'storeFeedback'], 'interviewer.interviews.feedback.store');
