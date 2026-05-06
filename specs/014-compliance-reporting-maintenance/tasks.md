# Tasks: Compliance Reporting Maintenance

**Input**: Design documents from `/specs/014-compliance-reporting-maintenance/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, route-map.md, contracts/compliance-reporting-web.md, quickstart.md

**Tests**: Manual acceptance testing through server-rendered HR, Candidate, and notification workflows; targeted PHP syntax checks; policy, repository, and service verification where practical. No automated test framework is specified; tests are manual demo flows and PHP lint checks per quickstart.md.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.
**Peer Review**: Include a peer-review task before implementation starts for each phase or user story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3, US4)
- Include exact file paths in descriptions

## Path Conventions

- **Vanilla PHP MVC monolith**: `app/`, `routes/web.php`, `views/`, `database/`
- **Controllers**: `app/Controllers/`
- **Enums**: `app/Enums/`
- **Policies**: `app/Policies/`
- **Repositories**: `app/Repositories/`
- **Services**: `app/Services/`
- **Views**: `views/`
- **Schema/Migrations**: `database/schema.sql`, `database/migrations/`
- Do not create `api/`, `backend/`, `frontend/`, or REST contract paths.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Database schema changes, new enums, and shared repository/service scaffolds required before any user story can begin.

- [x] T001 Create migration file `database/migrations/014_compliance_reporting_maintenance.sql` with tables for `candidate_demographics`, `compliance_run_check_batches`, `compliance_run_check_findings`, `archive_actions`, `compliance_audit_events`, and ALTER statements adding `archived_at` and `archived_by` columns to `job_requisitions` and `applications`
- [x] T002 [P] Update `database/schema.sql` with the canonical schema additions matching the migration file
- [x] T003 [P] Create enum `app/Enums/ComplianceRunCheckType.php` with values: MISSING_FEEDBACK, OFFER_EXPIRY, BACKGROUND_CHECK_DELAY, ONBOARDING_OVERDUE, ARCHIVE_CLOSED_REQUISITIONS, ARCHIVE_REJECTED_CANDIDATES, ALL_CHECKS
- [x] T004 [P] Create enum `app/Enums/ComplianceAuditAction.php` with values: REPORT_GENERATED, RUN_CHECK_EXECUTED, ESCALATION_CREATED, ARCHIVE_APPROVED, ARCHIVE_BLOCKED, SENSITIVE_ACCESS_DENIED, DEMOGRAPHIC_UPDATED, DEMOGRAPHIC_WITHDRAWN
- [x] T005 [P] Create enum `app/Enums/ArchiveActionStatus.php` with values: PENDING, APPROVED, BLOCKED, ARCHIVED
- [x] T006 [P] Extend `app/Enums/NotificationType.php` with new escalation types: MISSING_FEEDBACK_ESCALATION, OFFER_EXPIRY_ESCALATION, BACKGROUND_CHECK_ESCALATION, ONBOARDING_OVERDUE_ESCALATION, ARCHIVE_FOLLOWUP

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core repository, service, and policy scaffolds that MUST be complete before ANY user story can be implemented.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [ ] T007 Create `app/Repositories/ComplianceMaintenanceRepository.php` with methods for CRUD on `compliance_run_check_batches`, `compliance_run_check_findings`, `archive_actions`, and `compliance_audit_events` tables via PDO
- [ ] T008 [P] Extend `app/Repositories/ReportRepository.php` with methods for pipeline throughput queries (stage counts, conversion rates, average stage age, time-to-hire) and D&I aggregate queries from `candidate_demographics` joined through `applications`
- [ ] T009 [P] Extend `app/Repositories/DataRetentionRepository.php` with methods for archive eligibility queries (closed requisitions with all terminal applications, rejected applications with no pending work)
- [ ] T010 [P] Extend `app/Repositories/NotificationRepository.php` with methods to check for existing open escalation by reference type/id and to create escalation notifications with the new types
- [ ] T011 [P] Create `app/Policies/ReportPolicy.php` with `canViewPipelineReport`, `canViewDiversityReport`, and `canViewTimeToHire` methods enforcing HR Admin role
- [ ] T012 [P] Create `app/Policies/DataRetentionPolicy.php` with `canViewArchive`, `canApproveArchive`, and `canViewArchiveDetail` methods enforcing HR Admin role
- [ ] T013 [P] Extend `app/Policies/NotificationPolicy.php` to allow escalation notifications to reach assigned interviewers and HR Admins only
- [ ] T014 Register new routes in `routes/web.php`: GET `/hr/reports/diversity`, GET `/hr/run-checks`, POST `/hr/run-checks`, GET `/hr/run-checks/{id}`, GET `/hr/archive`, GET `/hr/archive/{entityType}/{id}`, POST `/hr/archive/{entityType}/{id}/approve`, POST `/candidate/profile/demographics`
- [ ] T015 Add sidebar/navigation links for "D&I Report", "Run Checks", and "Archive" in the HR layout and "Demographics" in the candidate profile layout

**Checkpoint**: Foundation ready — user story implementation can now begin in parallel.

---

## Phase 3: User Story 1 — Review Pipeline Bottlenecks (Priority: P1) 🎯 MVP

**Goal**: HR Admin views pipeline throughput analytics with stage counts, conversion rates, average stage age, time-to-hire, bottleneck labels, date-range and requisition/department filters, and empty-state handling.

**Independent Test**: Open `GET /hr/reports/pipeline` with applications across multiple stages and verify stage counts, average age, time-to-hire, and bottleneck labels are shown for the selected date range and filters.

### Implementation for User Story 1

- [ ] T016 [US1] Peer review spec, plan, route map, RBAC, privacy, and acceptance criteria for US1 before implementation
- [ ] T017 [US1] Implement `HrReportController::pipeline` action in `app/Controllers/HrReportController.php` with date-range, requisition, department, and stage filter validation; call `ReportRepository` for throughput data and `ReportPolicy::canViewPipelineReport` for authorization; write `compliance_audit_events` entry for report generation
- [ ] T018 [US1] Implement bottleneck detection logic in `HrReportController::pipeline` or a private helper: flag stages where average age exceeds 7 days or stage share is unusually high; attach bottleneck labels and affected requisitions to view data
- [ ] T019 [US1] Update `views/hr/reports/pipeline.php` to render filter form (date range, requisition, department, stage), stage count table with conversion rates, average age column, time-to-hire summary, bottleneck flag badges, and empty-state message when no data matches filters
- [ ] T020 [US1] Validate edge cases: invalid date ranges return page with validation errors; unknown requisition/department filters show empty state; non-HR users receive access-denied redirect

**Checkpoint**: User Story 1 is fully functional — HR Admin can view pipeline bottleneck analytics independently.

---

## Phase 4: User Story 2 — Audit Diversity and Inclusion Metrics (Priority: P2)

**Goal**: HR Admin reviews aggregate D&I audit reports from optional candidate demographic fields with privacy suppression (groups < 3 candidates), "Not provided" totals, and RBAC enforcement. Candidates can manage their own demographic disclosure.

**Independent Test**: Add candidates with provided, partially provided, and not-provided demographics, generate the D&I report, and verify aggregate counts, suppression, and "Not provided" handling.

### Implementation for User Story 2

- [ ] T021 [US2] Peer review spec, plan, route map, RBAC, privacy, and acceptance criteria for US2 before implementation
- [ ] T022 [P] [US2] Create `app/Services/DiversityReportSuppressor.php` with method to take raw aggregate rows and apply small-group suppression (< 3 candidates) by combining into a privacy-safe "Other/Suppressed" category while keeping overall totals consistent
- [ ] T023 [US2] Implement `CandidateController::updateDemographics` action in `app/Controllers/CandidateController.php` with CSRF validation, ownership check, approved-category-list validation, consent/withdraw handling, and `compliance_audit_events` write for demographic updates/withdrawals
- [ ] T024 [US2] Update `views/candidate/profile.php` to include optional demographic disclosure form (gender, ethnicity, disability, veteran status categories, consent checkbox, withdraw option) with clear privacy explanation
- [ ] T025 [US2] Implement `HrReportController::diversity` action in `app/Controllers/HrReportController.php` with date-range, requisition, department, outcome, and demographic category filters; call `ReportRepository` for aggregate D&I data, `DiversityReportSuppressor` for privacy, and `ReportPolicy::canViewDiversityReport` for authorization; write audit event
- [ ] T026 [US2] Create `views/hr/reports/diversity.php` to render filter form, aggregate category table with stage/outcome breakdown, suppressed-group indicators, "Not provided" row, scope summary, and empty-state message
- [ ] T027 [US2] Validate edge cases: non-HR users receive access-denied; all demographics blank shows only "Not provided"; groups < 3 are suppressed; overall totals remain consistent after suppression

**Checkpoint**: User Stories 1 AND 2 both work independently — pipeline analytics and D&I reports are functional.

---

## Phase 5: User Story 3 — Run Operational Checks and Escalations (Priority: P3)

**Goal**: HR Admin manually triggers Run Checks for missing feedback, offer expiry, simulated background-check delays, and overdue onboarding tasks. The system creates in-system escalation notifications, prevents duplicates, and shows a reviewable batch summary.

**Independent Test**: Prepare overdue feedback, offer, background-check, and onboarding records; run checks as HR Admin; verify findings, notification counts, duplicate skip behavior on repeated runs.

### Implementation for User Story 3

- [ ] T028 [US3] Peer review spec, plan, route map, RBAC, privacy, and acceptance criteria for US3 before implementation
- [ ] T029 [US3] Create `app/Services/ComplianceRunCheckService.php` with methods: `runMissingFeedbackCheck`, `runOfferExpiryCheck`, `runBackgroundCheckDelayCheck`, `runOnboardingOverdueCheck`, and `runAllChecks`; each method queries relevant repository, identifies overdue/missing items per threshold defaults (24h feedback, 24h offer expiry, 48h background check, due date onboarding), creates findings, and returns counts
- [ ] T030 [US3] Implement duplicate escalation prevention in `ComplianceRunCheckService`: before creating a notification, call `NotificationRepository` to check for an existing open notification with the same reference_type and reference_id; if found, mark finding as "duplicate skipped" and record the existing notification id
- [ ] T031 [US3] Implement `HrComplianceCheckController::index` action in `app/Controllers/HrComplianceCheckController.php` to list recent run-check batches from `ComplianceMaintenanceRepository` with date/type/status filters
- [ ] T032 [US3] Implement `HrComplianceCheckController::store` action in `app/Controllers/HrComplianceCheckController.php` with CSRF validation, check-type and scope validation, call `ComplianceRunCheckService`, persist batch and findings to `ComplianceMaintenanceRepository`, create escalation notifications via `NotificationRepository`, write `compliance_audit_events`, and redirect to batch detail page
- [ ] T033 [US3] Implement `HrComplianceCheckController::show` action in `app/Controllers/HrComplianceCheckController.php` to load a batch and its findings from `ComplianceMaintenanceRepository`, grouped by type/severity
- [ ] T034 [P] [US3] Create `views/hr/run-checks/index.php` to render filter form (date range, check type, status), batch history table with columns: batch id, check type, actor, started at, total findings, new notifications, duplicates skipped, and link to details
- [ ] T035 [US3] Create `views/hr/run-checks/show.php` to render batch summary (actor, time, type, status, counts) and findings table grouped by type with columns: entity, candidate, responsible user, due date, severity, recommended action, notification status (created/skipped/existing), and archive recommendation if applicable
- [ ] T036 [US3] Update `views/notifications/index.php` to display new escalation notification types with appropriate labels (e.g., "Missing Feedback Reminder", "Offer Expiry Alert", "Background Check Follow-Up", "Onboarding Task Overdue") and reference links
- [ ] T037 [US3] Validate edge cases: duplicate run produces zero new notifications; non-HR users receive access-denied; invalid check type returns validation error; empty scope returns "no findings" summary

**Checkpoint**: User Stories 1, 2, AND 3 all work independently — pipeline analytics, D&I reports, and run checks with escalations are functional.

---

## Phase 6: User Story 4 — Archive Closed and Rejected Records (Priority: P4)

**Goal**: HR Admin reviews archive eligibility for closed requisitions and rejected applications, approves archive with a reason after eligibility revalidation, and views archived records in a dedicated archive view while active queues exclude them. All archive actions are audited.

**Independent Test**: Close a requisition or reject a candidate with no pending work, run archive eligibility checks, approve archive with a reason, and verify the record leaves active lists but remains in archive views with full audit history.

### Implementation for User Story 4

- [ ] T038 [US4] Peer review spec, plan, route map, RBAC, privacy, and acceptance criteria for US4 before implementation
- [ ] T039 [US4] Create `app/Services/ArchiveEligibilityService.php` with methods: `checkRequisitionEligibility(requisitionId)` (closed + all applications terminal), `checkApplicationEligibility(applicationId)` (rejected + no pending assessment, interview, feedback, offer, background check, onboarding, or appeal), and `revalidateEligibility(entityType, entityId)` for approval-time recheck
- [ ] T040 [US4] Implement `HrDataRetentionController::archiveIndex` action in `app/Controllers/HrDataRetentionController.php` to list archive recommendations from run-check findings plus archive action history from `ComplianceMaintenanceRepository` and `DataRetentionRepository` with entity type, status, and date filters; enforce `DataRetentionPolicy::canViewArchive`
- [ ] T041 [US4] Implement `HrDataRetentionController::archiveShow` action in `app/Controllers/HrDataRetentionController.php` to load archive detail including eligibility status, blockers (pending work counts), related records, audit events, and archive action history; enforce `DataRetentionPolicy::canViewArchiveDetail`
- [ ] T042 [US4] Implement `HrDataRetentionController::approveArchive` action in `app/Controllers/HrDataRetentionController.php` with CSRF validation, reason required, `DataRetentionPolicy::canApproveArchive`, call `ArchiveEligibilityService::revalidateEligibility` before applying, set `archived_at`/`archived_by` on the target record, persist `archive_actions` row, write `compliance_audit_events`, and redirect with success/blocked message
- [ ] T043 [P] [US4] Create `views/hr/archive/index.php` to render filter form (entity type, status, date range), archive recommendation table with columns: entity type, entity id, requisition/candidate name, current status, terminal date, related active work counts, eligibility status, and action link
- [ ] T044 [US4] Create `views/hr/archive/show.php` to render archive detail: eligibility status and reason, blocker list (pending assessments, interviews, feedback, offers, onboarding), affected record summary, archive action history, audit event log, and approve form with reason textarea (if eligible)
- [ ] T045 [US4] Update active queue queries in relevant repositories (`app/Repositories/ReportRepository.php`, existing requisition/application listing queries) to exclude records where `archived_at IS NOT NULL`
- [ ] T046 [US4] Validate edge cases: archive blocked when pending work exists; duplicate archive request returns validation error; eligibility changes between review and approval triggers revalidation block; non-HR users receive access-denied; archived records remain visible in archive views and aggregate reports

**Checkpoint**: All four user stories are independently functional — pipeline analytics, D&I reports, run checks with escalations, and archive management are complete.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories.

- [ ] T047 [P] Run `php -l` syntax checks on all new and modified PHP files per quickstart.md verification commands
- [ ] T048 [P] Verify RBAC enforcement: non-HR users cannot access `/hr/reports/diversity`, `/hr/run-checks`, `/hr/archive`, or sensitive archive details; candidates can only update their own demographics
- [ ] T049 [P] Verify audit trail completeness: report generation, run-check execution, escalation creation, archive approval, archive block, demographic update, demographic withdrawal, and sensitive-access denial all produce `compliance_audit_events` records
- [ ] T050 Walk through the full quickstart.md manual demo path end-to-end and document acceptance evidence (screenshots or notes)
- [ ] T051 Code cleanup: consistent error handling, validation messages, empty-state messaging, and Tailwind CSS styling alignment across all new views
- [ ] T052 Performance spot-check: pipeline report and D&I report render in under 3 seconds with demo data; run checks for 25+ flagged records complete in under 5 minutes; archive views for 100 records render in under 3 seconds

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 (migration applied, enums exist) — BLOCKS all user stories
- **User Stories (Phases 3–6)**: All depend on Phase 2 completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3 → P4)
- **Polish (Phase 7)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Phase 2 — No dependencies on other stories
- **User Story 2 (P2)**: Can start after Phase 2 — No dependencies on other stories (candidate demographics and D&I reports are self-contained)
- **User Story 3 (P3)**: Can start after Phase 2 — No dependencies on other stories (run checks query existing data; archive recommendations created here are consumed by US4)
- **User Story 4 (P4)**: Can start after Phase 2 — Optionally benefits from US3 archive recommendations but can function independently using `ArchiveEligibilityService` directly

### Within Each User Story

- Peer review gate before implementation
- Repository/service methods before controller actions
- Controller actions before views
- Core implementation before edge-case validation
- Story complete before moving to next priority

### Parallel Opportunities

- All Phase 1 tasks marked [P] can run in parallel (T002–T006)
- All Phase 2 tasks marked [P] can run in parallel (T008–T013)
- Once Phase 2 completes, all user stories can start in parallel (if team capacity allows)
- Within US2: T022 (DiversityReportSuppressor) and T023–T024 (candidate demographics) can run in parallel
- Within US3: T034 (index view) can run in parallel with service/controller tasks
- Within US4: T043 (index view) can run in parallel with service/controller tasks

---

## Parallel Example: User Story 1

```bash
# After Phase 2 is complete, launch US1:
Task T016: "Peer review for US1"

