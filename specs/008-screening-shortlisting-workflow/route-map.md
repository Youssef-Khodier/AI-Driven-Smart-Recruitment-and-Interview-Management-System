# Route Map: Screening & Shortlisting Workflow

**Feature**: `008-screening-shortlisting-workflow`
**Date**: 2026-05-05

## New Routes

All routes are added to `routes/web.php` and are HR Admin only (enforced by `ScreeningPolicy`).

### Screening Configuration

| Method | URI | Controller Method | Route Name | Description |
|--------|-----|-------------------|------------|-------------|
| `GET` | `/hr/requisitions/{id}/screening` | `HrScreeningController::config` | `hr.screening.config` | Show screening configuration form (create or edit) |
| `POST` | `/hr/requisitions/{id}/screening` | `HrScreeningController::storeConfig` | `hr.screening.config.store` | Save/update screening configuration |

### Score Recalculation & Shortlist

| Method | URI | Controller Method | Route Name | Description |
|--------|-----|-------------------|------------|-------------|
| `POST` | `/hr/requisitions/{id}/screening/recalculate` | `HrScreeningController::recalculate` | `hr.screening.recalculate` | Trigger score recalculation for all applicants |
| `GET` | `/hr/requisitions/{id}/shortlist` | `HrScreeningController::shortlist` | `hr.screening.shortlist` | View simulated AI-ranked shortlist |

### Automated Triage

| Method | URI | Controller Method | Route Name | Description |
|--------|-----|-------------------|------------|-------------|
| `GET` | `/hr/requisitions/{id}/triage` | `HrScreeningController::triagePreview` | `hr.screening.triage` | Preview triage results before execution |
| `POST` | `/hr/requisitions/{id}/triage` | `HrScreeningController::executeTriage` | `hr.screening.triage.execute` | Execute triage on APPLIED applications |

### Duplicate Detection

| Method | URI | Controller Method | Route Name | Description |
|--------|-----|-------------------|------------|-------------|
| `GET` | `/hr/requisitions/{id}/duplicates` | `HrScreeningController::duplicates` | `hr.screening.duplicates` | Run duplicate check and show suggestions |
| `POST` | `/hr/requisitions/{id}/duplicates/{mergeId}` | `HrScreeningController::resolveDuplicate` | `hr.screening.duplicates.resolve` | Record merge/ignore/defer decision |

### Screening Audit

| Method | URI | Controller Method | Route Name | Description |
|--------|-----|-------------------|------------|-------------|
| `GET` | `/hr/requisitions/{id}/screening/audit` | `HrScreeningController::audit` | `hr.screening.audit` | View screening audit trail for requisition |

## Controller: `HrScreeningController`

**File**: `app/Controllers/HrScreeningController.php`
**Extends**: `App\Core\Controller`
**RBAC**: All methods call `$this->requireRole('HR_ADMIN')` via `ScreeningPolicy`

### Method Summary

| Method | Dependencies | Key Logic |
|--------|-------------|-----------|
| `config($request, $id)` | `ScreeningConfigRepository` | Load requisition + active config (if any); render form |
| `storeConfig($request, $id)` | `ScreeningConfigRepository`, `ScreeningAuditRepository` | Validate weights sum=100, thresholds contiguous; deactivate old config; save new; audit |
| `recalculate($request, $id)` | `ScreeningScoreService`, `ScreeningAuditRepository` | Load config + APPLIED applications; call scorer; update match_score + breakdown; audit |
| `shortlist($request, $id)` | `ScreeningConfigRepository` | Load applications ordered by score desc, experience desc, applied_at asc; render |
| `triagePreview($request, $id)` | `ScreeningConfigRepository` | Load APPLIED applications with scores; show which status each would get |
| `executeTriage($request, $id)` | `ScreeningScoreService`, `ScreeningAuditRepository` | Apply thresholds to APPLIED-only apps; update statuses; audit each change |
| `duplicates($request, $id)` | `DuplicateDetectionService`, `ScreeningAuditRepository` | Run duplicate check; show suggestions with evidence; audit |
| `resolveDuplicate($request, $id, $mergeId)` | `DuplicateRepository`, `ScreeningAuditRepository` | Validate reason present; record decision; audit |
| `audit($request, $id)` | `ScreeningAuditRepository` | Load + filter audit records for requisition; render |

## Views

All views are PHP templates in `views/hr/screening/` using the existing layout pattern.

| View File | Route | Data Required |
|-----------|-------|---------------|
| `config.php` | `hr.screening.config` | `$requisition`, `$config`, `$skills`, `$thresholds`, `$errors` |
| `shortlist.php` | `hr.screening.shortlist` | `$requisition`, `$applications`, `$config` |
| `triage-confirm.php` | `hr.screening.triage` | `$requisition`, `$preview` (applications with projected statuses) |
| `triage-results.php` | `hr.screening.triage.execute` | `$requisition`, `$results` (changed applications with audit) |
| `duplicates.php` | `hr.screening.duplicates` | `$requisition`, `$suggestions` |
| `duplicate-resolve.php` | `hr.screening.duplicates.resolve` | `$requisition`, `$suggestion`, `$errors` |
| `audit.php` | `hr.screening.audit` | `$requisition`, `$records`, `$filters`, `$pagination` |

## Policy: `ScreeningPolicy`

**File**: `app/Policies/ScreeningPolicy.php`

| Method | Description |
|--------|-------------|
| `canConfigure(array $user): bool` | HR_ADMIN + ACTIVE only |
| `canRecalculate(array $user): bool` | HR_ADMIN + ACTIVE only |
| `canTriage(array $user): bool` | HR_ADMIN + ACTIVE only |
| `canViewShortlist(array $user): bool` | HR_ADMIN + ACTIVE only |
| `canManageDuplicates(array $user): bool` | HR_ADMIN + ACTIVE only |
| `canViewAudit(array $user): bool` | HR_ADMIN + ACTIVE only |

All screening actions are restricted to HR_ADMIN role with ACTIVE account status.
