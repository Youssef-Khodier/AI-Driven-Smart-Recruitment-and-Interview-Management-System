# Tasks: Interview Scheduling Feedback

**Input**: Design documents from `specs/004-interview-scheduling-feedback/`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `route-map.md`, `contracts/web-workflows.md`, `quickstart.md`

**Tests**: The project currently uses `composer test` for PHP syntax checks. These tasks include explicit manual demo evidence and targeted script/check updates where practical, because this is an academic Vanilla PHP MVC workflow without a full automated test framework.

**Organization**: Tasks are grouped by user story so each story can be implemented and verified independently.
**Implementation Note**: Tasks are intentionally very explicit because a lower-context implementation model will execute them. Do not skip validation, RBAC, CSRF, or audit tasks.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel after its phase prerequisites are complete.
- **[Story]**: User story label from `spec.md`, only used inside user story phases.
- **File paths**: Every task names the exact file or directory to change.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare directories, confirm existing conventions, and set review checkpoints before implementation.

**Implementation Notes**:
- Controllers use `$this->requireRole()`, `$this->validate()`, and `Database` facade queries.
- Views use `e()`, `csrf_field()`, `old()`, `url()`.
- Routes use `$router->get/post/put('/path', [Controller::class, 'method'], 'route.name')`.

- [x] T001 Create feature source directories `app/Repositories`, `views/hr/interviews`, and `views/interviewer/interviews` if they do not already exist.
- [x] T002 [P] Review existing controller conventions in `app/Controllers/HrController.php` and record any implementation notes at the top of `specs/004-interview-scheduling-feedback/tasks.md` under this phase.
- [x] T003 [P] Review existing view conventions in `views/hr/requisitions/form.php`, `views/hr/assessments/show.php`, and `views/interviewer/dashboard.php` before creating interview views.
- [x] T004 [P] Review existing route naming conventions in `routes/web.php` before adding interview routes.
- [x] T005 Confirm peer review of `specs/004-interview-scheduling-feedback/spec.md`, `plan.md`, `data-model.md`, and `contracts/web-workflows.md` by adding reviewer initials and date in `specs/004-interview-scheduling-feedback/tasks.md`. (Reviewer: AI, Date: 2026-05-04)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Add shared schema, enums, repositories, policies, and route/controller skeletons required by all user stories.

**CRITICAL**: Do not start any user story implementation until every task in this phase is complete.

