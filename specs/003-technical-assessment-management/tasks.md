# Tasks: Technical Assessment Management

**Input**: Design documents from `specs/003-technical-assessment-management/`  
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `route-map.md`, `contracts/web-workflows.md`, `quickstart.md`

**Tests**: Laravel feature, policy, validation, model, and unit tests are included because the plan and template require automated evidence for acceptance criteria unless a manual demo is explicitly justified.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirm the existing Laravel monolith and create feature documentation alignment before implementation begins.

- [X] T001 Review assessment specification and planning artifacts in specs/003-technical-assessment-management/spec.md and specs/003-technical-assessment-management/plan.md
- [X] T002 [P] Verify Laravel 12 and PHP 8.2 dependency assumptions in composer.json
- [X] T003 [P] Verify existing authenticated HR and Candidate web route grouping in routes/web.php
- [X] T004 [P] Create assessment Blade view directories in resources/views/hr/assessments/ and resources/views/candidate/assessments/

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core schema, models, policies, and scoring infrastructure required before any user story can be implemented.

**Critical**: No user story work can begin until this phase is complete.

- [X] T005 [P] Create assessment attempt status enum in app/Enums/AssessmentAttemptStatus.php
- [X] T006 [P] Create assessment question type enum in app/Enums/AssessmentQuestionType.php
- [X] T007 [P] Create assessment type enum in app/Enums/AssessmentType.php
- [X] T008 [P] Create assessments migration in database/migrations/0001_01_01_000010_create_assessments_table.php
- [X] T009 [P] Create questions migration in database/migrations/0001_01_01_000011_create_questions_table.php
- [X] T010 [P] Create candidate assessments migration in database/migrations/0001_01_01_000012_create_candidate_assessments_table.php
- [X] T011 [P] Create candidate assessment question snapshots migration in database/migrations/0001_01_01_000013_create_candidate_assessment_questions_table.php
- [X] T012 [P] Create submissions migration in database/migrations/0001_01_01_000014_create_submissions_table.php
- [X] T013 [P] Create assessment integrity events migration in database/migrations/0001_01_01_000015_create_assessment_integrity_events_table.php
- [X] T014 [P] Create Assessment Eloquent model and relationships in app/Models/Assessment.php
- [X] T015 [P] Create Question Eloquent model and relationships in app/Models/Question.php
- [X] T016 [P] Create CandidateAssessment Eloquent model and relationships in app/Models/CandidateAssessment.php
- [X] T017 [P] Create CandidateAssessmentQuestion Eloquent model and immutable snapshot casts in app/Models/CandidateAssessmentQuestion.php
- [X] T018 [P] Create Submission Eloquent model and casts in app/Models/Submission.php
- [X] T019 [P] Create AssessmentIntegrityEvent Eloquent model and casts in app/Models/AssessmentIntegrityEvent.php
- [X] T020 [P] Add assessment relationships to JobRequisition model in app/Models/JobRequisition.php
- [X] T021 [P] Add assessment attempt relationships to Application and Candidate models in app/Models/Application.php and app/Models/Candidate.php
- [X] T022 [P] Create AssessmentPolicy for HR assessment authoring in app/Policies/AssessmentPolicy.php
- [X] T023 [P] Create CandidateAssessmentPolicy for candidate ownership and HR review in app/Policies/CandidateAssessmentPolicy.php
- [X] T024 [P] Create deterministic simulated assessment scorer in app/Support/SimulatedAssessmentScorer.php
- [X] T025 [P] Add model relationship and enum coverage tests in tests/Feature/Foundation/AssessmentModelRelationshipTest.php
- [X] T026 [P] Add simulated assessment scorer unit tests in tests/Unit/SimulatedAssessmentScorerTest.php

**Checkpoint**: Foundation ready; user story implementation can begin.

---

## Phase 3: User Story 1 - HR Defines Job Assessment (Priority: P1) MVP

