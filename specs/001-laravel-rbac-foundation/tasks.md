# Tasks: Laravel RBAC Foundation

**Input**: Design documents from `specs/001-laravel-rbac-foundation/`
**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `route-map.md`, `contracts/web-workflows.md`, `quickstart.md`

**Tests**: Laravel feature, validation, policy/middleware, model, and seeder tests are included because the plan and constitution require testable acceptance evidence.

**Organization**: Tasks are grouped by user story so each story can be implemented and tested independently after shared setup and foundation are complete.

**Important Guardrails For Implementers**: Build one Laravel monolithic MVC application at the repository root. Use Blade pages, `routes/web.php`, form submissions, redirects, sessions, CSRF, server-side validation, middleware/policies, Eloquent, migrations, and MySQL. Do not create REST APIs, `routes/api.php` feature endpoints, a separated frontend, SPA pages, JWT/token authentication, SSO, MFA, password reset, email verification, full profile editing, account deletion, recruitment pipeline features, assessments, interviews, offers, onboarding, AI, proctoring, calendar, email, or job-board integrations.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel after its prerequisite phase is complete because it touches different files and does not depend on incomplete tasks.
- **[Story]**: User story label for story phases only.
- Every task includes an exact target path.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create the Laravel application foundation at the repository root without disturbing existing Speckit and diagram artifacts.