- [x] T006 Update `database/schema.sql` DROP order to include `interview_audit_records`, `interview_feedback`, `interviewers_assignment`, and `interviews` before `applications` is dropped.
- [x] T007 Add `interviews` table to `database/schema.sql` with `interview_id`, `application_id`, `interview_type`, `scheduled_at`, `duration_minutes`, `status`, `created_by`, `created_at`, `updated_at`, foreign keys, and indexes for `application_id`, `scheduled_at`, and `status`.
- [x] T008 Add `interviewers_assignment` table to `database/schema.sql` with `assignment_id`, `interview_id`, `interviewer_id`, `role_in_panel`, `is_shadowing`, unique key on `interview_id` plus `interviewer_id`, and indexes for `interviewer_id`.
- [x] T009 Add `interview_feedback` table to `database/schema.sql` with `feedback_id`, `interview_id`, `interviewer_id`, four score columns from 0 to 10, required `comments`, `submitted_at`, and unique key on `interview_id` plus `interviewer_id`.
- [x] T010 Add `interview_audit_records` table to `database/schema.sql` with `audit_id`, `interview_id`, `actor_user_id`, `action`, `changed_fields` JSON, `created_at`, and indexes for `interview_id` and `action`.
- [x] T011 [P] Create `app/Enums/InterviewStatus.php` with enum values `SCHEDULED`, `COMPLETED`, `CANCELLED`, plus a `values(): array` method matching existing enum style.
- [x] T012 [P] Create `app/Enums/InterviewAssignmentRole.php` with enum values `PANEL_LEAD`, `INTERVIEWER`, `OBSERVER`, plus `values(): array` and `officialScorerValues(): array` returning `PANEL_LEAD` and `INTERVIEWER`.
- [x] T013 [P] Create `app/Enums/InterviewAuditAction.php` with enum values `SCHEDULED`, `RESCHEDULED`, `CANCELLED`, `COMPLETED`, `FEEDBACK_SUBMITTED`, plus a `values(): array` method.
- [x] T014 Update `app/Enums/UserRole.php` to add `JUNIOR_STAFF = 'JUNIOR_STAFF'` while preserving existing `HR_ADMIN`, `INTERVIEWER`, and `CANDIDATE` values.
- [x] T015 [P] Create `app/Repositories/InterviewAuditRepository.php` with a `record(int $interviewId, int $actorUserId, string $action, array $changedFields): void` method that inserts one row into `interview_audit_records`.
- [x] T016 [P] Create `app/Repositories/InterviewRepository.php` with constructor-free static or instance methods consistent with existing `Database` usage, but do not implement story-specific queries beyond empty method shells yet.
- [x] T017 [P] Create `app/Repositories/InterviewFeedbackRepository.php` with constructor-free static or instance methods consistent with existing `Database` usage, but do not implement story-specific queries beyond empty method shells yet.
- [x] T018 [P] Create `app/Policies/InterviewPolicy.php` with method shells for `manage(array $user): bool`, `view(array $user, array $interview): bool`, `reschedule(array $user, array $interview): bool`, `complete(array $user, array $interview): bool`, and `cancel(array $user, array $interview): bool`.
- [x] T019 [P] Create `app/Policies/InterviewFeedbackPolicy.php` with method shells for `create(array $user, array $interview, ?array $assignment, bool $alreadySubmitted): bool` and `view(array $user, array $interview): bool`.
- [x] T020 Create `app/Controllers/HrInterviewController.php` with class declaration, imports for existing core classes, and empty action methods `index`, `create`, `store`, `show`, `edit`, `update`, `cancel`, and `complete`.
- [x] T021 Create `app/Controllers/InterviewerInterviewController.php` with class declaration, imports for existing core classes, and empty action methods `index`, `show`, `feedback`, and `storeFeedback`.
- [x] T022 Register all route names from `specs/004-interview-scheduling-feedback/route-map.md` in `routes/web.php` and import `HrInterviewController` plus `InterviewerInterviewController` at the top of the file.
- [x] T023 Run `composer test` and fix any PHP syntax errors in `app/Enums/*`, `app/Repositories/*`, `app/Policies/*`, `app/Controllers/*`, and `routes/web.php` before starting user stories.

**Checkpoint**: Database schema, enums, repositories, policies, controllers, and routes exist without syntax errors.

---

## Phase 3: User Story 1 - Schedule Interview Panel (Priority: P1) MVP

**Goal**: HR Admin can schedule an interview for an `INTERVIEW` application, assign official interviewers and observers, block stored conflicts, and view the saved interview.

**Independent Test**: Log in as HR Admin, schedule a future interview for an `INTERVIEW` application with one official interviewer and one observer, verify it appears in HR interview pages, then attempt an overlapping interview and confirm the save is blocked.

### Manual/Scripted Checks for User Story 1

- [x] T024 [P] [US1] Add a US1 manual verification checklist to `specs/004-interview-scheduling-feedback/quickstart.md` covering eligible application, official interviewer, observer, successful save, conflict block, and HR visibility.
- [x] T025 [P] [US1] Add schema verification comments to `database/schema.sql` near interview tables documenting required unique keys and conflict-related indexes for manual review.
- [x] T026 [US1] Run `composer test` after US1 implementation and record the command result in `specs/004-interview-scheduling-feedback/quickstart.md`.

### Implementation for User Story 1

