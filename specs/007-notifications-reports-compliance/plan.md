# Implementation Plan: Notifications, Reports & Compliance

**Branch**: `main` | **Date**: 2026-05-05 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/007-notifications-reports-compliance/spec.md`

## Summary

Build the SRIM notifications, recruitment reports, consolidated audit log, manual compliance checks, and candidate data-retention vertical slice. Delivery targets the existing framework-free Vanilla PHP MVC monolith with server-rendered PHP templates, `routes/web.php` browser/form workflows, PDO-backed repositories, plain SQL schema changes, native sessions, CSRF protection, server-side validation, role/ownership policies, and Composer/PHP check evidence.

## Technical Context

**Language/Version**: PHP 8.2+ with no runtime framework dependency
**Primary Dependencies**: Existing Vanilla PHP MVC core, server-rendered PHP templates, PDO, `routes/web.php`, controller/view/repository/policy structure, native sessions, CSRF, server-side validation, existing RBAC foundation, existing application/interview/offer/audit/status-history data, and Composer script evidence
**Storage**: MySQL via `database/schema.sql` and PDO-backed repositories; add `notifications` to the live schema, add `reference_id` and `reference_type` for notification deduplication/linking, add indexes for unread counts/report filters, and adjust audit retention storage so deletion actions remain auditable after candidate hard-delete
**Testing**: `composer test`, PHP syntax checks, targeted PHP/manual web-flow evidence for notification creation/read state, manual Run Checks, HR reports, audit-log filters, and data-retention actions
**Target Platform**: Server-rendered web application in modern Chrome, Firefox, and Edge browsers
**Project Type**: Vanilla PHP monolithic MVC web application; no REST API, SPA, separated frontend, mobile-native project, or runtime framework dependency
**Performance Goals**: Unread notification count query completes within one indexed lookup per page load; notifications page, audit log page, and reports render within 3 seconds for demo-scale data; pipeline report supports up to 50 open requisitions; audit log paginates at 25 records per page
**Constraints**: Server-rendered PHP pages, `routes/web.php`, form submissions, redirects, MySQL, sessions, CSRF, server-side validation, active-account guards, role/ownership policies, no cron/background worker, no email delivery, no public API contract, no separated frontend, no runtime framework dependency
**Scale/Scope**: 3-person academic delivery; one compliance/reporting vertical slice aligned to baseline SRIM functions 6, 37, 39, and 42; D&I audit reporting, template versioning, database integrity archiving, email notifications, external integrations, and automatic scheduler execution remain out of scope

## Baseline Materials Review

- **SRS / Use Case Trace**: `Diagrams/document.md` functions 6 Pipeline Throughput Analytics, 37 Data Retention & Privacy, 39 System Audit Trail, and 42 Automated Notification Escalator; SRS DOCX snippets for GDPR data retention/right-to-erasure, RBAC data isolation, auditability, email notification context, and pipeline analytics; `Diagrams/Use-case Diagram/Usecase.pdf` compliance use cases for RBAC, data retention/right-to-be-forgotten, system audit trail, and notification-related workflows.
- **Database / ERD Trace**: `Diagrams/Database/schema.sql`, `schema-erd.svg`, and README baseline entities `users`, `candidates`, `job_requisitions`, `applications`, `application_status_histories`, `interviews`, `interviewers_assignment`, `interview_feedback`, `offers`, and `notifications`; live `database/schema.sql` already has account/interview/post-offer audit tables and status histories but currently lacks `notifications`.
- **Activity / Class / Object Trace**: `Activity 1.pdf` application-to-offer lifecycle status changes, `Activity 4.pdf` feedback aggregation and HR recommendation review, `Activity 6.pdf` login role detection and permission application, `Activity 7.pdf` offer validity timer/expiry context, `Class Diagram.drawio` User, Role, HRStaff, TechnicalInterviewer, Candidate, Application, Interview, Feedback, OfferPackage, and Onboarding concepts, and `Object Diagram.pdf` candidate/application/interview/feedback/offer examples.
- **Architecture Trace**: `system-architecture.drawio` maps this feature to Candidate Portal, HR Admin Portal, Interviewer Portal, Auth & RBAC, Notification & Escalation Module, Audit & Compliance Module, Feedback & Recommendation Module, Offer & Onboarding Module, and MySQL Database inside the single SRIM platform.
- **Scope Changes**: No constitution amendment is required. The live implementation must reconcile the baseline `notifications` table with the current schema by adding it now. Real email delivery and background scheduling are explicitly deferred; manual HR Admin Run Checks is the execution mechanism for feedback-reminder and offer-expiry checks.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL schema files and PDO-backed models/repositories.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff/observer where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed.
- [x] AI, proctoring, background checks, job board sync, calendar, and email are out of scope or simulated unless explicitly added later.
- [x] Acceptance criteria are testable by PHP checks or documented server-rendered page demo flows.
- [x] Peer review is required before implementation begins.

Gate result: PASS. No constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/007-notifications-reports-compliance/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── route-map.md
├── contracts/
│   └── web-workflows.md
├── checklists/
│   └── requirements.md
└── tasks.md             # Created later by /speckit.tasks
```

