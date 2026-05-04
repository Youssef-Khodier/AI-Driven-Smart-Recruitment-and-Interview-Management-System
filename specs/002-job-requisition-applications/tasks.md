# Tasks: Job Requisition and Candidate Applications

**Input**: Design documents from `specs/002-job-requisition-applications/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, route-map.md, contracts/web-workflows.md, quickstart.md

**Tests**: Laravel feature, policy, validation, model, and support-class tests are included because the plan requires acceptance coverage for each story.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.
**Peer Review**: Peer-review gates are included before each implementation phase.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel because it touches different files and has no dependency on another incomplete task in the same phase.
- **[Story]**: User story traceability label, used only in user-story phases.
- Every task includes an exact file path.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirm the existing Laravel foundation and prepare feature paths without adding new architecture.

- [X] T001 Verify current Laravel 12/PHP 8.2 dependencies and test script in composer.json
- [X] T002 Confirm existing auth, role, and active-account middleware route conventions in routes/web.php
- [X] T003 [P] Create HR requisition Blade directory in resources/views/hr/requisitions/.gitkeep
- [X] T004 [P] Create HR application Blade directory in resources/views/hr/applications/.gitkeep
- [X] T005 [P] Create candidate jobs Blade directory in resources/views/candidate/jobs/.gitkeep
- [X] T006 [P] Create candidate applications Blade directory in resources/views/candidate/applications/.gitkeep

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core domain data, enums, relationships, policies, and shared scoring infrastructure that all stories depend on.

**CRITICAL**: No user story work can begin until this phase is complete.

- [X] T007 Add JobRequisitionStatus enum with Draft/Pending Approval/Approved/Open/Closed values in app/Enums/JobRequisitionStatus.php
- [X] T008 Add ApplicationStatus enum with Applied/Screening/Assessment/Interview/Offer/Rejected/Hired values in app/Enums/ApplicationStatus.php
- [X] T009 Add skill_keywords column to candidates table in database/migrations/0001_01_01_000005_add_skill_keywords_to_candidates_table.php
- [X] T010 Add job_requisitions table migration in database/migrations/0001_01_01_000006_create_job_requisitions_table.php
- [X] T011 Add applications table migration with unique candidate/job constraint in database/migrations/0001_01_01_000007_create_applications_table.php
- [X] T012 [P] Add job_requisition_status_histories table migration in database/migrations/0001_01_01_000008_create_job_requisition_status_histories_table.php
- [X] T013 [P] Add application_status_histories table migration in database/migrations/0001_01_01_000009_create_application_status_histories_table.php
- [X] T014 Create JobRequisition model with casts and relationships in app/Models/JobRequisition.php
- [X] T015 Create Application model with casts and relationships in app/Models/Application.php
- [X] T016 [P] Create JobRequisitionStatusHistory model with relationships in app/Models/JobRequisitionStatusHistory.php
- [X] T017 [P] Create ApplicationStatusHistory model with relationships in app/Models/ApplicationStatusHistory.php
- [X] T018 Update Candidate model fillable fields and applications relationship in app/Models/Candidate.php
- [X] T019 Update User model created/approved requisition and status-history actor relationships in app/Models/User.php
- [X] T020 Update Department model requisitions relationship in app/Models/Department.php
- [X] T021 Implement deterministic simulated match scorer with 70/15/15 weighting in app/Support/SimulatedMatchScorer.php
- [X] T022 Create JobRequisitionPolicy with HR lifecycle, self-approval, candidate-open-view, and stale-edit authorization rules in app/Policies/JobRequisitionPolicy.php
- [X] T023 Create ApplicationPolicy with HR review/status-update and candidate ownership rules in app/Policies/ApplicationPolicy.php
- [X] T024 Register JobRequisitionPolicy and ApplicationPolicy in app/Providers/AppServiceProvider.php
- [X] T025 [P] Add model relationship tests for requisitions, applications, and status histories in tests/Feature/Foundation/RecruitmentModelRelationshipTest.php
- [X] T026 [P] Add simulated match scorer tests for skills/title/experience weighting in tests/Unit/SimulatedMatchScorerTest.php

**Checkpoint**: Database, models, policies, and shared scoring are ready; user story implementation can begin.

---

## Phase 3: User Story 1 - Manage Job Requisition Lifecycle (Priority: P1) MVP

**Goal**: HR Admin can create, edit, submit, approve by a different HR Admin, open, and close job requisitions; candidate visibility changes only when status is Open.

**Independent Test**: Sign in as HR Admin, create one requisition, move it through every allowed status, verify self-approval is denied, verify stale edit is blocked, and confirm candidate visibility only when Open.

### Tests for User Story 1

- [X] T027 [P] [US1] Add HR requisition lifecycle feature tests in tests/Feature/Hr/JobRequisitionLifecycleTest.php
- [X] T028 [P] [US1] Add HR requisition validation and stale-edit tests in tests/Feature/Hr/JobRequisitionValidationTest.php
- [X] T029 [P] [US1] Add requisition policy tests for HR role, self-approval denial, inactive users, and candidate Open visibility in tests/Feature/Hr/JobRequisitionPolicyTest.php
- [X] T030 [P] [US1] Add requisition status history tests in tests/Feature/Hr/JobRequisitionStatusHistoryTest.php
- [ ] T031 [US1] Peer review spec, plan, route-map, RBAC, privacy, and US1 acceptance criteria before implementing app/Http/Controllers/Hr/JobRequisitionController.php

### Implementation for User Story 1

- [X] T032 [P] [US1] Create StoreJobRequisitionRequest validation for title, department, description, and requirements in app/Http/Requests/Hr/StoreJobRequisitionRequest.php
- [X] T033 [P] [US1] Create UpdateJobRequisitionRequest validation including last_seen_updated_at in app/Http/Requests/Hr/UpdateJobRequisitionRequest.php
- [X] T034 [US1] Implement HR requisition index/create/store/show/edit/update actions with stale-edit blocking in app/Http/Controllers/Hr/JobRequisitionController.php
- [X] T035 [US1] Implement HR requisition submit/approve/open/close actions and status-history writes in app/Http/Controllers/Hr/JobRequisitionController.php
- [X] T036 [US1] Add HR requisition web routes for index/create/store/show/edit/update/submit/approve/open/close in routes/web.php
- [X] T037 [P] [US1] Create HR requisition index Blade view with status filters in resources/views/hr/requisitions/index.blade.php
- [X] T038 [P] [US1] Create HR requisition form Blade view with stale-edit hidden field in resources/views/hr/requisitions/form.blade.php
- [X] T039 [P] [US1] Create HR requisition create Blade view in resources/views/hr/requisitions/create.blade.php
- [X] T040 [P] [US1] Create HR requisition edit Blade view in resources/views/hr/requisitions/edit.blade.php
- [X] T041 [P] [US1] Create HR requisition detail Blade view with lifecycle actions in resources/views/hr/requisitions/show.blade.php
- [X] T042 [US1] Add HR dashboard link to requisitions in resources/views/hr/dashboard.blade.php

**Checkpoint**: US1 is independently functional and testable as the MVP.

---

## Phase 4: User Story 2 - Candidate Profile, Job Browsing, and Application (Priority: P2)

**Goal**: Candidate can maintain required profile fields, browse only Open jobs, apply once per job, and receive a persisted simulated match score.

**Independent Test**: Sign in as candidate, complete profile including comma-separated skills, browse an Open job, apply once, verify score is shown, and verify duplicate/non-open/incomplete-profile attempts are blocked.

### Tests for User Story 2

- [X] T043 [P] [US2] Add candidate profile skill keyword validation tests in tests/Feature/Candidate/CandidateProfileSkillKeywordsTest.php
- [X] T044 [P] [US2] Add candidate open job browsing and non-open hiding tests in tests/Feature/Candidate/OpenJobBrowsingTest.php
- [X] T045 [P] [US2] Add candidate apply-once, closed-job, and incomplete-profile tests in tests/Feature/Candidate/CandidateApplicationSubmissionTest.php
- [X] T046 [P] [US2] Add match score persistence and advisory-label tests in tests/Feature/Candidate/SimulatedMatchScoreTest.php
- [ ] T047 [US2] Peer review spec, plan, route-map, RBAC, privacy, and US2 acceptance criteria before implementing app/Http/Controllers/Candidate/ApplicationController.php

### Implementation for User Story 2

- [X] T048 [US2] Extend candidate profile request validation for skill_keywords, current_title, years_experience, location, and resume_url in app/Http/Requests/Candidate/UpdateProfileRequest.php
- [X] T049 [US2] Update candidate profile controller to save skill_keywords in app/Http/Controllers/Candidate/ProfileController.php
- [X] T050 [US2] Update candidate profile Blade form to capture comma-separated skill_keywords in resources/views/candidate/profile.blade.php
- [X] T051 [P] [US2] Create StoreApplicationRequest to validate Open requisition, complete profile, and duplicate application rules in app/Http/Requests/Candidate/StoreApplicationRequest.php
- [X] T052 [P] [US2] Implement candidate Open job index and detail actions in app/Http/Controllers/Candidate/JobController.php
- [X] T053 [US2] Implement candidate application store action with simulated score calculation and duplicate redirect in app/Http/Controllers/Candidate/ApplicationController.php
- [X] T054 [US2] Add candidate job browse, detail, and apply routes in routes/web.php
- [X] T055 [P] [US2] Create candidate open jobs index Blade view in resources/views/candidate/jobs/index.blade.php
- [X] T056 [P] [US2] Create candidate job detail Blade view with apply form and simulated-score disclaimer in resources/views/candidate/jobs/show.blade.php
- [X] T057 [US2] Add candidate dashboard link to open jobs in resources/views/candidate/dashboard.blade.php

**Checkpoint**: US2 is independently functional with candidate profile, browsing, one-time application, and scoring behavior.

---

## Phase 5: User Story 3 - Manage Applications and Track Status (Priority: P3)

**Goal**: HR Admin can review applicants and update statuses; candidates can track exact pipeline statuses for only their own applications.

**Independent Test**: Create applications for one open job, sign in as HR Admin to view applicants and update statuses, then sign in as each candidate to confirm only their own exact status is visible.

### Tests for User Story 3

- [X] T058 [P] [US3] Add HR applicant review and sorting tests in tests/Feature/Hr/HrApplicantReviewTest.php
- [X] T059 [P] [US3] Add HR application status update and history tests in tests/Feature/Hr/ApplicationStatusUpdateTest.php
- [X] T060 [P] [US3] Add candidate application tracking and privacy tests in tests/Feature/Candidate/CandidateApplicationTrackingTest.php
- [X] T061 [P] [US3] Add application policy tests for HR access, candidate ownership, inactive users, and wrong-role denial in tests/Feature/Hr/ApplicationPolicyTest.php
- [ ] T062 [US3] Peer review spec, plan, route-map, RBAC, privacy, and US3 acceptance criteria before implementing app/Http/Controllers/Hr/ApplicationController.php

### Implementation for User Story 3

- [X] T063 [P] [US3] Create UpdateApplicationStatusRequest with valid status and optional reason validation in app/Http/Requests/Hr/UpdateApplicationStatusRequest.php
- [X] T064 [US3] Implement HR applicant list and application status update actions with status-history writes in app/Http/Controllers/Hr/ApplicationController.php
- [X] T065 [US3] Implement candidate application index and detail ownership-filtered actions in app/Http/Controllers/Candidate/ApplicationController.php
- [X] T066 [US3] Add HR applicant review and status update routes in routes/web.php
- [X] T067 [US3] Add candidate application tracking routes in routes/web.php
- [X] T068 [P] [US3] Create HR applicant index Blade view with candidate summary, score, status, and sorting in resources/views/hr/applications/index.blade.php
- [X] T069 [P] [US3] Create candidate applications index Blade view in resources/views/candidate/applications/index.blade.php
- [X] T070 [P] [US3] Create candidate application detail Blade view in resources/views/candidate/applications/show.blade.php
- [X] T071 [US3] Add candidate dashboard link to application tracking in resources/views/candidate/dashboard.blade.php

**Checkpoint**: All user stories are independently functional and candidate data remains role-protected.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Verification, cleanup, documentation evidence, and cross-story hardening.

- [ ] T072 [P] Run Laravel code formatting for changed PHP files with vendor/bin/pint
- [ ] T073 Run full automated test suite for tests/ with php artisan test
- [ ] T074 [P] Validate quickstart manual demo path and record notes in specs/002-job-requisition-applications/quickstart.md
- [X] T075 [P] Review all Blade pages for CSRF tokens, validation errors, empty states, and simulated-score labels in resources/views/hr/ and resources/views/candidate/
- [X] T076 [P] Review route names and authorization middleware consistency in routes/web.php
- [X] T077 [P] Update known limitations and demo evidence notes in specs/002-job-requisition-applications/quickstart.md
- [ ] T078 Perform final peer review for diagram traceability, Laravel monolith compliance, RBAC, candidate privacy, migrations, and acceptance criteria in specs/002-job-requisition-applications/plan.md

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion; blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Foundational completion; MVP target.
- **User Story 2 (Phase 4)**: Depends on Foundational completion and needs an Open requisition from US1 for full demo, but candidate profile and browsing logic are independently testable with seeded Open requisitions.
- **User Story 3 (Phase 5)**: Depends on Foundational completion and needs applications from US2 for full demo, but HR/candidate access behavior is independently testable with seeded applications.
- **Polish (Phase 6)**: Depends on all desired user stories being complete.

### User Story Dependencies

- **US1 (P1)**: MVP; no dependency on other user stories after Foundational.
- **US2 (P2)**: Can be implemented after Foundational; full end-to-end flow benefits from US1 Open requisition workflow.
- **US3 (P3)**: Can be implemented after Foundational; full end-to-end flow benefits from US2 application creation workflow.

### Within Each User Story

- Tests are written before implementation tasks.
- Peer review gate precedes implementation.
- Request validation and policies precede controller behavior.
- Controllers and routes precede final Blade integration.
- Status-history writes are part of the story implementation before checkpoint validation.

---

## Parallel Opportunities

- Setup directory tasks T003-T006 can run in parallel.
- Foundational history migrations/models T012-T013 and T016-T017 can run in parallel after core schema naming is agreed.
- Foundational tests T025-T026 can run in parallel after model/scorer contracts are clear.
- US1 test tasks T027-T030 can run in parallel before US1 implementation.
- US1 Blade tasks T037-T041 can run in parallel after controller route names are stable.
- US2 test tasks T043-T046 can run in parallel before US2 implementation.
- US2 job/request/view tasks T051-T052 and T055-T056 can run in parallel after foundational models exist.
- US3 test tasks T058-T061 can run in parallel before US3 implementation.
- US3 Blade tasks T068-T070 can run in parallel after route names are stable.
- Polish review tasks T074-T077 can run in parallel after implementation is complete.

## Parallel Example: User Story 1

```bash
# Tests can be drafted together:
Task: "T027 [US1] Add HR requisition lifecycle feature tests in tests/Feature/Hr/JobRequisitionLifecycleTest.php"
Task: "T028 [US1] Add HR requisition validation and stale-edit tests in tests/Feature/Hr/JobRequisitionValidationTest.php"
Task: "T029 [US1] Add requisition policy tests in tests/Feature/Hr/JobRequisitionPolicyTest.php"
Task: "T030 [US1] Add requisition status history tests in tests/Feature/Hr/JobRequisitionStatusHistoryTest.php"

