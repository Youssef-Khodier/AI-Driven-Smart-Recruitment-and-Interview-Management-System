# Quickstart: Advanced Job Requisition Governance

**Feature**: 009-requisition-governance  
**Date**: 2026-05-06

## Prerequisites

1. SRIM project running on XAMPP with PHP 8.1+ and MySQL 8+.
2. Specs 001 (RBAC foundation) and 002 (Job Requisition CRUD) fully implemented.
3. Existing seed data includes at least 2 HR Admin users and 2+ departments.

## Database Setup

Run the schema migration to add new tables and columns:

```bash
# From project root, apply the governance schema additions to your local MySQL
mysql -u root srim < database/migrations/009_governance_tables.sql
```

The migration file should:
1. Add `is_department_head` column to `users`.
2. Create `requisition_approval_steps`.
3. Create `requisition_template_versions`.
4. Create `job_board_platforms` (with seed data).
5. Create `job_board_sync_records`.
6. Create `requisition_governance_audit`.

## Key Files to Create/Modify

### New Files
| File | Purpose |
|------|---------|
| `app/Controllers/HrGovernanceController.php` | All governance actions |
| `app/Repositories/GovernanceRepository.php` | Data access for governance entities |
| `app/Policies/GovernancePolicy.php` | Authorization for governance actions |
| `app/Enums/GovernanceAuditAction.php` | Audit action type enum |
| `app/Enums/SyncStatus.php` | Sync record status enum |
| `app/Enums/ApprovalDecision.php` | Approval decision enum |
| `app/Services/TemplateVersionDiffService.php` | Version comparison logic |
| `views/hr/governance/*.php` | 9 new view templates |
| `database/migrations/009_governance_tables.sql` | Schema changes |

### Modified Files
| File | Change |
|------|--------|
| `app/Enums/JobRequisitionStatus.php` | Add `REJECTED` case |
| `app/Policies/JobRequisitionPolicy.php` | Support REJECTED status in transitions |
| `app/Controllers/HrController.php` | Hook governance into submit flow |
| `app/Repositories/AuditLogRepository.php` | Add UNION ALL for governance audit |
| `routes/web.php` | Add ~12 new routes |
| `views/hr/requisitions/show.php` | Add governance links |
| `views/hr/requisitions/index.php` | Show REJECTED status |
| `views/hr/dashboard.php` | Add pending approvals widget |

## Testing Workflow

### Manual Acceptance Test Flow

1. **Assign department heads**: Sign in as HR Admin → `/hr/department-heads` → assign HR users as heads for their departments.
2. **Create & submit requisition**: `/hr/requisitions/create` → fill form → save → submit for approval.
3. **Approve/reject as department head**: Sign in as the department head → `/hr/approvals` → approve or reject with comments.
4. **Verify cross-department block**: Try approving a requisition from another department → should be denied.
5. **View template versions**: `/hr/requisitions/{id}/versions` → see version history → compare two versions.
6. **Publish to boards**: Open the approved requisition → `/hr/requisitions/{id}/publish` → select platforms → verify sync records.
7. **Unpublish on close**: Close the requisition → unpublish → verify UNPUBLISHED records.
8. **Audit trail**: `/hr/requisitions/{id}/governance-audit` → verify all actions logged.
9. **Unified audit**: `/hr/audit-log` → filter by REQUISITION_GOVERNANCE → verify governance events appear.

### Negative Tests

- Candidate tries to access `/hr/approvals` → 403
- Department head from wrong department tries to approve → denied
- HR Admin tries to approve their own requisition → denied
- Publish non-OPEN requisition → blocked
- Duplicate publish to same platform → blocked
- Concurrent edit test → stale overwrite blocked
