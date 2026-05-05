# Data Model: Notifications, Reports & Compliance

## Entity: Notification

**Purpose**: Durable in-app message addressed to one authenticated user.

**Fields**:

- `notification_id`: unique identifier.
- `user_id`: required recipient user reference.
- `title`: required short display title.
- `message`: required body text.
- `type`: required notification type such as `STATUS_CHANGE`, `FEEDBACK_REMINDER`, `OFFER_EXPIRING_SOON`, or `OFFER_EXPIRED`.
- `reference_id`: nullable triggering entity identifier.
- `reference_type`: nullable triggering entity type such as `APPLICATION`, `INTERVIEW`, or `OFFER`.
- `is_read`: boolean default `false`.
- `read_at`: nullable timestamp set when marked read.
- `created_at`: timestamp.

**Relationships**:

- Belongs to one User recipient.
- Optionally references an application, interview, or offer through `reference_type` and `reference_id`.

**Validation and state rules**:

- `title`, `message`, and `type` are required.
- Notification reads are scoped to the authenticated recipient.
- Marking as read changes `is_read` from `false` to `true` and sets `read_at` once.
- Repository dedupe checks `user_id`, `type`, `reference_id`, and `reference_type` before insert.

## Entity: Application Status Notification Trigger

**Purpose**: Status-change event that creates candidate notifications.

**Existing fields used**: `applications.application_id`, `candidate_id`, `job_id`, `status`; `application_status_histories.old_status`, `new_status`, `actor_user_id`, `created_at`; `job_requisitions.title`.

**Relationships**:

- Application belongs to Candidate and Job Requisition.
- Candidate maps to User recipient through `candidate_id = users.user_id`.

**Rules**:

- Created when HR Admin changes an application status.
- Recipient is the candidate who owns the application.
- Reference is `APPLICATION` plus `application_id`.
- Message includes job title and new status.

## Entity: Feedback Reminder Check

**Purpose**: Manual compliance check for completed interviews missing interviewer feedback after 24 hours.

**Existing fields used**: `interviews.interview_id`, `application_id`, `scheduled_at`, `status`, `updated_at`; `interviewers_assignment.interviewer_id`; `interview_feedback.feedback_id`; candidate and job details through application.

**Relationships**:

- Interview belongs to Application.
- Interview has assigned interviewers.
- Interview feedback belongs to one interview and interviewer.

**Rules**:

- HR Admin Run Checks selects interviews with `status = 'COMPLETED'` completed more than 24 hours ago.
- For each assigned interviewer without feedback for that interview, create one `FEEDBACK_REMINDER` notification.
- Reference is `INTERVIEW` plus `interview_id`.
- Existing matching notification prevents duplicates.

## Entity: Offer Expiry Check

**Purpose**: Manual compliance check for sent offers nearing or past expiry.

**Existing fields used**: `offers.offer_id`, `application_id`, `status`, `expiry_date`, `created_by`; candidate and job details through application.

**Relationships**:

- Offer belongs to Application.
- Offer creator is the HR Admin notification recipient.

**Rules**:

- For `SENT` offers expiring within 48 hours, create one `OFFER_EXPIRING_SOON` notification to the creator.
- For `SENT` offers past expiry, transition offer to `EXPIRED`, set expiry metadata if available, create one `OFFER_EXPIRED` notification to the creator, and preserve audit/status evidence.
- Accepted or rejected offers are ignored.

## Derived Entity: Pipeline Report

**Purpose**: HR-only read model showing application counts by status per open requisition and aggregate totals.

**Source fields**: `job_requisitions.job_id`, `title`, `department_id`, `status`; `applications.status`; `departments.name`.

**Relationships**:

- Job Requisition has many Applications.
- Job Requisition belongs to Department.

**Rules**:

- Include open requisitions.
- Count statuses `APPLIED`, `SCREENING`, `ASSESSMENT`, `INTERVIEW`, `OFFER`, `HIRED`, and `REJECTED`.
- Requisitions with zero applications show zero counts.
- No rows are persisted.

## Derived Entity: Time-To-Hire Summary

**Purpose**: HR-only read model showing average days from application submission to hired transition by requisition and department.

**Source fields**: `applications.applied_at`; `application_status_histories.new_status`, `created_at`; `job_requisitions.title`; `departments.name`.

**Relationships**:

- Application belongs to Job Requisition.
- Job Requisition belongs to Department.
- Application has many status history rows.

**Rules**:

- Include applications with a `HIRED` status-history transition.
- Use the first `HIRED` transition timestamp per application.
- Average date difference by requisition and department.
- Display `N/A` when no hired candidates exist.

## Derived Entity: Consolidated Audit Entry

**Purpose**: Unified HR-only display of audit and status-history records.

**Source fields**:

- `account_audit_records`: account/retention actions.
- `interview_audit_records`: interview and feedback changes.
- `post_offer_audit_records`: final evaluation, offer, onboarding changes.
- `application_status_histories`: application status transitions.
- `job_requisition_status_histories`: requisition status transitions.

**Normalized display fields**:

- `occurred_at`: timestamp.
- `actor_user_id` and actor name/email.
- `entity_type`: account, interview, post-offer, application status, or job requisition status.
- `entity_id`: affected record identifier when available.
- `action`: action/status change.
- `summary`: human-readable changed fields.

**Rules**:

- Audit log is read-only through the application UI.
- Filter by date range, actor, action, and entity type.
- Paginate 25 records per page.

## Entity: Candidate Data Retention Candidate

**Purpose**: HR-only view of candidates eligible for anonymization or deletion.

**Source fields**: `users.user_id`, `name`, `email`, `status`; `candidates.phone`, `resume_url`, `skill_keywords`; `applications.applied_at`, `status`; `job_requisitions.status`.

**Relationships**:

- Candidate profile is keyed by `users.user_id`.
- Candidate has many Applications.
- Applications belong to Job Requisitions.

**Eligibility rules**:

- Candidate's most recent application is older than the configured threshold, default 365 days.
- Candidate has no active application.
- Terminal applications are either `REJECTED` or linked to a `CLOSED` requisition.
- Eligibility is recalculated during POST actions.

## Retention Action: Anonymization

**Purpose**: Irreversibly redact candidate PII while preserving referential integrity.

**Field changes**:

- `users.name` becomes `Anonymized Candidate`.
- `users.email` becomes a unique hashed placeholder.
- `candidates.phone` becomes `REDACTED`.
- `candidates.resume_url` becomes `NULL`.
- `candidates.skill_keywords` becomes `NULL`.

**Rules**:

- HR Admin only.
- Requires confirmation token/value.
- Blocks if eligibility recheck fails.
- Writes audit action `CANDIDATE_ANONYMIZED` with actor and changed-field snapshot.

## Retention Action: Deletion

**Purpose**: Remove candidate account/profile and related candidate-owned recruitment records where allowed.

**Rules**:

- HR Admin only.
- Requires confirmation token/value.
- Blocks if eligibility recheck fails.
- Writes audit action `CANDIDATE_DELETED` with former candidate snapshot before deletion.
- Audit storage must not cascade-delete the deletion audit record when the target user row is removed.

## State Transition Summary

### Notification

```text
UNREAD -> READ
```

### Offer Expiry Check

```text
SENT -> EXPIRED
```

### Candidate Data Retention

```text
IDENTIFIED_ELIGIBLE -> ANONYMIZED
IDENTIFIED_ELIGIBLE -> DELETED
```
