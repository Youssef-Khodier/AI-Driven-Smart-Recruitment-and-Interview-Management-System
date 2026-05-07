<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\HttpException;
use App\Core\Session;
use App\Core\Database;
use App\Models\OnboardingModel;

/**
 * Candidate-facing onboarding welcome portal.
 * Candidates can view their onboarding tasks and mark documents as completed.
 */
final class CandidateOnboardingController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new HttpException(403, 'Unauthorized.');
        }

        $candidateId = (int)$actor['user_id'];

        // Find onboarding records for this candidate via accepted offers
        $onboardings = Database::fetchAll(
            'SELECT ob.*, o.offer_type, o.ctc, j.title AS job_title, d.name AS department_name
             FROM onboarding ob
             JOIN offers o ON ob.offer_id = o.offer_id
             JOIN applications a ON o.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN departments d ON j.department_id = d.department_id
             WHERE a.candidate_id = ?
             ORDER BY ob.created_at DESC',
            [$candidateId]
        );

        return $this->view('candidate/onboarding/index', [
            'title' => 'My Onboarding',
            'onboardings' => $onboardings,
        ]);
    }

    public function show(Request $request, int $onboardingId): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new HttpException(403, 'Unauthorized.');
        }

        $candidateId = (int)$actor['user_id'];

        $onboarding = Database::fetch(
            'SELECT ob.*, o.offer_type, o.ctc, o.bonus, o.stock_options,
                    j.title AS job_title, d.name AS department_name, j.location
             FROM onboarding ob
             JOIN offers o ON ob.offer_id = o.offer_id
             JOIN applications a ON o.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN departments d ON j.department_id = d.department_id
             WHERE ob.onboarding_id = ? AND a.candidate_id = ?',
            [$onboardingId, $candidateId]
        );

        if (!$onboarding) {
            throw new HttpException(403, 'Onboarding record not found or unauthorized.');
        }

        // Define the welcome task checklist
        $tasks = $this->getOnboardingTasks($onboarding);

        return $this->view('candidate/onboarding/welcome', [
            'title' => 'Welcome — ' . $onboarding['job_title'],
            'onboarding' => $onboarding,
            'tasks' => $tasks,
        ]);
    }

    public function completeTask(Request $request, int $onboardingId): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new HttpException(403, 'Unauthorized.');
        }

        $candidateId = (int)$actor['user_id'];

        $onboarding = Database::fetch(
            'SELECT ob.*, o.application_id, o.offer_id
             FROM onboarding ob
             JOIN offers o ON ob.offer_id = o.offer_id
             JOIN applications a ON o.application_id = a.application_id
             WHERE ob.onboarding_id = ? AND a.candidate_id = ?',
            [$onboardingId, $candidateId]
        );

        if (!$onboarding) {
            throw new HttpException(403, 'Unauthorized.');
        }

        $taskKey = trim($request->input('task_key') ?? '');
        if (!$taskKey) {
            return $this->redirect(url('candidate.onboarding.show', [$onboardingId]))->with('error', 'Invalid task.');
        }

        $allTasks = array_column($this->getAllTaskDefinitions(), 'key');
        if (!in_array($taskKey, $allTasks, true)) {
            return $this->redirect(url('candidate.onboarding.show', [$onboardingId]))->with('error', 'Invalid task.');
        }

        OnboardingModel::completeTask($onboarding, $taskKey, $candidateId);
        $completed = OnboardingModel::completedTaskKeys($onboardingId);

        // If all tasks are done, update the DB boolean.
        if (count(array_intersect($allTasks, $completed)) >= count($allTasks)) {
            Database::update('onboarding', [
                'documents_completed' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'onboarding_id = ?', [$onboardingId]);
        }

        Session::flash('status', 'Task marked as complete!');
        return $this->redirect(url('candidate.onboarding.show', [$onboardingId]));
    }

    /**
     * Build the onboarding task checklist for the candidate.
     */
    private function getOnboardingTasks(array $onboarding): array
    {
        $onboardingId = (int)$onboarding['onboarding_id'];

        // If documents_completed is true in DB, mark all as done.
        if (!empty($onboarding['documents_completed'])) {
            $allTasks = $this->getAllTaskDefinitions();
            return array_map(fn($t) => array_merge($t, ['completed' => true]), $allTasks);
        }

        $completed = OnboardingModel::completedTaskKeys($onboardingId);

        return array_map(function ($task) use ($completed) {
            $task['completed'] = in_array($task['key'], $completed);
            return $task;
        }, $this->getAllTaskDefinitions());
    }

    private function getAllTaskDefinitions(): array
    {
        return [
            ['key' => 'personal_info', 'label' => 'Verify Personal Information', 'description' => 'Confirm your name, address, and contact details are correct.'],
            ['key' => 'tax_forms', 'label' => 'Submit Tax Forms', 'description' => 'Complete and submit required tax documentation.'],
            ['key' => 'id_verification', 'label' => 'Identity Verification', 'description' => 'Upload a valid government-issued ID for verification.'],
            ['key' => 'emergency_contact', 'label' => 'Emergency Contact Info', 'description' => 'Provide emergency contact details.'],
            ['key' => 'bank_details', 'label' => 'Banking Information', 'description' => 'Submit direct deposit/banking information for payroll.'],
            ['key' => 'nda_agreement', 'label' => 'Sign NDA / Confidentiality Agreement', 'description' => 'Review and acknowledge the non-disclosure agreement.'],
            ['key' => 'company_handbook', 'label' => 'Review Company Handbook', 'description' => 'Read the employee handbook and acknowledge receipt.'],
            ['key' => 'it_setup', 'label' => 'IT Equipment Request', 'description' => 'Submit your hardware/software preferences for day-one setup.'],
        ];
    }
}
