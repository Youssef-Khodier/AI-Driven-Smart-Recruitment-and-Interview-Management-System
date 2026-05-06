# Data Model: Interview Coordination Workflows

## Existing Baseline Entities

### User

- **Purpose**: Account for HR Admins, Technical Interviewers, Candidates, and Junior Staff/observers.
- **Existing fields used**: `user_id`, `department_id`, `name`, `email`, `role`, `status`.
- **Validation rules**: Only `ACTIVE` staff users can be recommended for panels. Candidate users cannot be assigned as panel members.
- **Relationships**: Staff users can have many panel assignments; candidates are reached through `applications.candidate_id`.

### Application

- **Purpose**: Candidate application that reaches the interview stage.
- **Existing fields used**: `application_id`, `candidate_id`, `job_id`, `status`, `match_score`.
- **Validation rules**: Scheduling is allowed only when status is `INTERVIEW`.
- **Relationships**: One application can have many interview sessions.

### Interview Session

- **Purpose**: Scheduled interview for one application.
- **Existing fields used**: `interview_id`, `application_id`, `interview_type`, `scheduled_at`, `duration_minutes`, `status`, `created_by`, timestamps.
- **New or extended fields**: `extended_duration_minutes` default `0`, `last_extension_decision_at` nullable.
- **Validation rules**: Duration must be positive. Scheduled time must be in the future for new sessions. Status values are `SCHEDULED`, `COMPLETED`, `CANCELLED`.
- **Relationships**: Belongs to application; has many panel assignments, workspace records, extension requests, briefing snapshots, and audit events.
- **State transitions**: Draft form is not persisted; `SCHEDULED` to `COMPLETED`; `SCHEDULED` to `CANCELLED`; extension approval updates effective duration while status remains `SCHEDULED`.

### Panel Assignment

- **Purpose**: Links one staff user to one interview session with an official or observer role.
- **Existing fields used**: `assignment_id`, `interview_id`, `interviewer_id`, `role_in_panel`, `is_shadowing`.
- **New or extended fields**: `assignment_source` (`RECOMMENDED`, `MANUAL`, `OVERRIDE`), `override_reason` nullable, `conflict_overridden` boolean default `false`, `assigned_by`, `assigned_at`.
- **Validation rules**: Unique `interview_id` plus `interviewer_id`. `OBSERVER` assignments must be `is_shadowing = true`. Official scorer roles must not be shadowing. Manual conflict overrides require `override_reason`.
- **Relationships**: Belongs to interview session and user.

## New Entities

### Staff Panel Capability

- **Purpose**: Defines whether active staff can serve as HR representative, senior technical interviewer, interviewer, or observer for recommendation purposes.
- **Fields**: `capability_id`, `user_id`, `can_represent_hr`, `can_lead_technical`, `can_interview`, `can_observe`, `specialization`, `seniority_level`, `created_at`, `updated_at`.
- **Validation rules**: User must be active staff. HR representative capability requires HR Admin role. Observer capability is allowed for Junior Staff and Interviewer roles. Senior technical capability requires interviewer role plus seniority/capability flag.
- **Relationships**: Belongs to user; used by panel recommendation queries.

### Panel Recommendation Snapshot

- **Purpose**: Records the recommendation candidates shown to HR for a scheduling attempt.
- **Fields**: `recommendation_id`, `application_id`, `requested_start_at`, `requested_duration_minutes`, `required_panel_mix`, `generated_by`, `generated_at`, `recommendation_payload`, `accepted_interview_id` nullable.
- **Validation rules**: Requested duration must be positive. Payload must include candidate staff IDs, role fit, workload count, conflict status, and recommendation reason.
- **Relationships**: Belongs to application and generated-by user; optionally linked to the interview created from the recommendation.

### Interview Briefing Snapshot

- **Purpose**: Saved interview pack containing candidate context, assessment summaries, and job requirements.
- **Fields**: `briefing_id`, `interview_id`, `candidate_summary`, `assessment_summary`, `job_requirements_summary`, `missing_data_flags`, `created_at`, `updated_at`.
- **Validation rules**: Snapshot must belong to one interview. Missing resume, missing assessment score, or missing job requirements are recorded as flags rather than blocking scheduling.
- **Relationships**: Belongs to interview session; visible to assigned participants according to policy.

