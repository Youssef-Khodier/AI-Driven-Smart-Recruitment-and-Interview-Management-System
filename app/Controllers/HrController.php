<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Policies\ApplicationPolicy;
use App\Policies\JobRequisitionPolicy;
use App\Repositories\NotificationRepository;

final class HrController extends Controller
{
    public function users(Request $request): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $users = Database::fetchAll(
            'SELECT u.*, d.name AS department_name FROM users u LEFT JOIN departments d ON d.department_id = u.department_id ORDER BY u.created_at DESC'
        );

        return $this->view('hr/users/index', ['title' => 'Users', 'users' => $users]);
    }

    public function createUser(Request $request): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        return $this->view('hr/users/create', ['title' => 'Create User', 'departments' => $this->departments()]);
    }

    public function storeUser(Request $request): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $data = $this->validate($request->body(), [
            'name' => ['required', ['max', 160]],
            'email' => ['required', 'email', ['max', 180]],
            'password' => ['required', ['min', 8]],
            'role' => ['required', ['in', [UserRole::HR_ADMIN->value, UserRole::INTERVIEWER->value, UserRole::JUNIOR_STAFF->value]]],
            'department_id' => [],
        ]);

        if (Database::fetch('SELECT user_id FROM users WHERE email = ?', [$data['email']])) {
            throw new ValidationException(['email' => ['That email address is already registered.']]);
        }

        $now = date('Y-m-d H:i:s');
        $id = Database::insert('users', [
            'department_id' => $data['department_id'] !== '' ? $data['department_id'] : null,
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'status' => AccountStatus::ACTIVE->value,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->audit((int) $actor['user_id'], $id, 'CREATED', null, ['role' => $data['role'], 'status' => AccountStatus::ACTIVE->value]);
        Session::flash('status', 'User created.');

        return $this->redirect(url('hr.users.index'));
    }

    public function editAccess(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $user = $this->findUser((int) $id);

        return $this->view('hr/users/access', ['title' => 'Update Access', 'target' => $user]);
    }

    public function updateAccess(Request $request, string $id): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $target = $this->findUser((int) $id);
        $data = $this->validate($request->body(), [
            'role' => ['required', ['in', UserRole::values()]],
            'status' => ['required', ['in', AccountStatus::values()]],
        ]);

        if ($target['role'] === UserRole::HR_ADMIN->value && ($data['role'] !== UserRole::HR_ADMIN->value || $data['status'] !== AccountStatus::ACTIVE->value)) {
            $activeHrCount = Database::fetch('SELECT COUNT(*) AS count FROM users WHERE role = ? AND status = ?', [UserRole::HR_ADMIN->value, AccountStatus::ACTIVE->value]);
            if ((int) $activeHrCount['count'] <= 1) {
                throw new ValidationException(['status' => ['At least one active HR admin must remain.']]);
            }
        }

        Database::update('users', [
            'role' => $data['role'],
            'status' => $data['status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'user_id = ?', [(int) $id]);

        $this->audit((int) $actor['user_id'], (int) $id, 'ACCESS_UPDATED', [
            'role' => $target['role'],
            'status' => $target['status'],
        ], $data);

        Session::flash('status', 'User access updated.');

        return $this->redirect(url('hr.users.index'));
    }

    public function requisitions(Request $request): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $status = $request->input('status');
        $params = [];
        $where = '';
        if ($status) {
            $where = 'WHERE r.status = ?';
            $params[] = $status;
        }

        $requisitions = Database::fetchAll(
            "SELECT r.*, d.name AS department_name, u.name AS creator_name
             FROM job_requisitions r
             JOIN departments d ON d.department_id = r.department_id
             JOIN users u ON u.user_id = r.created_by
             $where ORDER BY r.created_at DESC",
            $params
        );

        return $this->view('hr/requisitions/index', ['title' => 'Requisitions', 'requisitions' => $requisitions, 'status' => $status]);
    }

    public function createRequisition(Request $request): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        return $this->view('hr/requisitions/form', ['title' => 'Create Requisition', 'departments' => $this->departments(), 'requisition' => null]);
    }

    public function storeRequisition(Request $request): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $data = $this->validate($request->body(), [
            'department_id' => ['required'],
            'title' => ['required', ['max', 180]],
            'location' => [['max', 160]],
            'description' => ['required'],
            'requirements' => ['required'],
        ]);
        $now = date('Y-m-d H:i:s');
        $id = Database::insert('job_requisitions', [
            'department_id' => $data['department_id'],
            'title' => $data['title'],
            'location' => $data['location'] ?? null,
            'description' => $data['description'],
            'requirements' => $data['requirements'],
            'status' => JobRequisitionStatus::DRAFT->value,
            'created_by' => $actor['user_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->recordJobStatus($id, (int) $actor['user_id'], null, JobRequisitionStatus::DRAFT->value, 'Created requisition.');
        Session::flash('status', 'Requisition created.');

        return $this->redirect(url('hr.requisitions.show', [$id]));
    }

    public function showRequisition(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $requisition = $this->findRequisition((int) $id);
        $assessments = Database::fetchAll('SELECT * FROM assessments WHERE job_id = ? ORDER BY created_at DESC', [$id]);
        $history = Database::fetchAll('SELECT h.*, u.name AS actor_name FROM job_requisition_status_histories h JOIN users u ON u.user_id = h.actor_user_id WHERE h.job_id = ? ORDER BY h.created_at DESC', [$id]);
        $repo = new \App\Repositories\GovernanceRepository();
        $approvalHistory = $repo->getApprovalHistory((int) $id);
        $versionCount = count($repo->getVersionHistory((int) $id));

        return $this->view('hr/requisitions/show', compact('requisition', 'assessments', 'history', 'approvalHistory', 'versionCount') + ['title' => $requisition['title']]);
    }

    public function editRequisition(Request $request, string $id): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        return $this->view('hr/requisitions/form', ['title' => 'Edit Requisition', 'departments' => $this->departments(), 'requisition' => $this->findRequisition((int) $id)]);
    }

    public function updateRequisition(Request $request, string $id): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $requisition = $this->findRequisition((int) $id);
        if (! (new JobRequisitionPolicy())->update($actor, $requisition)) {
            throw new \App\Core\HttpException(403, 'This requisition cannot be edited in its current status.');
        }

        $data = $this->validate($request->body(), [
            'department_id' => ['required'],
            'title' => ['required', ['max', 180]],
            'location' => [['max', 160]],
            'description' => ['required'],
            'requirements' => ['required'],
        ]);
        $data['updated_at'] = date('Y-m-d H:i:s');

        $flashMessage = 'Requisition updated.';
        if ($requisition['status'] === JobRequisitionStatus::APPROVED->value) {
            if ($requisition['description'] !== $data['description'] || $requisition['requirements'] !== $data['requirements']) {
                $repo = new \App\Repositories\GovernanceRepository();
                $repo->createTemplateVersion((int)$id, $data['description'], $data['requirements'], (int)$actor['user_id']);
                
                $data['status'] = JobRequisitionStatus::DRAFT->value;
                $flashMessage = 'Requisition updated. Status reset to Draft because description or requirements changed.';
            }
        }

        $data['location'] = $data['location'] ?? null;
        Database::update('job_requisitions', $data, 'job_id = ?', [(int) $id]);
        Session::flash('status', $flashMessage);

        return $this->redirect(url('hr.requisitions.show', [$id]));
    }

    public function transitionRequisition(Request $request, string $id, string $status): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $requisition = $this->findRequisition((int) $id);
        if (! (new JobRequisitionPolicy())->transition($actor, $requisition, $status)) {
            throw new \App\Core\HttpException(403, 'This requisition status transition is not allowed.');
        }

        $now = date('Y-m-d H:i:s');
        $updates = ['status' => $status, 'updated_at' => $now];
        if ($status === JobRequisitionStatus::OPEN->value) {
            $updates['opened_at'] = $now;
        }
        if ($status === JobRequisitionStatus::CLOSED->value) {
            $updates['closed_at'] = $now;
        }
        Database::update('job_requisitions', $updates, 'job_id = ?', [(int) $id]);
        $this->recordJobStatus((int) $id, (int) $actor['user_id'], $requisition['status'], $status, 'Status changed by HR.');
        
        $repo = new \App\Repositories\GovernanceRepository();
        if ($status === JobRequisitionStatus::PENDING->value) {
            $repo->createTemplateVersion((int)$id, $requisition['description'], $requisition['requirements'], (int)$actor['user_id']);
            
            $action = $requisition['status'] === JobRequisitionStatus::REJECTED->value ? \App\Enums\GovernanceAuditAction::REQUISITION_RESUBMITTED->value : \App\Enums\GovernanceAuditAction::REQUISITION_SUBMITTED->value;
            $repo->recordGovernanceAudit((int) $id, (int) $actor['user_id'], $action, ['status' => $requisition['status']], ['status' => $status]);
        } elseif ($status === JobRequisitionStatus::OPEN->value) {
            $repo->recordGovernanceAudit((int) $id, (int) $actor['user_id'], \App\Enums\GovernanceAuditAction::REQUISITION_OPENED->value, ['status' => $requisition['status']], ['status' => $status]);
        } elseif ($status === JobRequisitionStatus::CLOSED->value) {
            $repo->recordGovernanceAudit((int) $id, (int) $actor['user_id'], \App\Enums\GovernanceAuditAction::REQUISITION_CLOSED->value, ['status' => $requisition['status']], ['status' => $status]);
        }
        
        Session::flash('status', 'Requisition status updated.');

        return $this->redirect(url('hr.requisitions.show', [$id]));
    }

    public function applications(Request $request, string $jobId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);
        $requisition = $this->findRequisition((int) $jobId);
        $applications = Database::fetchAll(
            'SELECT a.*, u.name, u.email, c.current_title, c.years_experience
             FROM applications a JOIN candidates c ON c.candidate_id = a.candidate_id JOIN users u ON u.user_id = c.candidate_id
             WHERE a.job_id = ? ORDER BY a.applied_at DESC',
            [$jobId]
        );
        return $this->view('hr/applications/index', compact('requisition', 'applications') + ['title' => 'Applications']);
    }

    public function updateApplication(Request $request, string $id): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $application = $this->findApplication((int) $id);
        $data = $this->validate($request->body(), ['status' => ['required', ['in', ApplicationStatus::values()]], 'reason' => []]);
        if (! (new ApplicationPolicy())->transition($actor, $application, $data['status'])) {
            throw new \App\Core\HttpException(403, 'This application status transition is not allowed.');
        }

        Database::update('applications', ['status' => $data['status'], 'updated_at' => date('Y-m-d H:i:s')], 'application_id = ?', [(int) $id]);
        Database::insert('application_status_histories', ['application_id' => $id, 'actor_user_id' => $actor['user_id'], 'old_status' => $application['status'], 'new_status' => $data['status'], 'reason' => $data['reason'] ?? null, 'created_at' => date('Y-m-d H:i:s')]);
        NotificationRepository::createApplicationStatusNotification((int) $id, $data['status']);
        Session::flash('status', 'Application status updated.');

        return $this->redirect(url('hr.applications.index', [$application['job_id']]));
    }

    private function departments(): array
    {
        return Database::fetchAll('SELECT * FROM departments ORDER BY name');
    }

    private function findUser(int $id): array
    {
        $user = Database::fetch('SELECT * FROM users WHERE user_id = ?', [$id]);
        if (! $user) {
            throw new \App\Core\HttpException(404, 'User not found.');
        }
        return $user;
    }

    private function findRequisition(int $id): array
    {
        $row = Database::fetch('SELECT r.*, d.name AS department_name FROM job_requisitions r JOIN departments d ON d.department_id = r.department_id WHERE r.job_id = ?', [$id]);
        if (! $row) {
            throw new \App\Core\HttpException(404, 'Requisition not found.');
        }
        return $row;
    }

    private function findApplication(int $id): array
    {
        $row = Database::fetch('SELECT * FROM applications WHERE application_id = ?', [$id]);
        if (! $row) {
            throw new \App\Core\HttpException(404, 'Application not found.');
        }
        return $row;
    }

    private function recordJobStatus(int $jobId, int $actorId, ?string $old, string $new, ?string $reason): void
    {
        Database::insert('job_requisition_status_histories', ['job_id' => $jobId, 'actor_user_id' => $actorId, 'old_status' => $old, 'new_status' => $new, 'reason' => $reason, 'created_at' => date('Y-m-d H:i:s')]);
    }

    private function audit(int $actorId, int $targetId, string $action, ?array $old, array $new): void
    {
        Database::insert('account_audit_records', ['actor_user_id' => $actorId, 'target_user_id' => $targetId, 'action' => $action, 'old_values' => $old ? json_encode($old) : null, 'new_values' => json_encode($new), 'created_at' => date('Y-m-d H:i:s')]);
    }
}
