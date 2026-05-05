<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Enums\UserRole;
use App\Policies\AuditLogPolicy;
use App\Repositories\AuditLogRepository;

final class HrAuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        if (! (new AuditLogPolicy())->view($user)) {
            throw new HttpException(403, 'You are not authorized to view audit logs.');
        }

        $filters = [
            'from' => $this->dateInput($request->input('from')),
            'to' => $this->dateInput($request->input('to')),
            'actor' => trim((string) $request->input('actor', '')),
            'action' => trim((string) $request->input('action', '')),
            'entity' => in_array($request->input('entity'), AuditLogRepository::entities(), true) ? $request->input('entity') : '',
        ];
        $page = max(1, (int) $request->input('page', 1));
        $result = AuditLogRepository::search($filters, $page, 25);

        return $this->view('hr/audit-log/index', $result + [
            'title' => 'Audit Log',
            'filters' => $filters,
            'entities' => AuditLogRepository::entities(),
        ]);
    }

    private function dateInput(mixed $value): string
    {
        $value = trim((string) $value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
    }
}
