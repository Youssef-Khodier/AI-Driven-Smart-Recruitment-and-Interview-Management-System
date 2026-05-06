# Tasks: Advanced Assessment Integrity and Adaptive Testing

**Input**: Design documents from `specs/010-assessment-integrity-adaptive-testing/`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/assessment-integrity-web.md`, `route-map.md`, `quickstart.md`

**Tests**: Manual acceptance checks are included because the plan specifies documented server-rendered page demos and targeted PHP syntax checks. Automated tests are not required by the current spec.

**Organization**: Tasks are grouped by user story so each story can be implemented and validated independently.
**Peer Review**: Peer-review tasks are included before implementation for each user story.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare the existing Vanilla PHP MVC assessment slice and documentation references.

- [X] T001 Review active feature context in specs/010-assessment-integrity-adaptive-testing/spec.md and specs/010-assessment-integrity-adaptive-testing/plan.md
- [X] T002 [P] Review route and UI contracts in specs/010-assessment-integrity-adaptive-testing/route-map.md and specs/010-assessment-integrity-adaptive-testing/contracts/assessment-integrity-web.md
- [X] T003 [P] Review data and research decisions in specs/010-assessment-integrity-adaptive-testing/data-model.md and specs/010-assessment-integrity-adaptive-testing/research.md
- [X] T004 Verify current agent context points to the active plan in AGENTS.md

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Establish shared schema, route, authorization, and service prerequisites required by all assessment stories.

**CRITICAL**: No user story work should begin until this phase is complete.

- [x] T005 Apply assessment integrity schema additions in database/migrations/010_assessment_integrity_adaptive_testing.sql
- [x] T006 Update fresh-install schema for assessment integrity tables and columns in database/schema.sql
- [x] T007 [P] Verify assessment role checks and candidate ownership guards in app/Controllers/AssessmentController.php
- [x] T008 [P] Verify assessment route names and HTTP methods in routes/web.php
- [x] T009 [P] Verify assessment attempt statuses and question type enums in app/Enums/AssessmentAttemptStatus.php and app/Enums/AssessmentQuestionType.php
- [x] T010 Add shared validation helpers for non-negative counts, positive durations, and difficulty tier values in app/Controllers/AssessmentController.php
- [x] T011 Document baseline manual demo setup requirements in specs/010-assessment-integrity-adaptive-testing/quickstart.md

**Checkpoint**: Foundation ready; user story implementation can proceed.

---

## Phase 3: User Story 1 - HR Defines Assessment Rules (Priority: P1) MVP

**Goal**: HR Admin can define and review difficulty-tier question counts and cooldown months for an assessment.

**Independent Test**: Create or edit an assessment with Easy, Medium, and Hard counts; confirm valid rules save, invalid counts fail, and insufficient bank warnings are visible.

### Manual Acceptance for User Story 1

- [x] T012 [P] [US1] Document HR assessment rule configuration acceptance steps in specs/010-assessment-integrity-adaptive-testing/quickstart.md
- [x] T013 [US1] Peer review US1 scope, RBAC, validation rules, and acceptance criteria in specs/010-assessment-integrity-adaptive-testing/spec.md

### Implementation for User Story 1

- [x] T014 [US1] Implement assessment rule persistence for assessment_question_rules in app/Controllers/AssessmentController.php
- [x] T015 [US1] Implement assessment cooldown persistence for assessments.cooldown_months in app/Controllers/AssessmentController.php
- [x] T016 [US1] Add Easy, Medium, Hard count inputs and cooldown input to HR assessment form in views/hr/assessments/form.php
- [x] T017 [US1] Display configured rules, cooldown, and bank sufficiency warnings in views/hr/assessments/show.php
- [x] T018 [US1] Add server-side validation errors for invalid rule counts and total zero rules in app/Controllers/AssessmentController.php
- [x] T019 [US1] Ensure HR assessment create and edit routes preserve CSRF-safe form behavior in routes/web.php and views/hr/assessments/form.php
- [x] T020 [US1] Run manual US1 validation using specs/010-assessment-integrity-adaptive-testing/quickstart.md

**Checkpoint**: User Story 1 is independently functional and demonstrates the MVP.

---

## Phase 4: User Story 2 - Candidate Receives Randomized Timed Test (Priority: P1)

**Goal**: Candidate starts an eligible assessment, receives a randomized snapshot matching HR rules, and timer heartbeat/expiry are enforced by the server deadline.

**Independent Test**: Start an assessment as a candidate, verify the generated question mix, save heartbeat updates, and confirm expiry uses the server deadline.

### Manual Acceptance for User Story 2

- [x] T021 [P] [US2] Document candidate randomized timed attempt acceptance steps in specs/010-assessment-integrity-adaptive-testing/quickstart.md
- [x] T022 [US2] Peer review US2 candidate privacy, timer authority, and attempt snapshot behavior in specs/010-assessment-integrity-adaptive-testing/spec.md

### Implementation for User Story 2

- [x] T023 [US2] Implement bank sufficiency check and blocked start behavior in app/Controllers/AssessmentController.php
- [x] T024 [US2] Implement randomized candidate_assessment_questions snapshot creation from configured rules in app/Controllers/AssessmentController.php
- [x] T025 [US2] Implement server deadline, remaining_seconds, and last_heartbeat_at setup in app/Controllers/AssessmentController.php
- [x] T026 [US2] Implement candidate heartbeat save and expiry handling in app/Controllers/AssessmentController.php
- [x] T027 [US2] Add candidate timer display and heartbeat submission behavior in views/candidate/assessments/show.php
- [x] T028 [US2] Ensure answer saves reject expired or submitted attempts in app/Controllers/AssessmentController.php
- [x] T029 [US2] Ensure candidate attempt result uses candidate-safe fields only in views/candidate/assessments/result.php
- [x] T030 [US2] Run manual US2 validation using specs/010-assessment-integrity-adaptive-testing/quickstart.md

**Checkpoint**: User Story 2 works independently after foundation and can be demonstrated without US3-US5.

---

## Phase 5: User Story 3 - Simulated Integrity Checks Are Recorded (Priority: P2)

**Goal**: HR can maintain hidden expected outputs and common answers; candidate submissions are scored with simulated local output matching and plagiarism similarity.

**Independent Test**: Configure hidden expected outputs and common answers, submit a coding answer, and verify HR sees simulated output/plagiarism results with review-only flags.

### Manual Acceptance for User Story 3

- [x] T031 [P] [US3] Document simulated integrity review acceptance steps in specs/010-assessment-integrity-adaptive-testing/quickstart.md
- [x] T032 [US3] Peer review US3 simulated labels, HR-only reference data, and no automatic rejection rule in specs/010-assessment-integrity-adaptive-testing/spec.md

### Implementation for User Story 3

- [x] T033 [US3] Implement hidden expected output create and update handling in app/Controllers/AssessmentController.php
- [x] T034 [US3] Implement common-answer create and update handling in app/Controllers/AssessmentController.php
- [x] T035 [US3] Add expected output and common-answer fields to HR question form in views/hr/assessment-questions/form.php
- [x] T036 [US3] Implement simulated output matching against question_expected_outputs in app/Services/SimulatedAssessmentScorer.php
- [x] T037 [US3] Implement simulated plagiarism similarity against assessment_common_answers in app/Services/SimulatedAssessmentScorer.php
- [x] T038 [US3] Persist output_matched, plagiarism_score, awarded_points, and simulated result labels in app/Services/SimulatedAssessmentScorer.php
- [x] T039 [US3] Show simulated output match, plagiarism similarity, and >=80% HR review flag in views/hr/assessments/attempt.php
- [x] T040 [US3] Confirm candidate views hide expected outputs, common answers, and HR review flags in views/candidate/assessments/show.php and views/candidate/assessments/result.php
- [x] T041 [US3] Run manual US3 validation using specs/010-assessment-integrity-adaptive-testing/quickstart.md

**Checkpoint**: User Story 3 is functional and integrity results are reviewable without external services.

---

## Phase 6: User Story 4 - HR Receives Adaptive Difficulty Suggestions (Priority: P3)

**Goal**: HR sees an explainable difficulty suggestion based on at least five completed attempts and clarified score bands.

**Independent Test**: Create or seed five completed attempts and confirm <=50 suggests easier, >=80 suggests harder, and other averages suggest unchanged.

### Manual Acceptance for User Story 4

- [x] T042 [P] [US4] Document adaptive suggestion acceptance steps in specs/010-assessment-integrity-adaptive-testing/quickstart.md
- [x] T043 [US4] Peer review US4 score band behavior and no automatic rule-change assumption in specs/010-assessment-integrity-adaptive-testing/spec.md

### Implementation for User Story 4

- [x] T044 [US4] Implement average-score query for at least five completed attempts in app/Controllers/AssessmentController.php
- [x] T045 [US4] Implement <=50 easier, >=80 harder, and unchanged suggestion calculation in app/Controllers/AssessmentController.php
- [x] T046 [US4] Display adaptive difficulty suggestion and supporting score context in views/hr/assessments/show.php
- [x] T047 [US4] Ensure HR rule edits after suggestions affect future attempts only in app/Controllers/AssessmentController.php
- [x] T048 [US4] Run manual US4 validation using specs/010-assessment-integrity-adaptive-testing/quickstart.md

**Checkpoint**: User Story 4 is functional and HR can interpret suggestions without automatic changes.

---

## Phase 7: User Story 5 - Candidate Retakes Respect Cooldown (Priority: P3)

**Goal**: Candidate retakes are blocked until the configured cooldown period elapses and show the next eligible date.

**Independent Test**: Complete or expire an attempt, retry before cooldown, verify blocked messaging, then verify retry after eligibility.

### Manual Acceptance for User Story 5

- [x] T049 [P] [US5] Document cooldown acceptance steps in specs/010-assessment-integrity-adaptive-testing/quickstart.md
- [x] T050 [US5] Peer review US5 cooldown semantics across applications for the same assessment in specs/010-assessment-integrity-adaptive-testing/spec.md

### Implementation for User Story 5

- [x] T051 [US5] Implement prior completed-or-expired attempt lookup for same candidate and assessment in app/Controllers/AssessmentController.php
- [x] T052 [US5] Implement next eligible date calculation from assessments.cooldown_months in app/Controllers/AssessmentController.php
- [x] T053 [US5] Block retake start and flash candidate-safe cooldown message in app/Controllers/AssessmentController.php
- [x] T054 [US5] Display candidate blocked-retake messaging on application or assessment start flow in views/candidate/applications/show.php and views/candidate/assessments/result.php
- [x] T055 [US5] Ensure HR cooldown changes are used for future eligibility checks in app/Controllers/AssessmentController.php
- [x] T056 [US5] Run manual US5 validation using specs/010-assessment-integrity-adaptive-testing/quickstart.md

**Checkpoint**: User Story 5 is functional and retake eligibility is clear to candidates.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final checks and cleanup across all user stories.

- [x] T057 [P] Run PHP syntax check for app/Controllers/AssessmentController.php
- [x] T058 [P] Run PHP syntax check for app/Services/SimulatedAssessmentScorer.php
- [x] T059 [P] Run PHP syntax check for routes/web.php
- [x] T060 [P] Run PHP syntax checks for assessment views in views/hr/assessments/form.php, views/hr/assessments/show.php, views/hr/assessments/attempt.php, views/hr/assessment-questions/form.php, views/candidate/assessments/show.php, and views/candidate/assessments/result.php
- [x] T061 Verify all simulated output validation and plagiarism labels are present in views/hr/assessments/attempt.php and views/candidate/assessments/result.php
- [x] T062 Verify hidden expected outputs and common answers are never rendered to candidate pages in views/candidate/assessments/show.php and views/candidate/assessments/result.php
- [x] T063 Update seed/demo data for assessment rules, expected outputs, common answers, and completed attempts in scripts/seed.php
- [x] T064 Run complete manual demo flow from specs/010-assessment-integrity-adaptive-testing/quickstart.md
- [x] T065 Capture peer review notes for spec, plan, data model, contracts, route map, quickstart, and tasks in specs/010-assessment-integrity-adaptive-testing/tasks.md

---

## Dependencies & Execution Order

### Phase Dependencies

- Setup (Phase 1): no dependencies.
- Foundational (Phase 2): depends on Setup completion and blocks all user stories.
- User Story 1 (Phase 3): depends on Foundational and is the MVP.
- User Story 2 (Phase 4): depends on Foundational and benefits from US1 rules being available.
- User Story 3 (Phase 5): depends on Foundational and can be implemented after candidate submission paths exist.
- User Story 4 (Phase 6): depends on completed attempt scoring data from US2 or seeded attempts.
- User Story 5 (Phase 7): depends on candidate attempt lifecycle from US2.
- Polish (Phase 8): depends on all desired user stories.

### User Story Dependencies

- US1: no other story dependency after foundation.
- US2: uses rules from US1 for full behavior but can be tested with pre-seeded rules.
- US3: uses candidate submissions from US2 for end-to-end validation but can be tested with seeded attempts.
- US4: uses completed attempts from US2 or seed data.
- US5: uses completed or expired attempts from US2.

### Parallel Opportunities

- T002 and T003 can run in parallel after T001.
- T007, T008, and T009 can run in parallel after T005-T006 are understood.
- Manual acceptance documentation tasks T012, T021, T031, T042, and T049 can run in parallel by different contributors.
- US3 question-reference work T033-T035 can proceed in parallel with scorer work T036-T037 after foundation.
- Polish syntax checks T057-T060 can run in parallel.

---

## Parallel Example: User Story 3

```bash
Task: "T033 [US3] Implement hidden expected output create and update handling in app/Controllers/AssessmentController.php"
Task: "T035 [US3] Add expected output and common-answer fields to HR question form in views/hr/assessment-questions/form.php"
Task: "T036 [US3] Implement simulated output matching against question_expected_outputs in app/Services/SimulatedAssessmentScorer.php"
```

## Parallel Example: Polish

```bash
Task: "T057 Run PHP syntax check for app/Controllers/AssessmentController.php"
Task: "T058 Run PHP syntax check for app/Services/SimulatedAssessmentScorer.php"
Task: "T059 Run PHP syntax check for routes/web.php"
Task: "T060 Run PHP syntax checks for assessment views in views/hr/assessments/form.php, views/hr/assessments/show.php, views/hr/assessments/attempt.php, views/hr/assessment-questions/form.php, views/candidate/assessments/show.php, and views/candidate/assessments/result.php"
```

---

## Implementation Strategy

### MVP First

1. Complete Phase 1 Setup.
2. Complete Phase 2 Foundation.
3. Complete Phase 3 User Story 1.
4. Stop and validate HR can configure rules and cooldown.

### Incremental Delivery

1. Add US1 for HR-controlled rules.
2. Add US2 for candidate randomized timed attempts.
3. Add US3 for simulated local integrity checks.
4. Add US4 for adaptive suggestions.
5. Add US5 for cooldown retake enforcement.
6. Run Phase 8 polish and quickstart validation.

### Team Strategy

1. One contributor owns schema/controller foundation.
2. One contributor owns HR forms and review pages.
3. One contributor owns candidate attempt pages and manual demo evidence.
4. Peer reviewer checks diagram traceability, Vanilla PHP compliance, RBAC, privacy, and acceptance coverage before implementation merges.

---

## Notes

- `[P]` means the task touches separate files or can be completed without depending on another incomplete task in the same phase.
- `[US#]` labels map tasks to the corresponding user story in `spec.md`.
- Every user story includes a manual acceptance task and a peer-review task.
- Keep code execution and plagiarism detection simulated and local.
- Do not introduce REST APIs, a separated frontend, framework dependencies, real code execution, external plagiarism services, or SPA-only behavior.