# Blade views can be built together after routes are named:
Task: "T037 [US1] Create resources/views/hr/requisitions/index.blade.php"
Task: "T038 [US1] Create resources/views/hr/requisitions/form.blade.php"
Task: "T039 [US1] Create resources/views/hr/requisitions/create.blade.php"
Task: "T040 [US1] Create resources/views/hr/requisitions/edit.blade.php"
Task: "T041 [US1] Create resources/views/hr/requisitions/show.blade.php"
```

## Parallel Example: User Story 2

```bash
# Tests can be drafted together:
Task: "T043 [US2] Add tests/Feature/Candidate/CandidateProfileSkillKeywordsTest.php"
Task: "T044 [US2] Add tests/Feature/Candidate/OpenJobBrowsingTest.php"
Task: "T045 [US2] Add tests/Feature/Candidate/CandidateApplicationSubmissionTest.php"
Task: "T046 [US2] Add tests/Feature/Candidate/SimulatedMatchScoreTest.php"

# Candidate job pages can be built together after route names are stable:
Task: "T055 [US2] Create resources/views/candidate/jobs/index.blade.php"
Task: "T056 [US2] Create resources/views/candidate/jobs/show.blade.php"
```

## Parallel Example: User Story 3

```bash
# Tests can be drafted together:
Task: "T058 [US3] Add tests/Feature/Hr/HrApplicantReviewTest.php"
Task: "T059 [US3] Add tests/Feature/Hr/ApplicationStatusUpdateTest.php"
Task: "T060 [US3] Add tests/Feature/Candidate/CandidateApplicationTrackingTest.php"
Task: "T061 [US3] Add tests/Feature/Hr/ApplicationPolicyTest.php"

