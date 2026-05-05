# Feature Specification: Notifications, Reports & Compliance

**Feature Branch**: `007-notifications-reports-compliance`  
**Created**: 2026-05-05  
**Status**: Draft  
**Input**: User description: "Build notifications, recruitment reports, audit logs, and basic compliance features. The system sends in-app notifications for missing feedback, offer expiry, and application status changes. HR admins can view pipeline reports, time-to-hire summaries, audit history, and candidate data retention actions."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/document.md` §F (System Administration & Compliance, functions 36–42); `Diagrams/Database/schema.sql` (`notifications`, `account_audit_records`, `interview_audit_records`, `post_offer_audit_records`, `application_status_histories`, `job_requisition_status_histories` tables); `database/schema.sql` (live schema with audit tables)
- **SRS / Use Case IDs**: Function 36 (RBAC — already delivered), Function 37 (Data Retention & Privacy), Function 38 (Diversity & Inclusion Audit Reporter — out of scope for this slice), Function 39 (System Audit Trail), Function 42 (Automated Notification Escalator); Pipeline Throughput Analytics (Function 6)
- **Baseline Entities**: `notifications`, `account_audit_records`, `interview_audit_records`, `post_offer_audit_records`, `application_status_histories`, `job_requisition_status_histories`, `applications`, `interviews`, `interview_feedback`, `offers`, `candidates`
- **Baseline Workflow**: Notification creation triggered by status-change events; HR Admin reviews audit history and pipeline analytics; Data retention actions allow anonymization or deletion of candidate data
- **Scope Decision**: Matches baseline functions 6, 37, 39, and 42. Function 38 (Diversity & Inclusion Audit Reporter) is deferred to a future slice. Function 40 (Template Versioning) and 41 (Database Integrity Manager) are out of scope.

## Clarifications

### Session 2026-05-05

- Q: Should the `notifications` table include a polymorphic reference (`reference_id` + `reference_type`) to the entity that triggered the notification? → A: Yes — add `reference_id BIGINT UNSIGNED NULL` + `reference_type VARCHAR(40) NULL` columns to enable reliable deduplication and future entity linking.
- Q: What is the notification UI pattern (dropdown panel, dedicated page, or both)? → A: Header badge with unread count linking to a single dedicated notifications page (no dropdown).
- Q: How should the periodic notification checks (feedback reminders, offer expiry) be triggered? → A: HR Admin clicks a "Run Checks" button on the HR dashboard (manual trigger only).

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates.
- **Routing**: Web routes and form submissions only; no REST API contract.
- **Data Access**: MySQL through PDO, plain SQL schema files, and model/repository classes.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and authorization policies.

## User Scenarios & Testing *(mandatory)*

### User Story 1 – Candidate Receives Application Status Notifications (Priority: P1)

A candidate who has submitted one or more job applications receives an in-app notification whenever the status of any of their applications changes (e.g., from APPLIED → SCREENING, SCREENING → ASSESSMENT, INTERVIEW → OFFER, or any transition to REJECTED). The candidate sees an unread notification count badge in the page header on every page, which links to a dedicated notifications page where they can read messages and mark individual notifications as read.

**Why this priority**: Status-change notifications are the highest-value user-facing feature because they keep candidates informed without requiring them to manually check each application. This drives engagement and trust in the platform.

**Independent Test**: Log in as a candidate, have an HR Admin change the candidate's application status, verify a notification appears with the correct title and message, mark it as read, and confirm the unread count updates.

**Acceptance Scenarios**:

1. **Given** a candidate has an active application with status APPLIED, **When** an HR Admin advances the application to SCREENING, **Then** a new notification is created for the candidate with title "Application Status Updated" and a message referencing the job title and new status.
2. **Given** a candidate has three unread notifications, **When** the candidate opens the notifications page and clicks "Mark as read" on one notification, **Then** the unread count decreases to two and the notification displays a read timestamp.
3. **Given** a candidate has zero notifications, **When** the candidate views the notifications page, **Then** the page displays a "No notifications" empty state.

---

### User Story 2 – Interviewer Receives Missing Feedback Reminder (Priority: P2)

An interviewer who was assigned to a completed interview but has not yet submitted feedback receives an in-app notification reminding them to provide their feedback. The system creates reminder notifications for any interview that transitioned to COMPLETED status more than 24 hours ago without a corresponding feedback record from the assigned interviewer.

**Why this priority**: Missing feedback delays the entire pipeline and blocks final evaluations. Automated reminders reduce HR's manual follow-up effort and keep the hiring cycle moving.

**Independent Test**: Complete an interview without submitting feedback, wait (or simulate) 24 hours passing, have an HR Admin click the "Run Checks" button on the HR dashboard, and verify a notification is created for the assigned interviewer.

**Acceptance Scenarios**:

1. **Given** an interview is marked COMPLETED and 24 hours have passed, **When** an HR Admin clicks "Run Checks" on the HR dashboard, **Then** each assigned interviewer who has not submitted feedback receives a notification with title "Feedback Reminder" and a message naming the candidate and interview date.
2. **Given** an interviewer has already submitted feedback for an interview, **When** the HR Admin clicks "Run Checks", **Then** no duplicate reminder notification is created for that interviewer.
3. **Given** an interview is still SCHEDULED (not completed), **When** the HR Admin clicks "Run Checks", **Then** no reminder is generated.

---

### User Story 3 – HR Admin Receives Offer Expiry Alerts (Priority: P2)

An HR Admin receives an in-app notification when an offer is approaching its expiry date (within 48 hours) and another notification when an offer has actually expired without being accepted. This allows the HR team to follow up with candidates or revise offers before they lapse.

**Why this priority**: Expired offers waste recruitment effort and can lead to lost candidates. Proactive alerts allow HR to take action before the deadline.

**Independent Test**: Create an offer with an expiry date 47 hours from now, click "Run Checks" on the HR dashboard, and verify a "nearing expiry" notification is created for the HR Admin who created the offer.

**Acceptance Scenarios**:

1. **Given** an offer has status SENT and its expiry_date is within 48 hours, **When** an HR Admin clicks "Run Checks" on the HR dashboard, **Then** the HR Admin who created the offer receives a notification titled "Offer Expiring Soon" with the candidate name and expiry date.
2. **Given** an offer has status SENT and its expiry_date has passed, **When** an HR Admin clicks "Run Checks", **Then** the offer status is updated to EXPIRED and the HR Admin receives a notification titled "Offer Expired."
3. **Given** an offer has already been accepted, **When** the HR Admin clicks "Run Checks", **Then** no expiry notification is generated.

---

### User Story 4 – HR Admin Views Recruitment Pipeline Report (Priority: P3)

An HR Admin navigates to a Reports section and views a recruitment pipeline report showing, for each open job requisition, the count of applications at each pipeline stage (APPLIED, SCREENING, ASSESSMENT, INTERVIEW, OFFER, HIRED, REJECTED). The report also shows aggregate totals across all requisitions.

**Why this priority**: Pipeline visibility is essential for HR to identify bottlenecks and allocate resources, but the system is functional without it — notifications and audit logs deliver more immediate operational value.

**Independent Test**: Log in as HR Admin, navigate to the pipeline report page, verify that stage counts match the actual data in the applications table for each open job requisition.

**Acceptance Scenarios**:

1. **Given** there are three open job requisitions with applications in various stages, **When** the HR Admin opens the pipeline report, **Then** each requisition row shows accurate counts per status stage and a total row aggregates all requisitions.
2. **Given** a requisition has zero applications, **When** the HR Admin views the pipeline report, **Then** that requisition row displays zero for every stage.

---

### User Story 5 – HR Admin Views Time-to-Hire Summary (Priority: P3)

An HR Admin navigates to the Reports section and views a time-to-hire summary that calculates the average number of days from application submission (applied_at) to HIRED status for each job requisition and department. This helps identify slow-moving requisitions and departmental bottlenecks.

**Why this priority**: Time-to-hire is the canonical recruitment efficiency metric and directly supports Function 6 (Pipeline Throughput Analytics) from the baseline, but it is analytical and non-blocking.

**Independent Test**: Create test applications with known applied_at and hired dates, navigate to the time-to-hire page, and verify the calculated averages match expected values.

**Acceptance Scenarios**:

1. **Given** three candidates were hired for the same job with time-to-hire of 10, 20, and 30 days, **When** the HR Admin views the time-to-hire summary, **Then** the average for that requisition shows 20 days.
2. **Given** no candidates have been hired for a requisition, **When** the HR Admin views the time-to-hire summary, **Then** that requisition shows "N/A" or "—" for average time-to-hire.

---

### User Story 6 – HR Admin Views Audit History (Priority: P3)

An HR Admin navigates to an Audit Log page that consolidates audit records from account changes, interview changes, and post-offer changes into a single chronological view. The HR Admin can filter by date range, actor (who performed the action), action type, and affected entity. Each audit entry shows the timestamp, actor name, action, and a summary of changed fields.

**Why this priority**: Audit trail visibility satisfies compliance requirements (Function 39) and supports accountability, but the underlying audit records are already being written by existing features — this story adds the viewing interface.

**Independent Test**: Log in as HR Admin, perform an auditable action (e.g., change an interview status), navigate to the audit log, and verify the action appears with correct actor, timestamp, and changed fields.

**Acceptance Scenarios**:

1. **Given** multiple audit records exist across account, interview, and post-offer audit tables, **When** the HR Admin opens the audit log page, **Then** all records appear in reverse-chronological order with pagination (25 per page).
2. **Given** the HR Admin filters by actor "admin@srim.test" and date range "2026-04-01 to 2026-04-30", **When** the filter is applied, **Then** only matching records are displayed.
3. **Given** an audit record contains changed_fields JSON, **When** it is displayed, **Then** the changed fields are rendered as a human-readable summary (e.g., "status: SCHEDULED → COMPLETED").

---

### User Story 7 – HR Admin Performs Candidate Data Retention Actions (Priority: P4)

An HR Admin navigates to a Candidate Data Retention page that lists candidates whose applications are older than a configurable retention threshold (default: 365 days) and whose most recent application status is REJECTED or the requisition is CLOSED. The HR Admin can select candidates and perform anonymization (replace PII with placeholder values) or deletion (hard-delete candidate record and cascade). A confirmation dialog prevents accidental actions, and all retention actions are logged in the audit trail.

**Why this priority**: Data retention supports compliance with privacy requirements (Function 37) and the SRS nonfunctional requirement for GDPR-style data handling, but it is a back-office administrative function used infrequently.

**Independent Test**: Create a candidate with an application older than 365 days in REJECTED status, navigate to the data retention page, select the candidate, perform anonymization, and verify PII fields are replaced and an audit record is created.

**Acceptance Scenarios**:

1. **Given** a candidate applied 400 days ago and their application status is REJECTED, **When** the HR Admin opens the data retention page, **Then** the candidate appears in the eligible list with their name, email, last application date, and status.
2. **Given** the HR Admin selects a candidate and clicks "Anonymize", **When** confirmed, **Then** the candidate's name is replaced with "Anonymized Candidate", email with a unique hash, phone with "REDACTED", resume_url is cleared, and an audit record is created with action "CANDIDATE_ANONYMIZED".
3. **Given** the HR Admin selects a candidate and clicks "Delete", **When** confirmed, **Then** the candidate record and all related applications, assessments, and feedback are cascade-deleted, and an audit record is created with action "CANDIDATE_DELETED" (logged against the former user_id).
4. **Given** a candidate has an application less than 365 days old, **When** the HR Admin views the data retention page, **Then** that candidate does not appear in the eligible list.

---

### Edge Cases

- What happens when an HR Admin tries to anonymize or delete a candidate who has an active (non-closed, non-rejected) application? The system MUST block the action and display an error.
- What happens when the notification table grows very large? The notifications panel MUST paginate and the index on `(user_id, is_read)` ensures performant queries.
- What happens when an authenticated interviewer tries to access the reports or audit pages? The system MUST deny access and redirect to the interviewer dashboard with an "Unauthorized" flash message.
- What happens when a candidate tries to access another candidate's notifications? The system MUST enforce user-scoped queries so candidates only see their own notifications.
- How does the system handle the reminder check for missing feedback when the interview was just completed (less than 24 hours ago)? The system MUST not generate a reminder until 24 hours have elapsed.
- What happens if the same offer-expiry or feedback-reminder notification would be generated twice (e.g., the check runs multiple times)? The system MUST use a deduplication mechanism (e.g., check for existing notification with the same type and reference entity before creating a new one) to prevent duplicate notifications.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST create an in-app notification record in the `notifications` table whenever an application's status changes, addressed to the candidate who owns the application.
- **FR-002**: System MUST create an in-app reminder notification for each assigned interviewer who has not submitted feedback within 24 hours of an interview being marked COMPLETED.
- **FR-003**: System MUST create an in-app notification for the HR Admin who created an offer when that offer is within 48 hours of expiry (status SENT) or has passed its expiry date.
- **FR-004**: System MUST auto-transition offer status from SENT to EXPIRED when the expiry_date has passed and the offer has not been accepted or rejected.
- **FR-005**: System MUST display an unread notification count badge in the page header for all authenticated users, linking to the dedicated notifications page.
- **FR-006**: System MUST provide a dedicated notifications page (no dropdown panel) where users can view all their notifications in reverse-chronological order, mark individual items as read, and mark all as read.
- **FR-007**: System MUST provide a pipeline report page (HR Admin only) showing application counts by status for each open job requisition.
- **FR-008**: System MUST provide a time-to-hire summary page (HR Admin only) showing average days-to-hire per requisition and per department.
- **FR-009**: System MUST provide a consolidated audit log page (HR Admin only) combining records from `account_audit_records`, `interview_audit_records`, and `post_offer_audit_records` with filters for date range, actor, action type, and pagination.
- **FR-010**: System MUST provide a candidate data retention page (HR Admin only) listing candidates eligible for anonymization or deletion based on a configurable retention threshold.
- **FR-011**: System MUST anonymize candidate PII (name → "Anonymized Candidate", email → hashed placeholder, phone → "REDACTED", resume_url → NULL, skill_keywords → NULL) when the HR Admin confirms an anonymization action.
- **FR-012**: System MUST cascade-delete a candidate and all related records when the HR Admin confirms a deletion action.
- **FR-013**: System MUST log all data retention actions (anonymization and deletion) to the `account_audit_records` table with the acting HR Admin as actor.
- **FR-014**: System MUST prevent anonymization or deletion of candidates who have any application in a non-terminal status (i.e., not REJECTED and the associated requisition is not CLOSED).
- **FR-015**: System MUST deduplicate notifications by checking for an existing record matching the same `user_id`, `type`, `reference_id`, and `reference_type` before creating a new notification.
- **FR-016**: System MUST provide a "Run Checks" button on the HR Admin dashboard that triggers feedback-reminder and offer-expiry notification checks in a single action.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST include viewing all reports (pipeline, time-to-hire), audit logs, data retention actions, and their own notifications.
- **RP-002**: Technical Interviewer access MUST be limited to viewing their own notifications (including feedback reminders). Interviewers MUST NOT access reports, audit logs, or data retention pages.
- **RP-003**: Candidate access MUST be limited to viewing their own notifications. Candidates MUST NOT access any report, audit, or data retention pages.
- **RP-004**: All notification queries MUST be scoped to the authenticated user's user_id to prevent cross-user data leakage.
- **RP-005**: Candidate PII displayed on the data retention page MUST only be visible to HR Admins. The retention page MUST be protected by an HR_ADMIN role check.
- **RP-006**: Audit log entries MUST be immutable — no update or delete operations are permitted on audit tables through the application interface.

### Key Entities *(include if feature involves data)*

- **Notification**: An in-app message addressed to a specific user. Key attributes: user_id, title, message, type (e.g., STATUS_CHANGE, FEEDBACK_REMINDER, OFFER_EXPIRY), reference_id (nullable, points to the triggering entity), reference_type (nullable, e.g., APPLICATION, INTERVIEW, OFFER), is_read, read_at. Extends the baseline ERD schema with two polymorphic reference columns for deduplication and entity linking.
- **Audit Record (consolidated view)**: A virtual entity combining account_audit_records, interview_audit_records, and post_offer_audit_records for unified display. Key attributes: timestamp, actor name, action, entity type, changed fields summary.
- **Pipeline Report (derived)**: An aggregated view computed from the applications table, grouping by job_id and status. Not a persisted entity.
- **Time-to-Hire Summary (derived)**: An aggregated calculation from applications where status = HIRED, computing the difference between applied_at and the timestamp of the HIRED status transition. Not a persisted entity.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Candidates receive an in-app notification within the same page load cycle (or next page load) after their application status is changed by an HR Admin.
- **SC-002**: 100% of interviewers with missing feedback for interviews completed more than 24 hours ago receive a reminder notification when the reminder process executes.
- **SC-003**: HR Admins receive an offer-expiry alert for every SENT offer within 48 hours of expiry when the expiry check process runs.
- **SC-004**: The pipeline report page loads and displays accurate data for up to 50 open requisitions within 3 seconds.
- **SC-005**: The time-to-hire summary correctly calculates averages consistent with manual verification against raw application data.
- **SC-006**: The audit log page displays up to 25 records per page with filters applied, loading within 3 seconds.
- **SC-007**: HR Admins can anonymize a candidate's data in under 5 clicks from the data retention page, and the anonymization is irreversible and complete.
- **SC-008**: No candidate can view notifications belonging to another user — verified by attempting direct URL manipulation.
- **SC-009**: No interviewer or candidate can access report, audit, or data retention pages — verified by attempting direct URL access.

## Assumptions

- The `notifications` table defined in the baseline ERD (`Diagrams/Database/schema.sql`) will be added to the live `database/schema.sql` as part of this feature's implementation. The current live schema does not include it.
- Notification trigger logic for status-change notifications will be implemented inline within existing controller actions (e.g., the controller that updates application status also inserts a notification record). Periodic checks (feedback reminders, offer expiry) are triggered manually by an HR Admin clicking a "Run Checks" button on the HR dashboard — no background job queue or cron is required.
- The existing audit tables (`account_audit_records`, `interview_audit_records`, `post_offer_audit_records`) are already being populated by previous features. This feature only adds the viewing/filtering UI.
- The configurable retention threshold (365 days default) will be stored as a PHP constant or configuration value, not in a database settings table.
- Email notifications are out of scope — only in-app notifications are delivered.
- The notifications unread count badge will be computed on every page load via a lightweight `SELECT COUNT(*)` query using the existing index.
- Anonymization replaces PII with non-reversible placeholder values; it does not delete the user record, preserving referential integrity in audit logs and application history.
