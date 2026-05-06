# Tasks: Advanced Job Requisition Governance

**Input**: Design documents from `specs/009-requisition-governance/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, route-map.md, quickstart.md

**Tests**: Manual acceptance testing via documented server-rendered page workflows per spec. No automated test framework explicitly requested.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.
**Peer Review**: Include a peer-review task before implementation starts for each phase or user story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Vanilla PHP monolith**: `app/`, `routes/web.php`, `views/`, `database/`
- **Controllers**: `app/Controllers/`
- **Authorization**: `app/Policies/`
- **Repositories**: `app/Repositories/`
- **Services**: `app/Services/`
- **Enums**: `app/Enums/`
- **Views**: `views/hr/governance/`, `views/hr/requisitions/`
- Do not create `api/`, `backend/`, `frontend/`, or REST contract paths.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Schema migration and new enums/shared types needed by all user stories

- [x] T001 Create SQL migration file with all new tables and column additions in `database/migrations/009_governance_tables.sql` — include: ALTER TABLE users ADD is_department_head, CREATE TABLE requisition_approval_steps, CREATE TABLE requisition_template_versions, CREATE TABLE job_board_platforms (with seed data), CREATE TABLE job_board_sync_records, CREATE TABLE requisition_governance_audit. Use exact DDL from `data-model.md`.
- [x] T002 [P] Add `REJECTED` case to `JobRequisitionStatus` enum in `app/Enums/JobRequisitionStatus.php`
- [x] T003 [P] Create `GovernanceAuditAction` enum in `app/Enums/GovernanceAuditAction.php` with all 11 action types from data-model.md
- [x] T004 [P] Create `SyncStatus` enum in `app/Enums/SyncStatus.php` with cases QUEUED, PUBLISHED, UNPUBLISHED, FAILED
- [x] T005 [P] Create `ApprovalDecision` enum in `app/Enums/ApprovalDecision.php` with cases APPROVED, REJECTED
- [x] T006 Apply migration to local MySQL database by running `database/migrations/009_governance_tables.sql`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core repository, policy, and routing infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T007 Create `GovernanceRepository` class in `app/Repositories/GovernanceRepository.php` with all method stubs from route-map.md: getPendingApprovals, recordApprovalStep, getApprovalHistory, createTemplateVersion, getVersionHistory, getVersion, getLatestVersionNumber, getActivePlatforms, createSyncRecord, getSyncHistory, hasPublishedSync, getPublishedPlatforms, recordGovernanceAudit, getGovernanceAuditLog, getDepartmentHeads, setDepartmentHead. Use `App\Core\Database` for PDO queries following the pattern in existing repositories like `ScreeningAuditRepository.php`.
- [x] T008 [P] Create `GovernancePolicy` class in `app/Policies/GovernancePolicy.php` with methods: viewApprovalQueue (HR_ADMIN + is_department_head), approveRequisition (HR_ADMIN + dept head for requisition's dept + not self-approval), publishRequisition (HR_ADMIN + requisition is OPEN), viewGovernance (HR_ADMIN), manageDepartmentHeads (HR_ADMIN). Follow the pattern in `app/Policies/JobRequisitionPolicy.php`.
- [x] T009 [P] Update `JobRequisitionPolicy` in `app/Policies/JobRequisitionPolicy.php`: add REJECTED to editable statuses in `update()` method, add REJECTED → PENDING transition in `transition()` method, remove PENDING → APPROVED from `transition()` (moved to GovernancePolicy).
- [x] T010 Create skeleton `HrGovernanceController` in `app/Controllers/HrGovernanceController.php` extending `App\Core\Controller` with all method stubs from route-map.md: approvalQueue, approveRequisition, rejectRequisition, versionHistory, showVersion, compareVersions, publishForm, publishRequisition, unpublishRequisition, syncHistory, governanceAudit, departmentHeads, assignDepartmentHead, removeDepartmentHead.
- [x] T011 Register all governance routes in `routes/web.php`: replace existing approve route (line 74) with GovernanceController routes, add reject route, add approval queue route, add version routes (index, show, compare), add publish routes (form, store, unpublish), add sync history route, add governance audit route, add department head management routes (index, assign, remove). Total ~14 new/replaced route entries.
- [x] T012 Peer review: verify migration DDL matches data-model.md, enum values match spec, policy rules match FR-005/FR-006/RP-001–RP-006, route names don't conflict with existing routes

**Checkpoint**: Foundation ready — user story implementation can now begin in parallel

---

## Phase 3: User Story 1 — Department-Head Requisition Approval Workflow (Priority: P1) 🎯 MVP

**Goal**: HR Admins submit requisitions for approval, department heads approve or reject within their department scope, and the full approval chain is recorded.

**Independent Test**: Sign in as HR Admin → create requisition → submit for approval → sign in as department head → approve or reject → verify department-scoped queue, cross-department denial, self-approval denial, and rejection-revision-resubmission cycle.

### Implementation for User Story 1

- [x] T013 [US1] Implement `GovernanceRepository::getDepartmentHeads()` — query users WHERE role = HR_ADMIN AND is_department_head = TRUE, joining departments for name. Implement `setDepartmentHead(int $userId, bool $isHead)` — UPDATE users SET is_department_head = ? WHERE user_id = ?, with application-level check that no other user in the same department already has is_department_head = TRUE. Implement `recordGovernanceAudit()` for DEPT_HEAD_ASSIGNED and DEPT_HEAD_REMOVED actions. All in `app/Repositories/GovernanceRepository.php`.
- [x] T014 [P] [US1] Implement department head management view in `views/hr/governance/department-heads.php` — list all departments with their current head (if any), show assign/remove forms per department, display HR Admin users available for assignment (must have matching department_id). Use existing layout from `views/layouts/`.
- [x] T015 [US1] Implement `HrGovernanceController::departmentHeads()` — render department-heads view with data from GovernanceRepository. Implement `assignDepartmentHead()` — validate user is HR_ADMIN with matching department_id, check 1:1 constraint, call setDepartmentHead(true), record audit. Implement `removeDepartmentHead()` — call setDepartmentHead(false), record audit. All in `app/Controllers/HrGovernanceController.php`.
- [x] T016 [US1] Implement `GovernanceRepository::getPendingApprovals(int $departmentId)` — SELECT from job_requisitions WHERE status = PENDING AND department_id = ?, joining users for created_by name. Implement `recordApprovalStep()` — INSERT into requisition_approval_steps. Implement `getApprovalHistory(int $jobId)` — SELECT from requisition_approval_steps joining users for approver name. All in `app/Repositories/GovernanceRepository.php`.
- [x] T017 [P] [US1] Implement approval queue view in `views/hr/governance/approval-queue.php` — list pending requisitions for the department head's department, show title, department, creator, submitted date, link to requisition detail and approve/reject form.
- [x] T018 [P] [US1] Implement approve/reject form view in `views/hr/governance/approve-form.php` — show requisition details (title, description, requirements, department, creator), optional comments textarea, approve button and reject button, CSRF token.
- [x] T019 [US1] Implement `HrGovernanceController::approvalQueue()` — require HR_ADMIN + is_department_head via GovernancePolicy, get user's department_id, call getPendingApprovals, render approval-queue view. Implement `approveRequisition()` — validate CSRF, load requisition, check GovernancePolicy::approveRequisition (dept match, not self, status PENDING), record approval step (APPROVED), update job_requisitions status to APPROVED + set approved_by + approved_at, record governance audit (REQUISITION_APPROVED), redirect with success flash. Implement `rejectRequisition()` — same validation, record step (REJECTED), update status to REJECTED, record governance audit (REQUISITION_REJECTED), redirect. All in `app/Controllers/HrGovernanceController.php`.
- [x] T020 [US1] Modify `HrController::transitionRequisition()` in `app/Controllers/HrController.php`: when transitioning DRAFT → PENDING or REJECTED → PENDING, record governance audit (REQUISITION_SUBMITTED or REQUISITION_RESUBMITTED). Remove the PENDING → APPROVED transition logic from this method (now handled by HrGovernanceController). Keep APPROVED → OPEN and OPEN/APPROVED → CLOSED transitions, adding governance audit records (REQUISITION_OPENED, REQUISITION_CLOSED) for each.
- [x] T021 [US1] Modify `HrController::updateRequisition()` in `app/Controllers/HrController.php`: when updating an APPROVED requisition where description or requirements changed, reset status to DRAFT (triggering re-approval requirement), flash message explaining re-approval needed.
- [x] T022 [US1] Update `views/hr/requisitions/index.php`: add visual badge/indicator for REJECTED status (e.g., red badge like existing status badges). Add "Pending Approvals" link in sidebar/nav for users with is_department_head = TRUE.
- [x] T023 [US1] Update `views/hr/requisitions/show.php`: display approval history section (from getApprovalHistory), show current approval status, department head info, rejection reason if rejected. Add "Submit for Approval" button when status is DRAFT or REJECTED. Remove direct approve button (replaced by governance flow).
- [x] T024 [US1] Update `views/hr/dashboard.php`: add "Pending Approvals" count widget for department heads showing number of requisitions awaiting their review, linking to `/hr/approvals`.

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 4 — Comprehensive Audit Trail (Priority: P1)

**Goal**: Every governance action is recorded in an immutable audit log with filterable viewing for authorized users. This is co-P1 with US1 because the audit foundation is needed by all other stories.

**Independent Test**: Perform governance actions (submit, approve, reject) and verify each creates an audit entry with correct actor, action, timestamp, old/new values. Filter by action type and date range.

### Implementation for User Story 4

- [x] T025 [US4] Implement `GovernanceRepository::getGovernanceAuditLog(int $jobId, array $filters)` in `app/Repositories/GovernanceRepository.php` — query requisition_governance_audit WHERE job_id = ? with optional filters for action type (WHERE action = ?), date range (WHERE created_at BETWEEN ? AND ?), and actor. Join users for actor name. Paginate results (25 per page). Return rows + total count.
- [x] T026 [P] [US4] Implement governance audit log view in `views/hr/governance/governance-audit.php` — show requisition title/ID header, filter form (action type dropdown from GovernanceAuditAction enum, date range pickers, actor text input), table with columns: date/time, actor, action, old value, new value, comments. Pagination controls. Use existing Tailwind styling patterns.
- [x] T027 [US4] Implement `HrGovernanceController::governanceAudit()` in `app/Controllers/HrGovernanceController.php` — require HR_ADMIN via GovernancePolicy::viewGovernance, load requisition, parse filter query params, call getGovernanceAuditLog, render governance-audit view.
- [x] T028 [US4] Update `AuditLogRepository` in `app/Repositories/AuditLogRepository.php`: add `'REQUISITION_GOVERNANCE'` to the `entities()` array. Add a new UNION ALL leg in `baseUnionSql()` joining `requisition_governance_audit` with users for actor info, mapping to entity_type = 'REQUISITION_GOVERNANCE'.
- [x] T029 [US4] Add governance audit link to `views/hr/requisitions/show.php` — add a "Governance Audit" tab/link pointing to `/hr/requisitions/{id}/governance-audit`.

**Checkpoint**: At this point, User Stories 1 AND 4 should both work independently and audit trail captures all US1 actions

---

## Phase 5: User Story 2 — Template Versioning for Job Descriptions and Requirements (Priority: P2)

**Goal**: System automatically creates versioned snapshots of description + requirements on submission, HR Admins can view version history and compare any two versions.

**Independent Test**: Create requisition → submit (version 1 created) → reject → edit description → resubmit (version 2 created) → view version history → compare v1 vs v2 → verify inline diff shows changes.

### Implementation for User Story 2

- [x] T030 [US2] Implement `GovernanceRepository::createTemplateVersion()` in `app/Repositories/GovernanceRepository.php` — compute next version_number via getLatestVersionNumber()+1, INSERT into requisition_template_versions with job_id, version_number, description_body, requirements_body, created_by. Also call recordGovernanceAudit with TEMPLATE_VERSION_CREATED action. Return version_id.
- [x] T031 [P] [US2] Implement `GovernanceRepository::getVersionHistory(int $jobId)` — SELECT from requisition_template_versions WHERE job_id = ? ORDER BY version_number DESC, joining users for creator name. Implement `getVersion(int $jobId, int $versionId)` — SELECT single version with validation that it belongs to the given job_id. Implement `getLatestVersionNumber(int $jobId)` — SELECT MAX(version_number) or return 0. All in `app/Repositories/GovernanceRepository.php`.
- [x] T032 [US2] Create `TemplateVersionDiffService` in `app/Services/TemplateVersionDiffService.php` — implement a simple line-by-line diff algorithm that takes two text strings and returns an array of diff chunks, each with type (unchanged/added/removed) and content. Use longest common subsequence approach. Provide a static method `diff(string $old, string $new): array`.
- [x] T033 [P] [US2] Implement version history view in `views/hr/governance/version-history.php` — list all versions for a requisition with version number, creation date, author, and first 100 chars of description as preview. Include checkboxes to select two versions for comparison, with "Compare Selected" button. Link each version to its detail view.
- [x] T034 [P] [US2] Implement single version detail view in `views/hr/governance/version-show.php` — show full version content: version number, created by, created at, full description body, full requirements body. Back link to version history.
- [x] T035 [P] [US2] Implement version comparison view in `views/hr/governance/version-compare.php` — display inline diff output from TemplateVersionDiffService for both description and requirements. Show version numbers being compared (v1 ↔ v2). Color-code: green for additions, red for removals, neutral for unchanged. Back link to version history.
- [x] T036 [US2] Implement `HrGovernanceController::versionHistory()` — require HR_ADMIN, load requisition, call getVersionHistory, render version-history view. Implement `showVersion()` — load and render single version. Implement `compareVersions()` — parse v1/v2 query params, load both versions, compute diff via TemplateVersionDiffService, render version-compare view. All in `app/Controllers/HrGovernanceController.php`.
- [x] T037 [US2] Hook template versioning into submission flow: modify `HrController::transitionRequisition()` in `app/Controllers/HrController.php` so that when transitioning DRAFT/REJECTED → PENDING, call `GovernanceRepository::createTemplateVersion()` with current description + requirements content. Also hook into `HrController::updateRequisition()` so that when an APPROVED requisition's content is changed (triggering re-approval), a new version is created before status reset.
- [x] T038 [US2] Add version history link to `views/hr/requisitions/show.php` — add a "Version History (N versions)" link/tab pointing to `/hr/requisitions/{id}/versions`, showing the count of existing versions.

**Checkpoint**: At this point, User Stories 1, 2, AND 4 should all work independently. Version snapshots are created on submission and visible in history.

---

## Phase 6: User Story 3 — Simulated Job-Board Publishing (Priority: P3)

**Goal**: HR Admins simulate publishing Open requisitions to selected platforms, creating local sync records with payload, status, and timestamps. Unpublish on close.

**Independent Test**: Open a requisition → select platforms (LinkedIn, Indeed) → publish → verify sync records with PUBLISHED status, correct payload summary, timestamps → close requisition → unpublish → verify UNPUBLISHED records → attempt duplicate publish → blocked.

### Implementation for User Story 3

- [x] T039 [US3] Implement `GovernanceRepository::getActivePlatforms()` in `app/Repositories/GovernanceRepository.php` — SELECT from job_board_platforms WHERE is_active = TRUE ORDER BY name. Implement `hasPublishedSync(int $jobId, int $platformId)` — SELECT EXISTS from job_board_sync_records WHERE job_id = ? AND platform_id = ? AND status = 'PUBLISHED'. Implement `getPublishedPlatforms(int $jobId)` — return platform_ids with active PUBLISHED records.
- [x] T040 [US3] Implement `GovernanceRepository::createSyncRecord()` in `app/Repositories/GovernanceRepository.php` — build payload_summary JSON from requisition data (title, department name, description excerpt first 200 chars, requirements), INSERT into job_board_sync_records with status = QUEUED, then immediately UPDATE to PUBLISHED with completed_at = NOW() (simulated instant sync). Record governance audit (SYNC_PUBLISHED). Return sync_id.
- [x] T041 [US3] Implement `GovernanceRepository::getSyncHistory(int $jobId)` in `app/Repositories/GovernanceRepository.php` — SELECT from job_board_sync_records WHERE job_id = ? JOIN job_board_platforms for platform name, ORDER BY queued_at DESC.
- [x] T042 [P] [US3] Implement publish form view in `views/hr/governance/publish-form.php` — show requisition title and status, list active platforms as checkboxes, indicate which platforms already have a PUBLISHED record (disabled/checked with note), submit button with CSRF token. Block form if requisition not OPEN.
- [x] T043 [P] [US3] Implement sync history view in `views/hr/governance/sync-history.php` — table with columns: platform name, payload summary (formatted JSON), status (with color badges: green=PUBLISHED, gray=UNPUBLISHED, yellow=QUEUED), queued at, completed at, created by. Back link to requisition.
- [x] T044 [US3] Implement `HrGovernanceController::publishForm()` — require HR_ADMIN, load requisition, check status = OPEN via GovernancePolicy::publishRequisition, get active platforms, get published platforms, render publish-form view. Implement `publishRequisition()` — validate CSRF, validate selected platforms array, check no duplicate published records, call createSyncRecord for each selected platform, redirect with success. Implement `unpublishRequisition()` — get published platforms, INSERT sync records with status = UNPUBLISHED + completed_at for each, record governance audit (SYNC_UNPUBLISHED), redirect. Implement `syncHistory()` — load sync records, render sync-history view. All in `app/Controllers/HrGovernanceController.php`.
- [x] T045 [US3] Add publish and sync links to `views/hr/requisitions/show.php` — add "Publish to Job Boards" button/link (visible when status = OPEN), add "Sync History" link, add "Unpublish" button (visible when status = CLOSED and published records exist).
- [x] T046 [US3] Verify no external HTTP calls: review all sync-related code to confirm no `curl`, `file_get_contents(http...)`, `Http::`, or similar external request functions are used anywhere in the publishing flow.

**Checkpoint**: All user stories should now be independently functional

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories and final validation

- [x] T047 [P] Add flash message styling and consistency across all governance views — ensure success/error flash messages render consistently in approval, versioning, publishing, and audit views following existing SRIM flash message patterns.
- [x] T048 [P] Add navigation links: update HR sidebar/navigation to include governance section with links to "Approval Queue" (dept heads only), "Department Heads", in `views/layouts/` header or sidebar partial.
- [x] T049 Verify RBAC isolation: manually test that Candidate users accessing any `/hr/governance/` or `/hr/approvals` or `/hr/requisitions/{id}/versions` URL receive 403 errors. Verify Interviewer users also receive 403. Document test results.
- [x] T050 Verify edge cases from spec: (1) submit requisition with no dept head → error shown, (2) edit APPROVED requisition → re-approval triggered, (3) concurrent edit → OCC rejection, (4) single version → no compare option shown, (5) self-approval → denied. Document test results.
- [x] T051 Update `database/schema.sql` to include all governance tables and seed data so fresh installs include the complete schema. Add the tables in correct FK dependency order (after departments and users, before any dependent tables).
- [x] T052 Run full quickstart.md acceptance test workflow end-to-end: assign dept heads → create/submit/approve/reject requisitions → view versions/compare → publish/unpublish → verify audit trail → verify unified audit log integration.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **User Stories (Phase 3–6)**: All depend on Foundational phase completion
  - US1 (Phase 3) and US4 (Phase 4) are both P1 — implement US1 first (US4 depends on audit records created by US1 actions)
  - US2 (Phase 5) can start after Phase 4 (hooks into submission flow from US1)
  - US3 (Phase 6) can start after Phase 4 (uses audit infrastructure from US4)
- **Polish (Phase 7)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) — No dependencies on other stories
- **User Story 4 (P1)**: Can start after US1 (Phase 3) — needs audit records from governance actions
- **User Story 2 (P2)**: Can start after US1 (Phase 3) — hooks into submission flow
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) — independently testable but benefits from audit foundation (US4)

### Within Each User Story

- Repository methods before controller actions
- Controller actions before views (unless prototyping)
- Policy checks wired before controller logic
- Governance audit calls integrated into every state-changing action
- Story complete before moving to next priority
- Commit after each task or logical group

### Parallel Opportunities

- All Setup tasks T002–T005 marked [P] can run in parallel
- Foundational tasks T008, T009 marked [P] can run in parallel
- Within US1: T014, T017, T018 (views) marked [P] can run in parallel
- Within US2: T031, T033, T034, T035 marked [P] can run in parallel
- Within US3: T042, T043 marked [P] can run in parallel
- Within Polish: T047, T048 marked [P] can run in parallel

---

## Parallel Example: User Story 1

```bash
# After T013 (repository) is done, launch view tasks together:
Task: "T014 [P] [US1] Implement department-heads.php view"
Task: "T017 [P] [US1] Implement approval-queue.php view"
Task: "T018 [P] [US1] Implement approve-form.php view"

