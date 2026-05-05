# Web Workflow Contracts: Notifications, Reports & Compliance

These are server-rendered page/form contracts for the Vanilla PHP monolith. They are not REST API contracts.

## Authenticated Notifications

**GET `/notifications`**

- Actor: any active authenticated user.
- Renders: dedicated notification list in reverse chronological order.
- Shows: title, message, type label, created timestamp, read state, read timestamp when present, pagination controls, empty state.
- Guard: user must be authenticated.
- Privacy: query must filter by `notifications.user_id = current_user_id`.

**POST `/notifications/{id}/read`**

- Actor: notification owner.
- Input: CSRF token.
- Behavior: marks one owned notification as read and redirects back.
- Validation: notification must belong to current user.

**POST `/notifications/read-all`**

- Actor: any active authenticated user.
- Input: CSRF token.
- Behavior: marks all unread notifications owned by current user as read and redirects back.

## Header Badge

**Layout contract**

- Actor: any active authenticated user.
- Behavior: every authenticated server-rendered page displays unread count and links to `/notifications`.
- Privacy: count query is scoped to current user.
- Empty state: count may show `0` or hide badge count, but link remains available.

## HR Manual Checks

**POST `/hr/checks/run`**

- Actor: active HR Admin.
- Input: CSRF token.
- Behavior: runs feedback-reminder and offer-expiry checks in one request.
- Output: redirect to HR dashboard with flash summary counts for reminders created, expiring-offer notifications created, and offers expired.
- Guard: non-HR users are denied and redirected/errored according to existing app pattern.
- Idempotency: repeated submissions must not create duplicate notifications for the same user/type/reference.

## HR Pipeline Report

**GET `/hr/reports/pipeline`**

- Actor: active HR Admin.
- Renders: application counts by status per open requisition plus aggregate totals.
- Filters: none required for this slice.
- Empty state: open requisitions with zero applications display zero counts.
- Guard: HR Admin only.

## HR Time-To-Hire Report

**GET `/hr/reports/time-to-hire`**

- Actor: active HR Admin.
- Renders: average days-to-hire by requisition and by department.
- Source: `applications.applied_at` to first `application_status_histories.new_status = 'HIRED'` timestamp.
- Empty state: show `N/A` where no hired applications exist.
- Guard: HR Admin only.

## HR Consolidated Audit Log

**GET `/hr/audit-log`**

- Actor: active HR Admin.
- Query inputs: `from`, `to`, `actor`, `action`, `entity`, `page`.
- Renders: 25 records per page, reverse chronological order.
- Shows: timestamp, actor name/email, entity type, action, affected identifier, changed-fields summary.
- Validation: invalid dates are ignored or surfaced as validation messages without exposing SQL errors.
- Guard: HR Admin only.
- Mutation: no update/delete actions are available from this page.

## HR Data Retention

**GET `/hr/data-retention`**

- Actor: active HR Admin.
- Renders: candidates eligible under the configured retention threshold.
- Shows: candidate name, email, last application date, terminal/closed status context, and action controls.
- Privacy: candidate PII is visible only on this HR-only page.

**POST `/hr/data-retention/{candidate}/anonymize`**

- Actor: active HR Admin.
- Input: CSRF token and confirmation value.
- Behavior: rechecks eligibility, anonymizes candidate PII, writes audit action `CANDIDATE_ANONYMIZED`, redirects with flash message.
- Failure: active/ineligible candidate is blocked and no PII is modified.

**POST `/hr/data-retention/{candidate}/delete`**

- Actor: active HR Admin.
- Input: CSRF token and confirmation value.
- Behavior: rechecks eligibility, writes durable deletion audit snapshot, deletes candidate record and related candidate-owned data according to schema constraints, redirects with flash message.
- Failure: active/ineligible candidate is blocked and no rows are deleted.
