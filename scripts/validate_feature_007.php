<?php

require dirname(__DIR__) . '/bootstrap/autoload.php';

use App\Core\Auth;
use App\Core\Config;
use App\Core\Database;
use App\Core\Session;
use App\Enums\ApplicationStatus;
use App\Enums\NotificationType;
use App\Enums\OfferStatus;
use App\Policies\AuditLogPolicy;
use App\Policies\DataRetentionPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\ReportPolicy;
use App\Repositories\AuditLogRepository;
use App\Repositories\DataRetentionRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OfferRepository;
use App\Repositories\ReportRepository;

Config::load(dirname(__DIR__));
Session::start();
Database::configure(Config::database());

function assert_true(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function create_user(string $name, string $email, string $role, ?int $departmentId = null): int
{
    return Database::insert('users', [
        'department_id' => $departmentId,
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        'role' => $role,
        'status' => 'ACTIVE',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

function create_candidate(string $name, string $email, string $phone = '01000000000'): int
{
    $userId = create_user($name, $email, 'CANDIDATE');
    Database::insert('candidates', [
        'candidate_id' => $userId,
        'phone' => $phone,
        'current_title' => 'Software Engineer',
        'years_experience' => 4,
        'location' => 'Cairo',
        'resume_url' => 'resume.pdf',
        'skill_keywords' => 'php,mysql',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

    return $userId;
}

function create_application(int $candidateId, int $jobId, string $status, string $appliedAt): int
{
    return Database::insert('applications', [
        'candidate_id' => $candidateId,
        'job_id' => $jobId,
        'status' => $status,
        'match_score' => 80,
        'applied_at' => $appliedAt,
        'created_at' => $appliedAt,
        'updated_at' => $appliedAt,
    ]);
}

$suffix = date('YmdHis');
$hr = Database::fetch('SELECT * FROM users WHERE email = ?', [Config::get('FIRST_HR_ADMIN_EMAIL')]);
assert_true((bool) $hr, 'Seeded HR admin is missing.');
$department = Database::fetch('SELECT department_id FROM departments WHERE name = ?', ['Engineering']);
$departmentId = (int) $department['department_id'];
$interviewerId = create_user('Feature 007 Interviewer', "feature007.interviewer.$suffix@example.com", 'INTERVIEWER', $departmentId);
$candidateId = create_candidate('Feature 007 Candidate', "feature007.candidate.$suffix@example.com");

assert_true(Auth::attempt((string) Config::get('FIRST_HR_ADMIN_EMAIL'), (string) Config::get('FIRST_HR_ADMIN_PASSWORD')), 'HR login failed.');
Auth::logout();
assert_true(Auth::attempt("feature007.interviewer.$suffix@example.com", 'password'), 'Interviewer login failed.');
Auth::logout();
assert_true(Auth::attempt("feature007.candidate.$suffix@example.com", 'password'), 'Candidate login failed.');
Auth::logout();

$openJobId = Database::insert('job_requisitions', [
    'department_id' => $departmentId,
    'title' => 'Feature 007 Open Job',
    'description' => 'Validation job',
    'requirements' => 'PHP, MySQL',
    'status' => 'OPEN',
    'created_by' => $hr['user_id'],
    'opened_at' => date('Y-m-d H:i:s'),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);
$closedJobId = Database::insert('job_requisitions', [
    'department_id' => $departmentId,
    'title' => 'Feature 007 Closed Job',
    'description' => 'Validation closed job',
    'requirements' => 'PHP',
    'status' => 'CLOSED',
    'created_by' => $hr['user_id'],
    'closed_at' => date('Y-m-d H:i:s'),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);

$applicationId = create_application($candidateId, $openJobId, ApplicationStatus::SCREENING->value, date('Y-m-d H:i:s', strtotime('-20 days')));
$notificationId = NotificationRepository::createApplicationStatusNotification($applicationId, ApplicationStatus::SCREENING->value);
assert_true($notificationId !== null, 'Status notification was not created.');
assert_true(NotificationRepository::unreadCount($candidateId) === 1, 'Unread notification count is incorrect.');
$notification = NotificationRepository::find($notificationId);
assert_true((new NotificationPolicy())->markRead(Database::fetch('SELECT * FROM users WHERE user_id = ?', [$candidateId]), $notification), 'Candidate cannot mark own notification read.');
assert_true(! (new NotificationPolicy())->markRead(Database::fetch('SELECT * FROM users WHERE user_id = ?', [$interviewerId]), $notification), 'Interviewer can mark candidate notification read.');
NotificationRepository::markRead($notificationId, $candidateId);
assert_true(NotificationRepository::unreadCount($candidateId) === 0, 'Read notification count did not update.');

$interviewId = Database::insert('interviews', [
    'application_id' => $applicationId,
    'interview_type' => 'TECHNICAL',
    'scheduled_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
    'duration_minutes' => 60,
    'status' => 'COMPLETED',
    'created_by' => $hr['user_id'],
    'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
    'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
]);
Database::insert('interviewers_assignment', ['interview_id' => $interviewId, 'interviewer_id' => $interviewerId, 'role_in_panel' => 'INTERVIEWER', 'is_shadowing' => 0]);
$missingFeedback = NotificationRepository::findMissingFeedbackReminders();
assert_true((bool) array_filter($missingFeedback, fn (array $row): bool => (int) $row['interview_id'] === $interviewId), 'Missing feedback reminder candidate not found.');
$feedbackReminderId = NotificationRepository::createUnique($interviewerId, 'Feedback Reminder', 'Please submit feedback.', NotificationType::FEEDBACK_REMINDER->value, $interviewId, 'INTERVIEW');
assert_true($feedbackReminderId !== null, 'Feedback reminder was not created.');
assert_true(NotificationRepository::createUnique($interviewerId, 'Feedback Reminder', 'Please submit feedback.', NotificationType::FEEDBACK_REMINDER->value, $interviewId, 'INTERVIEW') === null, 'Duplicate feedback reminder was created.');

$offerSoonId = Database::insert('offers', [
    'application_id' => $applicationId,
    'offer_sequence' => 1,
    'offer_type' => 'FULL_TIME',
    'ctc' => 10000,
    'bonus' => 0,
    'stock_options' => 0,
    'status' => OfferStatus::SENT->value,
    'expiry_date' => date('Y-m-d H:i:s', strtotime('+47 hours')),
    'sent_at' => date('Y-m-d H:i:s'),
    'created_by' => $hr['user_id'],
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);
$offerExpiredId = Database::insert('offers', [
    'application_id' => $applicationId,
    'offer_sequence' => 2,
    'offer_type' => 'FULL_TIME',
    'ctc' => 11000,
    'bonus' => 0,
    'stock_options' => 0,
    'status' => OfferStatus::SENT->value,
    'expiry_date' => date('Y-m-d H:i:s', strtotime('-1 hour')),
    'sent_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
    'created_by' => $hr['user_id'],
    'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
    'updated_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
]);
assert_true((bool) array_filter(NotificationRepository::findOffersExpiringWithin48Hours(), fn (array $row): bool => (int) $row['offer_id'] === $offerSoonId), 'Expiring offer not found.');
assert_true((bool) array_filter(NotificationRepository::findExpiredSentOffers(), fn (array $row): bool => (int) $row['offer_id'] === $offerExpiredId), 'Expired sent offer not found.');
assert_true(OfferRepository::enforceExpiryForOffer($offerExpiredId, (int) $hr['user_id']), 'Expired offer was not transitioned.');
assert_true(Database::fetch('SELECT status FROM offers WHERE offer_id = ?', [$offerExpiredId])['status'] === OfferStatus::EXPIRED->value, 'Offer status is not EXPIRED.');

$hiredCandidateId = create_candidate('Feature 007 Hired Candidate', "feature007.hired.$suffix@example.com");
$hiredApplicationId = create_application($hiredCandidateId, $openJobId, ApplicationStatus::HIRED->value, date('Y-m-d H:i:s', strtotime('-30 days')));
Database::insert('application_status_histories', ['application_id' => $hiredApplicationId, 'actor_user_id' => $hr['user_id'], 'old_status' => 'OFFER', 'new_status' => 'HIRED', 'reason' => 'Validation hire', 'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))]);
$pipeline = ReportRepository::pipelineByOpenRequisition();
assert_true((bool) array_filter($pipeline['rows'], fn (array $row): bool => (int) $row['job_id'] === $openJobId), 'Pipeline report missing open job.');
assert_true((bool) ReportRepository::timeToHireByRequisition(), 'Time-to-hire report is empty.');

$audit = AuditLogRepository::search(['action' => 'OFFER_EXPIRE'], 1, 25);
assert_true($audit['total'] >= 1, 'Audit log does not include offer expiry.');
$hrUser = Database::fetch('SELECT * FROM users WHERE user_id = ?', [$hr['user_id']]);
$candidateUser = Database::fetch('SELECT * FROM users WHERE user_id = ?', [$candidateId]);
assert_true((new ReportPolicy())->viewPipeline($hrUser), 'HR cannot view reports.');
assert_true(! (new ReportPolicy())->viewPipeline($candidateUser), 'Candidate can view reports.');
assert_true((new AuditLogPolicy())->view($hrUser), 'HR cannot view audit log.');
assert_true(! (new AuditLogPolicy())->view($candidateUser), 'Candidate can view audit log.');
assert_true((new DataRetentionPolicy())->performAction($hrUser), 'HR cannot perform retention actions.');
assert_true(! (new DataRetentionPolicy())->performAction($candidateUser), 'Candidate can perform retention actions.');

$retentionCandidateId = create_candidate('Feature 007 Retention Candidate', "feature007.retention.$suffix@example.com");
create_application($retentionCandidateId, $closedJobId, ApplicationStatus::REJECTED->value, date('Y-m-d H:i:s', strtotime('-400 days')));
assert_true(DataRetentionRepository::anonymize($retentionCandidateId, (int) $hr['user_id'], 365), 'Retention anonymization failed.');
$anonymized = Database::fetch('SELECT u.name, u.email, c.phone, c.resume_url FROM users u JOIN candidates c ON c.candidate_id = u.user_id WHERE u.user_id = ?', [$retentionCandidateId]);
assert_true($anonymized['name'] === 'Anonymized Candidate' && $anonymized['phone'] === 'REDACTED' && $anonymized['resume_url'] === null, 'Candidate PII was not anonymized.');

$deleteCandidateId = create_candidate('Feature 007 Delete Candidate', "feature007.delete.$suffix@example.com");
create_application($deleteCandidateId, $closedJobId, ApplicationStatus::REJECTED->value, date('Y-m-d H:i:s', strtotime('-410 days')));
assert_true(DataRetentionRepository::delete($deleteCandidateId, (int) $hr['user_id'], 365), 'Retention deletion failed.');
assert_true(Database::fetch('SELECT user_id FROM users WHERE user_id = ?', [$deleteCandidateId]) === null, 'Deleted candidate user still exists.');
assert_true((bool) Database::fetch('SELECT audit_id FROM account_audit_records WHERE action = ? ORDER BY audit_id DESC LIMIT 1', ['CANDIDATE_DELETED']), 'Deletion audit record missing.');

print "Feature 007 validation passed.\n";
print "Scenario logins: HR " . Config::get('FIRST_HR_ADMIN_EMAIL') . " / " . Config::get('FIRST_HR_ADMIN_PASSWORD') . "; Interviewer feature007.interviewer.$suffix@example.com / password; Candidate feature007.candidate.$suffix@example.com / password.\n";
