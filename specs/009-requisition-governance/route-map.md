# Route Map: Advanced Job Requisition Governance

**Feature**: 009-requisition-governance  
**Date**: 2026-05-06

## Route Summary

All routes use `routes/web.php`. No REST API or separated frontend routes.

### Approval Workflow Routes

| Method | URI | Controller | Action | Name | Auth |
|--------|-----|------------|--------|------|------|
| GET | `/hr/approvals` | `HrGovernanceController` | `approvalQueue` | `hr.approvals.index` | HR_ADMIN + is_department_head |
| POST | `/hr/requisitions/{id}/approve` | `HrGovernanceController` | `approveRequisition` | `hr.requisitions.approve` | HR_ADMIN + dept head for requisition's dept |
| POST | `/hr/requisitions/{id}/reject` | `HrGovernanceController` | `rejectRequisition` | `hr.requisitions.reject` | HR_ADMIN + dept head for requisition's dept |

**Note**: The existing submit route (`hr.requisitions.submit`) in `HrController` will be modified to also create a template version and governance audit record. The existing approve route (line 74 of `web.php`) will be **replaced** by the new governance-aware approve action.

### Template Versioning Routes

| Method | URI | Controller | Action | Name | Auth |
|--------|-----|------------|--------|------|------|
| GET | `/hr/requisitions/{id}/versions` | `HrGovernanceController` | `versionHistory` | `hr.requisitions.versions.index` | HR_ADMIN |
| GET | `/hr/requisitions/{id}/versions/{versionId}` | `HrGovernanceController` | `showVersion` | `hr.requisitions.versions.show` | HR_ADMIN |
| GET | `/hr/requisitions/{id}/versions/compare` | `HrGovernanceController` | `compareVersions` | `hr.requisitions.versions.compare` | HR_ADMIN (query params: v1, v2) |

### Simulated Job-Board Publishing Routes

| Method | URI | Controller | Action | Name | Auth |
|--------|-----|------------|--------|------|------|
| GET | `/hr/requisitions/{id}/publish` | `HrGovernanceController` | `publishForm` | `hr.requisitions.publish.form` | HR_ADMIN |
| POST | `/hr/requisitions/{id}/publish` | `HrGovernanceController` | `publishRequisition` | `hr.requisitions.publish.store` | HR_ADMIN |
| POST | `/hr/requisitions/{id}/unpublish` | `HrGovernanceController` | `unpublishRequisition` | `hr.requisitions.unpublish` | HR_ADMIN |
| GET | `/hr/requisitions/{id}/sync-history` | `HrGovernanceController` | `syncHistory` | `hr.requisitions.sync.index` | HR_ADMIN |

### Governance Audit Log Routes

| Method | URI | Controller | Action | Name | Auth |
|--------|-----|------------|--------|------|------|
| GET | `/hr/requisitions/{id}/governance-audit` | `HrGovernanceController` | `governanceAudit` | `hr.requisitions.governance-audit` | HR_ADMIN |

**Note**: The unified audit log at `/hr/audit-log` (existing `HrAuditLogController`) will also surface governance events via the UNION ALL integration in `AuditLogRepository`.

### Department Head Management Routes

| Method | URI | Controller | Action | Name | Auth |
|--------|-----|------------|--------|------|------|
| GET | `/hr/department-heads` | `HrGovernanceController` | `departmentHeads` | `hr.department-heads.index` | HR_ADMIN |
| POST | `/hr/users/{id}/department-head` | `HrGovernanceController` | `assignDepartmentHead` | `hr.department-heads.assign` | HR_ADMIN |
| DELETE | `/hr/users/{id}/department-head` | `HrGovernanceController` | `removeDepartmentHead` | `hr.department-heads.remove` | HR_ADMIN |

## Controllers

### New: `HrGovernanceController`

**Location**: `app/Controllers/HrGovernanceController.php`  
**Extends**: `App\Core\Controller`

**Responsibilities**:
- Approval queue (department-head scoped)
- Approve/reject actions with validation
- Template version history and comparison
- Simulated publish/unpublish
- Sync history display
- Per-requisition governance audit log
- Department head assignment management

### Modified: `HrController`

**Changes**:
- `transitionRequisition()`: Modify the PENDING transition to create a template version and governance audit record.
- Remove the APPROVED transition logic (moved to `HrGovernanceController::approveRequisition`).
- Add re-approval logic: editing an APPROVED requisition resets to DRAFT with a new template version.

## Views

### New Views

```text
views/hr/governance/
├── approval-queue.php          # Department head's pending approvals list
├── approve-form.php            # Approve/reject decision form with comments
├── version-history.php         # List of template versions for a requisition
├── version-show.php            # Single version detail view
├── version-compare.php         # Inline diff between two versions
├── publish-form.php            # Platform selection for simulated publish
├── sync-history.php            # Sync records list for a requisition
├── governance-audit.php        # Per-requisition governance audit log
└── department-heads.php        # Department head assignment management
```

