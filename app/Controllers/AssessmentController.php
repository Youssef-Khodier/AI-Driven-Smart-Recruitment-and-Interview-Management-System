<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Enums\ApplicationStatus;
use App\Enums\AssessmentAttemptStatus;
use App\Enums\AssessmentQuestionType;
use App\Enums\AssessmentType;
use App\Enums\UserRole;
use App\Services\SimulatedAssessmentScorer;

final class AssessmentController extends Controller
{
    public function index(Request $request, string $jobId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $requisition = $this->requisition((int) $jobId);
        $assessments = Database::fetchAll(
            'SELECT a.*, COUNT(q.question_id) AS question_count, COUNT(ca.ca_id) AS attempt_count
             FROM assessments a
             LEFT JOIN questions q ON q.assessment_id = a.assessment_id
             LEFT JOIN candidate_assessments ca ON ca.assessment_id = a.assessment_id
             WHERE a.job_id = ? GROUP BY a.assessment_id ORDER BY a.created_at DESC',
            [$jobId]
        );

        return $this->view('hr/assessments/index', compact('requisition', 'assessments') + ['title' => 'Assessments']);
    }

    public function create(Request $request, string $jobId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        return $this->view('hr/assessments/form', ['title' => 'Create Assessment', 'requisition' => $this->requisition((int) $jobId), 'assessment' => null]);
    }