**Goal**: HR Admins can create assessments for job requisitions and manage MCQ, theory/free-text, and coding-as-text questions with validation and authorization.

**Independent Test**: Sign in as HR Admin, create an assessment for an existing job, add valid questions, verify invalid question data is rejected, and confirm unauthorized users cannot manage assessments.

### Tests for User Story 1

- [X] T027 [P] [US1] Add HR assessment CRUD feature test in tests/Feature/Hr/AssessmentManagementTest.php
- [X] T028 [P] [US1] Add HR assessment and question validation test in tests/Feature/Hr/AssessmentValidationTest.php
- [X] T029 [P] [US1] Add assessment authoring policy test in tests/Feature/Hr/AssessmentPolicyTest.php
- [X] T030 [US1] Peer review US1 spec, route map, RBAC, privacy, and validation criteria in specs/003-technical-assessment-management/tasks.md

### Implementation for User Story 1

- [X] T031 [P] [US1] Create StoreAssessmentRequest validation in app/Http/Requests/Hr/StoreAssessmentRequest.php
- [X] T032 [P] [US1] Create UpdateAssessmentRequest validation in app/Http/Requests/Hr/UpdateAssessmentRequest.php
- [X] T033 [P] [US1] Create StoreAssessmentQuestionRequest validation in app/Http/Requests/Hr/StoreAssessmentQuestionRequest.php
- [X] T034 [P] [US1] Create UpdateAssessmentQuestionRequest validation in app/Http/Requests/Hr/UpdateAssessmentQuestionRequest.php
- [X] T035 [US1] Add HR assessment and question routes in routes/web.php
- [X] T036 [P] [US1] Implement HR assessment CRUD and results entry actions in app/Http/Controllers/Hr/AssessmentController.php
- [X] T037 [P] [US1] Implement HR assessment question CRUD actions in app/Http/Controllers/Hr/AssessmentQuestionController.php
- [X] T038 [P] [US1] Create HR assessment create and edit pages in resources/views/hr/assessments/create.blade.php and resources/views/hr/assessments/edit.blade.php
- [X] T039 [P] [US1] Create shared HR assessment form partial in resources/views/hr/assessments/form.blade.php
- [X] T040 [P] [US1] Create HR assessment detail page in resources/views/hr/assessments/show.blade.php
- [X] T041 [P] [US1] Create HR question create and edit pages in resources/views/hr/assessment-questions/create.blade.php and resources/views/hr/assessment-questions/edit.blade.php
- [X] T042 [P] [US1] Create shared HR question form partial in resources/views/hr/assessment-questions/form.blade.php
- [X] T043 [US1] Add assessment management links to requisition detail page in resources/views/hr/requisitions/show.blade.php

**Checkpoint**: User Story 1 is fully functional and testable independently.

---

## Phase 4: User Story 2 - Candidate Completes Timed Assessment (Priority: P2)

**Goal**: Candidates with applications in `ASSESSMENT` status can start one timed attempt, receive randomized question snapshots, continuously save answers, submit final answers, and view a simulated score.

**Independent Test**: Prepare an assessment and eligible candidate application, sign in as the Candidate, start the assessment, answer randomized questions, submit before expiry, and verify the simulated score label and duplicate-attempt protection.

### Tests for User Story 2

- [X] T044 [P] [US2] Add candidate assessment start and eligibility feature test in tests/Feature/Candidate/CandidateAssessmentStartTest.php
- [X] T045 [P] [US2] Add randomized snapshot and duplicate attempt test in tests/Feature/Candidate/CandidateAssessmentSnapshotTest.php
- [X] T046 [P] [US2] Add continuous answer save and submit scoring test in tests/Feature/Candidate/CandidateAssessmentSubmissionTest.php
- [X] T047 [P] [US2] Add candidate assessment ownership policy test in tests/Feature/Candidate/CandidateAssessmentPolicyTest.php
- [X] T048 [US2] Peer review US2 attempt lifecycle, scoring, privacy, and route contracts in specs/003-technical-assessment-management/tasks.md

