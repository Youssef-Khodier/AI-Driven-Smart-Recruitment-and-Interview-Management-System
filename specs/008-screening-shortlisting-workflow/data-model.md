# Data Model: Screening & Shortlisting Workflow

**Feature**: `008-screening-shortlisting-workflow`
**Date**: 2026-05-05

## New Tables

### `screening_configs`

Per-requisition screening configuration header. One active config per requisition at a time.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `config_id` | `BIGINT UNSIGNED` | `PK AUTO_INCREMENT` | Primary key |
| `job_id` | `BIGINT UNSIGNED` | `NOT NULL, FK → job_requisitions.job_id` | Requisition this config belongs to |
| `is_active` | `BOOLEAN` | `NOT NULL DEFAULT TRUE` | Whether this is the current active config |
| `created_by` | `BIGINT UNSIGNED` | `NOT NULL, FK → users.user_id` | HR Admin who created this config |
| `created_at` | `TIMESTAMP` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | Creation timestamp |

**Indexes**: `KEY idx_screening_configs_job (job_id, is_active)`
**Unique**: Only one `is_active = TRUE` per `job_id` (enforced in application logic; deactivate previous on new save)

**Relationships**:
- `screening_configs.job_id` → `job_requisitions.job_id` (many configs per requisition, one active)
- `screening_configs.created_by` → `users.user_id`

---

### `screening_skills`

Weighted skills for a screening configuration. Each skill is a free-text label with a percentage weight.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `skill_id` | `BIGINT UNSIGNED` | `PK AUTO_INCREMENT` | Primary key |
| `config_id` | `BIGINT UNSIGNED` | `NOT NULL, FK → screening_configs.config_id ON DELETE CASCADE` | Parent config |
| `skill_name` | `VARCHAR(160)` | `NOT NULL` | Free-text skill label entered by HR |
| `weight` | `DECIMAL(5,2)` | `NOT NULL` | Percentage weight (e.g., 25.00 for 25%) |
| `evidence_field` | `VARCHAR(80)` | `NOT NULL DEFAULT 'skill_keywords'` | Which candidate field to check for this skill |

**Constraints**:
- `CHECK (weight > 0 AND weight <= 100)`
- All skills for a config must sum to exactly 100 (validated in application logic)
- `UNIQUE KEY uq_screening_skill_name (config_id, skill_name)`

**Relationships**:
- `screening_skills.config_id` → `screening_configs.config_id` (cascade delete)

---

### `screening_thresholds`

Score bands for automated triage. Each row maps a score range to a target application status.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `threshold_id` | `BIGINT UNSIGNED` | `PK AUTO_INCREMENT` | Primary key |
| `config_id` | `BIGINT UNSIGNED` | `NOT NULL, FK → screening_configs.config_id ON DELETE CASCADE` | Parent config |
| `min_score` | `TINYINT UNSIGNED` | `NOT NULL` | Lower bound of score band (inclusive) |
| `max_score` | `TINYINT UNSIGNED` | `NOT NULL` | Upper bound of score band (inclusive) |
| `target_status` | `VARCHAR(40)` | `NOT NULL` | Target status: SCREENING, ASSESSMENT, INTERVIEW, or REJECTED |

**Constraints**:
- `CHECK (min_score <= max_score)`
- `CHECK (min_score >= 0 AND max_score <= 100)`
- Contiguous coverage of 0–100 with no gaps or overlaps (validated in application logic)
- `target_status IN ('SCREENING', 'ASSESSMENT', 'INTERVIEW', 'REJECTED')`

**Relationships**:
- `screening_thresholds.config_id` → `screening_configs.config_id` (cascade delete)

---

### `screening_audit_records`

