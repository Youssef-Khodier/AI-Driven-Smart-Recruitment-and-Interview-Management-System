# Tasks: Notifications, Reports & Compliance

**Input**: Design documents from `specs/007-notifications-reports-compliance/`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/web-workflows.md`, `route-map.md`, `quickstart.md`

**Tests**: No TDD or automated test suite was requested. Each user story includes a manual demo/check task tied to the spec's independent test and acceptance scenarios. Final validation uses `composer test`, which runs `scripts/check.php` PHP syntax checks.

**Organization**: Tasks are grouped by user story so each story can be implemented and verified independently after the shared foundation is complete.

**Implementation Note For LLM Implementer**: This project is a framework-free Vanilla PHP MVC monolith. Do not add Laravel, REST APIs, a separated frontend, cron, queues, or JavaScript polling. Use existing patterns in `app/Controllers/*`, `app/Repositories/*`, `app/Policies/*`, `routes/web.php`, `views/*`, `database/schema.sql`, and `scripts/check.php`.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel because it touches a different file and does not depend on incomplete tasks.
- **[Story]**: Required only for user story phases, using `[US1]`, `[US2]`, etc.
- **File paths**: Every task names the exact file to create or modify.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirm scope and prepare the shared feature skeleton without changing behavior.

- [x] T001 Review Vanilla PHP constraints and implementation boundaries in specs/007-notifications-reports-compliance/plan.md
- [x] T002 Review user stories, priorities, and acceptance scenarios in specs/007-notifications-reports-compliance/spec.md
- [x] T003 [P] Review web route contracts before coding in specs/007-notifications-reports-compliance/contracts/web-workflows.md
- [x] T004 [P] Review entity and state rules before coding in specs/007-notifications-reports-compliance/data-model.md
- [x] T005 [P] Review manual validation flows before coding in specs/007-notifications-reports-compliance/quickstart.md
- [x] T006 Record peer-review approval for this task list in specs/007-notifications-reports-compliance/checklists/requirements.md

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared schema, enums, policies, and navigation prerequisites required before any user story can work.

**CRITICAL**: No user story implementation should begin until T007 through T019 are complete.

- [x] T007 Add `DROP TABLE IF EXISTS notifications;` before dependent table drops in database/schema.sql
- [x] T008 Add `notifications` table with `notification_id`, `user_id`, `title`, `message`, `type`, `reference_id`, `reference_type`, `is_read`, `created_at`, `read_at`, foreign key to `users.user_id`, `idx_notifications_user_read`, `idx_notifications_reference`, and `idx_notifications_type_created` in database/schema.sql
- [x] T009 Update `account_audit_records.target_user_id` to allow durable candidate deletion audits by making the target nullable or preserving a non-cascading snapshot-compatible relationship in database/schema.sql
- [x] T010 Add retention-audit snapshot support fields or JSON convention comments for `CANDIDATE_DELETED` and `CANDIDATE_ANONYMIZED` in database/schema.sql
- [x] T011 [P] Create notification type enum values `STATUS_CHANGE`, `FEEDBACK_REMINDER`, `OFFER_EXPIRING_SOON`, and `OFFER_EXPIRED` in app/Enums/NotificationType.php
- [x] T012 [P] Create retention audit action enum values `CANDIDATE_ANONYMIZED` and `CANDIDATE_DELETED` in app/Enums/RetentionAuditAction.php
- [x] T013 [P] Create user-owned notification authorization methods in app/Policies/NotificationPolicy.php
- [x] T014 [P] Create HR-only report authorization method in app/Policies/ReportPolicy.php
- [x] T015 [P] Create HR-only audit-log authorization method in app/Policies/AuditLogPolicy.php
- [x] T016 [P] Create HR-only retention authorization and eligibility guard methods in app/Policies/DataRetentionPolicy.php
- [x] T017 Add shared notification navigation placeholder and HR reports/audit/retention navigation links in views/layouts/app.php
- [x] T018 Add route declarations for notifications, HR checks, reports, audit log, and data retention in routes/web.php
- [x] T019 Run PHP syntax validation through composer test using scripts/check.php

**Checkpoint**: Database schema, enums, policies, navigation placeholders, and routes are ready for user story implementation.

---

## Phase 3: User Story 1 - Candidate Receives Application Status Notifications (Priority: P1) MVP

**Goal**: A candidate receives an in-app notification when HR changes their application status, sees the unread badge, opens a dedicated notifications page, and marks notifications read.

**Independent Test**: Log in as a candidate, have HR Admin change that candidate's application status, verify a notification appears with title `Application Status Updated`, mark it read, and confirm the unread count updates.

### Manual Check for User Story 1

- [x] T020 [US1] Document the candidate status notification manual check result in specs/007-notifications-reports-compliance/quickstart.md

### Implementation for User Story 1

- [x] T021 [P] [US1] Implement notification insert, dedupe, unread count, list, mark-one-read, and mark-all-read methods in app/Repositories/NotificationRepository.php
- [x] T022 [US1] Implement `index`, `markRead`, and `markAllRead` actions with ownership checks and CSRF-safe redirects in app/Controllers/NotificationController.php
- [x] T023 [US1] Create dedicated notification list page with empty state, read state, read timestamp, per-item read form, and mark-all form in views/notifications/index.php
- [x] T024 [US1] Replace the navigation placeholder with an authenticated notification badge that calls the repository unread count and links to `notifications.index` in views/layouts/app.php
- [x] T025 [US1] Add status-change notification creation after successful HR application status transition in app/Controllers/HrController.php
- [x] T026 [US1] Verify direct access to another user's notification is denied by `NotificationPolicy` inside app/Controllers/NotificationController.php
- [x] T027 [US1] Run PHP syntax validation after US1 implementation through composer test using scripts/check.php

**Checkpoint**: User Story 1 works independently as the MVP.

---

## Phase 4: User Story 2 - Interviewer Receives Missing Feedback Reminder (Priority: P2)

**Goal**: HR Admin manually runs checks and interviewers assigned to completed interviews older than 24 hours without feedback receive deduplicated in-app reminders.

**Independent Test**: Complete an interview, ensure the assigned interviewer has not submitted feedback after 24 hours, click Run Checks as HR Admin, and verify exactly one feedback reminder notification is created for that interviewer.

### Manual Check for User Story 2

- [x] T028 [US2] Document the missing feedback reminder manual check result in specs/007-notifications-reports-compliance/quickstart.md

### Implementation for User Story 2

- [x] T029 [P] [US2] Add `findMissingFeedbackReminders` query that returns completed interviews older than 24 hours with assigned interviewers lacking feedback in app/Repositories/NotificationRepository.php
- [x] T030 [US2] Implement HR-only `run` action for manual checks with feedback-reminder creation and flash summary in app/Controllers/HrComplianceCheckController.php
- [x] T031 [US2] Add Run Checks form with CSRF token and result guidance to the HR dashboard in views/hr/dashboard.php
- [x] T032 [US2] Ensure `/hr/checks/run` route points to `HrComplianceCheckController::run` and uses POST only in routes/web.php
- [x] T033 [US2] Verify duplicate Run Checks do not create duplicate `FEEDBACK_REMINDER` notifications through repository dedupe in app/Repositories/NotificationRepository.php
- [x] T034 [US2] Run PHP syntax validation after US2 implementation through composer test using scripts/check.php

**Checkpoint**: User Story 2 works independently after the foundation and US1 notification infrastructure.

---

## Phase 5: User Story 3 - HR Admin Receives Offer Expiry Alerts (Priority: P2)

**Goal**: HR Admin manually runs checks and receives notifications for sent offers expiring within 48 hours or already expired; expired sent offers transition to `EXPIRED`.

**Independent Test**: Create a sent offer expiring in 47 hours, click Run Checks as HR Admin, and verify an `Offer Expiring Soon` notification for the offer creator; then verify a past-due sent offer becomes `EXPIRED` and creates `Offer Expired`.

### Manual Check for User Story 3

- [x] T035 [US3] Document the offer expiry alert manual check result in specs/007-notifications-reports-compliance/quickstart.md

### Implementation for User Story 3

- [x] T036 [P] [US3] Add `findOffersExpiringWithin48Hours` and `findExpiredSentOffers` queries with candidate/job display details in app/Repositories/NotificationRepository.php
- [x] T037 [US3] Extend `HrComplianceCheckController::run` to create `OFFER_EXPIRING_SOON` notifications and expire past-due `SENT` offers in app/Controllers/HrComplianceCheckController.php
- [x] T038 [US3] Reuse or update `OfferRepository::enforceExpiryForOffer` so Run Checks sets `offers.status`, `expired_at`, and application status consistently in app/Repositories/OfferRepository.php
- [x] T039 [US3] Record offer expiry audit evidence with action `OFFER_EXPIRE` when Run Checks expires an offer in app/Repositories/PostOfferAuditRepository.php
- [x] T040 [US3] Verify accepted and rejected offers are excluded from expiry notifications in app/Repositories/NotificationRepository.php
- [x] T041 [US3] Run PHP syntax validation after US3 implementation through composer test using scripts/check.php

**Checkpoint**: User Story 3 works independently after the foundation and US1 notification infrastructure.

---

## Phase 6: User Story 4 - HR Admin Views Recruitment Pipeline Report (Priority: P3)

**Goal**: HR Admin views a read-only pipeline report showing counts for each application status per open requisition and aggregate totals.

**Independent Test**: Log in as HR Admin, open the pipeline report, and verify stage counts match applications for each open requisition, including zero-application requisitions.

### Manual Check for User Story 4

- [x] T042 [US4] Document the pipeline report manual check result in specs/007-notifications-reports-compliance/quickstart.md

### Implementation for User Story 4

- [x] T043 [P] [US4] Implement `pipelineByOpenRequisition` aggregate query with zero-count statuses and totals in app/Repositories/ReportRepository.php
- [x] T044 [US4] Implement HR-only `pipeline` action using `ReportPolicy` in app/Controllers/HrReportController.php
- [x] T045 [US4] Create pipeline report table with APPLIED, SCREENING, ASSESSMENT, INTERVIEW, OFFER, HIRED, REJECTED, row totals, and aggregate totals in views/hr/reports/pipeline.php
- [x] T046 [US4] Add HR navigation link to the pipeline report in views/layouts/app.php
- [x] T047 [US4] Verify candidate and interviewer access to `/hr/reports/pipeline` is denied in app/Controllers/HrReportController.php
- [x] T048 [US4] Run PHP syntax validation after US4 implementation through composer test using scripts/check.php

**Checkpoint**: User Story 4 works independently after foundational HR routes and policies.

---

## Phase 7: User Story 5 - HR Admin Views Time-to-Hire Summary (Priority: P3)

**Goal**: HR Admin views average days from application submission to first `HIRED` status-history transition by requisition and department.

**Independent Test**: Create hired applications with known applied and hired dates, open the time-to-hire report, and verify calculated averages match manual calculation.

### Manual Check for User Story 5

- [x] T049 [US5] Document the time-to-hire report manual check result in specs/007-notifications-reports-compliance/quickstart.md

### Implementation for User Story 5

- [x] T050 [P] [US5] Implement `timeToHireByRequisition` and `timeToHireByDepartment` queries using first `HIRED` history timestamp in app/Repositories/ReportRepository.php
- [x] T051 [US5] Implement HR-only `timeToHire` action using `ReportPolicy` in app/Controllers/HrReportController.php
- [x] T052 [US5] Create time-to-hire report page with requisition averages, department averages, hired counts, and `N/A` empty states in views/hr/reports/time-to-hire.php
- [x] T053 [US5] Add HR navigation link to the time-to-hire report in views/layouts/app.php
- [x] T054 [US5] Verify applications without `HIRED` history rows do not affect averages in app/Repositories/ReportRepository.php
- [x] T055 [US5] Run PHP syntax validation after US5 implementation through composer test using scripts/check.php

**Checkpoint**: User Story 5 works independently after foundational HR routes and policies.

---

## Phase 8: User Story 6 - HR Admin Views Audit History (Priority: P3)

**Goal**: HR Admin views one read-only audit log combining account, interview, post-offer, application status, and job status history records with filters and 25-row pagination.

**Independent Test**: Perform an auditable action, open the audit log as HR Admin, apply actor/date/action/entity filters, and verify matching reverse-chronological records with readable changed-field summaries.

### Manual Check for User Story 6

- [x] T056 [US6] Document the consolidated audit log manual check result in specs/007-notifications-reports-compliance/quickstart.md

### Implementation for User Story 6

- [x] T057 [P] [US6] Implement normalized union query for account, interview, post-offer, application status, and job status records in app/Repositories/AuditLogRepository.php
- [x] T058 [US6] Implement safe filter parsing for `from`, `to`, `actor`, `action`, `entity`, and `page` in app/Controllers/HrAuditLogController.php
- [x] T059 [US6] Implement HR-only audit index action with 25-record pagination using `AuditLogPolicy` in app/Controllers/HrAuditLogController.php
- [x] T060 [US6] Create audit log filter form and read-only paginated table in views/hr/audit-log/index.php
- [x] T061 [US6] Add human-readable JSON changed-field summary formatter in app/Repositories/AuditLogRepository.php
- [x] T062 [US6] Add HR navigation link to the audit log in views/layouts/app.php
- [x] T063 [US6] Verify audit log page exposes no update or delete forms in views/hr/audit-log/index.php
- [x] T064 [US6] Run PHP syntax validation after US6 implementation through composer test using scripts/check.php

**Checkpoint**: User Story 6 works independently after foundational HR routes and policies.

---

## Phase 9: User Story 7 - HR Admin Performs Candidate Data Retention Actions (Priority: P4)

**Goal**: HR Admin lists candidates eligible for retention action, anonymizes PII, deletes eligible candidates, blocks active/ineligible candidates, and records durable audit evidence.

**Independent Test**: Create a candidate with an application older than 365 days in `REJECTED` status, open data retention, anonymize the candidate, verify PII redaction, and verify an audit record exists.

### Manual Check for User Story 7

- [x] T065 [US7] Document the candidate retention manual check result in specs/007-notifications-reports-compliance/quickstart.md

### Implementation for User Story 7

- [x] T066 [P] [US7] Add default retention threshold constant or config value of 365 days in app/Core/Config.php
- [x] T067 [P] [US7] Implement candidate eligibility query with most recent application date, terminal status, and closed requisition handling in app/Repositories/DataRetentionRepository.php
- [x] T068 [US7] Implement server-side eligibility recheck method for POST actions in app/Repositories/DataRetentionRepository.php
- [x] T069 [US7] Implement anonymization transaction that updates `users` and `candidates` PII and records `CANDIDATE_ANONYMIZED` audit action in app/Repositories/DataRetentionRepository.php
- [x] T070 [US7] Implement deletion transaction that records durable `CANDIDATE_DELETED` audit snapshot before deleting candidate-owned data in app/Repositories/DataRetentionRepository.php
- [x] T071 [US7] Implement HR-only index, anonymize, and delete actions with confirmation validation using `DataRetentionPolicy` in app/Controllers/HrDataRetentionController.php
- [x] T072 [US7] Create data retention page with eligible list, confirmation inputs, anonymize forms, delete forms, and active-candidate warning text in views/hr/data-retention/index.php
- [x] T073 [US7] Add HR navigation link to data retention in views/layouts/app.php
- [x] T074 [US7] Verify active or recently-applied candidates are blocked during POST even if the page was stale in app/Controllers/HrDataRetentionController.php
- [x] T075 [US7] Run PHP syntax validation after US7 implementation through composer test using scripts/check.php

**Checkpoint**: User Story 7 works independently after foundational HR routes and policies.

---

## Phase 10: Polish & Cross-Cutting Concerns

**Purpose**: Final hardening, documentation, and full-feature validation across all selected stories.

- [x] T076 [P] Update quickstart evidence notes for all completed manual demo flows in specs/007-notifications-reports-compliance/quickstart.md
- [x] T077 [P] Confirm generated routes and route names match the route map in specs/007-notifications-reports-compliance/route-map.md
- [x] T078 Review all new SQL for indexes, foreign keys, and retention-audit durability in database/schema.sql
- [x] T079 Review all new controllers for `requireAuth`, `requireRole`, ownership checks, CSRF-protected forms, and redirect/flash behavior in app/Controllers/NotificationController.php
- [x] T080 Review all HR-only controllers for candidate/interviewer denial behavior in app/Controllers/HrReportController.php
- [x] T081 Review all HR-only controllers for candidate/interviewer denial behavior in app/Controllers/HrAuditLogController.php
- [x] T082 Review all HR-only controllers for candidate/interviewer denial behavior in app/Controllers/HrDataRetentionController.php
- [x] T083 Run final PHP syntax validation through composer test using scripts/check.php
- [x] T084 Execute the full manual quickstart from specs/007-notifications-reports-compliance/quickstart.md
- [x] T085 Record final peer-review notes for RBAC, privacy, audit immutability, and acceptance coverage in specs/007-notifications-reports-compliance/checklists/requirements.md

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 Setup**: No dependencies.
- **Phase 2 Foundational**: Depends on Phase 1; blocks every user story.
- **US1 MVP**: Depends on Phase 2.
- **US2 and US3**: Depend on Phase 2 and US1 notification repository basics.
- **US4, US5, US6, US7**: Depend on Phase 2 only, but should be integrated with shared layout navigation carefully.
- **Phase 10 Polish**: Depends on all selected stories being complete.

### User Story Dependencies

- **US1 (P1)**: MVP and notification infrastructure; no other story dependency.
- **US2 (P2)**: Requires `NotificationRepository` from US1; otherwise independent.
- **US3 (P2)**: Requires `NotificationRepository` from US1 and existing `OfferRepository`; otherwise independent.
- **US4 (P3)**: Independent after foundational HR policy/routes.
- **US5 (P3)**: Independent after foundational HR policy/routes; shares `ReportRepository` file with US4, so coordinate edits if parallel.
- **US6 (P3)**: Independent after foundational HR policy/routes.
- **US7 (P4)**: Independent after foundational HR policy/routes and retention-audit schema support.

### Within Each User Story

- Complete the manual check documentation task first so acceptance criteria are visible before coding.
- Complete repository/data tasks before controller tasks.
- Complete controller tasks before view tasks unless creating a static UI draft in the same file named by the task.
- Complete route and navigation tasks after controller action names are final.
- Run `composer test` through `scripts/check.php` before claiming the story complete.

---

## Parallel Opportunities

- T003, T004, and T005 can run in parallel during setup.
- T011 through T016 can run in parallel because each creates a separate enum or policy file.
- T021 can run while T020 documents the US1 manual check, but T022 depends on T021.
- T029 can run before T030 in US2, and T036 can run before T037 in US3.
- T043, T050, T057, T066, and T067 can run in parallel after Phase 2 because they touch different repository/config files, except coordinate T043 and T050 if both edit `app/Repositories/ReportRepository.php` at the same time.
- US4, US6, and US7 can be implemented in parallel after Phase 2 because they use different controllers, repositories, and views.
- T076 and T077 can run in parallel during polish because they touch different documentation files.

---

## Parallel Example: User Story 1

```text
Task: T020 [US1] Document the candidate status notification manual check result in specs/007-notifications-reports-compliance/quickstart.md
Task: T021 [P] [US1] Implement notification insert, dedupe, unread count, list, mark-one-read, and mark-all-read methods in app/Repositories/NotificationRepository.php
```

## Parallel Example: User Story 2

```text
Task: T028 [US2] Document the missing feedback reminder manual check result in specs/007-notifications-reports-compliance/quickstart.md
Task: T029 [P] [US2] Add findMissingFeedbackReminders query in app/Repositories/NotificationRepository.php
```

## Parallel Example: User Story 3

```text
Task: T035 [US3] Document the offer expiry alert manual check result in specs/007-notifications-reports-compliance/quickstart.md
Task: T036 [P] [US3] Add offer expiry lookup queries in app/Repositories/NotificationRepository.php
```

## Parallel Example: User Story 4

```text
Task: T042 [US4] Document the pipeline report manual check result in specs/007-notifications-reports-compliance/quickstart.md
Task: T043 [P] [US4] Implement pipelineByOpenRequisition aggregate query in app/Repositories/ReportRepository.php
```

## Parallel Example: User Story 5

```text
Task: T049 [US5] Document the time-to-hire report manual check result in specs/007-notifications-reports-compliance/quickstart.md
Task: T050 [P] [US5] Implement time-to-hire aggregate queries in app/Repositories/ReportRepository.php
```

## Parallel Example: User Story 6

```text
Task: T056 [US6] Document the consolidated audit log manual check result in specs/007-notifications-reports-compliance/quickstart.md
Task: T057 [P] [US6] Implement normalized audit union query in app/Repositories/AuditLogRepository.php
```

## Parallel Example: User Story 7

```text
Task: T066 [P] [US7] Add default retention threshold in app/Core/Config.php
Task: T067 [P] [US7] Implement candidate eligibility query in app/Repositories/DataRetentionRepository.php
```

---

## Implementation Strategy

### MVP First (US1 Only)

1. Complete Phase 1 setup tasks T001 through T006.
2. Complete Phase 2 foundational tasks T007 through T019.
3. Complete US1 tasks T020 through T027.
4. Stop and manually verify candidate status notifications, unread badge, notification list, mark-one-read, mark-all-read, and empty state.

### Incremental Delivery

1. Deliver US1 first because it provides the shared notification infrastructure and highest-priority candidate value.
2. Deliver US2 and US3 next because both reuse notification infrastructure and implement manual Run Checks.
3. Deliver US4, US5, and US6 as read-only HR pages after notification operations are stable.
4. Deliver US7 last because retention actions are irreversible and need the strongest audit/RBAC review.

### Low-Context LLM Guidance

1. Follow task order unless a task is marked `[P]` and you are certain it touches a separate file.
2. Before editing a file, read the nearest existing similar file and copy its style.
3. Do not invent new architecture; use `Database::fetch`, `Database::fetchAll`, `Database::insert`, `Database::update`, `Database::transaction`, `Controller::requireRole`, `Controller::requireAuth`, `Session::flash`, and `url()` patterns already in the repo.
4. If a task conflicts with existing uncommitted user changes, stop and ask before overwriting.
5. After each story, run `composer test` and complete the story's manual quickstart check.
