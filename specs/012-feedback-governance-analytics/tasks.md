# Tasks: Feedback Governance Analytics

**Input**: Design documents from `specs/012-feedback-governance-analytics/`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `route-map.md`, `contracts/feedback-governance-web.md`, `quickstart.md`

**Tests**: Automated tests were not explicitly requested. Each story includes manual demo verification tasks from `quickstart.md`, plus targeted policy/repository/service verification where practical for the academic demo.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing.
**Peer Review**: Include peer review before implementation starts for the foundation and each user story.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare task execution and align existing documentation, routes, and schema targets.

- [ ] T001 Review `specs/012-feedback-governance-analytics/spec.md`, `specs/012-feedback-governance-analytics/plan.md`, `specs/012-feedback-governance-analytics/data-model.md`, and `specs/012-feedback-governance-analytics/route-map.md` before implementation.
- [ ] T002 [P] Inspect existing feedback/evaluation controllers in `app/Controllers/InterviewerInterviewController.php`, `app/Controllers/HrFinalEvaluationController.php`, and `app/Controllers/CandidateInterviewController.php` for extension points.
- [ ] T003 [P] Inspect existing repositories in `app/Repositories/InterviewFeedbackRepository.php`, `app/Repositories/FinalEvaluationRepository.php`, `app/Repositories/NotificationRepository.php`, and `app/Repositories/AuditLogRepository.php` for query and audit conventions.
- [ ] T004 [P] Inspect existing policy patterns in `app/Policies/InterviewFeedbackPolicy.php`, `app/Policies/FinalEvaluationPolicy.php`, `app/Policies/AuditLogPolicy.php`, and `app/Policies/ReportPolicy.php` for RBAC conventions.
- [ ] T005 [P] Inspect existing server-rendered view patterns in `views/hr/evaluations/show.php`, `views/interviewer/interviews/feedback.php`, `views/candidate/interviews/show.php`, and `views/hr/reports/pipeline.php`.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Shared data, enum, repository, policy, route, audit, and notification foundation required by all stories.

**Critical**: No user story implementation should begin until this phase is complete.

- [ ] T006 Peer review `specs/012-feedback-governance-analytics/plan.md`, `specs/012-feedback-governance-analytics/data-model.md`, `specs/012-feedback-governance-analytics/route-map.md`, and `specs/012-feedback-governance-analytics/contracts/feedback-governance-web.md` before implementation.
- [ ] T007 Create feedback governance schema migration in `database/migrations/012_feedback_governance_analytics.sql` for `normalized_evaluation_snapshots`, `feedback_concern_flags`, `candidate_interview_sentiment`, `evaluation_debrief_records`, `job_competency_benchmarks`, `competency_gap_snapshots`, and `feedback_governance_audit_records`.
- [ ] T008 Update canonical schema in `database/schema.sql` with feedback governance tables, foreign keys, score checks, unique constraints, and indexes from `database/migrations/012_feedback_governance_analytics.sql`.
- [ ] T009 [P] Create feedback governance audit action enum in `app/Enums/FeedbackGovernanceAuditAction.php` for calculation, fallback, flag, sentiment, debrief, benchmark, recommendation, and override events.
- [ ] T010 [P] Create feedback concern status enum in `app/Enums/FeedbackConcernStatus.php` for open, resolved resume, resolved blocked, and resolved no-hire states.
- [ ] T011 [P] Create debrief status enum in `app/Enums/EvaluationDebriefStatus.php` for pending, blocked by flag, and completed states.
- [ ] T012 [P] Create competency gap severity enum in `app/Enums/CompetencyGapSeverity.php` for meeting, minor gap, and major gap labels.
- [ ] T013 Create `FeedbackGovernanceRepository` skeleton in `app/Repositories/FeedbackGovernanceRepository.php` with methods grouped for snapshots, flags, sentiment, debriefs, benchmarks, gaps, and governance audit writes.
- [ ] T014 Create `FeedbackNormalizationService` skeleton in `app/Services/FeedbackNormalizationService.php` with 0-10 raw score handling, 0-100 aggregate output, 5-comparable-submission threshold, and fallback result shape.
- [ ] T015 Extend feedback/evaluation authorization in `app/Policies/FinalEvaluationPolicy.php`, `app/Policies/InterviewFeedbackPolicy.php`, and `app/Policies/ReportPolicy.php` for HR governance pages, assigned interviewer flags, candidate sentiment, observer read-only boundaries, and audit visibility.
- [ ] T016 Add route definitions from `specs/012-feedback-governance-analytics/route-map.md` to `routes/web.php` using existing browser route and CSRF form patterns.
- [ ] T017 Extend consolidated audit query support in `app/Repositories/AuditLogRepository.php` and `app/Controllers/HrAuditLogController.php` to include `feedback_governance_audit_records`.
- [ ] T018 Add shared notification helper methods in `app/Repositories/NotificationRepository.php` for serious flag alerts, flag resolution notices, missing feedback/debrief notices, and duplicate-safe references.
- [ ] T019 Run a manual migration review against `database/schema.sql` and `database/migrations/012_feedback_governance_analytics.sql` to verify foreign keys, unique constraints, indexes, and audit retention choices.

