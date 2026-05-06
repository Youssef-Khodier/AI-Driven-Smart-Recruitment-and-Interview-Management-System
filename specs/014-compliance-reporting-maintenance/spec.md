# Feature Specification: Compliance Reporting Maintenance

**Feature Branch**: `014-compliance-reporting-maintenance`  
**Created**: 2026-05-06  
**Status**: Draft  
**Input**: User description: "Complete compliance reporting and operational maintenance in server-rendered Vanilla PHP. HR admins can view enhanced pipeline throughput analytics with bottleneck detection, diversity and inclusion audit reports from optional candidate demographic fields, database integrity archive actions for closed requisitions and rejected candidates, and notification escalations for missing feedback, offer expiry, background checks, and onboarding tasks. Automated behavior must be implemented as HR-triggered Run Checks unless scheduler scope is explicitly approved."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/document.md` design functions 6, 37, 38, 39, 41, and 42; `Diagrams/SRS/SRS-SRIM final ver1.docx` compliance and reporting baseline; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 4.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Acrivity Diagram/Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`.
- **SRS / Use Case IDs**: Pipeline Throughput Analytics; Data Retention and Right to be Forgotten; Diversity and Inclusion Audit Reporter; System Audit Trail; Database Integrity Manager; Automated Notification Escalator; related Offer Validity Timer, Background Check Integration (Simulated), and Pre-Onboarding Welcome Portal flows.
- **Baseline Entities**: `users`, `departments`, `candidates`, `job_requisitions`, `applications`, `candidate_assessments`, `interviews`, `interviewers_assignment`, `interview_feedback`, `final_evaluations`, `offers`, `onboarding`, `notifications`, candidate merge/audit records where available, and new compliance maintenance records required to track run checks, archive decisions, aggregate demographic reporting, and escalation outcomes.
- **Baseline Workflow**: Extends the end-to-end recruitment flow from application to rejection, offer, and onboarding; the RBAC dashboard flow for HR Admin and Interviewer access; the feedback finalization flow; and the offer validity/manual HR follow-up flow.
- **Scope Decision**: Matches baseline compliance and administration scope with one explicit delivery constraint: reminders, expiry detection, archive identification, background-check follow-up, and onboarding follow-up are performed only when an HR Admin runs checks manually. Scheduler, background worker, external email delivery, and external compliance integrations remain out of scope unless separately approved.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates.
- **Routing**: Browser web routes and form submissions only; no REST API contract or separated frontend.
- **Data Access**: MySQL through PDO-backed repositories and plain SQL schema or migration files.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and explicit policies.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Review Pipeline Bottlenecks (Priority: P1)

An HR Admin views pipeline throughput analytics to understand how long candidates spend in each recruitment stage and which requisitions or stages are slowing hiring.

**Why this priority**: Pipeline throughput is the central operational report and gives HR immediate value without requiring demographic data, archive action, or escalations.

**Independent Test**: Can be tested by opening the HR analytics view with applications across multiple stages and verifying that stage counts, average age, time-to-hire, and bottleneck labels are shown for the selected date range.

**Acceptance Scenarios**:

1. **Given** an HR Admin is authenticated and candidate applications have stage history, **When** the admin filters analytics by requisition and date range, **Then** the report shows candidate counts, average time in stage, conversion rates, and time-to-hire for the selected scope.
2. **Given** one stage exceeds the configured bottleneck threshold, **When** the analytics report is viewed, **Then** that stage is clearly flagged as a bottleneck with the affected requisitions and candidate count.
3. **Given** no matching applications exist for the selected filters, **When** the report is viewed, **Then** the admin sees an empty-state message explaining that no throughput data is available for that scope.

---

### User Story 2 - Audit Diversity and Inclusion Metrics (Priority: P2)

An HR Admin reviews aggregate diversity and inclusion reports based on optional candidate demographic fields so the organization can monitor applicant and hiring patterns without exposing individual demographic details.

**Why this priority**: D&I reporting is a core compliance need but must be privacy-aware because demographic data is sensitive and optional.

**Independent Test**: Can be tested by adding candidates with provided, partially provided, and not-provided demographic values, then verifying aggregate report totals and suppression rules.

**Acceptance Scenarios**:

1. **Given** candidate demographic values are optional, **When** the D&I report is generated, **Then** the report includes aggregate categories and a distinct "Not provided" group without requiring candidates to disclose demographics.
2. **Given** an aggregate group has fewer than 3 candidates, **When** the report is displayed, **Then** the group value is suppressed or combined into a privacy-safe category while totals remain consistent.
3. **Given** a non-HR user attempts to view the D&I report, **When** access is requested, **Then** the report is denied and no demographic metrics are shown.

---

### User Story 3 - Run Operational Checks and Escalations (Priority: P3)

An HR Admin manually runs operational checks to find missing feedback, expiring or expired offers, simulated background-check delays, and overdue onboarding tasks, then creates in-system escalations for the responsible users.