- [X] T001 Create Laravel 12 application scaffold at repository root with `composer.json`, `artisan`, `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, and `tests/`, preserving existing `Diagrams/`, `.specify/`, `specs/`, `.opencode/`, and `AGENTS.md`
- [X] T002 Configure PHP 8.2+ and Laravel app metadata in `composer.json` and `config/app.php`
- [X] T003 Configure database, session, app URL, and seeded HR admin placeholder variables in `.env.example`
- [X] T004 Configure PHPUnit environment for Laravel feature tests in `phpunit.xml`
- [X] T005 [P] Create SRIM Blade layout directories in `resources/views/layouts/`, `resources/views/auth/`, `resources/views/candidate/`, `resources/views/hr/`, and `resources/views/interviewer/`
- [X] T006 [P] Record phase setup peer-review checklist in `specs/001-laravel-rbac-foundation/checklists/setup-review.md`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core schema, models, role/status primitives, seed data, middleware, and shared layout that all user stories depend on.

**Critical**: No user story work can begin until this phase is complete.

- [X] T007 Create departments migration with `department_id`, `name`, `description`, `parent_department_id`, timestamps, unique name, and parent foreign key in `database/migrations/0001_01_01_000001_create_departments_table.php`
- [X] T008 Create users migration with `user_id`, `department_id`, `name`, `email`, `password_hash`, `role`, `status`, timestamps, unique email, role/status indexes, and department foreign key in `database/migrations/0001_01_01_000002_create_users_table.php`
- [X] T009 Create candidates migration with `candidate_id`, `phone`, `current_title`, `years_experience`, `location`, `resume_url`, timestamps, and user cascade foreign key in `database/migrations/0001_01_01_000003_create_candidates_table.php`
- [X] T010 Create account audit records migration with `audit_id`, `actor_user_id`, `target_user_id`, `action`, `old_values`, `new_values`, and `created_at` in `database/migrations/0001_01_01_000004_create_account_audit_records_table.php`
- [X] T011 [P] Create `UserRole` enum with `HR_ADMIN`, `INTERVIEWER`, and `CANDIDATE` in `app/Enums/UserRole.php`
- [X] T012 [P] Create `AccountStatus` enum with `ACTIVE` and `INACTIVE` in `app/Enums/AccountStatus.php`
- [X] T013 [P] Create `AuditAction` enum with `USER_CREATED`, `ROLE_CHANGED`, and `STATUS_CHANGED` in `app/Enums/AuditAction.php`
- [X] T014 Create `User` model using `user_id` primary key, `password_hash` authentication password mapping, role/status casts, department/candidate/audit relationships, and hidden password field in `app/Models/User.php`
- [X] T015 [P] Create `Department` model with `department_id` primary key, self-parent relationship, and users relationship in `app/Models/Department.php`
- [X] T016 [P] Create `Candidate` model with `candidate_id` primary key, user relationship, and fillable phone/profile fields in `app/Models/Candidate.php`
- [X] T017 [P] Create `AccountAuditRecord` model with actor user, target user, JSON casts, and append-only fillable fields in `app/Models/AccountAuditRecord.php`
- [X] T018 [P] Create department seed data for Human Resources and Engineering in `database/seeders/DepartmentSeeder.php`
- [X] T019 Create seeded initial active HR admin account using environment-backed name, email, and password defaults in `database/seeders/FirstHrAdminSeeder.php`
- [X] T020 Register `DepartmentSeeder` and `FirstHrAdminSeeder` in `database/seeders/DatabaseSeeder.php`
- [X] T021 [P] Create active-account middleware that denies inactive authenticated users and ends stale access in `app/Http/Middleware/EnsureUserIsActive.php`
- [X] T022 [P] Create role middleware that accepts allowed role names and denies mismatched users in `app/Http/Middleware/EnsureUserHasRole.php`
- [X] T023 Register `active` and `role` middleware aliases in `bootstrap/app.php`
- [X] T024 [P] Create `UserPolicy` for HR-only account administration, candidate self-view, and last-active-HR-admin protection helpers in `app/Policies/UserPolicy.php`
- [X] T025 [P] Create dashboard path resolver for current user role in `app/Support/RoleDashboard.php`
- [X] T026 Create shared authenticated Blade layout with navigation, logout form slot, flash messages, and validation error rendering in `resources/views/layouts/app.blade.php`
- [X] T027 Create guest Blade layout for registration/login pages with CSRF form styling and validation error area in `resources/views/layouts/guest.blade.php`
- [X] T028 Create base web route file with public landing route and grouped auth placeholders in `routes/web.php`
- [X] T029 [P] Create model relationship tests for User, Department, Candidate, and AccountAuditRecord in `tests/Feature/Foundation/ModelRelationshipTest.php`
- [X] T030 [P] Create seeder smoke test for departments and first active HR admin in `tests/Feature/Foundation/SeederTest.php`
- [X] T031 [P] Create middleware/policy unit coverage for role and active-account checks in `tests/Feature/Foundation/AccessMiddlewareTest.php`
- [X] T032 Record foundation peer-review approval against `specs/001-laravel-rbac-foundation/plan.md` and `specs/001-laravel-rbac-foundation/data-model.md` in `specs/001-laravel-rbac-foundation/checklists/foundation-review.md`

**Checkpoint**: Foundation ready. Migrations, models, seeders, middleware, policies, shared layouts, and base routes are ready for story implementation.

---

## Phase 3: User Story 1 - Candidate Self-Registration and Login (Priority: P1) - MVP

**Goal**: A visitor can register as a candidate using name, email, password, and phone, then authenticate and reach only the candidate dashboard/profile pages.

**Independent Test**: Register a candidate, log in with the new credentials, confirm candidate dashboard loads, confirm HR/interviewer pages are denied, and confirm duplicate/invalid registration input is rejected.

### Tests for User Story 1

- [X] T033 [P] [US1] Create candidate registration feature tests for valid registration, duplicate email, required phone, password confirmation, and submitted role rejection in `tests/Feature/Auth/CandidateRegistrationTest.php`
- [X] T034 [P] [US1] Create candidate login and dashboard redirect tests for active candidate accounts in `tests/Feature/Auth/CandidateLoginTest.php`
- [X] T035 [P] [US1] Create candidate dashboard privacy tests that deny `/hr/dashboard`, `/interviewer/dashboard`, and non-owned candidate data in `tests/Feature/Candidate/CandidateAccessTest.php`
- [X] T036 [US1] Record US1 peer-review approval against registration, login, RBAC, privacy, and acceptance criteria in `specs/001-laravel-rbac-foundation/checklists/us1-review.md`

### Implementation for User Story 1

- [X] T037 [P] [US1] Create candidate registration validation rules for name, unique email, password confirmation, and phone in `app/Http/Requests/Auth/RegisterCandidateRequest.php`
- [X] T038 [P] [US1] Create login validation rules for email and password in `app/Http/Requests/Auth/LoginRequest.php`
- [X] T039 [US1] Implement candidate account and candidate profile creation transaction in `app/Http/Controllers/Auth/CandidateRegistrationController.php`
- [X] T040 [US1] Implement login action, safe credential failure, active-status enforcement, and role dashboard redirect in `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- [X] T041 [P] [US1] Implement candidate dashboard controller returning the authenticated candidate view in `app/Http/Controllers/Candidate/DashboardController.php`
- [X] T042 [P] [US1] Implement candidate profile controller scoped to the authenticated candidate only in `app/Http/Controllers/Candidate/ProfileController.php`
- [X] T043 [P] [US1] Create candidate registration Blade form with name, email, phone, password, confirmation, CSRF, and validation errors in `resources/views/auth/register.blade.php`
- [X] T044 [P] [US1] Create login Blade form with email, password, CSRF, safe error rendering, and link to registration in `resources/views/auth/login.blade.php`
- [X] T045 [P] [US1] Create candidate dashboard empty-state Blade page in `resources/views/candidate/dashboard.blade.php`
- [X] T046 [P] [US1] Create candidate profile summary Blade page showing only authenticated candidate name, email, and phone in `resources/views/candidate/profile.blade.php`
- [X] T047 [US1] Wire public registration, login, candidate dashboard, and candidate profile routes in `routes/web.php`