# Tracking/review views can be built together after route names are stable:
Task: "T068 [US3] Create resources/views/hr/applications/index.blade.php"
Task: "T069 [US3] Create resources/views/candidate/applications/index.blade.php"
Task: "T070 [US3] Create resources/views/candidate/applications/show.blade.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational migrations, models, policies, and scorer.
3. Complete Phase 3: User Story 1.
4. Stop and validate HR requisition lifecycle independently.
5. Demo MVP if lifecycle tests and manual flow pass.

### Incremental Delivery

1. Setup + Foundational -> shared recruitment domain ready.
2. US1 -> HR can manage requisitions and open/close jobs.
3. US2 -> candidates can complete profiles, browse open jobs, and apply once with simulated scoring.
4. US3 -> HR can manage applicants and candidates can track exact statuses.
5. Polish -> formatting, full test suite, quickstart validation, evidence notes, and peer review.

### Parallel Team Strategy

1. Team completes Setup and Foundational together.
2. After Foundational: Developer A implements US1, Developer B drafts US2 tests/views against contracts, Developer C drafts US3 tests/views against contracts.
3. Integrate sequentially by priority: US1, then US2, then US3.

## Notes

- [P] tasks touch different files and can run in parallel when their phase prerequisites are satisfied.
- [US1], [US2], and [US3] labels map directly to prioritized stories in spec.md.
- Do not implement REST APIs, separated frontend apps, external job-board sync, email, real resume parsing, real AI/NLP, assessments, interviews, offers, onboarding, or analytics in these tasks.
- Use Blade forms, redirects, route policies, CSRF, Form Requests, sessions, and server-side validation throughout.
- Stop at each checkpoint to validate the story independently before moving to the next priority.
