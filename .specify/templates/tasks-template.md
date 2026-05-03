---

description: "Task list template for feature implementation"
---

# Tasks: [FEATURE NAME]

**Input**: Design documents from `/specs/[###-feature-name]/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, route-map.md

**Tests**: Include Laravel feature, policy, validation, model, or documented manual demo tasks for every acceptance criterion. Automated tests are required unless the spec explicitly justifies a manual academic demo check.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.
**Peer Review**: Include a peer-review task before implementation starts for each phase or user story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel monolith**: `app/`, `routes/web.php`, `resources/views/`, `database/migrations/`, `tests/`
- **Controllers**: `app/Http/Controllers/`
- **Validation**: `app/Http/Requests/` or controller validation for small flows
- **Authorization**: `app/Policies/` and `app/Http/Middleware/`
- **Models**: `app/Models/`
- **Blade pages**: `resources/views/[feature]/`
- **Tests**: `tests/Feature/` and `tests/Unit/`
- Do not create `api/`, `backend/`, `frontend/`, or REST contract paths for SRIM features.

<!-- 
  ============================================================================
  IMPORTANT: The tasks below are SAMPLE TASKS for illustration purposes only.
  
  The /speckit.tasks command MUST replace these with actual tasks based on:
  - User stories from spec.md (with their priorities P1, P2, P3...)
  - Feature requirements from plan.md
  - Entities from data-model.md
  - Web routes, controllers, and Blade views from route-map.md
  
  Tasks MUST be organized by user story so each story can be:
  - Implemented independently
  - Tested independently
  - Delivered as an MVP increment
  
  DO NOT keep these sample tasks in the generated tasks.md file.
  ============================================================================
-->

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [ ] T001 Create project structure per implementation plan
- [ ] T002 Initialize Laravel project dependencies and environment configuration
- [ ] T003 [P] Configure PHP/Laravel linting, formatting, and test tooling

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

Examples of foundational tasks (adjust based on your project):

- [ ] T004 Setup database schema and migrations framework
- [ ] T005 [P] Implement session authentication, roles, middleware, and policies
- [ ] T006 [P] Setup `routes/web.php` route groups and shared Blade layout structure
- [ ] T007 Create Eloquent models and relationships that all stories depend on
- [ ] T008 Configure validation, error handling, and audit-relevant logging infrastructure
- [ ] T009 Setup environment configuration management

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - [Title] (Priority: P1) 🎯 MVP

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 1 (OPTIONAL - only if tests requested) ⚠️

> **NOTE: Write automated tests FIRST when practical, ensure they FAIL before implementation. If a manual academic demo check is justified, write the demo steps before implementation.**

- [ ] T010 [P] [US1] Laravel feature test for [Blade workflow] in tests/Feature/[Name]Test.php
- [ ] T011 [P] [US1] Policy/validation test for [role/input rule] in tests/Feature/[Name]Test.php
- [ ] T011A [US1] Peer review spec, plan, route map, RBAC, privacy, and acceptance criteria before implementation

### Implementation for User Story 1

- [ ] T012 [P] [US1] Create/update migration(s) in database/migrations/
- [ ] T013 [P] [US1] Create/update Eloquent model(s) in app/Models/
- [ ] T014 [US1] Implement controller action(s) in app/Http/Controllers/ (depends on T012, T013)
- [ ] T015 [US1] Implement Blade view(s) in resources/views/[feature]/
- [ ] T016 [US1] Add server-side validation, CSRF-safe forms, and error handling
- [ ] T017 [US1] Add policy/middleware authorization and audit-relevant logging for user story 1 operations

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - [Title] (Priority: P2)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 2 (OPTIONAL - only if tests requested) ⚠️

- [ ] T018 [P] [US2] Laravel feature test for [Blade workflow] in tests/Feature/[Name]Test.php
- [ ] T019 [P] [US2] Policy/validation test for [role/input rule] in tests/Feature/[Name]Test.php
- [ ] T019A [US2] Peer review spec, plan, route map, RBAC, privacy, and acceptance criteria before implementation

### Implementation for User Story 2

- [ ] T020 [P] [US2] Create/update migration and Eloquent model in database/migrations/ and app/Models/
- [ ] T021 [US2] Implement optional domain service in app/Services/ only if reusable logic is needed
- [ ] T022 [US2] Implement web route, controller action, and Blade view
- [ ] T023 [US2] Integrate with User Story 1 components (if needed)

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - [Title] (Priority: P3)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 3 (OPTIONAL - only if tests requested) ⚠️

- [ ] T024 [P] [US3] Laravel feature test for [Blade workflow] in tests/Feature/[Name]Test.php
- [ ] T025 [P] [US3] Policy/validation test for [role/input rule] in tests/Feature/[Name]Test.php
- [ ] T025A [US3] Peer review spec, plan, route map, RBAC, privacy, and acceptance criteria before implementation

### Implementation for User Story 3

- [ ] T026 [P] [US3] Create/update migration and Eloquent model in database/migrations/ and app/Models/
- [ ] T027 [US3] Implement optional domain service in app/Services/ only if reusable logic is needed
- [ ] T028 [US3] Implement web route, controller action, and Blade view

**Checkpoint**: All user stories should now be independently functional

---

[Add more user story phases as needed, following the same pattern]

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] TXXX [P] Documentation updates in docs/
- [ ] TXXX Code cleanup and refactoring
- [ ] TXXX Performance optimization across all stories
- [ ] TXXX [P] Additional Laravel tests in tests/Feature/ or tests/Unit/
- [ ] TXXX Security hardening for RBAC, sessions, CSRF, validation, and candidate privacy
- [ ] TXXX Run quickstart.md validation

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3)
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - May integrate with US1 but should be independently testable
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - May integrate with US1/US2 but should be independently testable

### Within Each User Story

- Tests or manual demo checks MUST be written before implementation
- Peer review gate before implementation
- Migrations and models before controllers
- Controllers before Blade views, except for UI prototypes reviewed as non-merged spikes
- Core implementation before integration
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel
- All Foundational tasks marked [P] can run in parallel (within Phase 2)
- Once Foundational phase completes, all user stories can start in parallel (if team capacity allows)
- All tests for a user story marked [P] can run in parallel
- Models within a story marked [P] can run in parallel
- Different user stories can be worked on in parallel by different team members

---

## Parallel Example: User Story 1

```bash
# Launch all tests for User Story 1 together:
Task: "Laravel feature test for [Blade workflow] in tests/Feature/[Name]Test.php"
Task: "Policy/validation test for [role/input rule] in tests/Feature/[Name]Test.php"

# Launch independent Laravel implementation tasks together:
Task: "Create [Entity1] migration/model in database/migrations/ and app/Models/"
Task: "Create [Entity2] migration/model in database/migrations/ and app/Models/"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test User Story 1 independently
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo
4. Add User Story 3 → Test independently → Deploy/Demo
5. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1
   - Developer B: User Story 2
   - Developer C: User Story 3
3. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify tests fail or manual demo steps are documented before implementing
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
- Avoid: REST API contracts, separated frontend tasks, unreviewed implementation, and unverifiable acceptance criteria