**Checkpoint**: Foundation ready. User stories can now be implemented independently.

---

## Phase 3: User Story 1 - Produce Governed Evaluation Report (Priority: P1) MVP

**Goal**: HR can view a governed evaluation report with official feedback completeness, raw scores, normalized scores, fallback reasons, recommendation state, blockers, and audit history.

**Independent Test**: Complete all required official feedback for one interview panel, open the HR governance report, and confirm raw scores, normalized scores, recommendation state, missing-data status, fallback reasons, and audit history are visible.

### Manual Demo Checks for User Story 1

- [ ] T020 [P] [US1] Add US1 manual demo checklist to `specs/012-feedback-governance-analytics/quickstart.md` covering governed report with eligible normalization and raw-score fallback.
- [ ] T021 [P] [US1] Add repository/service verification notes to `specs/012-feedback-governance-analytics/quickstart.md` for normalization threshold, raw fallback, and audit event creation.
- [ ] T022 [US1] Peer review US1 requirements in `specs/012-feedback-governance-analytics/spec.md`, `specs/012-feedback-governance-analytics/data-model.md`, and `specs/012-feedback-governance-analytics/route-map.md` before implementation.

### Implementation for User Story 1

- [ ] T023 [P] [US1] Implement official feedback completeness queries in `app/Repositories/FeedbackGovernanceRepository.php`, excluding observer and shadowing assignments.
- [ ] T024 [P] [US1] Implement interviewer harshness history queries in `app/Repositories/FeedbackGovernanceRepository.php` using at least 5 comparable prior official submissions in the last 12 months.
- [ ] T025 [US1] Implement normalization calculations in `app/Services/FeedbackNormalizationService.php` with raw 0-10 scores, normalized 0-10 competency scores, 0-100 aggregate output, and fallback explanations.
- [ ] T026 [US1] Implement normalized snapshot persistence and audit writes in `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T027 [US1] Extend final evaluation aggregation in `app/Repositories/FinalEvaluationRepository.php` to consume governed snapshot data without overwriting raw feedback.
- [ ] T028 [US1] Add HR governance report actions in `app/Controllers/HrFinalEvaluationController.php` for `governance` and `recalculateGovernance`.
- [ ] T029 [US1] Create HR governance report view in `views/hr/evaluations/governance.php` showing completeness, raw scores, normalized scores, fallback reasons, recommendation state, blockers, and audit history.
- [ ] T030 [US1] Update HR evaluation detail links in `views/hr/evaluations/show.php` to expose the governance report for authorized HR users.
- [ ] T031 [US1] Add CSRF-safe recalculation form and validation handling in `views/hr/evaluations/governance.php` and `app/Controllers/HrFinalEvaluationController.php`.
- [ ] T032 [US1] Add governed evaluation audit entries in `app/Repositories/FeedbackGovernanceRepository.php` for calculation, fallback, recommendation, and HR override events.
- [ ] T033 [US1] Manually verify US1 via `specs/012-feedback-governance-analytics/quickstart.md` steps for completed official feedback, normalization fallback, and pending missing-feedback state.

**Checkpoint**: User Story 1 is independently functional and provides the MVP.

---

## Phase 4: User Story 2 - Flag Serious Candidate Concerns (Priority: P1)

**Goal**: Assigned interviewers and HR can create serious concern flags, HR can resolve them with rationale, and unresolved flags block final decision actions while allowing remaining official feedback.

**Independent Test**: Create a serious concern flag as an assigned interviewer, confirm HR notification and decision blockers, resolve as HR, and confirm blockers are recalculated with audit history.

### Manual Demo Checks for User Story 2

- [ ] T034 [P] [US2] Add US2 manual demo checklist to `specs/012-feedback-governance-analytics/quickstart.md` covering interviewer flag creation, HR resolution, unauthorized denial, and blocker behavior.
- [ ] T035 [US2] Peer review US2 RBAC and blocking rules in `specs/012-feedback-governance-analytics/spec.md`, `specs/012-feedback-governance-analytics/contracts/feedback-governance-web.md`, and `specs/012-feedback-governance-analytics/route-map.md` before implementation.

### Implementation for User Story 2

- [ ] T036 [P] [US2] Implement concern flag create, list, and resolve persistence methods in `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T037 [P] [US2] Implement assigned-interviewer flag authorization checks in `app/Policies/InterviewFeedbackPolicy.php` and HR resolution checks in `app/Policies/FinalEvaluationPolicy.php`.
- [ ] T038 [US2] Add interviewer flag form actions in `app/Controllers/InterviewerInterviewController.php` for `createFlag` and `storeFlag`.
- [ ] T039 [US2] Create interviewer concern flag form in `views/interviewer/interviews/flag.php` with category, severity, explanation, CSRF, validation errors, and privacy-safe candidate context.
- [ ] T040 [US2] Add HR flag review and resolution actions in `app/Controllers/HrFinalEvaluationController.php` for `flags` and `resolveFlag`.
- [ ] T041 [US2] Create HR flag review view in `views/hr/evaluations/flags.php` with open/resolved flags, resolution form, required rationale, and audit summary.
- [ ] T042 [US2] Integrate flag blockers into final recommendation and candidate status flows in `app/Repositories/FinalEvaluationRepository.php` and `app/Controllers/HrFinalEvaluationController.php`.
- [ ] T043 [US2] Create in-system notifications for flag creation and resolution in `app/Repositories/NotificationRepository.php` and `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T044 [US2] Add audit events for concern flag creation, resolution, and blocked decision attempts in `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T045 [US2] Manually verify US2 via `specs/012-feedback-governance-analytics/quickstart.md` using assigned interviewer, unassigned interviewer, HR, and observer/candidate access attempts.

