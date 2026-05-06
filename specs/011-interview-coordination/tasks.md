# Tasks: Interview Coordination Workflows

**Input**: Design documents from `specs/011-interview-coordination/`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/interview-coordination-web.md`, `route-map.md`, `quickstart.md`

**Tests**: Automated TDD was not explicitly requested. Each user story includes a manual acceptance/demo task tied to `specs/011-interview-coordination/quickstart.md`; add targeted PHP checks during polish.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirm planning baseline and create shared files/directories before code changes.

- [X] T001 Peer review spec, plan, data model, route map, RBAC, privacy, and acceptance criteria in `specs/011-interview-coordination/plan.md`
- [X] T002 [P] Review existing interview controllers and repositories against route map in `app/Controllers/HrInterviewController.php` and `app/Repositories/InterviewRepository.php`
- [X] T003 [P] Review existing interviewer workflow and feedback observer restrictions in `app/Controllers/InterviewerInterviewController.php` and `app/Policies/InterviewFeedbackPolicy.php`
- [X] T004 [P] Create migration placeholder for interview coordination schema changes in `database/migrations/011_interview_coordination_workflows.sql`
- [X] T005 [P] Create candidate interview view directory with placeholder page in `views/candidate/interviews/show.php`
- [X] T006 [P] Create shared manual acceptance notes section for this feature in `specs/011-interview-coordination/quickstart.md`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared database, enum, policy, repository, and routing support required by all stories.

**CRITICAL**: No user story work can begin until this phase is complete.

- [X] T007 Add staff capability, recommendation snapshot, briefing snapshot, workspace, workspace history, extension request, and audit-related schema changes in `database/migrations/011_interview_coordination_workflows.sql`
- [X] T008 Mirror finalized interview coordination schema additions in `database/schema.sql`
- [X] T009 [P] Add assignment source and extended panel role helpers in `app/Enums/InterviewAssignmentRole.php`
- [X] T010 [P] Add coordination audit actions for recommendations, assignments, briefing, workspace, extensions, and denied access in `app/Enums/InterviewAuditAction.php`
- [X] T011 [P] Add extension request status enum in `app/Enums/InterviewExtensionStatus.php`
- [X] T012 [P] Add interview policy methods for recommendations, workspace access, extension decisions, audit view, and observer restrictions in `app/Policies/InterviewPolicy.php`
- [X] T013 Extend audit recording to support changed fields, reasons, and denied access metadata in `app/Repositories/InterviewAuditRepository.php`
- [X] T014 Add shared interview lookup, candidate ownership, participant assignment, effective end time, and conflict helper methods in `app/Repositories/InterviewRepository.php`
- [X] T015 Register new HR, interviewer, and candidate interview routes in `routes/web.php`
- [X] T016 Add candidate interview controller skeleton with authorization checks in `app/Controllers/CandidateInterviewController.php`
- [X] T017 Update navigation links for HR, interviewer, and candidate interview entry points in `views/layouts/app.php`

**Checkpoint**: Foundation ready - user story implementation can now begin.

---

## Phase 3: User Story 1 - HR Schedules a Balanced Panel (Priority: P1) MVP

**Goal**: HR can schedule an eligible interview and save a balanced panel using active HR, senior technical, interviewer, and optional observer participants.

**Independent Test**: Move an application to interview stage, enter proposed date/duration, generate grouped panel candidates, save a balanced panel, and verify the HR detail page shows locked assignments.

### Manual Acceptance for User Story 1

- [ ] T018 [P] [US1] Document HR balanced-panel manual demo results in `specs/011-interview-coordination/quickstart.md`

### Implementation for User Story 1

- [ ] T019 [P] [US1] Implement staff panel capability repository queries in `app/Repositories/InterviewRepository.php`
- [ ] T020 [P] [US1] Add HR scheduling form fields for panel mix, assignment source, observer flag, and override reason in `views/hr/interviews/form.php`
- [ ] T021 [US1] Implement panel eligibility loading in `app/Controllers/HrInterviewController.php`
- [ ] T022 [US1] Implement balanced-panel validation for HR representative, senior technical interviewer, official interviewer, observer, duplicate users, and inactive users in `app/Controllers/HrInterviewController.php`
- [ ] T023 [US1] Persist assignment metadata, briefing snapshot creation, and scheduling audit events when saving interviews in `app/Repositories/InterviewRepository.php`
- [ ] T024 [US1] Render saved panel assignments, missing role warnings, and briefing snapshot status in `views/hr/interviews/show.php`
- [ ] T025 [US1] Validate HR scheduling MVP using steps 1-7 in `specs/011-interview-coordination/quickstart.md`

**Checkpoint**: User Story 1 is independently functional and demoable as the MVP.

---

## Phase 4: User Story 2 - System Recommends Low-Conflict Assignments (Priority: P1)

**Goal**: HR receives deterministic panel recommendations ranked by role eligibility, schedule conflicts, workload counts, and tie-breaker reason.

**Independent Test**: Prepare staff with different upcoming interview counts and overlapping sessions, generate recommendations, and verify lower-workload non-conflicted staff rank first.

### Manual Acceptance for User Story 2

- [ ] T026 [P] [US2] Document recommendation ranking manual demo results in `specs/011-interview-coordination/quickstart.md`

### Implementation for User Story 2

- [ ] T027 [P] [US2] Implement workload count and conflict candidate queries in `app/Repositories/InterviewRepository.php`
- [ ] T028 [P] [US2] Implement deterministic panel recommendation ranking in `app/Services/InterviewPanelRecommendationService.php`
- [ ] T029 [US2] Implement HR recommendation form action and validation in `app/Controllers/HrInterviewController.php`
- [ ] T030 [US2] Render recommendation results with role fit, workload counts, conflict markers, and reasons in `views/hr/interviews/recommendations.php`
- [ ] T031 [US2] Persist recommendation snapshots and accepted recommendation linkage in `app/Repositories/InterviewRepository.php`
- [ ] T032 [US2] Enforce conflict override reason handling during interview save/update in `app/Controllers/HrInterviewController.php`
- [ ] T033 [US2] Validate recommendation behavior using seeded workload and conflict cases in `specs/011-interview-coordination/quickstart.md`

**Checkpoint**: User Story 2 is independently functional and can be demoed with controlled staff workload data.

---

## Phase 5: User Story 5 - Interview Coordination Changes Are Audited (Priority: P1)

**Goal**: HR can trace scheduling, assignment, extension, and live coding changes with actor, timestamp, action type, changed values, and reasons where required.

**Independent Test**: Schedule an interview, change assignments, save workspace content, approve an extension, and verify each action appears in audit history.

### Manual Acceptance for User Story 5

- [ ] T034 [P] [US5] Document audit-history manual demo results in `specs/011-interview-coordination/quickstart.md`

### Implementation for User Story 5

- [ ] T035 [P] [US5] Add audit query methods for interview detail and dedicated audit view in `app/Repositories/InterviewAuditRepository.php`
- [ ] T036 [US5] Implement audit event writes for scheduling, rescheduling, cancellation, completion, recommendation acceptance, assignment changes, overrides, and removals in `app/Repositories/InterviewRepository.php`
- [ ] T037 [US5] Implement HR audit page action in `app/Controllers/HrInterviewController.php`
- [ ] T038 [US5] Render searchable interview coordination audit history in `views/hr/interviews/audit.php`
- [ ] T039 [US5] Add audit summary panel with actor, action, timestamp, and changed values in `views/hr/interviews/show.php`
- [ ] T040 [US5] Validate audit coverage using scheduling and assignment actions in `specs/011-interview-coordination/quickstart.md`

**Checkpoint**: User Story 5 auditability is independently testable for scheduling and assignment flows before workspace and extension stories add their event types.

---

## Phase 6: User Story 3 - Interview Participants Use Simulated Coding Workspace (Priority: P2)

**Goal**: Candidate and assigned interview participants use a database-backed, refresh-based simulated coding workspace with observer read-only behavior.

**Independent Test**: Save candidate code, refresh the interviewer workspace, verify latest content, confirm observer read-only behavior, and confirm unassigned access is blocked.

### Manual Acceptance for User Story 3

- [ ] T041 [P] [US3] Document simulated workspace manual demo results in `specs/011-interview-coordination/quickstart.md`

### Implementation for User Story 3

- [ ] T042 [P] [US3] Implement workspace state and history repository methods in `app/Repositories/InterviewRepository.php`
- [ ] T043 [P] [US3] Implement HR workspace actions in `app/Controllers/HrInterviewController.php`
- [ ] T044 [P] [US3] Implement interviewer workspace actions with observer read-only enforcement in `app/Controllers/InterviewerInterviewController.php`
- [ ] T045 [P] [US3] Implement candidate workspace actions with ownership checks in `app/Controllers/CandidateInterviewController.php`
- [ ] T046 [US3] Render HR workspace oversight page in `views/hr/interviews/workspace.php`
- [ ] T047 [US3] Render interviewer and observer workspace page in `views/interviewer/interviews/workspace.php`
- [ ] T048 [US3] Render candidate workspace page in `views/candidate/interviews/show.php`
- [ ] T049 [US3] Record workspace history and `WORKSPACE_UPDATED` audit events for every workspace save in `app/Repositories/InterviewRepository.php`
- [ ] T050 [US3] Validate refresh-based workspace and observer restrictions using steps 8-12 in `specs/011-interview-coordination/quickstart.md`

**Checkpoint**: User Story 3 is independently functional without real-time collaboration infrastructure.

---

## Phase 7: User Story 4 - HR Approves Technical-Issue Extensions (Priority: P2)

**Goal**: Assigned interviewers can request technical-issue extensions, and HR can approve, deny, or record cancellation with schedule conflict warnings.

**Independent Test**: Submit an extension request as assigned interviewer, approve it as HR, and verify the effective interview time and audit trail update.

### Manual Acceptance for User Story 4

- [ ] T051 [P] [US4] Document extension approval manual demo results in `specs/011-interview-coordination/quickstart.md`

### Implementation for User Story 4

- [ ] T052 [P] [US4] Implement extension request repository methods and state transitions in `app/Repositories/InterviewRepository.php`
- [ ] T053 [P] [US4] Add interviewer extension request and cancel actions in `app/Controllers/InterviewerInterviewController.php`
- [ ] T054 [P] [US4] Add HR extension review, approve, and deny actions in `app/Controllers/HrInterviewController.php`
- [ ] T055 [US4] Render extension request form and status on interviewer interview pages in `views/interviewer/interviews/show.php`
- [ ] T056 [US4] Render HR extension review and decision form in `views/hr/interviews/extension.php`
- [ ] T057 [US4] Update HR interview detail to show effective duration, pending requests, and extension decisions in `views/hr/interviews/show.php`
- [ ] T058 [US4] Add extension conflict warning and acknowledgement handling in `app/Repositories/InterviewRepository.php`
- [ ] T059 [US4] Record extension requested, approved, denied, and cancelled audit events in `app/Repositories/InterviewAuditRepository.php`
- [ ] T060 [US4] Validate extension approval and conflict warning behavior using steps 13-15 in `specs/011-interview-coordination/quickstart.md`

**Checkpoint**: User Story 4 is independently functional and preserves HR control over extra time.

---

## Final Phase: Polish & Cross-Cutting Concerns

**Purpose**: Verification, cleanup, security hardening, and final evidence across all stories.

- [ ] T061 [P] Run PHP syntax checks listed in quickstart and record results in `specs/011-interview-coordination/quickstart.md`
- [ ] T062 [P] Review RBAC and candidate privacy for HR, interviewer, observer, and candidate paths in `app/Policies/InterviewPolicy.php`
- [ ] T063 [P] Review CSRF tokens, validation errors, and flash messages across interview forms in `views/hr/interviews/form.php`, `views/interviewer/interviews/workspace.php`, and `views/candidate/interviews/show.php`
- [ ] T064 [P] Review page performance for recommendation and audit history demo data in `app/Repositories/InterviewRepository.php`
- [ ] T065 Update feature limitations and known non-real-time workspace behavior in `specs/011-interview-coordination/quickstart.md`
- [ ] T066 Run full manual demo path and collect evidence notes in `specs/011-interview-coordination/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- Setup (Phase 1) has no dependencies.
- Foundational (Phase 2) depends on Setup and blocks all user stories.
- User Story phases depend on Foundational completion.
- P1 delivery order is US1, US2, then US5.
- P2 delivery order is US3, then US4.
- Polish depends on all desired user stories being complete.