### Simulated Coding Workspace

- **Purpose**: Current state for the refresh-based live coding workspace.
- **Fields**: `workspace_id`, `interview_id`, `prompt_text`, `code_text`, `candidate_run_notes`, `interviewer_notes`, `version_number`, `last_saved_by`, `last_saved_at`, `created_at`, `updated_at`.
- **Validation rules**: Workspace belongs to one interview. Only authorized assigned candidate, official interviewers, observers with read-only access, and HR can view. Candidate-visible code can be changed only by permitted participants.
- **Relationships**: Belongs to interview session; has many workspace history records.

### Workspace History Record

- **Purpose**: Append-only save history for workspace changes.
- **Fields**: `history_id`, `workspace_id`, `interview_id`, `actor_user_id`, `changed_section`, `previous_version_number`, `new_version_number`, `change_summary`, `created_at`.
- **Validation rules**: Changed section must be one of prompt, code, candidate run notes, or interviewer notes. Every workspace save creates a history record and audit event.
- **Relationships**: Belongs to workspace, interview session, and actor user.

### Extension Request

- **Purpose**: Request and HR decision record for technical-issue interview extensions.
- **Fields**: `extension_request_id`, `interview_id`, `requested_by`, `requested_minutes`, `request_reason`, `status`, `decided_by` nullable, `decision_reason` nullable, `approved_minutes` nullable, `requested_at`, `decided_at` nullable, `cancelled_at` nullable.
- **Validation rules**: Requested minutes must be positive. Request reason is required. Approved minutes must be positive and cannot exceed requested minutes unless HR enters a decision reason. Status values are `PENDING`, `APPROVED`, `DENIED`, `CANCELLED`.
- **Relationships**: Belongs to interview session; requested by interviewer; decided by HR Admin.
- **State transitions**: `PENDING` to `APPROVED`; `PENDING` to `DENIED`; `PENDING` to `CANCELLED`. Approved/denied/cancelled requests are final.

### Interview Audit Event

- **Purpose**: Append-only record of interview coordination changes.
- **Existing table**: `interview_audit_records`.
- **Extended action values**: `SCHEDULED`, `RESCHEDULED`, `CANCELLED`, `COMPLETED`, `ASSIGNMENT_RECOMMENDED`, `ASSIGNMENT_ACCEPTED`, `ASSIGNMENT_CHANGED`, `ASSIGNMENT_REMOVED`, `ASSIGNMENT_OVERRIDE`, `BRIEFING_CREATED`, `WORKSPACE_UPDATED`, `EXTENSION_REQUESTED`, `EXTENSION_APPROVED`, `EXTENSION_DENIED`, `EXTENSION_CANCELLED`, `UNAUTHORIZED_ACCESS_DENIED`.
- **Validation rules**: Every audit event records `interview_id`, `actor_user_id`, action, changed fields JSON, and timestamp. Override and extension actions include reason fields.
- **Relationships**: Belongs to interview session and actor user.

## Recommendation Rules

- Required panel mix defaults to at least one HR representative and one official technical scorer for panel interviews.
- Senior technical interviewer is required when the interview type is `TECHNICAL` or `PANEL` and a senior eligible interviewer exists for the slot.
- Optional observers are training-only and never satisfy official scorer requirements.
- Recommendation ranking sorts by role eligibility, no conflict before conflict, lower upcoming workload count, then deterministic user ID/name tie-breaker.

## Conflict Rules

- Candidate conflict: another non-cancelled interview for the same application candidate overlaps the requested session range.
- Staff conflict: another non-cancelled interview assignment for the same staff member overlaps the requested session range.
- Extension conflict: approved additional minutes overlap another participant session; HR must see warning before approval.

## Retention and Privacy Notes

- Candidate interview data, briefing snapshots, workspace content, extension records, and audit events follow SRIM candidate data retention policy.
- Candidate-facing pages never expose another candidate's interview, workspace, briefing, or audit data.
- Observer access is read-only/training-only and excluded from official scoring.