**Checkpoint**: User Story 2 is independently functional and can be demonstrated without US3-US5.

---

## Phase 5: User Story 3 - Capture Candidate Experience Sentiment (Priority: P2)

**Goal**: Candidates can submit one post-interview sentiment entry for their own completed interview, and HR can view it separately from official scoring.

**Independent Test**: Candidate submits rating and optional comment for a completed interview, HR sees sentiment separately, duplicate/early/unauthorized submissions are rejected, and scoring is unchanged.

### Manual Demo Checks for User Story 3

- [ ] T046 [P] [US3] Add US3 manual demo checklist to `specs/012-feedback-governance-analytics/quickstart.md` covering candidate-owned sentiment, duplicate prevention, HR visibility, and scoring exclusion.
- [ ] T047 [US3] Peer review US3 candidate privacy in `specs/012-feedback-governance-analytics/spec.md`, `specs/012-feedback-governance-analytics/data-model.md`, and `specs/012-feedback-governance-analytics/contracts/feedback-governance-web.md` before implementation.

### Implementation for User Story 3

- [ ] T048 [P] [US3] Implement sentiment create, duplicate check, and HR read methods in `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T049 [P] [US3] Add candidate sentiment authorization checks in `app/Policies/InterviewPolicy.php` for own completed interviews only.
- [ ] T050 [US3] Add candidate sentiment actions in `app/Controllers/CandidateInterviewController.php` for `createSentiment` and `storeSentiment`.
- [ ] T051 [US3] Create candidate sentiment form in `views/candidate/interviews/sentiment.php` with rating, optional comment, CSRF, submitted status, and validation messages.
- [ ] T052 [US3] Add sentiment links and submitted-state messaging in `views/candidate/interviews/show.php`.
- [ ] T053 [US3] Show HR-only sentiment summary in `views/hr/evaluations/governance.php` without including sentiment in official scores.
- [ ] T054 [US3] Add sentiment submission audit event in `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T055 [US3] Manually verify US3 via `specs/012-feedback-governance-analytics/quickstart.md` for completed interview, pre-completion rejection, duplicate rejection, and HR-only visibility.