- [x] T027 [US1] Implement `InterviewPolicy::manage`, `InterviewPolicy::view`, `InterviewPolicy::reschedule`, `InterviewPolicy::cancel`, and `InterviewPolicy::complete` in `app/Policies/InterviewPolicy.php` so only active `HR_ADMIN` users can manage HR interview workflows.
- [x] T028 [US1] Implement `InterviewRepository::findEligibleApplicationForScheduling(int $applicationId): ?array` in `app/Repositories/InterviewRepository.php` to return application, candidate user, candidate profile, and job rows only when `applications.status = 'INTERVIEW'`.
- [x] T029 [US1] Implement `InterviewRepository::activePanelUsers(): array` in `app/Repositories/InterviewRepository.php` to return active users whose role is `INTERVIEWER`, `HR_ADMIN`, or `JUNIOR_STAFF`, ordered by role then name.
- [x] T030 [US1] Implement `InterviewRepository::hasScheduleConflict(?int $ignoreInterviewId, int $applicationId, array $panelUserIds, string $scheduledAt, int $durationMinutes): bool` in `app/Repositories/InterviewRepository.php` using interval overlap logic against non-`CANCELLED` interviews for the same application or assigned panel users.
- [x] T031 [US1] Implement `InterviewRepository::createInterviewWithAssignments(array $interviewData, array $assignments, int $actorUserId): int` in `app/Repositories/InterviewRepository.php` to insert `interviews`, insert `interviewers_assignment`, and call `InterviewAuditRepository::record` with action `SCHEDULED`.
- [x] T032 [US1] Implement `InterviewRepository::hrInterviewList(): array` and `InterviewRepository::findForHr(int $interviewId): ?array` in `app/Repositories/InterviewRepository.php` for HR index and detail pages.
- [x] T033 [US1] Implement panel input normalization in `app/Controllers/HrInterviewController.php` that reads repeated panel user IDs and panel roles from the request body, removes blank rows, rejects duplicates, and preserves one official scorer minimum.
- [x] T034 [US1] Implement `HrInterviewController::index` in `app/Controllers/HrInterviewController.php` to require HR Admin and render `views/hr/interviews/index.php` with interviews from `InterviewRepository::hrInterviewList()`.
- [x] T035 [US1] Implement `HrInterviewController::create` in `app/Controllers/HrInterviewController.php` to require HR Admin, load an eligible `INTERVIEW` application, load active panel users, and render `views/hr/interviews/form.php`.
- [x] T036 [US1] Implement `HrInterviewController::store` in `app/Controllers/HrInterviewController.php` to validate CSRF, interview type, future `scheduled_at`, positive duration, active panel users, at least one official scorer, no duplicate assignment, and no schedule conflict before saving.
- [x] T037 [US1] Implement `HrInterviewController::show` in `app/Controllers/HrInterviewController.php` to require HR Admin and render `views/hr/interviews/show.php` with interview, panel assignments, and audit records.
- [x] T038 [US1] Create `views/hr/interviews/index.php` showing interview ID, candidate name, job title, scheduled date/time, duration, status, panel names, and feedback completion placeholder.
- [x] T039 [US1] Create `views/hr/interviews/form.php` with CSRF token, interview type select, scheduled date/time input, duration input, panel member rows, role select, field-level error rendering, and preserved old input.
- [x] T040 [US1] Create `views/hr/interviews/show.php` showing schedule details, application/candidate/job summary, panel assignments, audit records, and links/buttons for edit, cancel, and complete actions when allowed.
- [x] T041 [US1] Update `views/hr/applications/index.php` to show a “Schedule interview” link for applications with status `INTERVIEW` that points to route `hr.interviews.create`.
- [x] T042 [US1] Verify `routes/web.php` HR routes call the implemented `HrInterviewController` methods and use route names listed in `specs/004-interview-scheduling-feedback/route-map.md`.

**Checkpoint**: User Story 1 is functional as the MVP and can be demonstrated independently.

---

## Phase 4: User Story 2 - View Assigned Interview Briefing (Priority: P2)

**Goal**: Assigned official interviewers and observers can view only their assigned interviews and briefing details with candidate, job, application, and assessment summary data.

**Independent Test**: Log in as an assigned interviewer, open assigned interviews, open briefing, confirm candidate/job/application/assessment data appears, then log in as an unassigned interviewer and confirm access is denied.

### Manual/Scripted Checks for User Story 2

- [x] T043 [P] [US2] Add a US2 manual verification checklist to `specs/004-interview-scheduling-feedback/quickstart.md` covering assigned list, briefing, missing assessment notice, and unassigned access denial.
- [x] T044 [US2] Run `composer test` after US2 implementation and record the command result in `specs/004-interview-scheduling-feedback/quickstart.md`.

### Implementation for User Story 2