    public function store(Request $request, string $jobId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $data = $this->validate($request->body(), [
            'title' => ['required', ['max', 180]],
            'description' => [],
            'type' => ['required', ['in', AssessmentType::values()]],
            'duration_minutes' => ['required', 'numeric'],
            'is_active' => [],
        ]);
        $now = date('Y-m-d H:i:s');
        $id = Database::insert('assessments', [
            'job_id' => $jobId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'duration_minutes' => (int) $data['duration_minutes'],
            'is_active' => $this->checkboxValue($data, 'is_active'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        Session::flash('status', 'Assessment created.');

        return $this->redirect(url('hr.assessments.show', [$id]));
    }

    public function show(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $assessment = $this->assessment((int) $id);
        $questions = Database::fetchAll('SELECT * FROM questions WHERE assessment_id = ? ORDER BY created_at DESC', [$id]);
        $attempts = Database::fetchAll(
            'SELECT ca.*, u.name, u.email FROM candidate_assessments ca JOIN users u ON u.user_id = ca.candidate_id WHERE ca.assessment_id = ? ORDER BY ca.updated_at DESC',
            [$id]
        );

        return $this->view('hr/assessments/show', compact('assessment', 'questions', 'attempts') + ['title' => $assessment['title']]);
    }

    public function edit(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $assessment = $this->assessment((int) $id);
        return $this->view('hr/assessments/form', ['title' => 'Edit Assessment', 'assessment' => $assessment, 'requisition' => $this->requisition((int) $assessment['job_id'])]);
    }

    public function update(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $data = $this->validate($request->body(), [
            'title' => ['required', ['max', 180]],
            'description' => [],
            'type' => ['required', ['in', AssessmentType::values()]],
            'duration_minutes' => ['required', 'numeric'],
            'is_active' => [],
        ]);
        Database::update('assessments', [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'duration_minutes' => (int) $data['duration_minutes'],
            'is_active' => $this->checkboxValue($data, 'is_active'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'assessment_id = ?', [(int) $id]);
        Session::flash('status', 'Assessment updated. Existing attempts keep their original snapshots.');

        return $this->redirect(url('hr.assessments.show', [$id]));
    }

    public function deactivate(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        Database::update('assessments', ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')], 'assessment_id = ?', [(int) $id]);
        Session::flash('status', 'Assessment deactivated for new attempts.');

        return $this->redirect(url('hr.assessments.show', [$id]));
    }

    public function createQuestion(Request $request, string $assessmentId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        return $this->view('hr/assessment-questions/form', ['title' => 'Create Question', 'assessment' => $this->assessment((int) $assessmentId), 'question' => null]);
    }

    public function storeQuestion(Request $request, string $assessmentId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $data = $this->questionData($request);
        $now = date('Y-m-d H:i:s');
        Database::insert('questions', $data + ['assessment_id' => $assessmentId, 'created_at' => $now, 'updated_at' => $now]);
        Session::flash('status', 'Question created.');

        return $this->redirect(url('hr.assessments.show', [$assessmentId]));
    }

    public function editQuestion(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $question = $this->question((int) $id);
        return $this->view('hr/assessment-questions/form', ['title' => 'Edit Question', 'assessment' => $this->assessment((int) $question['assessment_id']), 'question' => $question]);
    }

    public function updateQuestion(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $question = $this->question((int) $id);
        Database::update('questions', $this->questionData($request) + ['updated_at' => date('Y-m-d H:i:s')], 'question_id = ?', [(int) $id]);
        Session::flash('status', 'Question updated. Existing attempts keep their original snapshots.');

        return $this->redirect(url('hr.assessments.show', [$question['assessment_id']]));
    }

    public function deactivateQuestion(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $question = $this->question((int) $id);
        Database::update('questions', ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')], 'question_id = ?', [(int) $id]);
        Session::flash('status', 'Question deactivated.');

        return $this->redirect(url('hr.assessments.show', [$question['assessment_id']]));
    }

    public function results(Request $request, string $jobId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $requisition = $this->requisition((int) $jobId);
        $attempts = Database::fetchAll(
            'SELECT ca.*, a.title AS assessment_title, u.name, u.email,
             (SELECT COUNT(*) FROM assessment_integrity_events e WHERE e.ca_id = ca.ca_id) AS event_count
             FROM candidate_assessments ca
             JOIN assessments a ON a.assessment_id = ca.assessment_id
             JOIN users u ON u.user_id = ca.candidate_id
             WHERE a.job_id = ? ORDER BY ca.updated_at DESC',
            [$jobId]
        );

        return $this->view('hr/assessments/results', compact('requisition', 'attempts') + ['title' => 'Assessment Results']);
    }

    public function reviewAttempt(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $attempt = $this->attempt((int) $id);
        $questions = $this->attemptQuestions((int) $id);
        $events = Database::fetchAll('SELECT * FROM assessment_integrity_events WHERE ca_id = ? ORDER BY occurred_at', [$id]);

        return $this->view('hr/assessments/attempt', compact('attempt', 'questions', 'events') + ['title' => 'Assessment Attempt']);
    }

    public function startCandidate(Request $request, string $applicationId, string $assessmentId): Response
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);
        $application = Database::fetch('SELECT * FROM applications WHERE application_id = ? AND candidate_id = ?', [$applicationId, $user['user_id']]);
        $assessment = Database::fetch('SELECT * FROM assessments WHERE assessment_id = ? AND is_active = 1', [$assessmentId]);

        if (! $application || ! $assessment || (int) $application['job_id'] !== (int) $assessment['job_id']) {
            throw new \App\Core\HttpException(403, 'Assessment is not available for this application.');
        }
        if ($application['status'] !== ApplicationStatus::ASSESSMENT->value) {
            throw new ValidationException(['assessment' => ['This application is not in the assessment stage.']]);
        }
        $existing = Database::fetch('SELECT * FROM candidate_assessments WHERE candidate_id = ? AND assessment_id = ?', [$user['user_id'], $assessmentId]);
        if ($existing) {
            return $this->redirect($existing['status'] === AssessmentAttemptStatus::IN_PROGRESS->value ? url('candidate.assessments.show', [$existing['ca_id']]) : url('candidate.assessments.result', [$existing['ca_id']]));
        }

        $attemptId = Database::transaction(function () use ($applicationId, $assessmentId, $assessment, $user): int {
            $now = date('Y-m-d H:i:s');
            $expires = date('Y-m-d H:i:s', time() + ((int) $assessment['duration_minutes'] * 60));
            $id = Database::insert('candidate_assessments', ['application_id' => $applicationId, 'candidate_id' => $user['user_id'], 'assessment_id' => $assessmentId, 'start_time' => $now, 'expires_at' => $expires, 'status' => AssessmentAttemptStatus::IN_PROGRESS->value, 'created_at' => $now, 'updated_at' => $now]);
            $questions = Database::fetchAll('SELECT * FROM questions WHERE assessment_id = ? AND is_active = 1 ORDER BY RAND()', [$assessmentId]);
            $order = 1;
            foreach ($questions as $question) {
                Database::insert('candidate_assessment_questions', ['ca_id' => $id, 'question_id' => $question['question_id'], 'display_order' => $order++, 'question_type' => $question['type'], 'question_text' => $question['question_text'], 'options' => $question['options'], 'correct_answer' => $question['correct_answer'], 'points' => $question['points'], 'created_at' => $now]);
            }
            return $id;
        });
        Session::flash('status', 'Assessment started. Your answers are saved when you submit each answer.');

        return $this->redirect(url('candidate.assessments.show', [$attemptId]));
    }

    public function showCandidate(Request $request, string $id): Response
    {
        $this->requireCandidateAttempt((int) $id);
        if ($this->expireIfNeeded((int) $id)) {
            return $this->redirect(url('candidate.assessments.result', [$id]));
        }
        $attempt = $this->attempt((int) $id);
        $questions = $this->attemptQuestions((int) $id);

        return $this->view('candidate/assessments/show', compact('attempt', 'questions') + ['title' => 'Technical Assessment']);
    }

    public function saveAnswer(Request $request, string $id, string $questionId): Response
    {
        $this->requireCandidateAttempt((int) $id);
        if ($this->expireIfNeeded((int) $id)) {
            throw new ValidationException(['assessment' => ['Assessment time expired. Late answer changes were not saved.']]);
        }
        $question = Database::fetch('SELECT * FROM candidate_assessment_questions WHERE ca_id = ? AND attempt_question_id = ?', [$id, $questionId]);
        if (! $question) {
            throw new \App\Core\HttpException(404, 'Question not found.');
        }
        $answer = (string) $request->input('answer_text', '');
        $existing = Database::fetch('SELECT submission_id FROM submissions WHERE ca_id = ? AND attempt_question_id = ?', [$id, $questionId]);
        $now = date('Y-m-d H:i:s');
        if ($existing) {
            Database::update('submissions', ['answer_text' => $answer, 'saved_at' => $now, 'updated_at' => $now], 'submission_id = ?', [$existing['submission_id']]);
        } else {
            Database::insert('submissions', ['ca_id' => $id, 'attempt_question_id' => $questionId, 'question_id' => $question['question_id'], 'answer_text' => $answer, 'saved_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        }
        Session::flash('status', 'Answer saved.');

        return $this->redirect(url('candidate.assessments.show', [$id]));
    }

    public function submitCandidate(Request $request, string $id): Response
    {
        $this->requireCandidateAttempt((int) $id);
        if ($this->expireIfNeeded((int) $id)) {
            return $this->redirect(url('candidate.assessments.result', [$id]));
        }
        $now = date('Y-m-d H:i:s');
        $score = (new SimulatedAssessmentScorer())->score((int) $id);
        Database::query('UPDATE submissions SET finalized_at = ?, updated_at = ? WHERE ca_id = ?', [$now, $now, $id]);
        Database::update('candidate_assessments', ['status' => AssessmentAttemptStatus::SUBMITTED->value, 'score' => $score, 'end_time' => $now, 'updated_at' => $now], 'ca_id = ?', [(int) $id]);
        Session::flash('status', 'Assessment submitted. Your score is simulated and advisory.');

        return $this->redirect(url('candidate.assessments.result', [$id]));
    }

    public function focusEvent(Request $request, string $id): Response
    {
        $this->requireCandidateAttempt((int) $id);
        $data = $this->validate($request->body(), ['event_type' => ['required', ['in', ['FOCUS_LOST', 'FOCUS_RETURNED']]], 'visible_state' => []]);
        $now = date('Y-m-d H:i:s');
        Database::insert('assessment_integrity_events', ['ca_id' => $id, 'event_type' => $data['event_type'], 'occurred_at' => $now, 'metadata' => json_encode(['visible_state' => $data['visible_state'] ?? null]), 'created_at' => $now]);
        Session::flash('status', 'Simulated proctoring event recorded.');

        return $this->redirect(url('candidate.assessments.show', [$id]));
    }

    public function resultCandidate(Request $request, string $id): Response
    {
        $this->requireCandidateAttempt((int) $id);
        $this->expireIfNeeded((int) $id);
        $attempt = $this->attempt((int) $id);
        $questions = $this->attemptQuestions((int) $id);
        $events = Database::fetchAll('SELECT * FROM assessment_integrity_events WHERE ca_id = ? ORDER BY occurred_at', [$id]);

        return $this->view('candidate/assessments/result', compact('attempt', 'questions', 'events') + ['title' => 'Assessment Result']);
    }

    private function questionData(Request $request): array
    {
        $data = $this->validate($request->body(), ['type' => ['required', ['in', AssessmentQuestionType::values()]], 'difficulty_level' => ['required', ['in', ['EASY', 'MEDIUM', 'HARD']]], 'question_text' => ['required'], 'options' => [], 'correct_answer' => [], 'points' => ['required', 'numeric'], 'is_active' => []]);
        return ['type' => $data['type'], 'difficulty_level' => $data['difficulty_level'], 'question_text' => $data['question_text'], 'options' => ($data['options'] ?? '') ?: null, 'correct_answer' => ($data['correct_answer'] ?? '') ?: null, 'points' => (float) $data['points'], 'is_active' => $this->checkboxValue($data, 'is_active')];
    }

    private function checkboxValue(array $data, string $key): int
    {
        return isset($data[$key]) && in_array(strtolower((string) $data[$key]), ['1', 'on', 'yes', 'true'], true) ? 1 : 0;
    }

    private function expireIfNeeded(int $id): bool
    {
        $attempt = Database::fetch('SELECT * FROM candidate_assessments WHERE ca_id = ?', [$id]);
        if (! $attempt || $attempt['status'] !== AssessmentAttemptStatus::IN_PROGRESS->value || ! $attempt['expires_at'] || strtotime($attempt['expires_at']) > time()) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $score = (new SimulatedAssessmentScorer())->score($id, $attempt['expires_at']);
        Database::query('UPDATE submissions SET finalized_at = ?, updated_at = ? WHERE ca_id = ? AND saved_at <= ?', [$now, $now, $id, $attempt['expires_at']]);
        Database::update('candidate_assessments', ['status' => AssessmentAttemptStatus::EXPIRED->value, 'score' => $score, 'end_time' => $attempt['expires_at'], 'updated_at' => $now], 'ca_id = ?', [$id]);

        return true;
    }

    private function requireCandidateAttempt(int $id): array
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);
        $attempt = Database::fetch('SELECT * FROM candidate_assessments WHERE ca_id = ? AND candidate_id = ?', [$id, $user['user_id']]);
        if (! $attempt) {
            throw new \App\Core\HttpException(403, 'Assessment attempt is not available.');
        }
        return $attempt;
    }

    private function attemptQuestions(int $id): array
    {
        return Database::fetchAll('SELECT aq.*, s.answer_text, s.is_correct, s.awarded_points FROM candidate_assessment_questions aq LEFT JOIN submissions s ON s.attempt_question_id = aq.attempt_question_id AND s.ca_id = aq.ca_id WHERE aq.ca_id = ? ORDER BY aq.display_order', [$id]);
    }

    private function requisition(int $id): array
    {
        $row = Database::fetch('SELECT r.*, d.name AS department_name FROM job_requisitions r JOIN departments d ON d.department_id = r.department_id WHERE r.job_id = ?', [$id]);
        if (! $row) {
            throw new \App\Core\HttpException(404, 'Requisition not found.');
        }
        return $row;
    }

    private function assessment(int $id): array
    {
        $row = Database::fetch('SELECT a.*, r.title AS job_title FROM assessments a JOIN job_requisitions r ON r.job_id = a.job_id WHERE a.assessment_id = ?', [$id]);
        if (! $row) {
            throw new \App\Core\HttpException(404, 'Assessment not found.');
        }
        return $row;
    }

    private function question(int $id): array
    {
        $row = Database::fetch('SELECT * FROM questions WHERE question_id = ?', [$id]);
        if (! $row) {
            throw new \App\Core\HttpException(404, 'Question not found.');
        }
        return $row;
    }

    private function attempt(int $id): array
    {
        $row = Database::fetch('SELECT ca.*, a.title AS assessment_title, a.duration_minutes, r.title AS job_title, u.name, u.email FROM candidate_assessments ca JOIN assessments a ON a.assessment_id = ca.assessment_id JOIN job_requisitions r ON r.job_id = a.job_id JOIN users u ON u.user_id = ca.candidate_id WHERE ca.ca_id = ?', [$id]);
        if (! $row) {
            throw new \App\Core\HttpException(404, 'Attempt not found.');
        }
        return $row;
    }
}
