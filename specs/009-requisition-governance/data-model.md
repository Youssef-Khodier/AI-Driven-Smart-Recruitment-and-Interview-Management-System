# Data Model: Advanced Job Requisition Governance

**Feature**: 009-requisition-governance  
**Date**: 2026-05-06

## Schema Changes to Existing Tables

### `users` — Add Department-Head Flag

```sql
ALTER TABLE users ADD COLUMN is_department_head BOOLEAN NOT NULL DEFAULT FALSE AFTER status;
```

**Constraints**:
- Only users with `role = 'HR_ADMIN'` and `department_id IS NOT NULL` should have `is_department_head = TRUE`.
- Application-level enforcement: at most one user per department can have `is_department_head = TRUE`.
- Enforced by a unique partial constraint or application check (MySQL lacks partial unique indexes, so enforce in PHP).

### `job_requisitions` — Add REJECTED Status

The `status` column already uses VARCHAR(40), so no column change is needed. Add `REJECTED` as a valid status value in the `JobRequisitionStatus` PHP enum.

**New status transition map**:

```
DRAFT → PENDING (HR Admin submits for approval)
PENDING → APPROVED (Department head approves)
PENDING → REJECTED (Department head rejects)
REJECTED → PENDING (HR Admin revises and resubmits)
APPROVED → OPEN (HR Admin opens for candidates)
APPROVED → CLOSED (HR Admin closes)
OPEN → CLOSED (HR Admin closes)
```

Note: APPROVED → REJECTED is NOT allowed (only PENDING can be rejected).  
Note: Editing an APPROVED requisition's description/requirements triggers re-approval: APPROVED → DRAFT (content reset) then DRAFT → PENDING on resubmit.

## New Tables

### `requisition_approval_steps`

Records each approval/rejection decision by a department head.

```sql
CREATE TABLE requisition_approval_steps (
  step_id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id        BIGINT UNSIGNED NOT NULL,
  approver_id   BIGINT UNSIGNED NOT NULL,
  decision      VARCHAR(20) NOT NULL,          -- 'APPROVED' or 'REJECTED'
  comments      TEXT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_approval_steps_job FOREIGN KEY (job_id)
    REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_approval_steps_approver FOREIGN KEY (approver_id)
    REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_approval_steps_job (job_id),
  KEY idx_approval_steps_approver (approver_id)
) ENGINE=InnoDB;
```

**Fields**:
| Field | Type | Description |
|-------|------|-------------|
| step_id | BIGINT PK | Auto-increment ID |
| job_id | BIGINT FK | The requisition being approved/rejected |
| approver_id | BIGINT FK | The department head who made the decision |
| decision | VARCHAR(20) | `APPROVED` or `REJECTED` |
| comments | TEXT NULL | Optional comments/reason |
| created_at | TIMESTAMP | When the decision was made |

**Validation rules**:
- `approver_id` must be an active HR_ADMIN user with `is_department_head = TRUE`.
- `approver_id`'s `department_id` must match the requisition's `department_id`.
- `approver_id` must NOT equal `job_requisitions.created_by` (self-approval blocked).
- Requisition `status` must be `PENDING` at time of decision.

### `requisition_template_versions`

Versioned snapshots of requisition description and requirements content.

```sql
CREATE TABLE requisition_template_versions (
  version_id     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id         BIGINT UNSIGNED NOT NULL,
  version_number INT UNSIGNED NOT NULL,
  description_body TEXT NOT NULL,
  requirements_body TEXT NOT NULL,
  created_by     BIGINT UNSIGNED NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_template_versions_job FOREIGN KEY (job_id)
    REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_template_versions_user FOREIGN KEY (created_by)
    REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_template_version_job_num (job_id, version_number),
  KEY idx_template_versions_job (job_id)
) ENGINE=InnoDB;
```

**Fields**:
| Field | Type | Description |
|-------|------|-------------|
| version_id | BIGINT PK | Auto-increment ID |
| job_id | BIGINT FK | The requisition this version belongs to |
| version_number | INT UNSIGNED | Sequential version per requisition (1, 2, 3…) |
| description_body | TEXT | Snapshot of `job_requisitions.description` |
| requirements_body | TEXT | Snapshot of `job_requisitions.requirements` |
| created_by | BIGINT FK | User who triggered the version (submitter or editor) |
| created_at | TIMESTAMP | Version creation timestamp |

**Lifecycle**:
- Version created on: submit for approval (DRAFT/REJECTED → PENDING)
- Version created on: edit of approved requisition's description/requirements (APPROVED → re-approval flow)
- `version_number` = MAX(version_number for job_id) + 1, or 1 if first version

### `job_board_platforms`

Predefined list of simulated job board platforms.