### User Story Dependencies

- US1 (P1) can start after Foundational and is the MVP.
- US2 (P1) can start after Foundational but integrates naturally with US1 scheduling form and save flow.
- US5 (P1) can start after Foundational and becomes more complete as later stories add workspace and extension event types.
- US3 (P2) can start after Foundational and needs the interview session/assignment foundation.
- US4 (P2) can start after Foundational and needs the interview session effective-time foundation.

### Parallel Opportunities

- Setup tasks T002-T006 can run in parallel after T001 starts.
- Foundational enum/policy tasks T009-T012 can run in parallel with schema task T007.
- US1 tasks T019 and T020 can run in parallel before controller integration.
- US2 tasks T027 and T028 can run in parallel before controller/view integration.
- US3 controller tasks T043-T045 can run in parallel after T042 defines repository contracts.
- US4 controller tasks T053-T054 can run in parallel after T052 defines repository contracts.
- Polish tasks T061-T064 can run in parallel.

---

## Parallel Example: User Story 1

```text
Task: "T019 [US1] Implement staff panel capability repository queries in app/Repositories/InterviewRepository.php"
Task: "T020 [US1] Add HR scheduling form fields for panel mix, assignment source, observer flag, and override reason in views/hr/interviews/form.php"
```

## Parallel Example: User Story 2