### Implementation for User Story 2

- [X] T049 [P] [US2] Create SaveAssessmentAnswerRequest validation in app/Http/Requests/Candidate/SaveAssessmentAnswerRequest.php
- [X] T050 [P] [US2] Create SubmitAssessmentRequest validation in app/Http/Requests/Candidate/SubmitAssessmentRequest.php
- [X] T051 [US2] Add candidate assessment start, show, answer-save, submit, and result routes in routes/web.php
- [X] T052 [US2] Implement candidate assessment start and resume logic in app/Http/Controllers/Candidate/AssessmentController.php
- [X] T053 [US2] Implement randomized attempt snapshot creation in app/Http/Controllers/Candidate/AssessmentController.php
- [X] T054 [US2] Implement continuous answer saving in app/Http/Controllers/Candidate/AssessmentController.php
- [X] T055 [US2] Implement submit and simulated score finalization in app/Http/Controllers/Candidate/AssessmentController.php
- [X] T056 [P] [US2] Create active candidate assessment page with timer and answer forms in resources/views/candidate/assessments/show.blade.php
- [X] T057 [P] [US2] Create candidate assessment result page with simulated score label in resources/views/candidate/assessments/result.blade.php
- [X] T058 [US2] Add assessment start action to candidate application detail page in resources/views/candidate/applications/show.blade.php

**Checkpoint**: User Story 2 is fully functional and testable independently after foundational and HR assessment setup.

---

## Phase 5: User Story 3 - Assessment Expires on Timeout (Priority: P3)

**Goal**: Attempts expire consistently when time runs out, late answer changes are blocked, and expired attempts are scored only from answers saved before the deadline.

**Independent Test**: Start a short-duration assessment, wait beyond the deadline, attempt to save or submit, and confirm the attempt is expired, read-only, and scored from pre-deadline answers only.

### Tests for User Story 3

- [X] T059 [P] [US3] Add timeout expiry feature test in tests/Feature/Candidate/CandidateAssessmentExpiryTest.php
- [X] T060 [P] [US3] Add late answer rejection test in tests/Feature/Candidate/CandidateAssessmentLateSubmissionTest.php
- [X] T061 [P] [US3] Add expired-score-from-saved-answers test in tests/Feature/Candidate/CandidateAssessmentExpiredScoringTest.php
- [X] T062 [US3] Peer review US3 timeout enforcement and expired scoring criteria in specs/003-technical-assessment-management/tasks.md

### Implementation for User Story 3

- [X] T063 [US3] Add shared deadline enforcement helper methods in app/Http/Controllers/Candidate/AssessmentController.php
- [X] T064 [US3] Apply expiry checks to show, save answer, submit, focus event, and result actions in app/Http/Controllers/Candidate/AssessmentController.php
- [X] T065 [US3] Implement expired attempt scoring from pre-deadline saved answers in app/Support/SimulatedAssessmentScorer.php
- [X] T066 [P] [US3] Create expired assessment page in resources/views/candidate/assessments/expired.blade.php
- [X] T067 [US3] Update active assessment page to display expiry and read-only messaging in resources/views/candidate/assessments/show.blade.php

**Checkpoint**: User Story 3 is fully functional and testable independently after candidate attempt flow exists.

---

## Phase 6: User Story 4 - HR Reviews Simulated Proctoring Signals (Priority: P4)

**Goal**: HR Admins can review assessment attempts, simulated scores, timings, saved answer evidence, and focus-loss events without automatic rejection decisions.

**Independent Test**: Record focus-loss events during a candidate attempt, submit the assessment, sign in as HR Admin, and verify timestamped simulated proctoring events and attempt evidence are visible only to authorized users.

### Tests for User Story 4