**Checkpoint**: User Story 1 is independently functional and is the MVP demo slice.

---

## Phase 4: User Story 2 - HR Admin Creates and Manages User Accounts (Priority: P2)

**Goal**: An HR admin can sign in, see the HR dashboard, create HR/interviewer/candidate accounts, change role/status, preserve last-active-HR-admin safety, and produce audit records.

**Independent Test**: Sign in as seeded HR admin, create a technical interviewer and candidate account, change one user's role/status, verify audit records, verify non-HR users cannot perform the same actions, and verify the last active HR admin cannot be downgraded or deactivated.

### Tests for User Story 2

- [X] T048 [P] [US2] Create HR account creation tests for HR admin, interviewer, candidate, candidate phone handling, duplicate email, and invalid role/status in `tests/Feature/Hr/UserCreationTest.php`
- [X] T049 [P] [US2] Create HR role/status change tests including last-active-HR-admin denial and current-access update in `tests/Feature/Hr/UserAccessUpdateTest.php`
- [X] T050 [P] [US2] Create non-HR authorization denial tests for HR user administration routes in `tests/Feature/Hr/HrAuthorizationTest.php`
- [X] T051 [P] [US2] Create account audit tests for user creation, role change, status change, actor, target, old values, and new values in `tests/Feature/Hr/AccountAuditRecordTest.php`
- [X] T052 [US2] Record US2 peer-review approval against HR user management, audit, last-admin safeguard, validation, and RBAC in `specs/001-laravel-rbac-foundation/checklists/us2-review.md`

### Implementation for User Story 2