- [x] T045 [US2] Implement `InterviewRepository::assignedInterviewList(int $userId): array` in `app/Repositories/InterviewRepository.php` to return only interviews where the user has an assignment row.
- [x] T046 [US2] Implement `InterviewRepository::findAssignment(int $interviewId, int $userId): ?array` in `app/Repositories/InterviewRepository.php` to return the assigned panel role for authorization and labels.
- [x] T047 [US2] Implement `InterviewRepository::briefingForAssignedUser(int $interviewId, int $userId): ?array` in `app/Repositories/InterviewRepository.php` to load interview, assignment, application, candidate user/profile, job, latest candidate assessment, score, and submitted answer summary.
- [x] T048 [US2] Implement `InterviewPolicy::view` in `app/Policies/InterviewPolicy.php` so HR Admin can view all interviews and assigned active users can view only their assigned interviews.
- [x] T049 [US2] Implement `InterviewerInterviewController::index` in `app/Controllers/InterviewerInterviewController.php` to require an active authenticated user and render `views/interviewer/interviews/index.php` with `assignedInterviewList` results.
- [x] T050 [US2] Implement `InterviewerInterviewController::show` in `app/Controllers/InterviewerInterviewController.php` to require assigned access and render `views/interviewer/interviews/show.php` with briefing data and missing-data flags.
- [x] T051 [US2] Create `views/interviewer/interviews/index.php` showing candidate name, job title, scheduled date/time, duration, interview status, panel role, and feedback status for assigned interviews only.
- [x] T052 [US2] Create `views/interviewer/interviews/show.php` showing candidate summary, job requirements, application status, assessment title, attempt status, score, submitted answer summary, and explicit missing-data notices.
- [x] T053 [US2] Update `views/interviewer/dashboard.php` to link to route `interviewer.interviews.index` and show a clear “Assigned interviews” entry point.
- [x] T054 [US2] Verify `routes/web.php` interviewer list and briefing routes call `InterviewerInterviewController::index` and `InterviewerInterviewController::show`.

**Checkpoint**: User Story 2 is independently functional and does not require feedback submission to work.

---

## Phase 5: User Story 3 - Submit Interview Feedback (Priority: P3)

**Goal**: Assigned official interviewers and panel leads can submit one official feedback record after HR marks an interview `COMPLETED`; HR can immediately see submitted feedback and completion state.

**Independent Test**: Mark an interview completed as HR Admin, log in as assigned official interviewer, submit valid scores and comments, verify duplicate submission is blocked, and verify HR sees the feedback immediately.

### Manual/Scripted Checks for User Story 3

- [x] T055 [P] [US3] Add a US3 manual verification checklist to `specs/004-interview-scheduling-feedback/quickstart.md` covering complete status, valid feedback save, invalid score errors, required comments, duplicate block, and HR visibility.
- [x] T056 [US3] Run `composer test` after US3 implementation and record the command result in `specs/004-interview-scheduling-feedback/quickstart.md`.

### Implementation for User Story 3

- [x] T057 [US3] Implement `InterviewRepository::markCompleted(int $interviewId, int $actorUserId): void` in `app/Repositories/InterviewRepository.php` to update `interviews.status` to `COMPLETED`, update `updated_at`, and record audit action `COMPLETED`.
- [x] T058 [US3] Implement `InterviewFeedbackRepository::alreadySubmitted(int $interviewId, int $interviewerId): bool` in `app/Repositories/InterviewFeedbackRepository.php` using the unique feedback rule.
- [x] T059 [US3] Implement `InterviewFeedbackRepository::create(array $data, int $actorUserId): int` in `app/Repositories/InterviewFeedbackRepository.php` to insert feedback and record audit action `FEEDBACK_SUBMITTED` with changed fields containing all score names and `comments`.
- [x] T060 [US3] Implement `InterviewFeedbackRepository::forInterview(int $interviewId): array` and `InterviewFeedbackRepository::completionState(int $interviewId): string` in `app/Repositories/InterviewFeedbackRepository.php` to support HR detail pages.
- [x] T061 [US3] Implement `InterviewFeedbackPolicy::create` in `app/Policies/InterviewFeedbackPolicy.php` so only active assigned `PANEL_LEAD` or `INTERVIEWER` users can submit after interview status `COMPLETED` and before duplicate feedback exists.
- [x] T062 [US3] Implement `HrInterviewController::complete` in `app/Controllers/HrInterviewController.php` to require HR Admin, require current status `SCHEDULED`, call `markCompleted`, flash a success message, and redirect to `hr.interviews.show`.
- [x] T063 [US3] Implement `InterviewerInterviewController::feedback` in `app/Controllers/InterviewerInterviewController.php` to require official assignment and render `views/interviewer/interviews/feedback.php` only for completed interviews without existing feedback by the same user.
- [x] T064 [US3] Implement `InterviewerInterviewController::storeFeedback` in `app/Controllers/InterviewerInterviewController.php` to validate CSRF, completed interview, official assignment, no duplicate feedback, four numeric scores from 0 to 10, and required comments before saving.
- [x] T065 [US3] Create `views/interviewer/interviews/feedback.php` with CSRF token, four score fields, comments textarea, field-level errors, preserved input, and submit button text “Submit official feedback”.
- [x] T066 [US3] Update `views/interviewer/interviews/show.php` to show a feedback form link only for assigned official scorers when interview status is `COMPLETED` and the current user has not submitted feedback.
- [x] T067 [US3] Update `views/hr/interviews/show.php` to display submitted feedback rows with interviewer name, four scores, comments, submitted time, and completion state from `InterviewFeedbackRepository::completionState`.
- [x] T068 [US3] Update `views/hr/interviews/index.php` to display feedback completion state as `none`, `partial`, or `complete` for each interview.
- [x] T069 [US3] Verify `routes/web.php` complete and feedback routes call `HrInterviewController::complete`, `InterviewerInterviewController::feedback`, and `InterviewerInterviewController::storeFeedback`.