Audit trail for all screening workflow actions: config changes, recalculations, shortlist views, triage runs, and duplicate decisions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `audit_id` | `BIGINT UNSIGNED` | `PK AUTO_INCREMENT` | Primary key |
| `job_id` | `BIGINT UNSIGNED` | `NOT NULL, FK → job_requisitions.job_id` | Related requisition |
| `actor_user_id` | `BIGINT UNSIGNED` | `NOT NULL, FK → users.user_id` | Who performed the action |
| `action` | `VARCHAR(60)` | `NOT NULL` | Action type (see ScreeningAuditAction enum) |
| `entity_type` | `VARCHAR(40)` | `NULL` | Entity affected (e.g., 'APPLICATION', 'CONFIG', 'MERGE') |
| `entity_id` | `BIGINT UNSIGNED` | `NULL` | ID of affected entity |
| `old_values` | `JSON` | `NULL` | Previous state (for config changes, status transitions) |
| `new_values` | `JSON` | `NULL` | New state / action details |
| `created_at` | `TIMESTAMP` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` | When the action occurred |

**Indexes**:
- `KEY idx_screening_audit_job (job_id)`
- `KEY idx_screening_audit_action (action)`
- `KEY idx_screening_audit_actor (actor_user_id)`
- `KEY idx_screening_audit_created (created_at)`

**Relationships**:
- `screening_audit_records.job_id` → `job_requisitions.job_id`
- `screening_audit_records.actor_user_id` → `users.user_id`

---

## Altered Tables

### `applications` — Add Column

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `match_score_breakdown` | `JSON` | `NULL` | Per-skill scoring breakdown with weighted contributions and missing-evidence flags |

**JSON Structure Example**:
```json
{
  "skills": [
    {"name": "PHP", "weight": 30, "score": 1.0, "contribution": 30.0, "evidence": "skill_keywords", "found": true},
    {"name": "MySQL", "weight": 25, "score": 1.0, "contribution": 25.0, "evidence": "skill_keywords", "found": true},
    {"name": "React", "weight": 20, "score": 0.0, "contribution": 0.0, "evidence": "skill_keywords", "found": false},
    {"name": "Docker", "weight": 25, "score": 0.0, "contribution": 0.0, "evidence": "skill_keywords", "found": false}
  ],
  "raw_skill_score": 55.0,
  "experience_bonus": 10.0,
  "total_score": 65,
  "config_id": 1,
  "calculated_at": "2026-05-05T12:00:00Z"
}
```

---

### `candidate_merge_log` — Add Columns

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `decision_type` | `VARCHAR(20)` | `NOT NULL DEFAULT 'MERGE'` | MERGE, IGNORE, or DEFER |
| `confidence_category` | `VARCHAR(20)` | `NULL` | HIGH, MEDIUM, or LOW |
| `job_id` | `BIGINT UNSIGNED` | `NULL, FK → job_requisitions.job_id` | Requisition context for the duplicate check |
| `matching_evidence` | `JSON` | `NULL` | Fields that triggered the duplicate suggestion |

**Note**: The existing `UNIQUE KEY uq_candidate_merge_pair (primary_candidate_id, duplicate_candidate_id)` may need to be changed to include `decision_type` to allow a DEFER followed by a MERGE for the same pair. Alternative: drop the unique constraint and use application-level dedup (check existing non-DEFER decisions for the pair).

---

## State Transitions

### Application Status (screening-relevant transitions)

```
APPLIED ──[triage: score ≥ threshold]──→ SCREENING
APPLIED ──[triage: score ≥ threshold]──→ ASSESSMENT
APPLIED ──[triage: score ≥ threshold]──→ INTERVIEW
APPLIED ──[triage: score < threshold]──→ REJECTED
```

Triage ONLY moves applications currently in `APPLIED` status. Applications in any other status are skipped (FR-011).

### Screening Config Lifecycle

```
[No config] ──[HR creates config]──→ ACTIVE
ACTIVE ──[HR updates config]──→ INACTIVE (old) + ACTIVE (new)
```

Previous configs are soft-deactivated (`is_active = FALSE`) for audit trail. The new config becomes the sole active config.

### Duplicate Decision Lifecycle

```
[Duplicate detected] ──[HR decides MERGE]──→ Logged in candidate_merge_log (decision_type = MERGE)
[Duplicate detected] ──[HR decides IGNORE]──→ Logged in candidate_merge_log (decision_type = IGNORE)
[Duplicate detected] ──[HR decides DEFER]──→ Logged in candidate_merge_log (decision_type = DEFER)
[DEFER] ──[HR revisits]──→ Can be changed to MERGE or IGNORE
```

---

## Enum Values

### `ScreeningAuditAction`

| Value | Description |
|-------|-------------|
| `CONFIG_CREATED` | New screening config saved |
| `CONFIG_UPDATED` | Existing config replaced with new version |
| `SCORES_RECALCULATED` | Match scores recalculated for requisition |
| `SHORTLIST_GENERATED` | Shortlist viewed/generated |
| `TRIAGE_EXECUTED` | Automated triage run on APPLIED applications |
| `TRIAGE_STATUS_CHANGE` | Individual application status changed by triage |
| `DUPLICATE_CHECK_RUN` | Duplicate detection triggered |
| `DUPLICATE_DECISION` | HR recorded merge/ignore/defer decision |

### `DuplicateDecisionType`

| Value | Description |
|-------|-------------|
| `MERGE` | Profiles merged; primary candidate selected |
| `IGNORE` | Duplicate dismissed; no action taken |
| `DEFER` | Decision postponed for later review |

---

## SQL Migration

```sql
-- New tables for screening feature
CREATE TABLE screening_configs (
  config_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_screening_configs_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_screening_configs_user FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_screening_configs_job (job_id, is_active)
) ENGINE=InnoDB;