# Then sequential implementation:
Task T017: "Implement HrReportController::pipeline in app/Controllers/HrReportController.php"
Task T018: "Implement bottleneck detection logic"
Task T019: "Update views/hr/reports/pipeline.php"
Task T020: "Validate edge cases"
```

## Parallel Example: User Stories 2 & 3 (if staffed)

```bash
# Developer A works on US2:
Task T022: "Create DiversityReportSuppressor in app/Services/DiversityReportSuppressor.php"  # [P]
Task T023: "Implement CandidateController::updateDemographics"
Task T024: "Update views/candidate/profile.php"
Task T025: "Implement HrReportController::diversity"
Task T026: "Create views/hr/reports/diversity.php"

# Developer B works on US3 simultaneously:
Task T029: "Create ComplianceRunCheckService in app/Services/ComplianceRunCheckService.php"
Task T030: "Implement duplicate escalation prevention"
Task T031: "Implement HrComplianceCheckController::index"
Task T032: "Implement HrComplianceCheckController::store"
Task T034: "Create views/hr/run-checks/index.php"  # [P]
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
3. Complete Phase 3: User Story 1 — Pipeline Bottleneck Analytics
4. **STOP and VALIDATE**: Test User Story 1 independently via quickstart.md steps 1–3
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo (D&I + Demographics)
4. Add User Story 3 → Test independently → Deploy/Demo (Run Checks + Escalations)
5. Add User Story 4 → Test independently → Deploy/Demo (Archive Management)
6. Each story adds value without breaking previous stories

### Parallel Team Strategy

With 3 developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1 (P1 — MVP)
   - Developer B: User Story 2 (P2 — D&I)
   - Developer C: User Story 3 (P3 — Run Checks)
3. After US1–US3 complete, any developer picks up User Story 4 (P4 — Archive)
4. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story is independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
- Avoid: REST API contracts, separated frontend tasks, unreviewed implementation, and unverifiable acceptance criteria
- All controllers use `routes/web.php` browser routes, server-rendered PHP templates, PDO repositories, native sessions, CSRF protection, and server-side validation per constitution