**Why this priority**: The feature request explicitly requires HR-triggered Run Checks instead of scheduled automation, making this the control point for all maintenance and escalation behavior.

**Independent Test**: Can be tested by preparing overdue feedback, offer, background-check, and onboarding records, running checks as HR Admin, and verifying the resulting summary and notifications.

**Acceptance Scenarios**:

1. **Given** completed interviews have no submitted feedback after 24 hours, **When** an HR Admin runs operational checks, **Then** the check identifies each missing feedback item and creates an escalation notification for the assigned interviewer and HR Admin.
2. **Given** sent offers are within 24 hours of expiry or past their expiry date, **When** an HR Admin runs operational checks, **Then** the check flags the offers, creates follow-up notifications, and reports any status changes that need HR review.
3. **Given** simulated background checks or onboarding tasks are overdue, **When** an HR Admin runs operational checks, **Then** the check lists the affected candidates, due dates, current status, and escalation recipients.
4. **Given** the same HR Admin runs checks twice without new overdue records, **When** the second run completes, **Then** duplicate open escalations are not created and the run summary states that existing escalations were already active.

---

### User Story 4 - Archive Closed and Rejected Records (Priority: P4)

An HR Admin reviews database integrity recommendations and archives closed requisitions and rejected candidate applications so active workspaces stay focused while compliance history remains retained and auditable.

**Why this priority**: Archive actions improve operational hygiene but must follow reporting, privacy, and run-check controls to avoid accidental data loss.

**Independent Test**: Can be tested by closing requisitions and rejecting candidates, running integrity checks, approving archive actions, and verifying active lists exclude archived records while audit history remains available to authorized HR users.

**Acceptance Scenarios**:

1. **Given** a requisition is closed and all related applications are in terminal states, **When** an HR Admin runs integrity checks, **Then** the requisition is listed as eligible for archive with counts of related applications, offers, and onboarding records.
2. **Given** a rejected candidate application has no pending assessment, interview, feedback, offer, or onboarding action, **When** an HR Admin approves archive, **Then** the application is marked archived, removed from active queues, and retained in archive views.
3. **Given** a record still has unresolved operational work, **When** the HR Admin attempts to archive it, **Then** the archive action is blocked with a clear reason and no active data is hidden.

### Edge Cases

- If candidate demographic values are missing, blank, invalid, or withdrawn, reports must classify them as "Not provided" and must not block candidate progress.
- If a report filter combines date ranges, requisitions, or departments with no data, the report must show an empty state instead of misleading zero-valued metrics.
- If a user outside HR Admin attempts to view compliance reports, run checks, approve archives, or view archived sensitive data, access must be denied and no sensitive data must be shown.
- If an HR Admin submits an invalid date range, unknown requisition, duplicate archive request, or stale run-check approval, the system must reject the request with a clear validation message.
- If a check finds records that have already been escalated and remain unresolved, the system must show the existing escalation status instead of creating duplicates.
- If archive eligibility changes between review and approval, the system must revalidate eligibility before applying the archive action.
- If a candidate invokes privacy or erasure handling, active reports must keep only compliant aggregate counts and archive views must hide or anonymize candidate-identifying details according to retention rules.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide HR Admins with pipeline throughput analytics that include candidate counts by stage, conversion rates between stages, average time in stage, and time-to-hire for selectable date ranges and requisitions.
- **FR-002**: System MUST detect and label bottlenecks when a recruitment stage exceeds the stage-age threshold or contains an unusually high share of active candidates for the selected scope.
- **FR-003**: System MUST allow HR Admins to generate D&I audit reports using optional candidate demographic fields, grouped by recruitment stage and outcome where data volume allows safe aggregation.
- **FR-004**: System MUST treat demographic fields as optional and MUST include non-disclosure in reports as "Not provided" rather than excluding candidates from totals.
- **FR-005**: System MUST suppress or combine D&I report groups with fewer than 3 candidates to reduce re-identification risk.
- **FR-006**: System MUST provide HR-triggered Run Checks for operational maintenance and MUST NOT perform scheduler-driven or background-worker-driven maintenance unless separately approved.
- **FR-007**: System MUST let HR Admins run checks for missing interview feedback, offer expiry follow-up, simulated background-check delays, overdue onboarding tasks, closed requisition archive eligibility, and rejected candidate archive eligibility.
- **FR-008**: System MUST show each Run Check result with counts, affected records, responsible users, recommended actions, skipped records, duplicate escalation prevention, and the time and HR Admin who ran it.
- **FR-009**: System MUST create in-system escalation notifications for responsible users and HR Admins when a Run Check identifies missing feedback, expiring or expired offers, delayed simulated background checks, or overdue onboarding tasks.
- **FR-010**: System MUST prevent duplicate open escalation notifications for the same unresolved issue and MUST show existing escalation status in the Run Check summary.
- **FR-011**: System MUST allow HR Admins to review and approve archive actions for closed requisitions and rejected candidate applications only after eligibility is revalidated.
- **FR-012**: System MUST preserve archive history, reason, actor, timestamp, and affected-record summary for every archive action.
- **FR-013**: System MUST remove archived requisitions and rejected candidate applications from active operational queues while retaining them in authorized archive views and aggregate reports.
- **FR-014**: System MUST block archive actions when related assessments, interviews, feedback, offers, background checks, onboarding tasks, or appeals are still pending.
- **FR-015**: System MUST record audit-relevant events for report generation, Run Check execution, escalation creation, archive approval, archive block, and sensitive report access denial.
- **FR-016**: System MUST validate report filters, run-check selections, archive approval inputs, and demographic values before saving or displaying results.
- **FR-017**: System MUST present user-friendly messages for empty reports, blocked archive actions, denied access, invalid filters, and Run Checks with no findings.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST include compliance reports, Run Checks, archive recommendations, archive approval, escalation review, and authorized archive views.
- **RP-002**: Technical Interviewer access MUST be limited to assigned interview feedback obligations and escalation notifications for their own missing feedback; interviewers MUST NOT access aggregate D&I reports or archive actions.
- **RP-003**: Candidate access MUST be limited to their own profile, applications, assessments, offers, onboarding tasks, and optional demographic disclosure controls where available.
- **RP-004**: Junior Staff or observer access MUST NOT include D&I reports, Run Checks, archive approvals, or sensitive archive views unless explicitly granted read-only training access by HR policy.
- **RP-005**: Candidate PII, resumes, demographic fields, assessment results, interview feedback, final evaluations, offers, background-check status, onboarding details, and archive records MUST be hidden from unauthorized roles.
- **RP-006**: Demographic data MUST be optional, privacy-preserving in reports, excluded from individual hiring decisions in this feature, and never displayed as individual-level values in compliance reports.
- **RP-007**: Simulated background-check escalations MUST be labeled as simulated and reviewable by authorized HR Admins.