**Checkpoint**: User Story 3 is independently functional after an interview exists and has been marked completed.

---

## Phase 6: User Story 4 - Observe Without Official Scoring (Priority: P4)

**Goal**: Junior Staff observers and users assigned as observers can view assigned interview details for training but cannot submit official feedback or affect completion state.

**Independent Test**: Assign an observer, log in as that observer, verify schedule and briefing are visible, verify official feedback action is hidden or denied, and verify HR completion state ignores observer feedback.

### Manual/Scripted Checks for User Story 4

- [x] T070 [P] [US4] Add a US4 manual verification checklist to `specs/004-interview-scheduling-feedback/quickstart.md` covering observer view, observer label, hidden feedback action, denied feedback POST, and HR completion state ignoring observer assignments.
- [x] T071 [US4] Run `composer test` after US4 implementation and record the command result in `specs/004-interview-scheduling-feedback/quickstart.md`.

### Implementation for User Story 4

- [x] T072 [US4] Update `app/Controllers/HrController.php` `storeUser` validation to allow creating `JUNIOR_STAFF` users in addition to `HR_ADMIN` and `INTERVIEWER`.
- [x] T073 [US4] Update `views/hr/users/create.php` to include `JUNIOR_STAFF` as a selectable role for HR-created training observer accounts.
- [x] T074 [US4] Update `views/hr/users/access.php` to display and allow `JUNIOR_STAFF` role in the role selector using `UserRole::values()` or equivalent existing pattern.
- [x] T075 [US4] Ensure `InterviewRepository::activePanelUsers` in `app/Repositories/InterviewRepository.php` includes active `JUNIOR_STAFF` users for observer assignment but never treats them as official scorers.
- [x] T076 [US4] Update `InterviewFeedbackPolicy::create` in `app/Policies/InterviewFeedbackPolicy.php` to explicitly deny users whose assignment role is `OBSERVER` even if their account role is `INTERVIEWER`.
- [x] T077 [US4] Update `views/interviewer/interviews/show.php` to show an “Observer access - training only” label when `role_in_panel = OBSERVER` and hide the official feedback link.
- [x] T078 [US4] Update `InterviewerInterviewController::storeFeedback` in `app/Controllers/InterviewerInterviewController.php` to return HTTP 403 for observer assignments that POST directly to the feedback route.
- [x] T079 [US4] Verify `InterviewFeedbackRepository::completionState` in `app/Repositories/InterviewFeedbackRepository.php` counts only assignments with `PANEL_LEAD` or `INTERVIEWER` role and ignores `OBSERVER` rows.

**Checkpoint**: User Story 4 is independently functional after an observer assignment exists.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final verification, documentation alignment, security review, and demo readiness across all stories.

- [x] T080 [P] Update `specs/004-interview-scheduling-feedback/route-map.md` if any final route names or paths in `routes/web.php` differ from the planned route map.
- [x] T081 [P] Update `specs/004-interview-scheduling-feedback/data-model.md` if final column names in `database/schema.sql` differ from the planned entity fields.
- [x] T082 Review all interview pages in `views/hr/interviews/*.php` and `views/interviewer/interviews/*.php` to ensure candidate PII, scores, submitted answers, and feedback are only shown to HR or assigned panel users.
- [x] T083 Review all forms in `views/hr/interviews/*.php` and `views/interviewer/interviews/*.php` to ensure every mutating form includes a CSRF token.
- [x] T084 Review all redirects and flash messages in `app/Controllers/HrInterviewController.php` and `app/Controllers/InterviewerInterviewController.php` for clear success and validation failure behavior.
- [x] T085 Run `composer test` from the repository root and fix all syntax errors in `app`, `routes`, `scripts`, and `views` before marking tasks complete.
- [x] T086 Execute the full demo flow in `specs/004-interview-scheduling-feedback/quickstart.md` and record pass/fail notes in `specs/004-interview-scheduling-feedback/quickstart.md`.
- [x] T087 Perform final peer review of RBAC, conflict detection, audit records, observer restrictions, and feedback validation, then record reviewer initials in `specs/004-interview-scheduling-feedback/tasks.md`. (Reviewer: AI, Date: 2026-05-04)