- [X] T053 [P] [US2] Create HR user creation validation rules for name, email, password, role, status, department, and candidate phone in `app/Http/Requests/Hr/StoreUserRequest.php`
- [X] T054 [P] [US2] Create HR role/status update validation rules in `app/Http/Requests/Hr/UpdateUserAccessRequest.php`
- [X] T055 [P] [US2] Implement audit record writer for account creation, role changes, and status changes in `app/Support/AccountAuditLogger.php`
- [X] T056 [P] [US2] Implement HR dashboard controller returning the HR empty-state dashboard in `app/Http/Controllers/Hr/DashboardController.php`
- [X] T057 [US2] Implement HR user index, create form, and store actions with candidate profile creation when role is candidate in `app/Http/Controllers/Hr/UserController.php`
- [X] T058 [US2] Implement HR role/status edit and update actions with last-active-HR-admin guard in `app/Http/Controllers/Hr/UserAccessController.php`
- [X] T059 [P] [US2] Create HR dashboard Blade page in `resources/views/hr/dashboard.blade.php`
- [X] T060 [P] [US2] Create HR users index Blade page listing name, email, role, status, department, and access edit link in `resources/views/hr/users/index.blade.php`
- [X] T061 [P] [US2] Create HR user creation Blade form in `resources/views/hr/users/create.blade.php`
- [X] T062 [P] [US2] Create HR role/status edit Blade form in `resources/views/hr/users/access.blade.php`
- [X] T063 [US2] Wire HR dashboard and HR user administration routes with `auth`, `active`, and `role:HR_ADMIN` middleware in `routes/web.php`

**Checkpoint**: User Story 2 is independently functional after foundation and can be demonstrated with the seeded HR admin.

---

## Phase 5: User Story 3 - Technical Interviewer Role Dashboard (Priority: P3)

**Goal**: A technical interviewer can sign in and access only the interviewer dashboard while HR and candidate-only pages stay denied.

**Independent Test**: Create or seed an active interviewer, sign in, confirm interviewer dashboard loads, and confirm HR administration and candidate profile routes are denied.

### Tests for User Story 3

- [X] T064 [P] [US3] Create interviewer login and dashboard access tests in `tests/Feature/Interviewer/InterviewerDashboardTest.php`
- [X] T065 [P] [US3] Create cross-role denial tests for interviewer attempts to access HR and candidate routes in `tests/Feature/Interviewer/InterviewerAuthorizationTest.php`
- [X] T066 [US3] Record US3 peer-review approval against interviewer dashboard and role isolation in `specs/001-laravel-rbac-foundation/checklists/us3-review.md`

### Implementation for User Story 3

- [X] T067 [P] [US3] Implement interviewer dashboard controller in `app/Http/Controllers/Interviewer/DashboardController.php`
- [X] T068 [P] [US3] Create interviewer dashboard empty-state Blade page in `resources/views/interviewer/dashboard.blade.php`
- [X] T069 [US3] Wire interviewer dashboard route with `auth`, `active`, and `role:INTERVIEWER` middleware in `routes/web.php`

**Checkpoint**: User Story 3 is independently functional after foundation and can be validated without recruitment/interview features.

---

## Phase 6: User Story 4 - Session and Account State Protection (Priority: P4)

**Goal**: Users can securely sign out, inactive accounts cannot authenticate or keep protected access, and role/status changes apply on the next protected action.

**Independent Test**: Log in as each role, sign out and revisit protected pages, deactivate a user and verify access denial, change a role and verify the old dashboard is denied and the new dashboard is used.

### Tests for User Story 4

- [X] T070 [P] [US4] Create logout and protected-page-after-logout tests in `tests/Feature/Auth/LogoutTest.php`
- [X] T071 [P] [US4] Create inactive account login and active-session invalidation tests in `tests/Feature/Auth/InactiveAccountTest.php`
- [X] T072 [P] [US4] Create current role/status enforcement tests after HR changes a user's access in `tests/Feature/Auth/CurrentAccessEnforcementTest.php`
- [X] T073 [US4] Record US4 peer-review approval against logout, inactive-account handling, and current role/status enforcement in `specs/001-laravel-rbac-foundation/checklists/us4-review.md`

### Implementation for User Story 4