CREATE TABLE screening_skills (
  skill_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  config_id BIGINT UNSIGNED NOT NULL,
  skill_name VARCHAR(160) NOT NULL,
  weight DECIMAL(5,2) NOT NULL,
  evidence_field VARCHAR(80) NOT NULL DEFAULT 'skill_keywords',
  CONSTRAINT fk_screening_skills_config FOREIGN KEY (config_id) REFERENCES screening_configs(config_id) ON DELETE CASCADE,
  CONSTRAINT chk_screening_skill_weight CHECK (weight > 0 AND weight <= 100),
  UNIQUE KEY uq_screening_skill_name (config_id, skill_name)
) ENGINE=InnoDB;

CREATE TABLE screening_thresholds (
  threshold_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  config_id BIGINT UNSIGNED NOT NULL,
  min_score TINYINT UNSIGNED NOT NULL,
  max_score TINYINT UNSIGNED NOT NULL,
  target_status VARCHAR(40) NOT NULL,
  CONSTRAINT fk_screening_thresholds_config FOREIGN KEY (config_id) REFERENCES screening_configs(config_id) ON DELETE CASCADE,
  CONSTRAINT chk_screening_threshold_range CHECK (min_score <= max_score AND min_score >= 0 AND max_score <= 100)
) ENGINE=InnoDB;

CREATE TABLE screening_audit_records (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(60) NOT NULL,
  entity_type VARCHAR(40) NULL,
  entity_id BIGINT UNSIGNED NULL,
  old_values JSON NULL,
  new_values JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_screening_audit_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_screening_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_screening_audit_job (job_id),
  KEY idx_screening_audit_action (action),
  KEY idx_screening_audit_actor (actor_user_id),
  KEY idx_screening_audit_created (created_at)
) ENGINE=InnoDB;

-- Alter existing tables
ALTER TABLE applications ADD COLUMN match_score_breakdown JSON NULL AFTER match_score;

ALTER TABLE candidate_merge_log
  ADD COLUMN decision_type VARCHAR(20) NOT NULL DEFAULT 'MERGE' AFTER notes,
  ADD COLUMN confidence_category VARCHAR(20) NULL AFTER decision_type,
  ADD COLUMN job_id BIGINT UNSIGNED NULL AFTER confidence_category,
  ADD COLUMN matching_evidence JSON NULL AFTER job_id,
  ADD CONSTRAINT fk_merge_log_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE SET NULL;
```

---

## Entity Relationship Summary

```
job_requisitions 1──* screening_configs 1──* screening_skills
                                         1──* screening_thresholds

job_requisitions 1──* applications (match_score, match_score_breakdown)
                 1──* screening_audit_records

candidates *──* candidate_merge_log (decision_type, confidence_category, job_id)
```