### Source Code (repository root)

```text
app/
├── Controllers/
│   ├── NotificationController.php          # New authenticated user notification list/read workflows
│   ├── HrComplianceCheckController.php     # New manual Run Checks action
│   ├── HrReportController.php              # New pipeline and time-to-hire reports
│   ├── HrAuditLogController.php            # New consolidated audit-log viewer
│   └── HrDataRetentionController.php       # New eligible candidate retention actions
├── Enums/
│   ├── NotificationType.php                # New status, feedback, and offer notification types
│   └── RetentionAuditAction.php            # New candidate anonymized/deleted audit actions
├── Policies/
│   ├── NotificationPolicy.php              # New user-owned notification rules
│   ├── ReportPolicy.php                    # New HR-only report rules
│   ├── AuditLogPolicy.php                  # New HR-only audit-log rules
│   └── DataRetentionPolicy.php             # New HR-only retention rules
├── Repositories/
│   ├── NotificationRepository.php          # New notification persistence, unread counts, dedupe
│   ├── ReportRepository.php                # New aggregate report queries
│   ├── AuditLogRepository.php              # New unioned audit queries and filters
│   └── DataRetentionRepository.php         # New eligibility, anonymization, deletion actions
└── Core/
    └── Validator.php                       # Existing validation helper used by new controllers

database/
└── schema.sql                              # Add notifications and retention/audit-supporting indexes/constraints

views/
├── notifications/
│   └── index.php
├── hr/
│   ├── reports/
│   │   ├── pipeline.php
│   │   └── time-to-hire.php
│   ├── audit-log/
│   │   └── index.php
│   └── data-retention/
│       └── index.php
└── layouts/
    └── app.php                             # Add unread notification badge/link for authenticated users

routes/
└── web.php                                 # Add notification, HR report, audit, retention, and Run Checks routes

scripts/
└── check.php                               # Existing Composer test target; extend as needed for feature checks
```

**Structure Decision**: Extend the existing Vanilla PHP MVC app at the repository root. Keep all workflows as browser pages and POST/PUT forms in `routes/web.php`, implement repeated notification/report/audit/retention queries in small PDO repositories, and enforce role or ownership rules in controllers/policies. Do not introduce `routes/api.php`, public JSON contracts, cron workers, queue workers, separated frontend folders, framework migrations, or runtime framework dependencies.

## Phase 0: Research Summary

Research output: [research.md](./research.md)

Resolved decisions:

- Use only server-rendered page/form workflows for notifications, Run Checks, reports, audit log, and data retention.
- Add the live `notifications` table with polymorphic reference columns and repository-level deduplication.
- Compute unread notification count on page load using an indexed count query scoped to the authenticated user.
- Trigger feedback-reminder and offer-expiry checks only through an HR Admin Run Checks form action.
- Compute pipeline and time-to-hire reports from existing tables/status histories without persisted report tables.
- Consolidate audit log display from account, interview, post-offer, application status, and job requisition status histories.
- Preserve audit evidence for candidate deletion by changing audit storage to keep a durable snapshot even if the candidate user row is removed.
- Keep data-retention threshold in PHP configuration with a 365-day default.

## Phase 1: Design Summary

Design outputs:

- [data-model.md](./data-model.md)
- [contracts/web-workflows.md](./contracts/web-workflows.md)
- [route-map.md](./route-map.md)
- [quickstart.md](./quickstart.md)

Implementation boundaries:

- All authenticated users can view only their own notifications and mark them read.
- Candidate application status notifications are created when HR changes application status.
- HR Admins manually run feedback-reminder and offer-expiry checks from the HR dashboard.
- HR-only reports are read-only aggregate pages.
- HR-only audit log is read-only, filtered, paginated, and never mutates audit records.
- HR-only data retention can anonymize or delete only candidates whose applications are terminal or closed and older than the threshold.
- Retention actions are irreversible, confirmation-protected, and auditable.

## Post-Design Constitution Check

- [x] Design artifacts preserve diagram traceability and baseline entities.
- [x] Web workflow contracts are server-rendered page/form contracts, not REST API contracts.
- [x] Data model uses MySQL/PDO-compatible entities and relationships.
- [x] RBAC and candidate privacy are represented in route contracts, policies, model ownership rules, and validation expectations.
- [x] Notification, audit, report, and retention changes remain within the Vanilla PHP monolith.
- [x] External email delivery and automatic schedulers remain out of scope.
- [x] Quickstart includes test/demo evidence before implementation is considered complete.

Post-design gate result: PASS. No constitution violations.

## Complexity Tracking

No constitution violations or complexity exceptions are required.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

## Phase 2 Preview

Task generation should produce small, independently reviewable tasks for SQL schema changes, notification repository/controller/view/header badge, application-status notification insertion, HR Run Checks, report queries/pages, audit log union/filter pagination, retention eligibility/action workflows, policies/validation/CSRF, PHP syntax/test checks, and documented manual demo evidence. Do not start implementation until `/speckit.tasks` creates the task list and peer review is complete.
