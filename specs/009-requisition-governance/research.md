# Research: Advanced Job Requisition Governance

**Feature**: 009-requisition-governance  
**Date**: 2026-05-06

## R-001: Rejected Status in Existing Enum

**Context**: The baseline `JobRequisitionStatus` enum has `DRAFT, PENDING, APPROVED, OPEN, CLOSED`. The spec requires a `REJECTED` status for rejected requisitions.

**Decision**: Add `REJECTED` to the `JobRequisitionStatus` PHP enum and the `job_requisitions.status` column. Rejected requisitions are distinct from Draft — they carry rejection history and must be explicitly revised and resubmitted.

**Rationale**: Using a distinct REJECTED status (rather than reverting to DRAFT) preserves the audit trail of what was rejected and why. The HR Admin can edit a rejected requisition and resubmit, at which point it transitions REJECTED → PENDING via the same submit-for-approval action.

**Alternatives considered**:
- Revert to DRAFT on rejection: loses rejection context, harder to distinguish "never submitted" from "rejected for revision."
- Separate rejection tracking table only: overcomplicates queries; status column is simpler and aligns with existing pattern.

## R-002: Department Head Designation Mechanism

**Context**: The spec requires a one-to-one mapping between HR Admin users and departments for department-head authority. The existing `users` table has a `department_id` FK, but no department-head flag.

**Decision**: Add an `is_department_head` BOOLEAN column (default FALSE) to the `users` table. Only HR_ADMIN users with `is_department_head = TRUE` and a non-null `department_id` can approve requisitions for their department.

**Rationale**: A boolean flag is the simplest approach given the 1:1 constraint. The existing `department_id` FK already links users to departments, so no new join table is needed. A separate `department_heads` table would be over-engineered for a 1:1 relationship.

**Alternatives considered**:
- Separate `department_heads` table with `user_id` + `department_id`: unnecessary complexity for 1:1 mapping.
- New role enum value `DEPARTMENT_HEAD`: would break existing RBAC assumptions (department heads are still HR Admins with additional authority).

## R-003: Template Versioning Storage Pattern

**Context**: Template versions snapshot `description` + `requirements` fields from `job_requisitions`. Need to determine how to store version history.

**Decision**: Create a `requisition_template_versions` table with `version_id`, `job_id` (FK), `version_number`, `description_body` (TEXT), `requirements_body` (TEXT), `created_by` (FK to users), `created_at`. The version number auto-increments per requisition.

**Rationale**: A dedicated versioning table follows the same immutable-record pattern used throughout SRIM (e.g., `job_requisition_status_histories`, `application_status_histories`). Storing full snapshots rather than diffs simplifies retrieval and comparison logic; diffs can be computed at render time using PHP string comparison.

**Alternatives considered**:
- JSON column storing version array: harder to query, no FK constraints, doesn't follow existing patterns.
- Store only diffs: complex to reconstruct full version content; full snapshots are small enough for TEXT columns.

## R-004: Job Board Sync Record Design

**Context**: Simulated job-board publishing needs local records with platform, payload, status, and timestamps.

**Decision**: Create a `job_board_sync_records` table with `sync_id`, `job_id` (FK), `platform_name` VARCHAR(120), `payload_summary` TEXT (JSON string of job title, department, description excerpt, requirements), `status` VARCHAR(40) (QUEUED → PUBLISHED, or UNPUBLISHED), `queued_at`, `completed_at`, `created_by` (FK to users). Use a `UNIQUE KEY` on `(job_id, platform_name, status)` where status = PUBLISHED to prevent duplicates.

**Rationale**: Follows the existing audit-record pattern. Payload summary as TEXT (JSON-encoded) keeps it human-readable in the UI while structured enough for display. Status transitions are instantaneous (same request) since this is simulated.

**Alternatives considered**:
- Reuse existing audit tables: would conflate sync records with general audit data, making filtering harder.
- Separate queue and history tables: over-engineered for a simulated, instant operation.

## R-005: Governance Audit Log Integration

**Context**: The spec requires a dedicated governance audit log. The existing `AuditLogRepository` already aggregates audit records from multiple tables via UNION ALL.

**Decision**: Create a `requisition_governance_audit` table following the same pattern as `screening_audit_records` (job-scoped audit with JSON old/new values). Add it as a new UNION ALL leg in `AuditLogRepository::baseUnionSql()` with entity type `REQUISITION_GOVERNANCE`.

**Rationale**: The existing audit log infrastructure already handles multi-source aggregation. Adding another UNION ALL source is the lowest-friction approach and keeps governance audit entries visible in the unified HR audit log while also queryable independently.

**Alternatives considered**:
- Reuse `job_requisition_status_histories` for all audit: doesn't capture template versions or sync events.
- Separate standalone audit viewer: duplicates existing infrastructure.

## R-006: Optimistic Concurrency Control

**Context**: The spec requires OCC for concurrent requisition edits, consistent with spec 002.

**Decision**: Use the existing `updated_at` timestamp column on `job_requisitions`. Include `updated_at` in a hidden form field. On save, verify `WHERE job_id = ? AND updated_at = ?`; if zero rows affected, reject with "requisition was modified by another user."

**Rationale**: This is the exact pattern already used by `HrController::updateRequisition()`. No new columns or mechanisms needed.

**Alternatives considered**: None; reuse the established pattern.

## R-007: Version Comparison UI Approach

**Context**: FR-010 requires comparing two template versions with visible differences.

**Decision**: Server-side PHP diff. Use a simple line-by-line or word-by-word comparison function to highlight additions, deletions, and unchanged content. Render as an inline diff view (not side-by-side) for simplicity within the existing PHP template system.

**Rationale**: Vanilla PHP with no external dependencies. A simple word-level diff algorithm (longest common subsequence) can be implemented in ~50 lines of PHP. Inline diff is easier to render in a single-column PHP template.

**Alternatives considered**:
- Client-side JavaScript diff library: adds external dependency; constitution prefers server-side.
- Side-by-side view: more complex layout for minimal gain in an academic demo.

## R-008: Predefined Job Board Platforms

**Context**: The spec mentions a configurable list of simulated platforms.

**Decision**: Seed a `job_board_platforms` table with `platform_id`, `name`, `is_active`. Default entries: "LinkedIn Jobs", "Indeed", "Glassdoor", "Internal Careers Page". HR Admins can activate/deactivate platforms but not create new ones in this phase (platform management is configuration-level, not a user feature).

**Rationale**: A table is more flexible than a hardcoded array and allows the list to be extended without code changes. The is_active flag supports the "configurable" requirement.

**Alternatives considered**:
- PHP enum/constant array: less flexible, requires code changes to modify.
- Full CRUD for platforms: over-scoped for this feature; activate/deactivate is sufficient.
