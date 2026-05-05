# Tasks: Integrate Tailwind UI

## Phase 1: Setup

- [x] T001 Identify and download/copy the Tailwind CDN script block into the main layout configuration.

## Phase 2: Foundational 

- [x] T002 Update `views/layouts/app.php` to include Tailwind CSS CDN and structure the role-based navigation bar based on Stitch designs.
- [x] T003 Update `App\Controllers\DashboardController.php` to ensure any layout-specific shared data is properly passed.

## Phase 3: User Story 1 - Role-Based Layout and Navigation

**Goal**: As an authenticated SRIM user, I need a consistent application shell with role-appropriate navigation so that I can reach the pages relevant to my recruitment responsibilities without seeing unauthorized options.
**Independent Test**: Sign in as HR Admin, Candidate, and Interviewer. Verify navigation matches the role and unauthorized links are hidden. Check flash message styling.

- [x] T004 [US1] Implement dynamic role-based navigation links in `views/layouts/app.php` according to FR-001 and RP-001/002/003.
- [x] T005 [US1] Style flash messages and validation error blocks in `views/layouts/app.php` using Tailwind utility classes (FR-007).
- [x] T006 [US1] Update `views/welcome.php` to reflect the new Tailwind styling for the unauthenticated landing page.

## Phase 4: User Story 2 - Candidate Portal Experience

**Goal**: As a candidate, I need polished and responsive pages for registration, login, profile management, job browsing, application progress, assessment results, and offer visibility.
**Independent Test**: Register, log in, browse jobs, view application progress, and check mobile responsiveness.

- [x] T007 [P] [US2] Update `views/auth/login.php` using Tailwind styles derived from Stitch screen `3_User_Management.html` (adapted for login).
- [x] T008 [P] [US2] Update `views/auth/register.php` using Tailwind styles from Stitch screen `2_Candidate_Registration.html`.
- [x] T009 [P] [US2] Update `views/candidate/dashboard.php` using Tailwind styles from Stitch screen `5_Browse_Open_Jobs.html`.
- [x] T010 [P] [US2] Update `views/candidate/profile.php` using Tailwind styles from Stitch screen `4_My_Profile.html`.
- [x] T011 [P] [US2] Update `views/candidate/jobs.php` (Browse Open Jobs) using Tailwind styles from Stitch screen `5_Browse_Open_Jobs.html`.
- [x] T012 [P] [US2] Update `views/candidate/applications.php` using Tailwind styles from Stitch screen `6_My_Applications.html`.
- [x] T013 [P] [US2] Update `views/candidate/application_detail.php` using Tailwind styles from Stitch screen `15_Application_Detail.html` and `1_Application_Progress.html`.
- [x] T014 [US2] Update `App\Controllers\AuthController.php` to ensure error variables are correctly passed to Tailwind views.
- [x] T015 [US2] Update `App\Controllers\CandidateController.php` to ensure view data bindings match updated Blade structures.

## Phase 5: User Story 3 - HR Admin Portal Experience

**Goal**: As an HR Admin, I need dashboards and management pages that present recruitment data clearly.
**Independent Test**: Sign in as HR Admin, review dashboard metrics, requisition details, and user management tables.

- [x] T016 [P] [US3] Update `views/hr/dashboard.php` using Tailwind styles from Stitch screen `11_HR_Dashboard_SRIM.html`.
- [x] T017 [P] [US3] Update `views/hr/requisitions.php` using Tailwind styles from Stitch screen `12_Requisition_Detail_White_Nav.html`.
- [x] T018 [P] [US3] Update `views/hr/users.php` using Tailwind styles from Stitch screen `3_User_Management.html`.
- [x] T019 [US3] Update `App\Controllers\HrController.php` to ensure HR views receive correctly formatted data.

## Phase 6: User Story 4 - Interviewer Assessment Experience

**Goal**: As a Technical Interviewer, I need focused assessment and result pages.
**Independent Test**: Sign in as Interviewer, view assigned assessment and result pages.

- [x] T020 [P] [US4] Update `views/interviewer/assessment.php` using Tailwind styles from Stitch screen `14_Technical_Assessment.html`.
- [x] T021 [US4] Update `App\Controllers\InterviewerInterviewController.php` to ensure correct data binding for assessment views.

## Phase 7: User Story 5 - Form Feedback and Verification

**Goal**: As any SRIM user, I need forms to preserve security and show clear validation outcomes.
**Independent Test**: Submit forms with valid and invalid data, verify styling of inline validation errors.

- [x] T022 [US5] Refactor all form `<input>`, `<select>`, and `<textarea>` elements across all updated views to use consistent Tailwind form plugin classes.
- [x] T023 [US5] Ensure old input values are repopulated correctly in the new Tailwind-styled forms.

## Phase 8: Polish & Cross-Cutting Concerns

- [x] T024 Perform a final responsive design check across all updated views (Desktop and Mobile widths).
- [x] T025 Verify that no unauthorized data is exposed in the new views (RP-001 to RP-006).

## Implementation Strategy

- **MVP Scope**: Phase 1, Phase 2, and Phase 3 (Global layout and Role-based navigation) is the MVP to ensure the core shell works before migrating individual pages.
- **Parallel Execution**: Within Phases 4, 5, and 6, the view updates (`T007` - `T013`, `T016` - `T018`, `T020`) can be executed in parallel as they modify separate `.php` files.
- **Dependencies**:
  - Phase 2 (Layout) must be completed before starting any User Story phase.
  - Phase 7 (Form Feedback) should be reviewed after User Stories 2, 3, and 4 are complete.