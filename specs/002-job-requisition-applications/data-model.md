# Data Model: Job Requisition and Candidate Applications

## Overview

This feature extends the existing RBAC foundation entities with recruitment records for job requisitions, candidate applications, deterministic simulated match scores, and audit-relevant status history.

## Entity: Department

**Purpose**: Existing organizational unit used to classify job requisitions.

**Existing fields used**:

- `department_id`: Primary identifier.
- `name`: Department name shown on job listings.

**Relationships**:

- Has many Job Requisitions.
- Has many Users through the existing foundation relationship.

**Validation rules**:

- HR requisition forms must select an existing department.

## Entity: User

**Purpose**: Existing authenticated account for HR Admins, Candidates, and Technical Interviewers.

**Existing fields used**:

- `user_id`: Primary identifier.
- `name`, `email`: Identity shown in HR applicant summaries and audit actor labels.
- `role`: Must be `HR_ADMIN` or `CANDIDATE` for this feature's active workflows.
- `status`: Must be `ACTIVE` for protected actions.

**Relationships**:

- One Candidate Profile when role is Candidate.
- Has many created Job Requisitions when role is HR Admin.
- Has many approved Job Requisitions when acting as approver.
- Has many status-history records as actor.

**Validation rules**:

- Inactive users cannot perform HR or candidate actions.
- Technical Interviewer and Junior Staff access is not expanded by this feature.

## Entity: Candidate Profile

**Purpose**: Candidate-owned professional profile used to apply and calculate simulated match scores.

**Existing fields used**:

- `candidate_id`: Primary key and foreign key to `users.user_id`.
- `phone`: Candidate contact field from the foundation.
- `current_title`: Required before applying.
- `years_experience`: Required numeric value before applying; minimum `0`.
- `location`: Required before applying.
- `resume_url`: Required resume reference before applying; stored as a URL/reference string.

**New field**:

- `skill_keywords`: Required comma-separated skills or keywords list before applying.

**Relationships**:

- Belongs to User.
- Has many Applications.

**Validation rules**:

- Candidate can update only their own profile.
- `skill_keywords` must contain at least one non-empty skill or keyword.
- `years_experience` must be numeric and non-negative.
- Required application-scoring fields must be present before candidate can apply.

## Entity: Job Requisition

**Purpose**: HR-created hiring request that progresses through approval and publication before candidates can apply.

**Fields**:

- `job_id`: Primary identifier.
- `department_id`: Required department foreign key.
- `title`: Required job title.
- `description`: Required job description.
- `requirements`: Required requirements text used for candidate-facing details and simulated scoring.
- `status`: One of Draft, Pending Approval, Approved, Open, Closed.
- `created_by`: HR Admin who created the requisition.
- `approved_by`: HR Admin who approved the requisition; must differ from `created_by` when set.
- `approved_at`: Timestamp when approved.
- `opened_at`: Timestamp when opened for candidate applications.
- `closed_at`: Timestamp when closed.
- `created_at`, `updated_at`: Standard timestamps; `updated_at` is used for stale edit detection.

**Relationships**:

- Belongs to Department.
- Belongs to creator User.
- Belongs to approver User when approved.
- Has many Applications.
- Has many Job Requisition Status History records.

**Validation rules**:

- Title, department, description, and requirements are required before moving beyond Draft.
- Only active HR Admins can create, edit, submit, approve, open, or close requisitions.
- The creator cannot approve their own requisition.
- Candidate-visible browsing includes only Open requisitions.
- Stale edit saves are blocked when submitted last-seen `updated_at` differs from the current record.

**State transitions**:

- Draft -> Pending Approval when creator submits a complete requisition.
- Pending Approval -> Approved when a different active HR Admin approves.
- Approved -> Open when an active HR Admin opens the requisition.
- Approved -> Closed when an active HR Admin closes before opening.
- Open -> Closed when an active HR Admin stops accepting applications.
- Pending Approval -> Draft when material changes are required before approval.
- Approved -> Draft when material changes are required before opening.

## Entity: Application

**Purpose**: Candidate's single application to one job requisition, with current pipeline status and simulated match score.

**Fields**:

- `application_id`: Primary identifier.
- `candidate_id`: Required candidate foreign key.
- `job_id`: Required job requisition foreign key.
- `status`: One of Applied, Screening, Assessment, Interview, Offer, Rejected, Hired.
- `match_score`: Simulated advisory score from `0` to `100`.
- `applied_at`: Timestamp when application was submitted.
- `created_at`, `updated_at`: Standard timestamps.

**Relationships**:

- Belongs to Candidate Profile.
- Belongs to Job Requisition.
- Has many Application Status History records.

**Validation rules**:

- Candidate can apply only to Open requisitions.
- Candidate must have a complete profile before applying.
- Candidate can have at most one application per job requisition.
- Candidate can view only their own applications.
- HR Admin can view and update application statuses for all requisitions.
- Match score must be null only before calculation, or between `0` and `100` after accepted submission.

**State transitions**:

- New application starts as Applied.
- HR Admin may set status to Applied, Screening, Assessment, Interview, Offer, Rejected, or Hired.
- Candidate sees exact pipeline status values.

## Entity: Simulated Match Score

**Purpose**: Advisory score attached to an application at submission time.

**Inputs**:

- Job requirements text.
- Candidate `skill_keywords` list.
- Candidate `current_title`.
- Candidate `years_experience`.

**Rules**:

- Score range is `0` to `100`.
- Skills or keyword overlap contributes 70%.
- Current-title match contributes 15%.
- Years-of-experience match contributes 15%.
- Score is labeled simulated and advisory in candidate and HR pages.
- Score does not automatically reject, advance, hire, or close applications.
- Score is stored at application time and not silently recalculated after profile edits.

## Entity: Job Requisition Status History

**Purpose**: Audit-relevant record of requisition lifecycle changes.

**Fields**:

- `history_id`: Primary identifier.
- `job_id`: Related job requisition.
- `actor_user_id`: HR Admin who made the change.
- `old_status`: Previous status, nullable for creation if needed.
- `new_status`: New status.
- `reason`: Optional reason or note.
- `created_at`: Timestamp of the change.

**Relationships**:

- Belongs to Job Requisition.
- Belongs to actor User.

**Validation rules**:

- Actor must be an active HR Admin.
- Record is created for submit, approve, open, close, and return-to-draft status changes.

## Entity: Application Status History

**Purpose**: Audit-relevant record of application pipeline status changes.

**Fields**:

- `history_id`: Primary identifier.
- `application_id`: Related application.
- `actor_user_id`: HR Admin who made the change, or the candidate for initial application creation if recorded.
- `old_status`: Previous status, nullable for initial application creation if needed.
- `new_status`: New status.
- `reason`: Optional reason or note.
- `created_at`: Timestamp of the change.

**Relationships**:

- Belongs to Application.
- Belongs to actor User.

**Validation rules**:

- HR status updates require an active HR Admin.
- Candidates cannot change application pipeline status after applying.

## Data Integrity Rules

- Unique application constraint: one row per `candidate_id` and `job_id`.
- Foreign key deletes should preserve recruitment integrity; do not cascade-delete departments with requisitions.
- Closing a requisition does not delete existing applications.
- Candidate PII and scores are visible only through authorized HR or owner-candidate workflows.