**Checkpoint**: User Story 3 is independently functional and candidate sentiment remains separate from scoring.

---

## Phase 6: User Story 4 - Trigger Consensus/Debrief Workflow (Priority: P2)

**Goal**: Create exactly one in-app debrief record after all official feedback is submitted, then let HR complete it with participants, consensus, dissent, recommendation, rationale, and next action.

**Independent Test**: Submit all official feedback, confirm debrief record appears once, confirm it cannot complete with open flags, complete it as HR, and verify audit history.

### Manual Demo Checks for User Story 4

- [ ] T056 [P] [US4] Add US4 manual demo checklist to `specs/012-feedback-governance-analytics/quickstart.md` covering pending feedback, single debrief creation, open-flag blocking, HR completion, and audit history.
- [ ] T057 [US4] Peer review US4 debrief scope in `specs/012-feedback-governance-analytics/spec.md`, `specs/012-feedback-governance-analytics/research.md`, and `specs/012-feedback-governance-analytics/route-map.md` before implementation.

### Implementation for User Story 4

- [ ] T058 [P] [US4] Implement debrief create, find, block, unblock, and complete methods in `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T059 [P] [US4] Add debrief authorization checks in `app/Policies/FinalEvaluationPolicy.php` for HR-only creation and completion.
- [ ] T060 [US4] Trigger debrief record creation after final official feedback submission in `app/Controllers/InterviewerInterviewController.php` and `app/Repositories/InterviewFeedbackRepository.php`.
- [ ] T061 [US4] Add HR debrief actions in `app/Controllers/HrFinalEvaluationController.php` for `debrief` and `storeDebrief`.
- [ ] T062 [US4] Create HR debrief view in `views/hr/evaluations/debrief.php` with participants, consensus level, dissent notes, final recommendation, rationale, next action, blockers, and CSRF handling.
- [ ] T063 [US4] Add debrief status and blocker summary to `views/hr/evaluations/governance.php`.
- [ ] T064 [US4] Add debrief notifications and audit events in `app/Repositories/NotificationRepository.php` and `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T065 [US4] Manually verify US4 via `specs/012-feedback-governance-analytics/quickstart.md` for missing feedback, all feedback submitted, duplicate prevention, open flag blocker, and HR completion.

**Checkpoint**: User Story 4 is independently functional and does not require external calendar scheduling.

---

## Phase 7: User Story 5 - Visualize Competency Gaps (Priority: P3)

**Goal**: HR can maintain job competency benchmarks and view candidate-vs-ideal competency gaps with meeting, minor gap, and major gap severity labels.

**Independent Test**: HR creates benchmarks for a job, opens an evaluated candidate, and confirms candidate score, benchmark score, gap ratio, severity, and missing benchmark warnings are visible.

### Manual Demo Checks for User Story 5