# Then implement controller actions that depend on repo + views:
Task: "T015 [US1] Implement departmentHeads controller actions"
Task: "T019 [US1] Implement approvalQueue/approve/reject controller actions"
```

## Parallel Example: User Story 2

```bash
# After T030–T031 (repository methods) are done, launch views together:
Task: "T033 [P] [US2] Implement version-history.php view"
Task: "T034 [P] [US2] Implement version-show.php view"
Task: "T035 [P] [US2] Implement version-compare.php view"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001–T006)
2. Complete Phase 2: Foundational (T007–T012)
3. Complete Phase 3: User Story 1 (T013–T024)
4. **STOP and VALIDATE**: Test approval workflow end-to-end
5. Deploy/demo if ready — the core governance backbone is functional

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 4 → Test independently → Audit trail captures all actions
4. Add User Story 2 → Test independently → Template versioning works
5. Add User Story 3 → Test independently → Job-board publishing simulated
6. Each story adds value without breaking previous stories

### Parallel Team Strategy

With 3 developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1 (approval workflow)
   - Developer B: User Story 2 (template versioning — starts after A finishes T020)
   - Developer C: User Story 3 (job-board publishing — can start independently)
3. User Story 4 (audit trail) can be woven in by Developer A after US1
4. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
- Avoid: REST API contracts, separated frontend tasks, unreviewed implementation
