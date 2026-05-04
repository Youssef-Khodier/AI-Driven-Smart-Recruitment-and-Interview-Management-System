<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Enums\ApplicationStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Services\SimulatedMatchScorer;

final class CandidateController extends Controller
{
    public function profile(Request $request): Response
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);
        $candidate = $this->candidate((int) $user['user_id']);

        return $this->view('candidate/profile', compact('user', 'candidate') + ['title' => 'My Profile']);
    }

    public function updateProfile(Request $request): Response
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);
        $data = $this->validate($request->body(), [
            'phone' => ['required', ['max', 40]],
            'current_title' => [['max', 160]],
            'years_experience' => ['numeric'],
            'location' => [['max', 160]],
            'resume_url' => [['max', 2048]],
            'skill_keywords' => [['max', 2000]],
        ]);
        $data['updated_at'] = date('Y-m-d H:i:s');
        Database::update('candidates', $data, 'candidate_id = ?', [(int) $user['user_id']]);
        Session::flash('status', 'Profile updated.');

        return $this->redirect(url('candidate.profile'));
    }

    public function jobs(Request $request): Response
    {
        $this->requireRole(UserRole::CANDIDATE->value);
        $jobs = Database::fetchAll('SELECT r.*, d.name AS department_name FROM job_requisitions r JOIN departments d ON d.department_id = r.department_id WHERE r.status = ? ORDER BY r.opened_at DESC, r.created_at DESC', [JobRequisitionStatus::OPEN->value]);

        return $this->view('candidate/jobs/index', ['title' => 'Open Jobs', 'jobs' => $jobs]);
    }

    public function job(Request $request, string $id): Response
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);
        $job = Database::fetch('SELECT r.*, d.name AS department_name FROM job_requisitions r JOIN departments d ON d.department_id = r.department_id WHERE r.job_id = ? AND r.status = ?', [$id, JobRequisitionStatus::OPEN->value]);
        if (! $job) {
            throw new \App\Core\HttpException(404, 'Open job not found.');
        }
        $application = Database::fetch('SELECT * FROM applications WHERE candidate_id = ? AND job_id = ?', [$user['user_id'], $id]);

        return $this->view('candidate/jobs/show', compact('job', 'application') + ['title' => $job['title']]);
    }

    public function apply(Request $request, string $jobId): Response
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);
        $job = Database::fetch('SELECT * FROM job_requisitions WHERE job_id = ? AND status = ?', [$jobId, JobRequisitionStatus::OPEN->value]);
        if (! $job) {
            throw new \App\Core\HttpException(404, 'Open job not found.');
        }
        if (Database::fetch('SELECT application_id FROM applications WHERE candidate_id = ? AND job_id = ?', [$user['user_id'], $jobId])) {
            throw new ValidationException(['application' => ['You have already applied for this job.']]);
        }
        $candidate = $this->candidate((int) $user['user_id']);
        $score = (new SimulatedMatchScorer())->score($job, $candidate);
        $now = date('Y-m-d H:i:s');
        $applicationId = Database::insert('applications', ['candidate_id' => $user['user_id'], 'job_id' => $jobId, 'status' => ApplicationStatus::APPLIED->value, 'match_score' => $score, 'applied_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        Database::insert('application_status_histories', ['application_id' => $applicationId, 'actor_user_id' => $user['user_id'], 'old_status' => null, 'new_status' => ApplicationStatus::APPLIED->value, 'reason' => 'Candidate submitted application.', 'created_at' => $now]);
        Session::flash('status', 'Application submitted. Your match score is simulated.');

        return $this->redirect(url('candidate.applications.show', [$applicationId]));
    }

    public function applications(Request $request): Response
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);
        $applications = Database::fetchAll('SELECT a.*, r.title, r.status AS job_status FROM applications a JOIN job_requisitions r ON r.job_id = a.job_id WHERE a.candidate_id = ? ORDER BY a.applied_at DESC', [$user['user_id']]);

        return $this->view('candidate/applications/index', compact('applications') + ['title' => 'My Applications']);
    }

    public function application(Request $request, string $id): Response
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);
        $application = Database::fetch('SELECT a.*, r.title, r.description, r.requirements FROM applications a JOIN job_requisitions r ON r.job_id = a.job_id WHERE a.application_id = ? AND a.candidate_id = ?', [$id, $user['user_id']]);
        if (! $application) {
            throw new \App\Core\HttpException(404, 'Application not found.');
        }
        $assessments = Database::fetchAll('SELECT * FROM assessments WHERE job_id = ? AND is_active = 1 ORDER BY created_at DESC', [$application['job_id']]);
        $attempts = Database::fetchAll('SELECT * FROM candidate_assessments WHERE application_id = ? AND candidate_id = ?', [$id, $user['user_id']]);
        $attemptMap = [];
        foreach ($attempts as $attempt) {
            $attemptMap[(int) $attempt['assessment_id']] = $attempt;
        }

        return $this->view('candidate/applications/show', compact('application', 'assessments', 'attemptMap') + ['title' => 'Application']);
    }

    private function candidate(int $id): ?array
    {
        return Database::fetch('SELECT * FROM candidates WHERE candidate_id = ?', [$id]);
    }
}
