# Research: Notifications, Reports & Compliance

## Decision: Use Server-Rendered Page/Form Workflows Only

**Rationale**: SRIM is constitutionally constrained to a framework-free Vanilla PHP monolith with server-rendered PHP templates and `routes/web.php` browser flows. Notifications, HR reports, audit history, Run Checks, and retention actions are all human-facing workflows that fit GET pages, POST/PUT forms, redirects, sessions, CSRF, and validation.

**Alternatives considered**: REST endpoints for notifications or reports were rejected because they create machine-facing contracts not required by the feature. A separated notification frontend was rejected because it violates the approved monolith delivery mode.

## Decision: Add Notifications To Live Schema With Reference Columns

**Rationale**: The baseline ERD includes `notifications`, but the live schema does not. The feature requires durable in-app notification records plus `reference_id` and `reference_type` for deduplication and future linking. Repository-level dedupe will check `user_id`, `type`, `reference_id`, and `reference_type` before insert.

**Alternatives considered**: Computing notifications dynamically was rejected because users must mark items read. A strict unique key alone was rejected because MySQL treats nullable unique columns in a way that can still permit duplicate null-reference rows.

## Decision: Header Badge Uses Indexed Count Per Page Load

**Rationale**: The spec requires a badge on every authenticated page. A scoped `SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0` using `(user_id, is_read)` is simple, secure, and performant for the demo scale.

**Alternatives considered**: Session-cached counts were rejected because they can become stale after status changes or mark-read actions. A JavaScript poller was rejected because it adds unnecessary progressive enhancement and request surface.

## Decision: Manual HR Run Checks For Periodic Notifications

**Rationale**: Clarification selected a manual HR Admin button instead of cron. One POST action can run missing-feedback reminders and offer-expiry checks in a predictable, testable page request.

**Alternatives considered**: Cron or queue workers were rejected because they add operational complexity outside the Vanilla PHP academic scope. Running checks on every page load was rejected because it can create expensive and surprising writes.

## Decision: Reports Are Derived Read-Only Queries

**Rationale**: Pipeline counts and time-to-hire summaries are aggregate views over `applications`, `job_requisitions`, `departments`, and `application_status_histories`. Persisting report rows would introduce synchronization rules that are not needed for a read-only HR report page.

**Alternatives considered**: Materialized report tables were rejected as unnecessary for up to 50 open requisitions. Export files were rejected because the spec asks for page views, not data export.

## Decision: Time-To-Hire Uses HIRED Status History Timestamp

**Rationale**: Average days from `applications.applied_at` to the first `application_status_histories.new_status = 'HIRED'` record is auditable and independent of current application update timestamps. Grouping by requisition and department satisfies the requested summary.

**Alternatives considered**: Using `applications.updated_at` was rejected because unrelated edits can change it. Using offer acceptance timestamps alone was rejected because the application status history is the canonical pipeline transition record.

## Decision: Consolidated Audit Log Is A Unioned Read Model

**Rationale**: Existing audit and status-history tables already capture account, interview, post-offer, application status, and job status changes. A repository can normalize them into timestamp, actor, entity type, action, target, and changed-field summary columns for display with filters and pagination.

**Alternatives considered**: Copying all audit records into a new table was rejected because it duplicates immutable evidence. Separate audit pages were rejected because the spec requires one consolidated view.

## Decision: Retention Threshold Is PHP Configuration

**Rationale**: The spec assumes a default 365-day threshold and no database settings table. A PHP constant/config value keeps this slice small while still allowing controlled adjustment.

**Alternatives considered**: Admin-editable settings were rejected because they require settings persistence, validation, and audit scope not requested. Hard-coding the number inside SQL was rejected because it makes later adjustment harder.

## Decision: Deletion Audit Requires Durable Snapshot Data

**Rationale**: Candidate deletion conflicts with immutable audit expectations if audit tables cascade-delete rows tied to the deleted user. The implementation should preserve retention audit evidence by allowing nullable deleted targets or storing a snapshot of former candidate identifiers/details in audit JSON before deletion.

**Alternatives considered**: Logging deletion only before hard-delete was rejected because cascading foreign keys could remove the audit row. Blocking deletion entirely was rejected because the spec explicitly requires a delete action.

## Decision: Data Retention Requires Terminal Eligibility Recheck

**Rationale**: Eligibility must be recalculated inside the POST action before anonymization or deletion. This prevents stale list submissions from affecting candidates with active applications.

**Alternatives considered**: Trusting the page list was rejected because application status can change between page render and form submission. Client-side-only confirmation was rejected because server-side authorization and validation are mandatory.
