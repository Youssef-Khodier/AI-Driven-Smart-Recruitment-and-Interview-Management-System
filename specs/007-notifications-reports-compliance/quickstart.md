# Quickstart: Notifications, Reports & Compliance

## Prerequisites

- PHP 8.2+ available on the command line.
- Composer dependencies installed if the local environment uses Composer scripts.
- MySQL database configured for SRIM.
- Existing data includes at least one candidate application, one completed interview assignment, and one sent offer for full manual coverage.

## Setup

1. Review the specification and plan:

   ```bash
   # Read these files in your editor
   specs/007-notifications-reports-compliance/spec.md
   specs/007-notifications-reports-compliance/plan.md
   ```

2. Apply schema changes after implementation tasks add them:

   ```bash
   composer run db:schema
   composer run db:seed
   ```

3. Run project checks:

   ```bash
   composer test
   ```

## Manual Demo Flow

### Candidate Receives Status Notification

1. Log in as HR Admin.
2. Change a candidate application status.
3. Log in as that candidate.
4. Verify the header unread badge increased.
5. Open `/notifications` and verify the status-change message references the job and new status.
6. Mark the item read and verify the unread count decreases.

### HR Runs Manual Checks

1. Prepare a completed interview older than 24 hours with an assigned interviewer and no feedback from that interviewer.
2. Prepare a sent offer expiring within 48 hours and another sent offer past expiry.
3. Log in as HR Admin and click Run Checks on the HR dashboard.
4. Verify flash summary counts.
5. Log in as the interviewer and verify a feedback reminder notification exists.
6. Verify the HR offer creator receives expiring/expired offer notifications and the past-due offer becomes `EXPIRED`.

### HR Views Reports

1. Log in as HR Admin.
2. Open `/hr/reports/pipeline`.
3. Verify counts by application status match the database/application list.
4. Open `/hr/reports/time-to-hire`.
5. Verify hired applications show average days from `applied_at` to the `HIRED` status-history timestamp.

### HR Views Audit Log

1. Perform an auditable action such as interview completion, offer send, or application status change.
2. Open `/hr/audit-log` as HR Admin.
3. Filter by actor, action, entity, or date range.
4. Verify the matching record appears with readable changed-field summary and pagination.

### HR Performs Data Retention

1. Prepare a candidate whose most recent application is older than 365 days and terminal or tied to a closed requisition.
2. Open `/hr/data-retention` as HR Admin.
3. Verify the candidate appears in the eligible list.
4. Anonymize one candidate and verify PII fields are redacted plus an audit record is written.
5. Delete another eligible candidate and verify active/ineligible candidates are blocked.

## Required Evidence Before Completion

- `composer test` passes or failures are documented with cause.
- Notification ownership checks deny access to another user's notifications.
- Duplicate Run Checks do not create duplicate feedback or offer notifications.
- Candidate/interviewer direct access to HR reports, audit log, and retention pages is denied.
- Pipeline and time-to-hire reports match manually checked database data.
- Audit log displays immutable records and does not expose edit/delete controls.
- Retention anonymization and deletion are confirmation-protected, eligibility-checked, and auditable.

## Validation Evidence

Validation completed on 2026-05-05 after loading `database/schema.sql` with `composer run db:schema` and base seed data with `composer run db:seed`.

The executable scenario validation was run with:

```bash
php scripts/validate_feature_007.php
```

Result:

```text
Feature 007 validation passed.
Scenario logins: HR hr.admin@example.com / password; Interviewer feature007.interviewer.20260505190718@example.com / password; Candidate feature007.candidate.20260505190718@example.com / password.
```

Covered evidence:

- Candidate, interviewer, and HR role login checks passed via `Auth::attempt`.
- Candidate status notification was created, unread count changed, mark-read changed read state, and notification ownership denied another user.
- Missing feedback reminder query found a completed interview older than 24 hours, created one reminder, and deduplication blocked a duplicate reminder.
- Offer expiry checks found a sent offer expiring within 48 hours and a past-due sent offer; the past-due offer transitioned to `EXPIRED` and wrote audit evidence.
- Pipeline report returned the open requisition and status counts.
- Time-to-hire report returned requisition/department averages using `HIRED` status history timestamps and supports no-hire rows as `N/A`.
- Audit log returned the offer-expiry audit entry and denies non-HR access through policy checks.
- Data retention anonymization redacted name, email, phone, resume URL, and skill keywords; deletion removed the candidate and preserved a deletion audit record.