- [ ] T066 [P] [US5] Add US5 manual demo checklist to `specs/012-feedback-governance-analytics/quickstart.md` covering HR benchmark maintenance, missing benchmark warnings, gap thresholds, and assigned interviewer read limits.
- [ ] T067 [US5] Peer review US5 benchmark ownership and gap thresholds in `specs/012-feedback-governance-analytics/spec.md`, `specs/012-feedback-governance-analytics/data-model.md`, and `specs/012-feedback-governance-analytics/contracts/feedback-governance-web.md` before implementation.

### Implementation for User Story 5

- [ ] T068 [P] [US5] Implement benchmark create, update, list, and audit methods in `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T069 [P] [US5] Implement gap snapshot generation methods in `app/Repositories/FeedbackGovernanceRepository.php` using 90%, 75-89%, and below-75% severity thresholds.
- [ ] T070 [US5] Add HR benchmark actions in `app/Controllers/HrFinalEvaluationController.php` for `editBenchmarks` and `updateBenchmarks`.
- [ ] T071 [US5] Create HR benchmark maintenance view in `views/hr/evaluations/benchmarks.php` with repeating competency rows, 0-10 scores, optional weights, CSRF, and validation errors.
- [ ] T072 [US5] Add competency gap visualizer section in `views/hr/evaluations/governance.php` with accessible table and CSS bars for candidate score, benchmark score, gap ratio, and severity.
- [ ] T073 [US5] Add assigned-interviewer read-only gap summary to `views/interviewer/interviews/show.php` while preserving assigned-candidate-only access.
- [ ] T074 [US5] Add benchmark and gap audit events in `app/Repositories/FeedbackGovernanceRepository.php`.
- [ ] T075 [US5] Manually verify US5 via `specs/012-feedback-governance-analytics/quickstart.md` for complete benchmarks, missing benchmark warnings, severity thresholds, and unauthorized access denial.

**Checkpoint**: User Story 5 is independently functional and supports final decision review.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Finish integration, reporting, performance, documentation, and validation across all stories.

- [ ] T076 [P] Add feedback governance analytics queries in `app/Repositories/ReportRepository.php` for pending feedback, open flags, normalization fallbacks, sentiment averages, debrief completion, and benchmark gaps.
- [ ] T077 Add HR feedback governance report action in `app/Controllers/HrReportController.php` and route link in `routes/web.php`.
- [ ] T078 Create HR feedback governance report view in `views/hr/reports/feedback-governance.php` using server-rendered cards/tables and existing report layout conventions.
- [ ] T079 [P] Add dashboard/navigation links in `views/layouts/app.php`, `views/hr/evaluations/show.php`, and `views/hr/reports/pipeline.php` for authorized HR users.
- [ ] T080 [P] Add privacy-safe empty, denied, validation, duplicate, and stale-form messaging in `views/hr/evaluations/governance.php`, `views/hr/evaluations/flags.php`, `views/hr/evaluations/debrief.php`, `views/candidate/interviews/sentiment.php`, and `views/interviewer/interviews/flag.php`.
- [ ] T081 [P] Verify PHP syntax for touched files in `app/Controllers/`, `app/Repositories/`, `app/Services/`, `app/Policies/`, `app/Enums/`, and `views/`.
- [ ] T082 Verify end-to-end manual demo in `specs/012-feedback-governance-analytics/quickstart.md` and record any known limitations in `specs/012-feedback-governance-analytics/quickstart.md`.
- [ ] T083 Peer review implementation evidence against `specs/012-feedback-governance-analytics/spec.md`, `specs/012-feedback-governance-analytics/plan.md`, and `specs/012-feedback-governance-analytics/tasks.md` before marking the feature complete.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies and can start immediately.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks every user story.
- **User Story 1 (Phase 3)**: Depends on Foundational completion and is the MVP.
- **User Story 2 (Phase 4)**: Depends on Foundational completion and can run in parallel with US1 after shared routes/schema/policies exist.
- **User Story 3 (Phase 5)**: Depends on Foundational completion and can run independently of US1/US2.
- **User Story 4 (Phase 6)**: Depends on Foundational completion and integrates best after official feedback submission flow is stable.
- **User Story 5 (Phase 7)**: Depends on Foundational completion and benefits from US1 snapshot output, but benchmark maintenance can start independently.
- **Polish (Phase 8)**: Depends on the desired user stories being complete.

### User Story Dependencies

- **US1 Produce Governed Evaluation Report (P1)**: MVP; no dependency on other user stories after foundation.
- **US2 Flag Serious Candidate Concerns (P1)**: No dependency on other user stories after foundation; integrates with US1/US4 blockers when those are present.
- **US3 Capture Candidate Experience Sentiment (P2)**: No dependency on other user stories after foundation.
- **US4 Trigger Consensus/Debrief Workflow (P2)**: Requires official feedback completion logic from foundation; integrates with US2 blockers when present.
- **US5 Visualize Competency Gaps (P3)**: Benchmark maintenance is independent; visualizer is most valuable with US1 normalized snapshots.

### Within Each User Story

- Manual demo checks and peer review happen before implementation.
- Repository and policy work precede controller actions.
- Controller actions precede forms/views unless a view-only prototype is explicitly discarded.
- Audit and notification integration happen before story checkpoint validation.
- Story validation must pass before relying on the story in later phases.

---

## Parallel Execution Examples

### User Story 1

```text
Task: T023 Implement official feedback completeness queries in app/Repositories/FeedbackGovernanceRepository.php
Task: T024 Implement interviewer harshness history queries in app/Repositories/FeedbackGovernanceRepository.php
```

### User Story 2

```text
Task: T036 Implement concern flag persistence in app/Repositories/FeedbackGovernanceRepository.php
Task: T037 Implement flag authorization checks in app/Policies/InterviewFeedbackPolicy.php and app/Policies/FinalEvaluationPolicy.php
```

### User Story 3

```text
Task: T048 Implement sentiment persistence in app/Repositories/FeedbackGovernanceRepository.php
Task: T049 Add candidate sentiment authorization checks in app/Policies/InterviewPolicy.php
```

### User Story 4

```text
Task: T058 Implement debrief persistence methods in app/Repositories/FeedbackGovernanceRepository.php
Task: T059 Add debrief authorization checks in app/Policies/FinalEvaluationPolicy.php
```

### User Story 5

```text
Task: T068 Implement benchmark persistence in app/Repositories/FeedbackGovernanceRepository.php
Task: T069 Implement gap snapshot generation in app/Repositories/FeedbackGovernanceRepository.php
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 setup.
2. Complete Phase 2 foundation.
3. Complete Phase 3 User Story 1.
4. Validate governed report, normalization eligibility, raw fallback, pending feedback, recommendation state, and audit history independently.
5. Demo MVP before continuing to flags, sentiment, debriefs, and gap visualization.

### Incremental Delivery

1. Add US1 governed evaluation report and validate independently.
2. Add US2 serious concern flags and validate blocking/resolution independently.
3. Add US3 candidate sentiment and validate scoring separation independently.
4. Add US4 in-app debrief records and validate duplicate/blocker behavior independently.
5. Add US5 benchmark maintenance and competency gap visualizer.
6. Finish cross-cutting HR analytics report, navigation, syntax checks, and quickstart validation.

### Parallel Team Strategy

1. Team completes Setup and Foundational tasks together.
2. After foundation, one developer can own US1, one can own US2 or US3, and one can own US5 benchmark maintenance.
3. US4 should coordinate with the developer touching official feedback submission flow to avoid route/controller conflicts.

---

## Notes

- `[P]` tasks are parallelizable because they touch different files or clearly separable methods.
- `[US1]` through `[US5]` labels map to the five prioritized user stories in `specs/012-feedback-governance-analytics/spec.md`.
- All user interactions must remain server-rendered Vanilla PHP web routes and CSRF-protected form submissions.
- Do not introduce REST APIs, a separated frontend, SPA behavior, external calendar scheduling, external email delivery, or runtime framework dependencies.