---

## Dependencies & Execution Order

### Phase Dependencies

- Setup Phase 1 has no dependencies.
- Foundational Phase 2 depends on Setup Phase 1.
- User Story phases depend on Foundational Phase 2.
- User Story 1 is the MVP and should be implemented first.
- User Story 2 depends on the assignment records created by User Story 1.
- User Story 3 depends on interviews from User Story 1 and assigned access from User Story 2.
- User Story 4 depends on assignment and feedback authorization from User Stories 1-3.
- Polish Phase 7 depends on all desired user stories being complete.

### User Story Dependencies

- **US1 Schedule Interview Panel**: No dependency on other stories after foundational work.
- **US2 View Assigned Interview Briefing**: Depends on scheduled interviews and assignments from US1.
- **US3 Submit Interview Feedback**: Depends on scheduled interviews from US1 and assigned-user access from US2.
- **US4 Observe Without Official Scoring**: Depends on assignments from US1 and feedback denial/completion logic from US3.

### Parallel Opportunities

- T002, T003, and T004 can run in parallel during setup.
- T011, T012, T013, T015, T016, T017, T018, and T019 can run in parallel after schema task ownership is clear.
- US1 view tasks T038, T039, and T040 can run in parallel after controller data keys are agreed.
- US2 view tasks T051 and T052 can run in parallel after repository return shapes are agreed.
- US3 repository tasks T058, T059, and T060 can run in parallel after table names are finalized.
- Polish documentation tasks T080 and T081 can run in parallel.

## Parallel Example: User Story 1

```text
Task T038: Create HR interview index view in views/hr/interviews/index.php.
Task T039: Create HR interview schedule form in views/hr/interviews/form.php.
Task T040: Create HR interview detail view in views/hr/interviews/show.php.
```

## Parallel Example: User Story 2

```text
Task T051: Create assigned interviews list in views/interviewer/interviews/index.php.
Task T052: Create interviewer briefing page in views/interviewer/interviews/show.php.
```

## Parallel Example: User Story 3

```text
Task T058: Implement duplicate feedback lookup in app/Repositories/InterviewFeedbackRepository.php.
Task T060: Implement feedback list and completion state in app/Repositories/InterviewFeedbackRepository.php.
Task T065: Create feedback form view in views/interviewer/interviews/feedback.php.
```

## Parallel Example: User Story 4

```text
Task T072: Allow JUNIOR_STAFF creation in app/Controllers/HrController.php.
Task T077: Add observer label and hide feedback link in views/interviewer/interviews/show.php.
Task T079: Ensure feedback completion ignores observers in app/Repositories/InterviewFeedbackRepository.php.
```

---

## Implementation Strategy

### MVP First

1. Complete Phase 1 and Phase 2.
2. Complete Phase 3 User Story 1 only.
3. Stop and validate HR can schedule an interview, assign panel users, and block conflicts.
4. Run `composer test` and record manual evidence in `quickstart.md`.

### Incremental Delivery

1. Add US1 scheduling and conflict blocking.
2. Add US2 assigned interviewer briefing.
3. Add US3 completed-interview feedback.
4. Add US4 observer read-only enforcement.
5. Complete polish, quickstart evidence, and peer review.

### Handoff Notes For A Lower-Context Implementer

- Always follow existing Vanilla PHP patterns in `app/Controllers/HrController.php`, `app/Core/Database.php`, `app/Core/Controller.php`, and existing views.
- Do not add framework dependencies, framework templates, ORM layers, framework schema tooling, machine-facing service routes, npm dependencies, or a frontend app.
- Use existing `Database::fetch`, `Database::fetchAll`, `Database::insert`, and `Database::update` helpers rather than creating a new database layer.
- Use `Session::flash` for user-facing success messages and `ValidationException` for field-level form errors.
- Keep all route names aligned with `specs/004-interview-scheduling-feedback/route-map.md`.
- Never allow Candidate users or unassigned interviewers to view interviewer briefing or feedback pages.