```sql
CREATE TABLE job_board_platforms (
  platform_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL UNIQUE,
  is_active   BOOLEAN NOT NULL DEFAULT TRUE,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO job_board_platforms (name) VALUES
  ('LinkedIn Jobs'),
  ('Indeed'),
  ('Glassdoor'),
  ('Internal Careers Page')
ON DUPLICATE KEY UPDATE name = VALUES(name);
```

### `job_board_sync_records`

Local records representing simulated publish/unpublish operations.

```sql
CREATE TABLE job_board_sync_records (
  sync_id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id         BIGINT UNSIGNED NOT NULL,
  platform_id    BIGINT UNSIGNED NOT NULL,
  payload_summary TEXT NOT NULL,                 -- JSON: {title, department, description_excerpt, requirements}
  status         VARCHAR(40) NOT NULL,           -- 'QUEUED', 'PUBLISHED', 'UNPUBLISHED', 'FAILED'
  queued_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at   TIMESTAMP NULL,
  created_by     BIGINT UNSIGNED NOT NULL,
  CONSTRAINT fk_sync_records_job FOREIGN KEY (job_id)
    REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_sync_records_platform FOREIGN KEY (platform_id)
    REFERENCES job_board_platforms(platform_id) ON DELETE RESTRICT,
  CONSTRAINT fk_sync_records_user FOREIGN KEY (created_by)
    REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_sync_records_job (job_id),
  KEY idx_sync_records_platform_status (platform_id, status),
  KEY idx_sync_records_job_platform (job_id, platform_id)
) ENGINE=InnoDB;
```

**Fields**:
| Field | Type | Description |
|-------|------|-------------|
| sync_id | BIGINT PK | Auto-increment ID |
| job_id | BIGINT FK | The requisition being published |
| platform_id | BIGINT FK | Target platform from `job_board_platforms` |
| payload_summary | TEXT | JSON payload showing what was "sent" |
| status | VARCHAR(40) | Sync status lifecycle |
| queued_at | TIMESTAMP | When the sync was initiated |
| completed_at | TIMESTAMP NULL | When the sync completed |
| created_by | BIGINT FK | HR Admin who initiated the operation |

**Status lifecycle**: `QUEUED → PUBLISHED` (instant in same request), or `PUBLISHED → UNPUBLISHED` (on close).

**Duplicate prevention**: Application-level check — block publish if an active `PUBLISHED` record exists for the same job_id + platform_id.

### `requisition_governance_audit`

Immutable audit log for all governance actions.

```sql
CREATE TABLE requisition_governance_audit (
  audit_id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id         BIGINT UNSIGNED NOT NULL,
  actor_user_id  BIGINT UNSIGNED NOT NULL,
  action         VARCHAR(60) NOT NULL,
  old_values     JSON NULL,
  new_values     JSON NULL,
  comments       TEXT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gov_audit_job FOREIGN KEY (job_id)
    REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_gov_audit_actor FOREIGN KEY (actor_user_id)
    REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_gov_audit_job (job_id),
  KEY idx_gov_audit_action (action),
  KEY idx_gov_audit_actor (actor_user_id),
  KEY idx_gov_audit_created (created_at)
) ENGINE=InnoDB;
```

**Action types**:
| Action | Trigger |
|--------|---------|
| `REQUISITION_SUBMITTED` | HR Admin submits for approval |
| `REQUISITION_APPROVED` | Department head approves |
| `REQUISITION_REJECTED` | Department head rejects |
| `REQUISITION_RESUBMITTED` | HR Admin resubmits after rejection |
| `REQUISITION_OPENED` | HR Admin opens for candidates |
| `REQUISITION_CLOSED` | HR Admin closes |
| `TEMPLATE_VERSION_CREATED` | New version snapshot captured |
| `SYNC_PUBLISHED` | Simulated publish to a platform |
| `SYNC_UNPUBLISHED` | Simulated unpublish from a platform |
| `DEPT_HEAD_ASSIGNED` | HR Admin assigned as department head |
| `DEPT_HEAD_REMOVED` | Department head designation removed |

## Entity Relationship Summary

```
departments 1──∞ users (department_id FK)
users 1──∞ job_requisitions (created_by FK)
job_requisitions 1──∞ requisition_approval_steps (job_id FK)
job_requisitions 1──∞ requisition_template_versions (job_id FK)
job_requisitions 1──∞ job_board_sync_records (job_id FK)
job_requisitions 1──∞ requisition_governance_audit (job_id FK)
job_board_platforms 1──∞ job_board_sync_records (platform_id FK)
users 1──∞ requisition_approval_steps (approver_id FK)
users 1──∞ requisition_governance_audit (actor_user_id FK)
```