- [X] T074 [US4] Implement logout destroy action with session invalidation and CSRF-safe redirect in `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- [X] T075 [US4] Complete inactive-user session termination behavior in `app/Http/Middleware/EnsureUserIsActive.php`
- [X] T076 [US4] Complete current-role dashboard redirect behavior in `app/Http/Controllers/DashboardRedirectController.php`
- [X] T077 [US4] Wire logout and `/dashboard` redirect routes with authenticated middleware in `routes/web.php`
- [X] T078 [US4] Add logout control to authenticated navigation in `resources/views/layouts/app.blade.php`

**Checkpoint**: User Story 4 is independently functional after foundation and hardens all previous stories.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Validate the whole foundation, improve documentation, and ensure compliance with the constitution and quickstart.

- [X] T079 [P] Create project setup and seeded HR admin instructions in `README.md`
- [X] T080 [P] Document manual demo evidence checklist based on quickstart in `specs/001-laravel-rbac-foundation/checklists/demo-evidence.md`
- [X] T081 [P] Add security hardening notes for RBAC, sessions, CSRF, validation, candidate privacy, and audit records in `specs/001-laravel-rbac-foundation/checklists/security-review.md`
- [X] T082 Run `php artisan test` and record pass/fail evidence in `specs/001-laravel-rbac-foundation/checklists/demo-evidence.md`
- [X] T083 Verify no REST API or separated frontend scope was introduced by checking `routes/api.php`, `resources/js/`, and any `frontend/` path, then record result in `specs/001-laravel-rbac-foundation/checklists/security-review.md`
- [ ] T084 Execute the full quickstart demo flow and record reviewer notes in `specs/001-laravel-rbac-foundation/checklists/demo-evidence.md`
- [X] T085 Final peer review of implementation against `specs/001-laravel-rbac-foundation/spec.md`, `specs/001-laravel-rbac-foundation/plan.md`, and `specs/001-laravel-rbac-foundation/tasks.md` in `specs/001-laravel-rbac-foundation/checklists/final-review.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 Setup**: No dependencies.
- **Phase 2 Foundational**: Depends on Phase 1 and blocks all user stories.
- **Phase 3 US1**: Depends on Phase 2 and is the MVP.
- **Phase 4 US2**: Depends on Phase 2; can run after US1 for simpler manual testing, or in parallel after Phase 2 if the team coordinates `routes/web.php` edits.
- **Phase 5 US3**: Depends on Phase 2; can run in parallel with US1/US2 after Phase 2 if route edits are coordinated.
- **Phase 6 US4**: Depends on Phase 2 and integrates with auth/session routes; safest after US1 but can start after Phase 2 if route/controller conflicts are coordinated.
- **Phase 7 Polish**: Depends on all selected user stories being complete.

### User Story Dependencies

- **US1 Candidate Self-Registration and Login**: No dependency on other user stories after foundation; delivers MVP.
- **US2 HR Admin Creates and Manages User Accounts**: No dependency on US1 after foundation because seeded HR admin enables independent testing.
- **US3 Technical Interviewer Role Dashboard**: No dependency on US1/US2 after foundation if an interviewer fixture is created in tests.
- **US4 Session and Account State Protection**: Depends on shared authentication pieces from foundation and integrates naturally after US1.

### Within Each User Story

- Write or update tests before implementation tasks where practical.
- Complete peer-review checklist before implementation tasks in that story.
- Validation requests before controller actions.
- Controllers before Blade views if the view depends on controller-provided data.
- Route wiring after controllers and middleware exist.
- Run story tests before declaring the story checkpoint complete.

---

## Parallel Opportunities

- T005 and T006 can run after T001 because they touch different documentation/view directories.
- T011, T012, and T013 can run in parallel because each creates a different enum file.
- T015, T016, and T017 can run in parallel after migrations are defined because they are separate model files.
- T018, T021, T022, T024, and T025 can run in parallel because they touch separate seeder, middleware, policy, and support files.
- Test files within each user story are parallelizable before implementation.
- Blade views within each user story are parallelizable after route/controller data expectations are known.
- US1, US2, and US3 can be implemented by separate developers after Phase 2 if `routes/web.php` edits are merged carefully.