- [X] T068 [P] [US4] Add focus-loss event recording test in tests/Feature/Candidate/AssessmentFocusEventTest.php
- [X] T069 [P] [US4] Add HR assessment results review test in tests/Feature/Hr/AssessmentResultsReviewTest.php
- [X] T070 [P] [US4] Add HR attempt detail privacy test in tests/Feature/Hr/AssessmentAttemptPrivacyTest.php
- [X] T071 [US4] Peer review US4 simulated proctoring labels, privacy, and no-auto-reject behavior in specs/003-technical-assessment-management/tasks.md

### Implementation for User Story 4

- [X] T072 [US4] Add candidate focus event route and HR results routes in routes/web.php
- [X] T073 [US4] Implement focus event recording in app/Http/Controllers/Candidate/AssessmentController.php
- [X] T074 [US4] Implement HR job-level assessment results and attempt detail actions in app/Http/Controllers/Hr/AssessmentController.php
- [X] T075 [P] [US4] Create HR assessment results list page in resources/views/hr/assessments/results.blade.php
- [X] T076 [P] [US4] Create HR attempt detail section on assessment show page in resources/views/hr/assessments/show.blade.php
- [X] T077 [US4] Ensure simulated proctoring and simulated score labels appear in resources/views/hr/assessments/results.blade.php and resources/views/hr/assessments/show.blade.php

**Checkpoint**: User Story 4 is fully functional and testable independently after candidate attempts exist.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final validation, demo readiness, and cleanup across all user stories.

- [X] T078 [P] Add assessment demo seed data in database/seeders/AssessmentDemoSeeder.php
- [X] T079 [P] Add quickstart manual demo evidence notes in specs/003-technical-assessment-management/quickstart.md
- [X] T080 [P] Add navigation links for HR and Candidate assessment workflows in resources/views/layouts/app.blade.php
- [X] T081 Verify 50-attempt HR results review scenario in tests/Feature/Hr/AssessmentResultsReviewTest.php
- [X] T082 Run full Laravel test suite and record command outcome in specs/003-technical-assessment-management/quickstart.md
- [X] T083 Review RBAC, CSRF, validation, and candidate privacy coverage in tests/Feature/Hr/AssessmentPolicyTest.php and tests/Feature/Candidate/CandidateAssessmentPolicyTest.php
- [X] T084 Run final peer review against spec acceptance criteria in specs/003-technical-assessment-management/spec.md

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion; blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational; MVP scope.
- **User Story 2 (Phase 4)**: Depends on Foundational and needs an assessment created by US1 for end-to-end demo, but candidate flow can be tested with seeded assessment data.
- **User Story 3 (Phase 5)**: Depends on User Story 2 attempt lifecycle.
- **User Story 4 (Phase 6)**: Depends on User Story 2 attempt lifecycle and can use either submitted or expired attempts.
- **Polish (Phase 7)**: Depends on all selected user stories being complete.

### User Story Dependencies

- **US1 (P1)**: No story dependency after Foundational; delivers MVP.
- **US2 (P2)**: Can be developed after Foundational with seeded assessments; full demo benefits from US1.
- **US3 (P3)**: Builds on US2 start/save/submit actions.
- **US4 (P4)**: Builds on US2 attempts and US3 deadline checks for complete review coverage.

### Within Each User Story

- Tests should be written before implementation when practical and should fail before the corresponding implementation task.
- Peer review gate must complete before story implementation tasks.
- Requests and policies should precede controllers.
- Controllers should precede final Blade integration, except non-merged UI sketches.
- A story is complete only when its independent test criteria pass.

---

## Parallel Execution Examples

### User Story 1

