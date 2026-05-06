# Tasks: Screening & Shortlisting Workflow

**Input**: Design documents from `specs/008-screening-shortlisting-workflow/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, route-map.md, quickstart.md

**Tests**: Documented manual demo tasks for acceptance criteria. PHPUnit tests where practical for policy, validation, and scoring logic.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.
**Peer Review**: Peer-review gate included before implementation of each user story phase.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Controllers**: `app/Controllers/`
- **Enums**: `app/Enums/`
- **Policies**: `app/Policies/`
- **Repositories**: `app/Repositories/`
- **Services**: `app/Services/`
- **Views**: `views/hr/screening/`
- **Routes**: `routes/web.php`
- **Schema**: `database/schema.sql`
- Do not create `api/`, `backend/`, `frontend/`, or REST contract paths.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization — new schema tables, enums, and shared scaffolding needed by all user stories.

- [x] T001 Add new screening tables (`screening_configs`, `screening_skills`, `screening_thresholds`, `screening_audit_records`) and alter `applications` and `candidate_merge_log` tables in `database/schema.sql` per data-model.md SQL migration
- [x] T002 [P] Create `ScreeningAuditAction` enum with values CONFIG_CREATED, CONFIG_UPDATED, SCORES_RECALCULATED, SHORTLIST_GENERATED, TRIAGE_EXECUTED, TRIAGE_STATUS_CHANGE, DUPLICATE_CHECK_RUN, DUPLICATE_DECISION in `app/Enums/ScreeningAuditAction.php`
- [x] T003 [P] Create `DuplicateDecisionType` enum with values MERGE, IGNORE, DEFER in `app/Enums/DuplicateDecisionType.php`
- [x] T004 [P] Create `DuplicateConfidence` enum with values HIGH, MEDIUM, LOW in `app/Enums/DuplicateConfidence.php`
- [x] T005 [P] Create `ScreeningPolicy` with methods `canConfigure`, `canRecalculate`, `canTriage`, `canViewShortlist`, `canManageDuplicates`, `canViewAudit` — all restricted to HR_ADMIN + ACTIVE in `app/Policies/ScreeningPolicy.php`
- [x] T006 [P] Create `ScreeningAuditRepository` with `log(int $jobId, int $actorId, string $action, ?string $entityType, ?int $entityId, ?array $oldValues, ?array $newValues)` and `search(array $filters, int $page, int $perPage)` in `app/Repositories/ScreeningAuditRepository.php`
- [x] T007 Create stub `HrScreeningController` extending `App\Core\Controller` with all method signatures from route-map.md (config, storeConfig, recalculate, shortlist, triagePreview, executeTriage, duplicates, resolveDuplicate, audit) in `app/Controllers/HrScreeningController.php`
- [x] T008 Register all 9 screening routes in `routes/web.php` per route-map.md (GET/POST for config, POST recalculate, GET shortlist, GET/POST triage, GET/POST duplicates, GET audit)
- [x] T009 Create empty view directory and placeholder layout-compatible PHP template `views/hr/screening/_layout_check.php` to verify the view path is loadable from the controller

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Repository and service layer that multiple user stories depend on — MUST be complete before any user story work begins.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T010 Create `ScreeningConfigRepository` with methods: `findActiveByJobId(int $jobId)`, `findByConfigId(int $configId)`, `saveConfig(int $jobId, int $createdBy, array $skills, array $thresholds)`, `deactivateByJobId(int $jobId)`, `getSkills(int $configId)`, `getThresholds(int $configId)` in `app/Repositories/ScreeningConfigRepository.php`
- [x] T011 [P] Create `DuplicateRepository` with methods: `findByPair(int $candidateA, int $candidateB)`, `recordDecision(int $primaryId, int $duplicateId, int $mergedBy, string $decisionType, string $confidence, ?int $jobId, ?array $matchingEvidence, string $notes)`, `getDecisionsForJob(int $jobId)` in `app/Repositories/DuplicateRepository.php`
- [x] T012 Enhance `SimulatedMatchScorer` with new method `scoreWeighted(array $skills, array $candidate): array` that returns `['total' => int, 'breakdown' => array]` using per-skill weight × evidence-match algorithm from research.md R1, preserving existing `score()` method, in `app/Services/SimulatedMatchScorer.php`
- [x] T013 Create `ScreeningScoreService` with methods: `recalculateForJob(int $jobId, int $actorId): array`, `getShortlist(int $jobId): array`, `executeTriage(int $jobId, int $actorId): array` in `app/Services/ScreeningScoreService.php`. Depends on T010, T012.
- [x] T014 [P] Create `DuplicateDetectionService` with method `detectDuplicates(int $jobId): array` implementing the matching rules from research.md R2 (email, phone, name, title+experience, resume_url) and confidence categorization in `app/Services/DuplicateDetectionService.php`

**Checkpoint**: Foundation ready — user story implementation can now begin in parallel

---

## Phase 3: User Story 1 — Configure Screening Rules (Priority: P1) 🎯 MVP

**Goal**: HR Admins can configure required skills, per-skill weights, and triage thresholds for a job requisition through a form-based workflow with full validation and audit.

**Independent Test**: Open an approved/open requisition → submit valid screening config → verify saved config is shown back with audit entry. Submit invalid config → verify field-level errors with no state change. Access as non-HR → verify 403.

### Peer Review Gate

- [x] T015 [US1] Peer review spec acceptance scenarios 1–3, data-model (screening_configs, screening_skills, screening_thresholds), route-map config routes, ScreeningPolicy RBAC, and validation rules before implementation

### Implementation for User Story 1

- [x] T016 [US1] Implement `HrScreeningController::config($request, $id)` — load requisition (verify APPROVED/OPEN status), load active screening config with skills and thresholds (if any), render `views/hr/screening/config.php` with form data in `app/Controllers/HrScreeningController.php`
- [x] T017 [US1] Create `views/hr/screening/config.php` — form with dynamic skill rows (name + weight + evidence_field), threshold rows (min_score, max_score, target_status dropdown), CSRF token, validation error display, and "Simulated Screening Configuration" heading. Use existing Tailwind layout from `views/layouts/`. File: `views/hr/screening/config.php`
- [x] T018 [US1] Implement `HrScreeningController::storeConfig($request, $id)` — validate: at least 1 skill, each skill_name non-empty, each weight > 0, weights sum to 100, thresholds contiguous 0–100, no overlaps, target_status in (SCREENING, ASSESSMENT, INTERVIEW, REJECTED). On success: deactivate old config via repository, save new config, log CONFIG_CREATED or CONFIG_UPDATED audit with old/new values, redirect to config page with success flash. On failure: redirect back with errors and old input. File: `app/Controllers/HrScreeningController.php`
- [x] T019 [US1] Add "Configure Screening" link/button to the existing requisition show page `views/hr/requisitions/show.php` — only visible for APPROVED/OPEN requisitions and HR_ADMIN role
- [x] T020 [US1] Write manual demo verification steps for US1 acceptance scenarios 1–3 in `specs/008-screening-shortlisting-workflow/demo-us1.md`

**Checkpoint**: User Story 1 fully functional — HR can configure screening rules with validation and audit

---

## Phase 4: User Story 2 — Recalculate Match Scores and Shortlist (Priority: P1)

**Goal**: HR Admins can recalculate simulated match scores for all APPLIED candidates in a requisition and view a simulated AI-ranked shortlist ordered by score, experience, and application date.

**Independent Test**: Configure screening rules (from US1) → run recalculation → verify each application has a 0–100 score with per-skill breakdown. View shortlist → verify ranked order and "Simulated" label. Verify missing evidence is flagged.

### Peer Review Gate

- [x] T021 [US2] Peer review scoring algorithm (research.md R1), match_score_breakdown JSON schema (data-model.md), shortlist ranking rules (score → experience → date), and acceptance scenarios 1–4 before implementation

### Implementation for User Story 2

- [x] T022 [US2] Implement `HrScreeningController::recalculate($request, $id)` — verify active config exists (block with error if not), load all APPLIED applications with candidate profile data, call `ScreeningScoreService::recalculateForJob()`, log SCORES_RECALCULATED audit entry with candidate count and config_id, redirect to shortlist page. File: `app/Controllers/HrScreeningController.php`
- [x] T023 [US2] Implement `ScreeningScoreService::recalculateForJob(int $jobId, int $actorId)` — load active config + skills, fetch APPLIED applications joined with candidates, call `SimulatedMatchScorer::scoreWeighted()` per application, UPDATE `applications.match_score` and `applications.match_score_breakdown` (JSON) in a transaction, return summary. File: `app/Services/ScreeningScoreService.php`
- [x] T024 [US2] Implement `HrScreeningController::shortlist($request, $id)` — load applications for requisition ordered by `match_score DESC, years_experience DESC, applied_at ASC`, load config for display, render `views/hr/screening/shortlist.php`. File: `app/Controllers/HrScreeningController.php`
- [x] T025 [US2] Create `views/hr/screening/shortlist.php` — table with rank, candidate name, match score (0–100), per-skill breakdown (expand/collapse), missing evidence flags, years of experience, applied date. Header: "Simulated AI-Ranked Shortlist". Subheading: "Rankings are simulated and reviewable — not an external or final hiring decision." Include "Recalculate Scores" button (POST form with CSRF) and "Run Triage" link. File: `views/hr/screening/shortlist.php`
- [x] T026 [US2] Add "View Shortlist" link to the requisition show page `views/hr/requisitions/show.php` — only visible when an active screening config exists
- [x] T027 [US2] Write manual demo verification steps for US2 acceptance scenarios 1–4 in `specs/008-screening-shortlisting-workflow/demo-us2.md`

**Checkpoint**: User Stories 1 AND 2 both work independently — HR can configure rules and view scored shortlists

---

## Phase 5: User Story 3 — Automated Triage From Applied (Priority: P2)

**Goal**: HR Admins can run automated triage to move APPLIED candidates to SCREENING, ASSESSMENT, INTERVIEW, or REJECTED based on configured thresholds, with full audit trail.

**Independent Test**: Run triage on requisition with APPLIED applications spanning score bands → verify each moves to correct target status. Verify non-APPLIED applications are untouched. Verify audit entries include before/after status, score, threshold rule, actor, timestamp, and "simulated" label.

### Peer Review Gate

- [ ] T028 [US3] Peer review triage threshold model (research.md R3), status transition rules (data-model.md), FR-011 idempotency (only APPLIED moves), audit requirements (FR-012), and acceptance scenarios 1–4 before implementation

### Implementation for User Story 3

- [ ] T029 [US3] Implement `HrScreeningController::triagePreview($request, $id)` — load active config + thresholds, load APPLIED applications with current match_scores, compute projected target status per threshold band, render `views/hr/screening/triage-confirm.php` with preview table. File: `app/Controllers/HrScreeningController.php`
- [ ] T030 [US3] Create `views/hr/screening/triage-confirm.php` — preview table showing candidate name, current score, projected target status per threshold band. Include "Confirm Triage" POST form with CSRF, "Cancel" link back to shortlist. Warning banner: "This action will update application statuses. All changes are audited and labeled as simulated triage." File: `views/hr/screening/triage-confirm.php`
- [ ] T031 [US3] Implement `ScreeningScoreService::executeTriage(int $jobId, int $actorId)` — in a transaction: load thresholds, select APPLIED applications with scores, for each apply threshold mapping, UPDATE application status, INSERT application_status_histories row with old/new status + reason including score and threshold rule, INSERT screening_audit_records per status change (TRIAGE_STATUS_CHANGE with old_values/new_values JSON). Return array of changes. Guard: skip if application status ≠ APPLIED. File: `app/Services/ScreeningScoreService.php`
- [ ] T032 [US3] Implement `HrScreeningController::executeTriage($request, $id)` — call `ScreeningScoreService::executeTriage()`, log TRIAGE_EXECUTED audit entry with summary (total moved, per-status counts), render `views/hr/screening/triage-results.php`. File: `app/Controllers/HrScreeningController.php`
- [ ] T033 [US3] Create `views/hr/screening/triage-results.php` — results summary (X candidates moved to SCREENING, Y to ASSESSMENT, etc.), detail table with per-candidate before/after status and score. "Simulated Triage Results" heading. Link back to shortlist. File: `views/hr/screening/triage-results.php`
- [ ] T034 [US3] Write manual demo verification steps for US3 acceptance scenarios 1–4 in `specs/008-screening-shortlisting-workflow/demo-us3.md`

**Checkpoint**: User Stories 1, 2, AND 3 all work — HR can configure, score, and triage candidates

---

## Phase 6: User Story 4 — Detect and Resolve Duplicate Candidates (Priority: P2)

**Goal**: HR Admins can trigger on-demand duplicate detection within a requisition's applicant pool, review suggestions with matching evidence and confidence, and record merge/ignore/defer decisions with a reason.

**Independent Test**: Create candidates with matching email/phone/name → trigger duplicate check → verify suggestions appear with confidence category. Record merge/ignore/defer decision with reason → verify audit log entry. Attempt without reason → verify rejection. Attempt as non-HR → verify 403.

### Peer Review Gate

- [x] T035 [US4] Peer review duplicate detection rules (research.md R2), confidence categorization, extended `candidate_merge_log` schema (data-model.md), FR-013 through FR-017, and acceptance scenarios 1–4 before implementation

### Implementation for User Story 4

- [x] T036 [US4] Implement `DuplicateDetectionService::detectDuplicates(int $jobId)` — load all candidates for the requisition's applications, compare each pair on email (exact, case-insensitive), phone (normalized digits), name (case-insensitive trim), current_title + years_experience (combined), resume_url (exact). Compute confidence per research.md R2 rules. Exclude pairs with existing non-DEFER decisions. Return array of suggestions with matching_evidence JSON and confidence. File: `app/Services/DuplicateDetectionService.php`
- [x] T037 [US4] Implement `HrScreeningController::duplicates($request, $id)` — call `DuplicateDetectionService::detectDuplicates()`, log DUPLICATE_CHECK_RUN audit, render `views/hr/screening/duplicates.php`. File: `app/Controllers/HrScreeningController.php`
- [x] T038 [US4] Create `views/hr/screening/duplicates.php` — list of duplicate suggestions, each showing candidate A name/email vs candidate B name/email, matching fields highlighted, confidence badge (HIGH=red, MEDIUM=yellow, LOW=blue). Each row links to resolve form. Zero-state message if no duplicates found. File: `views/hr/screening/duplicates.php`
- [x] T039 [US4] Implement `HrScreeningController::resolveDuplicate($request, $id, $mergeId)` for GET — load suggestion details (both candidate profiles side-by-side), render `views/hr/screening/duplicate-resolve.php`. For POST — validate: decision_type required (MERGE/IGNORE/DEFER), notes required (non-empty reason), if MERGE then primary_candidate_id required. On success: call `DuplicateRepository::recordDecision()`, log DUPLICATE_DECISION audit, redirect to duplicates list with success flash. On failure: redirect back with errors. File: `app/Controllers/HrScreeningController.php`
- [x] T040 [US4] Create `views/hr/screening/duplicate-resolve.php` — side-by-side candidate comparison (name, email, phone, title, experience, location, resume_url), decision radio buttons (Merge, Ignore, Defer), primary candidate selector (shown only when Merge selected via JS toggle), mandatory reason textarea, CSRF form, submit button. File: `views/hr/screening/duplicate-resolve.php`
- [x] T041 [US4] Add "Check Duplicates" link to the requisition show page `views/hr/requisitions/show.php` — only visible for HR_ADMIN when requisition has applications
- [x] T042 [US4] Write manual demo verification steps for US4 acceptance scenarios 1–4 in `specs/008-screening-shortlisting-workflow/demo-us4.md`

**Checkpoint**: User Stories 1–4 all work — full screening pipeline including deduplication

---

## Phase 7: User Story 5 — Review Audit Evidence (Priority: P3)

**Goal**: HR Admins can review audit evidence for all screening workflow actions (config changes, recalculations, shortlist generation, duplicate decisions, triage) filtered by requisition, candidate, action type, or date range.

**Independent Test**: Perform each workflow action → navigate to screening audit → verify entries listed in reverse chronological order with actor, action, affected records, and before/after values. Filter by action type and date range → verify filtering works.

### Peer Review Gate

- [x] T043 [US5] Peer review audit schema (screening_audit_records), ScreeningAuditRepository search/filter, acceptance scenarios 1–2, and privacy requirements (RP-005: minimal candidate PII) before implementation

### Implementation for User Story 5

- [x] T044 [US5] Implement `HrScreeningController::audit($request, $id)` — load filters from query string (action_type, date_from, date_to, candidate_name), call `ScreeningAuditRepository::search()` with pagination, render `views/hr/screening/audit.php`. File: `app/Controllers/HrScreeningController.php`
- [x] T045 [US5] Extend `ScreeningAuditRepository::search()` to support filters: action (exact match), date range (from/to on created_at), entity_type, and join with `users` for actor name. Include pagination with page/perPage. File: `app/Repositories/ScreeningAuditRepository.php`
- [x] T046 [US5] Create `views/hr/screening/audit.php` — filter form (action type dropdown, date from/to, submit), results table with columns: timestamp, actor name, action type, entity, summary (formatted old→new values from JSON), detail expand. Pagination controls. "Screening Audit Trail" heading. File: `views/hr/screening/audit.php`
- [x] T047 [US5] Integrate screening audit records into the existing global audit log view by adding SCREENING entity type to `AuditLogRepository::entities()` array and adding a UNION ALL clause for `screening_audit_records` in `AuditLogRepository::baseUnionSql()`. Files: `app/Repositories/AuditLogRepository.php`
- [x] T048 [US5] Add "Screening Audit" link to the requisition show page `views/hr/requisitions/show.php` — only visible for HR_ADMIN
- [x] T049 [US5] Write manual demo verification steps for US5 acceptance scenarios 1–2 in `specs/008-screening-shortlisting-workflow/demo-us5.md`

**Checkpoint**: All 5 user stories functional — complete screening & shortlisting workflow

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories — security hardening, edge cases, performance, documentation.

- [x] T050 [P] Add anti-duplicate-submission guards: verify triage only moves APPLIED applications (WHERE status = 'APPLIED'), recalculation overwrites scores idempotently, and merge decisions check existing non-DEFER records via unique constraint in `app/Services/ScreeningScoreService.php` and `app/Repositories/DuplicateRepository.php`
- [x] T051 [P] Add missing-config guard to recalculate and triage controller methods — if no active screening config exists for the requisition, redirect back with error flash "Please configure screening rules first." in `app/Controllers/HrScreeningController.php`
- [x] T052 [P] Add zero-candidate edge case handling — if no APPLIED candidates exist when running recalculation or triage, show a clear "No APPLIED candidates found" message instead of an empty result in `app/Controllers/HrScreeningController.php`
- [x] T053 [P] Verify RBAC enforcement end-to-end: access each screening route as CANDIDATE role and INTERVIEWER role → confirm 403 response with no candidate data or scoring details leaked. Document results in `specs/008-screening-shortlisting-workflow/rbac-verification.md`
- [x] T054 [P] Verify candidate privacy: log in as a CANDIDATE user, navigate to own application status → confirm no match-score formulas, rankings, duplicate suggestions, or HR audit details are visible. Document in `specs/008-screening-shortlisting-workflow/privacy-verification.md`
- [x] T055 [P] Add seed data for screening demo: create a screening configuration with 4 skills and 4 threshold bands for at least one requisition in `scripts/mock_seed.php` (append to existing seed script)
- [x] T056 Run `quickstart.md` end-to-end validation — follow all demo walkthrough steps and verify each checkpoint passes
- [x] T057 Code review: verify all new files follow SRIM conventions (namespaces, Controller extends `App\Core\Controller`, repositories use `Database::fetch`/`fetchAll`, CSRF on all POST forms, flash messages via Session)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup (T001–T009) completion — BLOCKS all user stories
- **User Stories (Phase 3–7)**: All depend on Foundational (Phase 2) completion
  - US1 (Phase 3) and US4 (Phase 6) can proceed in parallel
  - US2 (Phase 4) depends on US1 (needs configured screening rules to test)
  - US3 (Phase 5) depends on US2 (needs recalculated scores to triage)
  - US5 (Phase 7) can start after any user story has produced audit entries
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (P1)**: Can start after Foundational — No dependencies on other stories
- **US2 (P1)**: Depends on US1 — needs a valid screening config to recalculate against
- **US3 (P2)**: Depends on US2 — needs current match scores to execute triage
- **US4 (P2)**: Can start after Foundational — independent of US1/US2/US3
- **US5 (P3)**: Can start after Foundational — but more valuable once other stories produce audit data

### Within Each User Story

- Peer review gate MUST complete before implementation
- Repository/service methods before controller actions
- Controller actions before views
- Core implementation before integration with existing pages
- Demo verification as final task per story

### Parallel Opportunities

- All Setup tasks T002–T006 marked [P] can run in parallel
- Foundational tasks T010–T011, T014 can run in parallel
- US1 and US4 can be worked on in parallel by different team members
- All Polish tasks T050–T055 marked [P] can run in parallel

---

## Parallel Example: User Story 1

```bash
# After Phase 2 completes, launch US1 tasks:
# Sequential (depends on prior):
Task T016: Implement controller config() method
Task T017: Create config.php view (depends on T016 for data contract)
Task T018: Implement controller storeConfig() method (depends on T016)
Task T019: Add link to requisition show page (depends on T017)
Task T020: Write demo steps (depends on T018, T019)
```

## Parallel Example: US1 + US4 Concurrent

```bash
# Developer A (US1):
Task T016 → T017 → T018 → T019 → T020

# Developer B (US4 — fully independent):
Task T036 → T037 → T038 → T039 → T040 → T041 → T042
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001–T009)
2. Complete Phase 2: Foundational (T010–T014)
3. Complete Phase 3: User Story 1 (T015–T020)
4. **STOP and VALIDATE**: Test US1 independently — configure screening rules
5. Demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test → Demo (MVP: config works!)
3. Add User Story 2 → Test → Demo (scoring + shortlist works!)
4. Add User Story 3 → Test → Demo (triage works!)
5. Add User Story 4 → Test → Demo (deduplication works!)
6. Add User Story 5 → Test → Demo (audit review works!)
7. Polish → Final validation → Feature complete

### Parallel Team Strategy

With 3 developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: US1 → US2 → US3 (sequential dependency chain)
   - Developer B: US4 (independent)
   - Developer C: US5 (independent, starts after US1 produces audit data)
3. All reconvene for Phase 8 Polish

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Vanilla PHP MVC only — no REST APIs, no separated frontend, no framework dependencies
- All scoring and ranking must be labeled as "simulated"
- All POST forms must include CSRF token
- All mutating actions must produce audit records