### Modified Views

- `views/hr/requisitions/show.php`: Add links to version history, publish, sync history, and governance audit. Show approval status with department head info.
- `views/hr/requisitions/index.php`: Add visual indicators for REJECTED status. Add link to approval queue for department heads.
- `views/hr/dashboard.php`: Add "Pending Approvals" widget for department heads.

## Policies

### New: `GovernancePolicy`

**Location**: `app/Policies/GovernancePolicy.php`

```php
class GovernancePolicy
{
    // Can the user view/act on the approval queue?
    public function viewApprovalQueue(array $user): bool;

    // Can the user approve/reject this specific requisition?
    public function approveRequisition(array $user, array $requisition): bool;

    // Can the user publish/unpublish requisitions?
    public function publishRequisition(array $user, array $requisition): bool;

    // Can the user view governance audit/versions?
    public function viewGovernance(array $user): bool;

    // Can the user manage department head assignments?
    public function manageDepartmentHeads(array $user): bool;
}
```

### Modified: `JobRequisitionPolicy`

- Add `REJECTED` to the list of editable statuses in `update()`.
- Update `transition()` to handle REJECTED → PENDING resubmission.
- Remove PENDING → APPROVED from this policy (moved to `GovernancePolicy::approveRequisition`).

## Repositories

### New: `GovernanceRepository`

**Location**: `app/Repositories/GovernanceRepository.php`

**Methods**:
- `getPendingApprovals(int $departmentId): array` — requisitions with PENDING status for a department
- `recordApprovalStep(int $jobId, int $approverId, string $decision, ?string $comments): void`
- `getApprovalHistory(int $jobId): array`
- `createTemplateVersion(int $jobId, string $description, string $requirements, int $userId): int`
- `getVersionHistory(int $jobId): array`
- `getVersion(int $jobId, int $versionId): ?array`
- `getLatestVersionNumber(int $jobId): int`
- `getActivePlatforms(): array`
- `createSyncRecord(int $jobId, int $platformId, string $payload, string $status, int $userId): int`
- `getSyncHistory(int $jobId): array`
- `hasPublishedSync(int $jobId, int $platformId): bool`
- `getPublishedPlatforms(int $jobId): array`
- `recordGovernanceAudit(int $jobId, int $actorId, string $action, ?array $oldValues, ?array $newValues, ?string $comments): void`
- `getGovernanceAuditLog(int $jobId, array $filters): array`
- `getDepartmentHeads(): array`
- `setDepartmentHead(int $userId, bool $isHead): void`

### Modified: `AuditLogRepository`

- Add `REQUISITION_GOVERNANCE` to `entities()` array.
- Add new UNION ALL leg in `baseUnionSql()` for `requisition_governance_audit`.

## Enums

### Modified: `JobRequisitionStatus`

Add `case REJECTED = 'REJECTED';`

### New: `GovernanceAuditAction`

```php
enum GovernanceAuditAction: string
{
    case REQUISITION_SUBMITTED = 'REQUISITION_SUBMITTED';
    case REQUISITION_APPROVED = 'REQUISITION_APPROVED';
    case REQUISITION_REJECTED = 'REQUISITION_REJECTED';
    case REQUISITION_RESUBMITTED = 'REQUISITION_RESUBMITTED';
    case REQUISITION_OPENED = 'REQUISITION_OPENED';
    case REQUISITION_CLOSED = 'REQUISITION_CLOSED';
    case TEMPLATE_VERSION_CREATED = 'TEMPLATE_VERSION_CREATED';
    case SYNC_PUBLISHED = 'SYNC_PUBLISHED';
    case SYNC_UNPUBLISHED = 'SYNC_UNPUBLISHED';
    case DEPT_HEAD_ASSIGNED = 'DEPT_HEAD_ASSIGNED';
    case DEPT_HEAD_REMOVED = 'DEPT_HEAD_REMOVED';
}
```

### New: `SyncStatus`

```php
enum SyncStatus: string
{
    case QUEUED = 'QUEUED';
    case PUBLISHED = 'PUBLISHED';
    case UNPUBLISHED = 'UNPUBLISHED';
    case FAILED = 'FAILED';
}
```

### New: `ApprovalDecision`

```php
enum ApprovalDecision: string
{
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
}
```

## Services

### New: `TemplateVersionDiffService`

**Location**: `app/Services/TemplateVersionDiffService.php`

Simple PHP diff utility for comparing two template version snapshots. Returns an array of diff chunks (unchanged, added, removed) for rendering in the comparison view.
