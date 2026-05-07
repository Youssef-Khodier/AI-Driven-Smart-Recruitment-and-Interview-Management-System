<?php

require dirname(__DIR__) . '/bootstrap/autoload.php';

use App\Core\Config;
use App\Core\Database;

Config::load(dirname(__DIR__));
Database::configure(Config::database());

$now = date('Y-m-d H:i:s');
$password = 'password';

function j(array $value): string
{
    return json_encode($value, JSON_UNESCAPED_SLASHES);
}

function rowId(string $table, string $idColumn, string $where, array $params, array $data): int
{
    $existing = Database::fetch("SELECT {$idColumn} FROM {$table} WHERE {$where} LIMIT 1", $params);
    if ($existing) {
        return (int) $existing[$idColumn];
    }

    return Database::insert($table, $data);
}

function department(string $name, string $description): int
{
    return rowId('departments', 'department_id', 'name = ?', [$name], [
        'name' => $name,
        'description' => $description,
    ]);
}

function userAccount(string $name, string $email, string $role, ?int $departmentId, bool $isHead = false): int
{
    global $now, $password;

    return rowId('users', 'user_id', 'email = ?', [$email], [
        'department_id' => $departmentId,
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => $role,
        'status' => 'ACTIVE',
        'is_department_head' => $isHead ? 1 : 0,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

function candidateAccount(string $name, string $email, string $phone, string $title, int $years, string $location, string $skills): int
{
    global $now;

    $userId = userAccount($name, $email, 'CANDIDATE', null);
    rowId('candidates', 'candidate_id', 'candidate_id = ?', [$userId], [
        'candidate_id' => $userId,
        'phone' => $phone,
        'current_title' => $title,
        'years_experience' => $years,
        'location' => $location,
        'resume_url' => 'https://example.com/resumes/' . strtolower(str_replace(' ', '-', $name)) . '.pdf',
        'skill_keywords' => $skills,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return $userId;
}

function application(int $candidateId, int $jobId, string $status, int $score, int $actorId, string $reason): int
{
    global $now;

    $id = rowId('applications', 'application_id', 'candidate_id = ? AND job_id = ?', [$candidateId, $jobId], [
        'candidate_id' => $candidateId,
        'job_id' => $jobId,
        'status' => $status,
        'match_score' => $score,
        'applied_at' => date('Y-m-d H:i:s', strtotime('-18 days')),
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    rowId('application_status_histories', 'history_id', 'application_id = ? AND new_status = ?', [$id, $status], [
        'application_id' => $id,
        'actor_user_id' => $actorId,
        'old_status' => null,
        'new_status' => $status,
        'reason' => $reason,
        'created_at' => $now,
    ]);

    return $id;
}

$hrDepartmentId = department('Human Resources', 'Recruitment and HR operations');
$engineeringDepartmentId = department('Engineering', 'Product engineering and platform delivery');
$dataDepartmentId = department('Data Science', 'Analytics, machine learning, and decision support');

$configuredHrEmail = Config::get('FIRST_HR_ADMIN_EMAIL');
$hrAdminId = userAccount(Config::get('FIRST_HR_ADMIN_NAME'), $configuredHrEmail, 'HR_ADMIN', $hrDepartmentId);
$approverId = userAccount('Dana Farouk', 'dana.head@example.com', 'HR_ADMIN', $engineeringDepartmentId, true);
$interviewerId = userAccount('Omar Nabil', 'omar.interviewer@example.com', 'INTERVIEWER', $engineeringDepartmentId);
$shadowId = userAccount('Mona Salem', 'mona.shadow@example.com', 'INTERVIEWER', $engineeringDepartmentId);

$candidateAId = candidateAccount('Lina Hassan', 'lina.candidate@example.com', '+20 100 555 0101', 'Backend Engineer', 5, 'Cairo, Egypt', 'PHP, MySQL, REST APIs, queues, JavaScript');
$candidateBId = candidateAccount('Karim Atef', 'karim.candidate@example.com', '+20 100 555 0102', 'Full-stack Developer', 3, 'Giza, Egypt', 'JavaScript, PHP, Laravel, MySQL, React');
$candidateCId = candidateAccount('Sara Mansour', 'sara.candidate@example.com', '+20 100 555 0103', 'Junior Data Analyst', 1, 'Alexandria, Egypt', 'SQL, dashboards, Python, Excel');

foreach ([
    [$interviewerId, 0, 1, 1, 0, 'Backend systems', 'Senior'],
    [$shadowId, 0, 0, 0, 1, 'Interview shadowing', 'Junior'],
    [$approverId, 1, 0, 1, 0, 'Department approval', 'Head'],
] as $capability) {
    rowId('staff_panel_capabilities', 'capability_id', 'user_id = ?', [$capability[0]], [
        'user_id' => $capability[0],
        'can_represent_hr' => $capability[1],
        'can_lead_technical' => $capability[2],
        'can_interview' => $capability[3],
        'can_observe' => $capability[4],
        'specialization' => $capability[5],
        'seniority_level' => $capability[6],
    ]);
}

$backendJobId = rowId('job_requisitions', 'job_id', 'title = ?', ['Senior Backend Engineer'], [
    'department_id' => $engineeringDepartmentId,
    'title' => 'Senior Backend Engineer',
    'location' => 'Cairo, Egypt',
    'description' => 'Build API services, improve hiring platform workflows, and support operational reporting.',
    'requirements' => 'PHP, MySQL, JavaScript, API design, testing, and production troubleshooting.',
    'status' => 'OPEN',
    'created_by' => $hrAdminId,
    'approved_by' => $approverId,
    'approved_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
    'opened_at' => date('Y-m-d H:i:s', strtotime('-14 days')),
    'created_at' => date('Y-m-d H:i:s', strtotime('-16 days')),
    'updated_at' => $now,
]);

$analystJobId = rowId('job_requisitions', 'job_id', 'title = ?', ['People Analytics Specialist'], [
    'department_id' => $dataDepartmentId,
    'title' => 'People Analytics Specialist',
    'location' => 'Giza, Egypt',
    'description' => 'Create recruiting dashboards, diversity reports, and pipeline quality insights.',
    'requirements' => 'SQL, statistics, dashboarding, stakeholder communication, and privacy-aware reporting.',
    'status' => 'OPEN',
    'created_by' => $hrAdminId,
    'approved_by' => $approverId,
    'approved_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
    'opened_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
    'created_at' => date('Y-m-d H:i:s', strtotime('-11 days')),
    'updated_at' => $now,
]);

foreach ([
    [$backendJobId, null, 'OPEN', 'Demo requisition opened after approval.'],
    [$analystJobId, null, 'OPEN', 'Demo requisition opened after approval.'],
] as $history) {
    rowId('job_requisition_status_histories', 'history_id', 'job_id = ? AND new_status = ?', [$history[0], $history[2]], [
        'job_id' => $history[0],
        'actor_user_id' => $hrAdminId,
        'old_status' => $history[1],
        'new_status' => $history[2],
        'reason' => $history[3],
        'created_at' => $now,
    ]);
}

foreach ([$backendJobId, $analystJobId] as $jobId) {
    rowId('requisition_approval_steps', 'step_id', 'job_id = ? AND approver_id = ?', [$jobId, $approverId], [
        'job_id' => $jobId,
        'approver_id' => $approverId,
        'decision' => 'APPROVED',
        'comments' => 'Approved for academic demo flow.',
        'created_at' => $now,
    ]);
    rowId('requisition_template_versions', 'version_id', 'job_id = ? AND version_number = ?', [$jobId, 1], [
        'job_id' => $jobId,
        'version_number' => 1,
        'description_body' => 'Initial approved demo requisition description.',
        'requirements_body' => 'Initial approved demo requirements.',
        'created_by' => $hrAdminId,
        'created_at' => $now,
    ]);
    rowId('requisition_template_versions', 'version_id', 'job_id = ? AND version_number = ?', [$jobId, 2], [
        'job_id' => $jobId,
        'version_number' => 2,
        'description_body' => 'Clarified scope, reporting expectations, and interview process.',
        'requirements_body' => 'Added screening keywords and assessment requirements for demo readiness.',
        'created_by' => $approverId,
        'created_at' => $now,
    ]);
}

$configId = rowId('screening_configs', 'config_id', 'job_id = ? AND is_active = 1', [$backendJobId], [
    'job_id' => $backendJobId,
    'is_active' => 1,
    'created_by' => $hrAdminId,
    'created_at' => $now,
]);
foreach ([
    ['PHP', 35, 'skill_keywords'],
    ['MySQL', 25, 'skill_keywords'],
    ['JavaScript', 20, 'skill_keywords'],
    ['API design', 20, 'skill_keywords'],
] as $skill) {
    rowId('screening_skills', 'skill_id', 'config_id = ? AND skill_name = ?', [$configId, $skill[0]], [
        'config_id' => $configId,
        'skill_name' => $skill[0],
        'weight' => $skill[1],
        'evidence_field' => $skill[2],
    ]);
}
foreach ([[0, 39, 'REJECTED'], [40, 59, 'SCREENING'], [60, 79, 'ASSESSMENT'], [80, 100, 'INTERVIEW']] as $threshold) {
    rowId('screening_thresholds', 'threshold_id', 'config_id = ? AND min_score = ? AND max_score = ?', [$configId, $threshold[0], $threshold[1]], [
        'config_id' => $configId,
        'min_score' => $threshold[0],
        'max_score' => $threshold[1],
        'target_status' => $threshold[2],
    ]);
}

$appAId = application($candidateAId, $backendJobId, 'HIRED', 92, $hrAdminId, 'Seeded as completed hire for full flow.');
$appBId = application($candidateBId, $backendJobId, 'INTERVIEW', 78, $hrAdminId, 'Seeded as active interview-stage candidate.');
$appCId = application($candidateCId, $backendJobId, 'REJECTED', 34, $hrAdminId, 'Seeded low match score for rejection and triage demo.');
$appDId = application($candidateAId, $analystJobId, 'APPLIED', 61, $candidateAId, 'Candidate submitted a second application.');

$assessmentId = rowId('assessments', 'assessment_id', 'job_id = ? AND title = ?', [$backendJobId, 'Backend Engineering Assessment'], [
    'job_id' => $backendJobId,
    'title' => 'Backend Engineering Assessment',
    'description' => 'Small mixed assessment for screening PHP, SQL, and output reasoning.',
    'type' => 'TECHNICAL',
    'duration_minutes' => 45,
    'cooldown_months' => 6,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);
foreach ([['EASY', 1], ['MEDIUM', 1], ['HARD', 1]] as $rule) {
    rowId('assessment_question_rules', 'rule_id', 'assessment_id = ? AND difficulty_level = ?', [$assessmentId, $rule[0]], [
        'assessment_id' => $assessmentId,
        'difficulty_level' => $rule[0],
        'question_count' => $rule[1],
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

$easyQuestionId = rowId('questions', 'question_id', 'assessment_id = ? AND question_text = ?', [$assessmentId, 'Which SQL clause filters grouped aggregate results?'], [
    'assessment_id' => $assessmentId,
    'type' => 'MCQ',
    'difficulty_level' => 'EASY',
    'question_text' => 'Which SQL clause filters grouped aggregate results?',
    'options' => j(['WHERE', 'HAVING', 'ORDER BY', 'LIMIT']),
    'correct_answer' => 'HAVING',
    'points' => 2,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);
$mediumQuestionId = rowId('questions', 'question_id', 'assessment_id = ? AND question_text = ?', [$assessmentId, 'Write the output of the PHP loop that prints even numbers from 2 to 6.'], [
    'assessment_id' => $assessmentId,
    'type' => 'CODE_OUTPUT',
    'difficulty_level' => 'MEDIUM',
    'question_text' => 'Write the output of the PHP loop that prints even numbers from 2 to 6.',
    'options' => null,
    'correct_answer' => "2\n4\n6",
    'points' => 4,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);
$hardQuestionId = rowId('questions', 'question_id', 'assessment_id = ? AND question_text = ?', [$assessmentId, 'Describe how you would make application status transitions auditable and retry-safe.'], [
    'assessment_id' => $assessmentId,
    'type' => 'TEXT',
    'difficulty_level' => 'HARD',
    'question_text' => 'Describe how you would make application status transitions auditable and retry-safe.',
    'options' => null,
    'correct_answer' => 'Use transactions, status history rows, idempotent commands, and validation around allowed transitions.',
    'points' => 6,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);
rowId('question_expected_outputs', 'output_id', 'question_id = ? AND label = ?', [$mediumQuestionId, 'Visible sample'], [
    'question_id' => $mediumQuestionId,
    'expected_output' => "2\n4\n6",
    'label' => 'Visible sample',
    'is_hidden' => 0,
    'created_at' => $now,
]);
rowId('question_expected_outputs', 'output_id', 'question_id = ? AND label = ?', [$mediumQuestionId, 'Hidden whitespace check'], [
    'question_id' => $mediumQuestionId,
    'expected_output' => "2\n4\n6\n",
    'label' => 'Hidden whitespace check',
    'is_hidden' => 1,
    'created_at' => $now,
]);
foreach ([
    [$easyQuestionId, 'HAVING'],
    [$hardQuestionId, 'I would use transactions and an audit table so every transition is recorded with old and new status.'],
] as $answer) {
    rowId('assessment_common_answers', 'common_answer_id', 'question_id = ? AND source_label = ?', [$answer[0], 'Demo common answer'], [
        'assessment_id' => $assessmentId,
        'question_id' => $answer[0],
        'answer_text' => $answer[1],
        'source_label' => 'Demo common answer',
        'created_at' => $now,
    ]);
}

$attemptAId = rowId('candidate_assessments', 'ca_id', 'application_id = ? AND assessment_id = ?', [$appAId, $assessmentId], [
    'application_id' => $appAId,
    'candidate_id' => $candidateAId,
    'assessment_id' => $assessmentId,
    'start_time' => date('Y-m-d H:i:s', strtotime('-12 days')),
    'end_time' => date('Y-m-d H:i:s', strtotime('-12 days +38 minutes')),
    'expires_at' => date('Y-m-d H:i:s', strtotime('-12 days +45 minutes')),
    'remaining_seconds' => 420,
    'last_heartbeat_at' => date('Y-m-d H:i:s', strtotime('-12 days +37 minutes')),
    'status' => 'SUBMITTED',
    'score' => 88.000,
    'created_at' => date('Y-m-d H:i:s', strtotime('-12 days')),
    'updated_at' => $now,
]);

$questionMap = [[$easyQuestionId, 1], [$mediumQuestionId, 2], [$hardQuestionId, 3]];
foreach ($questionMap as $pair) {
    $question = Database::fetch('SELECT * FROM questions WHERE question_id = ?', [$pair[0]]);
    rowId('candidate_assessment_questions', 'attempt_question_id', 'ca_id = ? AND question_id = ?', [$attemptAId, $pair[0]], [
        'ca_id' => $attemptAId,
        'question_id' => $pair[0],
        'display_order' => $pair[1],
        'question_type' => $question['type'],
        'question_text' => $question['question_text'],
        'options' => $question['options'],
        'correct_answer' => $question['correct_answer'],
        'points' => $question['points'],
        'created_at' => $now,
    ]);
}

foreach (Database::fetchAll('SELECT * FROM candidate_assessment_questions WHERE ca_id = ?', [$attemptAId]) as $attemptQuestion) {
    $answers = [
        $easyQuestionId => ['HAVING', null, 1, null, 2, 0.020],
        $mediumQuestionId => [null, "2\n4\n6", 1, 1, 4, 0.000],
        $hardQuestionId => ['Use a transaction to update the application and insert a status history record, with validation for allowed old and new statuses.', null, 1, null, 5, 0.180],
    ];
    $answer = $answers[(int) $attemptQuestion['question_id']];
    rowId('submissions', 'submission_id', 'ca_id = ? AND attempt_question_id = ?', [$attemptAId, $attemptQuestion['attempt_question_id']], [
        'ca_id' => $attemptAId,
        'attempt_question_id' => $attemptQuestion['attempt_question_id'],
        'question_id' => $attemptQuestion['question_id'],
        'answer_text' => $answer[0],
        'code_output' => $answer[1],
        'saved_at' => date('Y-m-d H:i:s', strtotime('-12 days +35 minutes')),
        'finalized_at' => date('Y-m-d H:i:s', strtotime('-12 days +38 minutes')),
        'is_correct' => $answer[2],
        'output_matched' => $answer[3],
        'awarded_points' => $answer[4],
        'plagiarism_score' => $answer[5],
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}
rowId('assessment_integrity_events', 'event_id', 'ca_id = ? AND event_type = ?', [$attemptAId, 'FOCUS_LOST'], [
    'ca_id' => $attemptAId,
    'event_type' => 'FOCUS_LOST',
    'occurred_at' => date('Y-m-d H:i:s', strtotime('-12 days +18 minutes')),
    'metadata' => j(['duration_seconds' => 9, 'source' => 'demo']),
    'created_at' => $now,
]);

$interviewAId = rowId('interviews', 'interview_id', 'application_id = ? AND interview_type = ?', [$appAId, 'TECHNICAL_PANEL'], [
    'application_id' => $appAId,
    'interview_type' => 'TECHNICAL_PANEL',
    'scheduled_at' => date('Y-m-d H:i:s', strtotime('-7 days')),
    'duration_minutes' => 60,
    'extended_duration_minutes' => 15,
    'status' => 'COMPLETED',
    'created_by' => $hrAdminId,
    'last_extension_decision_at' => date('Y-m-d H:i:s', strtotime('-7 days +50 minutes')),
    'created_at' => $now,
    'updated_at' => $now,
]);
$interviewBId = rowId('interviews', 'interview_id', 'application_id = ? AND interview_type = ?', [$appBId, 'TECHNICAL_PANEL'], [
    'application_id' => $appBId,
    'interview_type' => 'TECHNICAL_PANEL',
    'scheduled_at' => date('Y-m-d H:i:s', strtotime('+2 days')),
    'duration_minutes' => 60,
    'extended_duration_minutes' => 0,
    'status' => 'SCHEDULED',
    'created_by' => $hrAdminId,
    'created_at' => $now,
    'updated_at' => $now,
]);

foreach ([$interviewAId, $interviewBId] as $interviewId) {
    foreach ([[$approverId, 'HR_REPRESENTATIVE', 0], [$interviewerId, 'PANEL_LEAD', 0], [$shadowId, 'OBSERVER', 1]] as $assignment) {
        rowId('interviewers_assignment', 'assignment_id', 'interview_id = ? AND interviewer_id = ?', [$interviewId, $assignment[0]], [
            'interview_id' => $interviewId,
            'interviewer_id' => $assignment[0],
            'role_in_panel' => $assignment[1],
            'is_shadowing' => $assignment[2],
            'assignment_source' => 'MANUAL',
            'override_reason' => null,
            'conflict_overridden' => 0,
            'assigned_by' => $hrAdminId,
            'assigned_at' => $now,
        ]);
    }
}

foreach ([
    [$interviewAId, $interviewerId, 8.8, 8.0, 8.4, 8.6, 'Strong backend depth and practical tradeoff discussion.'],
    [$interviewAId, $approverId, 8.0, 8.7, 8.5, 8.4, 'Clear communication and good fit for cross-functional hiring workflows.'],
] as $feedback) {
    rowId('interview_feedback', 'feedback_id', 'interview_id = ? AND interviewer_id = ?', [$feedback[0], $feedback[1]], [
        'interview_id' => $feedback[0],
        'interviewer_id' => $feedback[1],
        'technical_score' => $feedback[2],
        'communication_score' => $feedback[3],
        'culture_fit_score' => $feedback[4],
        'overall_score' => $feedback[5],
        'comments' => $feedback[6],
        'submitted_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
    ]);
}

rowId('interview_extension_requests', 'extension_request_id', 'interview_id = ? AND requested_by = ?', [$interviewAId, $interviewerId], [
    'interview_id' => $interviewAId,
    'requested_by' => $interviewerId,
    'requested_minutes' => 15,
    'request_reason' => 'Candidate was mid-way through a debugging exercise.',
    'status' => 'APPROVED',
    'approved_minutes' => 15,
    'decided_by' => $hrAdminId,
    'decision_reason' => 'Approved for a complete technical signal.',
    'requested_at' => date('Y-m-d H:i:s', strtotime('-7 days +45 minutes')),
    'decided_at' => date('Y-m-d H:i:s', strtotime('-7 days +50 minutes')),
]);
rowId('interview_briefing_snapshots', 'briefing_id', 'interview_id = ?', [$interviewAId], [
    'interview_id' => $interviewAId,
    'candidate_summary' => 'Lina Hassan, Backend Engineer with 5 years of PHP and MySQL experience.',
    'assessment_summary' => 'Submitted assessment score 88 with matching code output.',
    'job_requirements_summary' => 'PHP, MySQL, JavaScript, API design, testing, and troubleshooting.',
    'missing_data_flags' => j([]),
]);
$workspaceId = rowId('simulated_coding_workspaces', 'workspace_id', 'interview_id = ?', [$interviewAId], [
    'interview_id' => $interviewAId,
    'prompt_text' => 'Implement a function that groups application status counts by requisition.',
    'code_text' => "<?php\nfunction statusCounts(array \$applications): array { return array_count_values(array_column(\$applications, 'status')); }\n",
    'candidate_run_notes' => 'Candidate explained edge cases around empty inputs.',
    'interviewer_notes' => 'Good concise implementation.',
    'version_number' => 2,
    'last_saved_by' => $interviewerId,
    'last_saved_at' => date('Y-m-d H:i:s', strtotime('-7 days +55 minutes')),
]);
rowId('workspace_history_records', 'history_id', 'workspace_id = ? AND new_version_number = ?', [$workspaceId, 2], [
    'workspace_id' => $workspaceId,
    'interview_id' => $interviewAId,
    'actor_user_id' => $interviewerId,
    'changed_section' => 'interviewer_notes',
    'previous_version_number' => 1,
    'new_version_number' => 2,
    'change_summary' => 'Added interviewer evaluation notes after run-through.',
    'created_at' => $now,
]);

$snapshotId = rowId('normalized_evaluation_snapshots', 'snapshot_id', 'application_id = ? AND interview_id = ?', [$appAId, $interviewAId], [
    'application_id' => $appAId,
    'interview_id' => $interviewAId,
    'calculated_by' => $hrAdminId,
    'raw_score_summary' => j(['technical' => 8.4, 'communication' => 8.35, 'culture_fit' => 8.45]),
    'normalized_score_summary' => j(['technical' => 8.7, 'communication' => 8.4, 'culture_fit' => 8.5]),
    'aggregate_score' => 86.00,
    'recommendation' => 'HIRE',
    'normalization_status' => 'FALLBACK_WITH_HISTORY',
    'fallback_reasons' => j(['Small demo data set']),
    'included_feedback_count' => 2,
    'missing_feedback_count' => 0,
    'created_at' => $now,
]);
foreach ([['Technical Depth', 8.0, 30, 8.7], ['Communication', 7.5, 20, 8.4], ['Culture Fit', 7.0, 15, 8.5]] as $benchmark) {
    $benchmarkId = rowId('job_competency_benchmarks', 'benchmark_id', 'job_id = ? AND competency = ?', [$backendJobId, $benchmark[0]], [
        'job_id' => $backendJobId,
        'competency' => $benchmark[0],
        'benchmark_score' => $benchmark[1],
        'weight' => $benchmark[2],
        'source' => 'demo seed',
        'updated_by' => $hrAdminId,
        'updated_at' => $now,
    ]);
    rowId('competency_gap_snapshots', 'gap_id', 'snapshot_id = ? AND benchmark_id = ?', [$snapshotId, $benchmarkId], [
        'snapshot_id' => $snapshotId,
        'benchmark_id' => $benchmarkId,
        'competency' => $benchmark[0],
        'candidate_score' => $benchmark[3],
        'benchmark_score' => $benchmark[1],
        'gap_ratio' => round($benchmark[3] - $benchmark[1], 2),
        'severity' => $benchmark[3] >= $benchmark[1] ? 'LOW' : 'MEDIUM',
    ]);
}
rowId('evaluation_debrief_records', 'debrief_id', 'interview_id = ?', [$interviewAId], [
    'application_id' => $appAId,
    'interview_id' => $interviewAId,
    'status' => 'COMPLETED',
    'participants' => j([$approverId, $interviewerId]),
    'consensus_level' => 'HIGH',
    'dissent_notes' => null,
    'final_recommendation' => 'HIRE',
    'rationale' => 'Assessment and panel feedback both support offer.',
    'next_action' => 'CREATE_OFFER',
    'created_at' => $now,
    'completed_by' => $hrAdminId,
    'completed_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
]);
rowId('candidate_interview_sentiment', 'sentiment_id', 'candidate_id = ? AND interview_id = ?', [$candidateAId, $interviewAId], [
    'candidate_id' => $candidateAId,
    'application_id' => $appAId,
    'interview_id' => $interviewAId,
    'rating' => 5,
    'comment' => 'Panel was organized and the coding prompt matched the role.',
    'submitted_at' => date('Y-m-d H:i:s', strtotime('-6 days +2 hours')),
]);

rowId('final_evaluations', 'evaluation_id', 'application_id = ?', [$appAId], [
    'application_id' => $appAId,
    'aggregate_score' => 87.25,
    'recommendation' => 'HIRE',
    'status' => 'HIRE',
    'decision_notes' => 'Strong technical evidence and positive panel consensus.',
    'partial_evidence_acknowledged' => 0,
    'evaluated_by' => $hrAdminId,
    'created_at' => $now,
    'updated_at' => $now,
]);
$offerAId = rowId('offers', 'offer_id', 'application_id = ? AND offer_sequence = ?', [$appAId, 1], [
    'application_id' => $appAId,
    'offer_sequence' => 1,
    'replaces_offer_id' => null,
    'offer_type' => 'FULL_TIME',
    'ctc' => 720000.00,
    'bonus' => 45000.00,
    'stock_options' => 12000.00,
    'status' => 'ACCEPTED',
    'expiry_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
    'sent_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
    'accepted_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
    'created_by' => $hrAdminId,
    'created_at' => $now,
    'updated_at' => $now,
]);
$onboardingId = rowId('onboarding', 'onboarding_id', 'offer_id = ?', [$offerAId], [
    'offer_id' => $offerAId,
    'status' => 'IN_PROGRESS',
    'start_date' => date('Y-m-d', strtotime('+14 days')),
    'documents_completed' => 0,
    'created_by' => $hrAdminId,
    'created_at' => $now,
    'updated_at' => $now,
]);
rowId('offers', 'offer_id', 'application_id = ? AND offer_sequence = ?', [$appBId, 1], [
    'application_id' => $appBId,
    'offer_sequence' => 1,
    'replaces_offer_id' => null,
    'offer_type' => 'FULL_TIME',
    'ctc' => 540000.00,
    'bonus' => 25000.00,
    'stock_options' => 6000.00,
    'status' => 'DRAFT',
    'expiry_date' => date('Y-m-d H:i:s', strtotime('+10 days')),
    'created_by' => $hrAdminId,
    'created_at' => $now,
    'updated_at' => $now,
]);

$referralId = rowId('referrals', 'referral_id', 'application_id = ?', [$appAId], [
    'application_id' => $appAId,
    'candidate_id' => $candidateAId,
    'referrer_user_id' => $interviewerId,
    'referrer_name' => 'Omar Nabil',
    'referrer_email' => 'omar.interviewer@example.com',
    'referral_source' => 'INTERNAL',
    'notes' => 'Referred from a previous platform project.',
    'created_at' => $now,
]);
rowId('referral_rewards', 'reward_id', 'referral_id = ?', [$referralId], [
    'referral_id' => $referralId,
    'reward_status' => 'APPROVED',
    'reward_amount' => 5000.00,
    'reward_type' => 'MONETARY',
    'approved_by' => $hrAdminId,
    'approved_at' => $now,
    'notes' => 'Reward approved after offer acceptance.',
]);
rowId('background_checks', 'background_check_id', 'application_id = ? AND check_type = ?', [$appAId, 'employment'], [
    'application_id' => $appAId,
    'candidate_id' => $candidateAId,
    'check_type' => 'employment',
    'status' => 'cleared',
    'result_notes' => 'Employment dates verified for demo candidate.',
    'requested_by' => $hrAdminId,
    'requested_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
    'completed_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
    'completed_by' => $hrAdminId,
    'created_at' => $now,
    'updated_at' => $now,
]);

foreach ([
    [$candidateAId, 'Female', 'Middle Eastern or North African', 'No disability disclosed', 'Not a veteran', 1],
    [$candidateBId, 'Male', 'Middle Eastern or North African', 'No disability disclosed', 'Not a veteran', 1],
    [$candidateCId, 'Female', 'Prefer not to say', 'Prefer not to say', 'Not a veteran', 1],
] as $demo) {
    rowId('candidate_demographics', 'demographic_id', 'candidate_id = ?', [$demo[0]], [
        'candidate_id' => $demo[0],
        'gender_category' => $demo[1],
        'ethnicity_category' => $demo[2],
        'disability_category' => $demo[3],
        'veteran_status_category' => $demo[4],
        'consent_flag' => $demo[5],
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

foreach ([
    [$candidateAId, 'Offer accepted', 'Your accepted offer is ready for onboarding.', 'STATUS_CHANGE', $offerAId, 'OFFER', 0],
    [$interviewerId, 'Upcoming interview', 'Karim Atef has a technical panel scheduled in two days.', 'FEEDBACK_REMINDER', $interviewBId, 'INTERVIEW', 0],
    [$hrAdminId, 'Background check cleared', 'Lina Hassan employment verification is cleared.', 'BACKGROUND_CHECK_ESCALATION', $appAId, 'APPLICATION', 1],
] as $notification) {
    rowId('notifications', 'notification_id', 'user_id = ? AND title = ?', [$notification[0], $notification[1]], [
        'user_id' => $notification[0],
        'title' => $notification[1],
        'message' => $notification[2],
        'type' => $notification[3],
        'reference_id' => $notification[4],
        'reference_type' => $notification[5],
        'is_read' => $notification[6],
        'read_at' => $notification[6] ? $now : null,
        'created_at' => $now,
    ]);
}

rowId('account_audit_records', 'audit_id', 'actor_user_id = ? AND action = ? AND target_user_id = ?', [$hrAdminId, 'USER_CREATED', $interviewerId], [
    'actor_user_id' => $hrAdminId,
    'target_user_id' => $interviewerId,
    'action' => 'USER_CREATED',
    'old_values' => null,
    'new_values' => j(['role' => 'INTERVIEWER', 'source' => 'demo seed']),
    'created_at' => $now,
]);
rowId('requisition_governance_audit', 'audit_id', 'job_id = ? AND action = ?', [$backendJobId, 'APPROVED'], [
    'job_id' => $backendJobId,
    'actor_user_id' => $approverId,
    'action' => 'APPROVED',
    'old_values' => j(['status' => 'PENDING']),
    'new_values' => j(['status' => 'OPEN']),
    'comments' => 'Approved demo requisition.',
    'created_at' => $now,
]);
rowId('screening_audit_records', 'audit_id', 'job_id = ? AND action = ?', [$backendJobId, 'CONFIG_UPDATED'], [
    'job_id' => $backendJobId,
    'actor_user_id' => $hrAdminId,
    'action' => 'CONFIG_UPDATED',
    'entity_type' => 'screening_config',
    'entity_id' => $configId,
    'old_values' => null,
    'new_values' => j(['skills' => ['PHP', 'MySQL', 'JavaScript', 'API design']]),
    'created_at' => $now,
]);
rowId('interview_audit_records', 'audit_id', 'interview_id = ? AND action = ?', [$interviewAId, 'EXTENSION_APPROVED'], [
    'interview_id' => $interviewAId,
    'actor_user_id' => $hrAdminId,
    'action' => 'EXTENSION_APPROVED',
    'changed_fields' => j(['extended_duration_minutes' => ['old' => 0, 'new' => 15]]),
    'created_at' => $now,
]);
rowId('post_offer_audit_records', 'audit_id', 'application_id = ? AND action = ?', [$appAId, 'OFFER_ACCEPT'], [
    'application_id' => $appAId,
    'offer_id' => $offerAId,
    'onboarding_id' => null,
    'actor_user_id' => $candidateAId,
    'action' => 'OFFER_ACCEPT',
    'changed_fields' => j(['status' => ['old' => 'SENT', 'new' => 'ACCEPTED']]),
    'created_at' => $now,
]);
foreach (['PROFILE', 'TAX_FORMS'] as $task) {
    rowId('post_offer_audit_records', 'audit_id', 'onboarding_id = ? AND action = ? AND changed_fields = ?', [$onboardingId, 'ONBOARDING_TASK_COMPLETE', j(['task_key' => $task])], [
        'application_id' => $appAId,
        'offer_id' => $offerAId,
        'onboarding_id' => $onboardingId,
        'actor_user_id' => $candidateAId,
        'action' => 'ONBOARDING_TASK_COMPLETE',
        'changed_fields' => j(['task_key' => $task]),
        'created_at' => $now,
    ]);
}
rowId('feedback_governance_audit_records', 'audit_id', 'application_id = ? AND action = ?', [$appAId, 'NORMALIZED_SNAPSHOT_REFRESHED'], [
    'actor_user_id' => $hrAdminId,
    'actor_role' => 'HR_ADMIN',
    'application_id' => $appAId,
    'interview_id' => $interviewAId,
    'entity_type' => 'normalized_evaluation_snapshot',
    'entity_id' => $snapshotId,
    'action' => 'NORMALIZED_SNAPSHOT_REFRESHED',
    'old_values' => null,
    'new_values' => j(['aggregate_score' => 86.00]),
    'reason' => 'Demo normalized feedback snapshot.',
    'created_at' => $now,
]);
rowId('compliance_audit_events', 'audit_id', 'actor_user_id = ? AND action = ?', [$hrAdminId, 'RUN_CHECK_EXECUTED'], [
    'actor_user_id' => $hrAdminId,
    'actor_role' => 'HR_ADMIN',
    'entity_type' => 'COMPLIANCE_RUN',
    'entity_id' => null,
    'action' => 'RUN_CHECK_EXECUTED',
    'old_values' => null,
    'new_values' => j(['new_notifications' => 3, 'seeded' => true]),
    'reason' => 'Seeded audit record for compliance and notification demo.',
    'created_at' => $now,
]);

print "Seeded simplified 42-function academic demo data.\n";
print "Demo password for all seeded accounts: {$password}\n";
print "HR Admin: {$configuredHrEmail}\n";
print "Department Head / HR approver: dana.head@example.com\n";
print "Technical Interviewer: omar.interviewer@example.com\n";
print "Shadow Interviewer: mona.shadow@example.com\n";
print "Candidate: lina.candidate@example.com\n";