### Key Entities *(include if feature involves data)*

- **Pipeline Throughput Report**: Aggregate view of applications by requisition, department, stage, date range, stage duration, conversion rate, time-to-hire, and bottleneck status.
- **Candidate Demographic Field Set**: Optional candidate-provided demographic attributes used only for aggregate D&I reporting, with explicit non-disclosure handling.
- **D&I Audit Report**: Aggregate report grouping optional demographic data by application stage and outcome while applying privacy suppression rules.
- **Run Check Batch**: HR-triggered operational check execution with type, actor, execution time, selected scope, findings, skipped records, and summary counts.
- **Notification Escalation**: In-system reminder or alert tied to a missing feedback item, offer expiry issue, simulated background-check delay, or onboarding task, with recipient and resolution status.
- **Archive Candidate**: Closed requisition or rejected candidate application that may be archived after eligibility rules confirm there is no unresolved operational work.
- **Archive Action**: Approved or blocked maintenance action with reason, actor, timestamp, affected records, and audit history.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can identify the top 3 pipeline bottleneck stages for a selected requisition or date range in under 2 minutes.
- **SC-002**: At least 95% of report views with valid filters display throughput or D&I results, or a clear empty-state explanation, within 3 seconds for academic demo data.
- **SC-003**: Run Checks detect missing feedback, offer expiry follow-up, simulated background-check delays, and overdue onboarding tasks with zero duplicate open escalations for the same unresolved issue during repeated runs.
- **SC-004**: 100% of archive approvals preserve an audit record containing actor, timestamp, reason, affected record summary, and eligibility result.
- **SC-005**: 100% of D&I reports suppress groups with fewer than 3 candidates and keep non-disclosing candidates included in overall totals.
- **SC-006**: Unauthorized users are blocked from compliance reports, Run Checks, archive approvals, and sensitive archive views in 100% of access-control test attempts.
- **SC-007**: HR Admins can complete an operational Run Check review and act on findings for at least 25 flagged records in under 5 minutes during a manual demo.

## Assumptions

- Existing authentication, HR Admin role controls, candidate/application stage data, offer/onboarding records, and in-system notifications will be reused.
- Stage duration is measured from existing application, assessment, interview, feedback, offer, and onboarding timestamps; where exact stage history is unavailable, the most recent status timestamp is used.
- Bottleneck thresholds default to 7 days in active application stages, 24 hours after interview completion for missing feedback, 24 hours before offer expiry for urgent offer follow-up, 48 hours of pending simulated background-check status, and task due date for onboarding follow-up.
- Archive means marking records as archived and hiding them from active queues, not hard-deleting candidate or requisition history.
- D&I reporting is aggregate-only and uses optional candidate-provided fields; this feature does not introduce individual demographic review as part of hiring decisions.
- External email, external background-check providers, payroll systems, schedulers, and background workers are outside this feature scope.
