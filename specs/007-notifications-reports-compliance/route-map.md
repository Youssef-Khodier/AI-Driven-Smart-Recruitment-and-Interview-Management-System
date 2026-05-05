# Route Map: Notifications, Reports & Compliance

## Authenticated Notifications

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/notifications` | `notifications.index` | `NotificationController::index` | View owned notifications |
| POST | `/notifications/{id}/read` | `notifications.read` | `NotificationController::markRead` | Mark one owned notification as read |
| POST | `/notifications/read-all` | `notifications.read-all` | `NotificationController::markAllRead` | Mark all owned notifications as read |

## HR Manual Checks

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| POST | `/hr/checks/run` | `hr.checks.run` | `HrComplianceCheckController::run` | Run missing-feedback and offer-expiry checks |

## HR Reports

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/hr/reports/pipeline` | `hr.reports.pipeline` | `HrReportController::pipeline` | View application counts by stage |
| GET | `/hr/reports/time-to-hire` | `hr.reports.time-to-hire` | `HrReportController::timeToHire` | View average days-to-hire summaries |

## HR Audit Log

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/hr/audit-log` | `hr.audit-log.index` | `HrAuditLogController::index` | View filtered consolidated audit history |

## HR Data Retention

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/hr/data-retention` | `hr.data-retention.index` | `HrDataRetentionController::index` | List candidates eligible for retention action |
| POST | `/hr/data-retention/{candidate}/anonymize` | `hr.data-retention.anonymize` | `HrDataRetentionController::anonymize` | Anonymize eligible candidate PII |
| POST | `/hr/data-retention/{candidate}/delete` | `hr.data-retention.delete` | `HrDataRetentionController::delete` | Delete eligible candidate and related data |

## Navigation Entry Points

- `views/layouts/app.php` should show the unread notification count and link to `/notifications` for authenticated users.
- `views/hr/dashboard.php` should include the Run Checks form for HR Admins.
- HR navigation should link to pipeline report, time-to-hire report, audit log, and data retention pages.
- Candidate and interviewer navigation should only expose the notifications page, not HR reports/audit/retention.

## Guard Expectations

- Notification routes require an active authenticated user and notification ownership for item actions.
- HR check, report, audit, and retention routes require active HR Admin role.
- Mutating routes require CSRF validation.
- Candidate and interviewer direct access attempts to HR pages must be denied.