```bash
# Parallel test authoring
Task: "T027 Add HR assessment CRUD feature test in tests/Feature/Hr/AssessmentManagementTest.php"
Task: "T028 Add HR assessment and question validation test in tests/Feature/Hr/AssessmentValidationTest.php"
Task: "T029 Add assessment authoring policy test in tests/Feature/Hr/AssessmentPolicyTest.php"

# Parallel request and view work after peer review
Task: "T031 Create StoreAssessmentRequest validation in app/Http/Requests/Hr/StoreAssessmentRequest.php"
Task: "T033 Create StoreAssessmentQuestionRequest validation in app/Http/Requests/Hr/StoreAssessmentQuestionRequest.php"
Task: "T039 Create shared HR assessment form partial in resources/views/hr/assessments/form.blade.php"
Task: "T042 Create shared HR question form partial in resources/views/hr/assessment-questions/form.blade.php"
```

### User Story 2

```bash
# Parallel test authoring
Task: "T044 Add candidate assessment start and eligibility feature test in tests/Feature/Candidate/CandidateAssessmentStartTest.php"
Task: "T045 Add randomized snapshot and duplicate attempt test in tests/Feature/Candidate/CandidateAssessmentSnapshotTest.php"
Task: "T046 Add continuous answer save and submit scoring test in tests/Feature/Candidate/CandidateAssessmentSubmissionTest.php"

# Parallel UI and request work after route/controller contracts are known
Task: "T049 Create SaveAssessmentAnswerRequest validation in app/Http/Requests/Candidate/SaveAssessmentAnswerRequest.php"
Task: "T056 Create active candidate assessment page with timer and answer forms in resources/views/candidate/assessments/show.blade.php"
Task: "T057 Create candidate assessment result page with simulated score label in resources/views/candidate/assessments/result.blade.php"
```

### User Story 3

```bash
# Parallel timeout test authoring
Task: "T059 Add timeout expiry feature test in tests/Feature/Candidate/CandidateAssessmentExpiryTest.php"
Task: "T060 Add late answer rejection test in tests/Feature/Candidate/CandidateAssessmentLateSubmissionTest.php"
Task: "T061 Add expired-score-from-saved-answers test in tests/Feature/Candidate/CandidateAssessmentExpiredScoringTest.php"
```

### User Story 4

```bash
# Parallel review and privacy test authoring
Task: "T068 Add focus-loss event recording test in tests/Feature/Candidate/AssessmentFocusEventTest.php"
Task: "T069 Add HR assessment results review test in tests/Feature/Hr/AssessmentResultsReviewTest.php"
Task: "T070 Add HR attempt detail privacy test in tests/Feature/Hr/AssessmentAttemptPrivacyTest.php"

# Parallel HR view work
Task: "T075 Create HR assessment results list page in resources/views/hr/assessments/results.blade.php"
Task: "T076 Create HR attempt detail section on assessment show page in resources/views/hr/assessments/show.blade.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 setup.
2. Complete Phase 2 foundational schema, models, policies, and scorer.
3. Complete Phase 3 User Story 1.
4. Stop and validate HR assessment authoring independently.
5. Demo HR assessment and question creation before starting candidate attempts.

### Incremental Delivery

1. Setup and Foundational deliver the assessment data layer.
2. US1 delivers HR assessment authoring MVP.
3. US2 delivers candidate timed assessment submission and simulated scoring.
4. US3 adds timeout expiry fairness.
5. US4 adds simulated proctoring review and HR evidence pages.
6. Polish validates quickstart, performance, privacy, and demo readiness.

### Parallel Team Strategy

1. Team completes Setup and Foundational together.
2. Developer A completes US1 HR authoring.
3. Developer B prepares US2 candidate tests and views after foundation.
4. Developer C prepares US3/US4 tests after US2 contracts stabilize.
5. Integrate in priority order to preserve MVP and acceptance traceability.

---

## Notes

- `[P]` tasks use different files or can be done without waiting on another incomplete task in the same phase.
- `[US1]` through `[US4]` labels map to prioritized user stories in `spec.md`.
- Keep all assessment flows inside `routes/web.php` and Blade pages.
- Do not add REST API routes, a separated frontend, real AI grading, real code execution, webcam/video proctoring, plagiarism detection, retakes, email links, or external integrations.