---

## Parallel Example: User Story 1

```bash
# Independent tests before implementation:
Task T033: tests/Feature/Auth/CandidateRegistrationTest.php
Task T034: tests/Feature/Auth/CandidateLoginTest.php
Task T035: tests/Feature/Candidate/CandidateAccessTest.php

# Independent implementation files after validation/controller contracts are agreed:
Task T037: app/Http/Requests/Auth/RegisterCandidateRequest.php
Task T038: app/Http/Requests/Auth/LoginRequest.php
Task T041: app/Http/Controllers/Candidate/DashboardController.php
Task T042: app/Http/Controllers/Candidate/ProfileController.php
Task T043: resources/views/auth/register.blade.php
Task T044: resources/views/auth/login.blade.php
Task T045: resources/views/candidate/dashboard.blade.php
Task T046: resources/views/candidate/profile.blade.php
```

## Parallel Example: User Story 2

```bash
# Independent tests before implementation:
Task T048: tests/Feature/Hr/UserCreationTest.php
Task T049: tests/Feature/Hr/UserAccessUpdateTest.php
Task T050: tests/Feature/Hr/HrAuthorizationTest.php
Task T051: tests/Feature/Hr/AccountAuditRecordTest.php

# Independent implementation files:
Task T053: app/Http/Requests/Hr/StoreUserRequest.php
Task T054: app/Http/Requests/Hr/UpdateUserAccessRequest.php
Task T055: app/Support/AccountAuditLogger.php
Task T056: app/Http/Controllers/Hr/DashboardController.php
Task T059: resources/views/hr/dashboard.blade.php
Task T060: resources/views/hr/users/index.blade.php
Task T061: resources/views/hr/users/create.blade.php
Task T062: resources/views/hr/users/access.blade.php
```

## Parallel Example: User Story 3

```bash
# Independent tests before implementation:
Task T064: tests/Feature/Interviewer/InterviewerDashboardTest.php
Task T065: tests/Feature/Interviewer/InterviewerAuthorizationTest.php

# Independent implementation files:
Task T067: app/Http/Controllers/Interviewer/DashboardController.php
Task T068: resources/views/interviewer/dashboard.blade.php
```

## Parallel Example: User Story 4

```bash
# Independent tests before implementation:
Task T070: tests/Feature/Auth/LogoutTest.php
Task T071: tests/Feature/Auth/InactiveAccountTest.php
Task T072: tests/Feature/Auth/CurrentAccessEnforcementTest.php
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 setup.
2. Complete Phase 2 foundation.
3. Complete Phase 3 User Story 1.
4. Stop and validate candidate registration, login, candidate dashboard, and cross-role denial.
5. Demo MVP before starting HR administration if schedule is tight.

### Incremental Delivery

1. Setup plus foundation establishes Laravel, schema, seed admin, roles, middleware, policies, and shared views.
2. US1 delivers public candidate registration and login MVP.
3. US2 adds HR account administration and audit records.
4. US3 adds interviewer dashboard role separation.
5. US4 hardens logout, inactive users, and current access enforcement.
6. Polish validates quickstart, tests, peer review, and constitution compliance.

### Cheaper Model Implementation Notes

- Follow task order unless a task is marked `[P]` and touches a separate file.
- Do not invent new roles; use only `HR_ADMIN`, `INTERVIEWER`, and `CANDIDATE`.
- Use `password_hash` in the database but make Laravel authentication read it as the password column through the `User` model.
- Keep all browser flows in `routes/web.php` and Blade views.
- If a task asks for a checklist file, create a short Markdown file that records reviewer, date, files reviewed, pass/fail items, and notes.
- If a Laravel scaffold already exists when implementation begins, adapt T001 by verifying the listed paths instead of recreating the app.