```text
Task: "T027 [US2] Implement workload count and conflict candidate queries in app/Repositories/InterviewRepository.php"
Task: "T028 [US2] Implement deterministic panel recommendation ranking in app/Services/InterviewPanelRecommendationService.php"
```

## Parallel Example: User Story 3

```text
Task: "T043 [US3] Implement HR workspace actions in app/Controllers/HrInterviewController.php"
Task: "T044 [US3] Implement interviewer workspace actions with observer read-only enforcement in app/Controllers/InterviewerInterviewController.php"
Task: "T045 [US3] Implement candidate workspace actions with ownership checks in app/Controllers/CandidateInterviewController.php"
```

## Parallel Example: User Story 4

```text
Task: "T053 [US4] Add interviewer extension request and cancel actions in app/Controllers/InterviewerInterviewController.php"
Task: "T054 [US4] Add HR extension review, approve, and deny actions in app/Controllers/HrInterviewController.php"
```

## Parallel Example: User Story 5

```text
Task: "T035 [US5] Add audit query methods for interview detail and dedicated audit view in app/Repositories/InterviewAuditRepository.php"
Task: "T038 [US5] Render searchable interview coordination audit history in views/hr/interviews/audit.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 setup.
2. Complete Phase 2 foundational schema, policy, repository, and routes.
3. Complete Phase 3 User Story 1.
4. Stop and validate HR balanced-panel scheduling independently with `specs/011-interview-coordination/quickstart.md`.

### Incremental Delivery

1. Add US1 for schedule and balanced panel MVP.
2. Add US2 for workload/conflict recommendation quality.
3. Add US5 for audit visibility across scheduling and assignments.
4. Add US3 for simulated coding workspace.
5. Add US4 for HR-approved technical-issue extensions.
6. Run polish verification and full quickstart demo.

### Team Strategy

1. One developer owns database/repository foundation.
2. One developer owns HR scheduling/recommendation views and controller actions.
3. One developer owns participant workspace/extension flows and policy hardening.
4. Reviewer verifies diagram traceability, Vanilla PHP monolith compliance, RBAC, validation, privacy, schema correctness, and acceptance criteria before implementation begins.

## Notes

- `[P]` tasks are marked only when they touch different files or can proceed before dependent integration work.
- `[US1]`, `[US2]`, `[US3]`, `[US4]`, and `[US5]` map directly to user stories in `specs/011-interview-coordination/spec.md`.
- Every story has a manual acceptance task tied to `specs/011-interview-coordination/quickstart.md`.
- Do not add REST APIs, websocket services, real-time collaboration services, separated frontend code, or real code execution.
