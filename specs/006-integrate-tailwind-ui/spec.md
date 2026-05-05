# Feature Specification: Integrate Tailwind UI

**Feature Branch**: `main`  
**Created**: 2026-05-05  
**Status**: Draft  
**Input**: User description: `@plan.md` UI/UX Implementation Plan for integrating the approved Stitch-generated SRIM screens into the existing application UI.

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.docx` extracted text covering sections 1.2-1.4 and 3.1-3.5; `Diagrams/document.md`; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf` through `Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`; current plan `specs/005-final-offer-onboarding/plan.md`; constitution `.specify/memory/constitution.md`.
- **SRS / Use Case IDs**: Cross-cutting presentation update for the three SRS roles and their browser flows, including HR Admin requisition and pipeline work, Candidate application/profile/assessment/status work, Technical Interviewer assessment and feedback work, Auth and RBAC, offer and onboarding visibility, and system audit/privacy expectations. Relevant baseline flows include automated screening triage, job requisition management, candidate application tracking, assessment execution/results, feedback and recommendation review, offer package review, offer validity, and role-based access control.
- **Baseline Entities**: Users, departments, candidates, job requisitions, applications, assessments, questions, candidate assessments, submissions, interviews, interviewer assignments, interview feedback, final evaluations, offers, onboarding, notifications, and candidate merge logs where shown by existing pages.
- **Baseline Workflow**: Candidate application-to-offer lifecycle from Activity 1, technical assessment from Activity 2, interview pack context from Activity 3, feedback and recommendation review from Activity 4, offer package review from Activity 5, login and role dashboard routing from Activity 6, and offer response from Activity 7.
- **Scope Decision**: Matches baseline. This feature changes user-facing presentation, navigation consistency, responsive behavior, form feedback, and role-specific dashboard usability only. It does not add new recruitment workflows, new actors, new data retention behavior, new external integrations, or new machine-facing service contracts.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered pages, aligned with the constitution and the Tailwind CSS Amendment.
- **Routing**: Browser pages, form submissions, redirects, and session-backed role navigation only; no REST API contract or separated frontend is introduced.
- **Data Access**: Existing MySQL-backed SRIM data remains the source for all displayed dashboards, forms, tables, and status indicators.
- **Security**: Sessions, CSRF protection, server-side validation, guards, policies, and role-based data visibility remain mandatory for every updated page.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Role-Based Layout and Navigation (Priority: P1)

As an authenticated SRIM user, I need a consistent application shell with role-appropriate navigation so that I can reach the pages relevant to my recruitment responsibilities without seeing unauthorized options.

**Why this priority**: The shared layout is the foundation for every updated screen and directly supports the SRS requirement for HR Admin, Technical Interviewer, and Candidate role separation.

**Independent Test**: Can be tested by signing in as each role and confirming that the landing page, navigation links, flash messages, and main content area match the role while hiding unauthorized destinations.

**Acceptance Scenarios**:

1. **Given** an HR Admin is signed in, **When** the dashboard loads, **Then** the user sees HR dashboard, requisition, user management, and offer navigation without candidate-only assessment actions.
2. **Given** a Candidate is signed in, **When** the portal loads, **Then** the user sees candidate profile, open jobs, application progress, and offer/status navigation without HR-only management links.
3. **Given** a Technical Interviewer is signed in, **When** the portal loads, **Then** the user sees assigned assessment/interview work without HR compensation or unrelated candidate records.
4. **Given** any signed-in user triggers a success or validation message, **When** the next page renders, **Then** the message is visible, styled consistently, and understandable.

---

### User Story 2 - Candidate Portal Experience (Priority: P2)

As a candidate, I need polished and responsive pages for registration, login, profile management, job browsing, application progress, assessment results, and offer visibility so that I can complete hiring tasks with confidence on desktop or mobile.

**Why this priority**: Candidate experience is central to the recruitment lifecycle and affects application completion, assessment participation, and offer response quality.

**Independent Test**: Can be tested by creating or signing in as a candidate, browsing open jobs, reviewing an application, editing profile details, viewing assessment or offer status, and resizing the browser to mobile width.

**Acceptance Scenarios**:

1. **Given** a candidate opens the sign-in or registration page, **When** the page is displayed, **Then** the primary action is clear, fields are readable, and validation feedback appears next to the relevant input.
2. **Given** a candidate views open jobs, **When** job listings are available, **Then** each listing presents the job title, status, key details, and next action in a scannable format.
3. **Given** a candidate views an application detail page, **When** the application has progressed through stages, **Then** the page shows current status, completed steps, pending steps, and any available result or offer information without exposing internal HR notes.
4. **Given** a candidate uses a phone-sized screen, **When** they navigate portal pages, **Then** content remains readable and usable without horizontal scrolling for normal page content.

---

### User Story 3 - HR Admin Portal Experience (Priority: P3)

As an HR Admin, I need dashboards and management pages that present recruitment data clearly so that I can review requisitions, candidates, users, and offers efficiently while preserving data privacy.

**Why this priority**: HR Admins manage the pipeline and need clear, high-density views to reduce time spent interpreting raw tables or inconsistent pages.

**Independent Test**: Can be tested by signing in as HR Admin and reviewing dashboard metrics, requisition detail, user management, and offer detail pages with representative records.

**Acceptance Scenarios**:

1. **Given** an HR Admin views the dashboard, **When** recruitment data exists, **Then** the dashboard shows key counts, pipeline highlights, and recent activity in a clear visual hierarchy.
2. **Given** an HR Admin views a requisition detail, **When** applications and candidate evidence exist, **Then** the page presents candidate status, scores, and next actions without mixing unrelated requisitions.
3. **Given** an HR Admin views user management, **When** users exist, **Then** the table or cards support quick scanning of role, status, and account identity.
4. **Given** an HR Admin views an offer page, **When** offer details exist, **Then** compensation, expiry, candidate response, and onboarding readiness are visible only to authorized HR users.

---

### User Story 4 - Interviewer Assessment Experience (Priority: P4)

As a Technical Interviewer, I need focused assessment and result pages so that I can review assigned candidate work, submit or view feedback, and avoid unrelated distractions during evaluation.

**Why this priority**: Interviewer work affects candidate evaluation quality and must stay aligned to assigned candidates and structured feedback flows.

**Independent Test**: Can be tested by signing in as an interviewer with assigned candidate work and reviewing the technical assessment and assessment result pages.

**Acceptance Scenarios**:

1. **Given** an interviewer has assigned assessment work, **When** they open the assessment page, **Then** candidate context, task details, scoring context, and permitted actions are clearly grouped.
2. **Given** assessment results are available, **When** the interviewer views the result page, **Then** scores, feedback status, and candidate identifiers are readable without exposing unrelated candidates.

---

### User Story 5 - Form Feedback and Verification (Priority: P5)

As any SRIM user, I need forms to preserve security and show clear validation outcomes so that I understand what succeeded, what failed, and how to correct invalid input.

**Why this priority**: UI polish must not weaken protected workflows, and clear feedback reduces repeated failed submissions.

**Independent Test**: Can be tested by submitting each updated form with missing, invalid, and valid values and confirming the displayed feedback and resulting page state.

**Acceptance Scenarios**:

1. **Given** a form is submitted with invalid values, **When** the page reloads, **Then** field-level or page-level feedback explains the issue in plain language.
2. **Given** a form is submitted successfully, **When** the user is redirected or shown the next page, **Then** a success message confirms the outcome.
3. **Given** an unauthorized user attempts to submit a protected form, **When** the request is handled, **Then** the action is blocked and no unauthorized data is shown.

### Edge Cases

- If a dashboard has no records, the page must show an empty state with a useful next step rather than a broken table or blank content area.
- If a user has a valid role but no assigned records, the page must explain that no work is currently available without exposing another role's data.
- If validation fails on an updated form, previously entered safe values must remain visible where appropriate and sensitive values must not be re-displayed.
- If a screen contains long names, long job titles, many table rows, or missing optional data, the layout must remain readable and must not hide required actions.
- If a user attempts an action outside their role, navigation must not offer that action and direct access must still be blocked.
- If candidate personal data, scores, feedback, compensation, or onboarding details appear on a page, unauthorized roles must not see them.
- If the generated visual prototype does not map exactly to an existing SRIM workflow, the existing SRIM workflow and privacy boundary take precedence.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a consistent application layout across authenticated pages with a shared header, role-aware navigation, main content region, and visible message area.
- **FR-002**: System MUST present authentication pages with clear primary actions, readable form fields, and clear error feedback for failed sign-in or registration attempts.
- **FR-003**: System MUST present candidate dashboard, job browsing, application detail, application progress, profile, assessment result, and offer/status views using a consistent visual hierarchy.
- **FR-004**: System MUST present HR Admin dashboard, requisition detail, user management, and offer detail views using scannable summaries, tables or cards, and clear next actions.
- **FR-005**: System MUST present Technical Interviewer assessment and result views focused on assigned candidate work, evidence, and permitted feedback actions.
- **FR-006**: System MUST keep all updated pages responsive for desktop and mobile-sized browsers without losing core navigation, status visibility, or form usability.
- **FR-007**: System MUST display validation errors, empty states, success messages, and blocked-action messages in consistent plain language across updated pages.
- **FR-008**: System MUST preserve existing recruitment workflow outcomes when UI screens are updated, including application status, assessment status, interview feedback, offer status, and onboarding status.
- **FR-009**: System MUST preserve security controls for every updated form and protected page, including role checks, session requirements, request authenticity checks, and server-side validation.
- **FR-010**: System MUST use existing SRIM data as the source for rendered profile, job, application, assessment, feedback, offer, onboarding, notification, and dashboard content.
- **FR-011**: System MUST clearly label simulated AI, proctoring, background-check, email, or external integration behavior wherever those concepts are presented to users.
- **FR-012**: System MUST not add new application workflow states, actors, data entities, external integrations, or machine-facing contracts as part of this presentation update.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST be limited to approved HR management pages and must not reveal authentication secrets or unneeded candidate private fields.
- **RP-002**: Technical Interviewer access MUST be limited to assigned candidate, assessment, interview, and feedback context.
- **RP-003**: Candidate access MUST be limited to the candidate's own profile, applications, assessments, offers, onboarding status, and notifications.
- **RP-004**: Junior Staff or observer access, where represented by baseline diagrams or future pages, MUST remain read-only or training-only and must not permit score, offer, or status changes.
- **RP-005**: Candidate PII, resumes, assessment results, interviewer feedback, final recommendations, compensation, and onboarding details MUST be hidden from unauthorized roles.
- **RP-006**: Simulated AI and proctoring results MUST remain reviewable by authorized users and understandable to affected candidates where the baseline workflow exposes them.

### Key Entities *(include if feature involves data)*

- **User**: Account identity and role used to determine navigation, page access, and displayed actions.
- **Candidate Profile**: Candidate-owned personal and professional information shown in profile, application, assessment, offer, and onboarding views.
- **Job Requisition**: Open or managed job record shown in candidate job browsing and HR requisition detail views.
- **Application**: Candidate-to-job record whose status is shown through progress, dashboards, requisition details, and offer context.
- **Assessment and Submission Evidence**: Assessment definitions, candidate attempts, scores, and submitted work shown in candidate and interviewer experiences.
- **Interview and Feedback Evidence**: Interview assignments, interviewer feedback, and score context shown to HR and assigned interviewers.
- **Offer and Onboarding**: Offer package and onboarding readiness information shown to authorized HR users and the owning candidate where applicable.
- **Notification and Flash Message**: User-facing status or system feedback shown after navigation, validation, or workflow actions.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of updated authenticated pages show role-appropriate navigation and hide unauthorized role actions during manual role-based review.
- **SC-002**: Candidates can complete the primary flows for sign-in, profile update, job browsing, application progress review, and offer/status review in under 5 minutes total during a guided demo.
- **SC-003**: HR Admins can locate key dashboard metrics, open a requisition detail, review user management, and inspect an offer detail in under 4 minutes during a guided demo.
- **SC-004**: Technical Interviewers can open assigned assessment work and review assessment results in under 2 minutes during a guided demo.
- **SC-005**: 100% of updated forms display understandable validation feedback for missing or invalid required inputs in manual testing.
- **SC-006**: 100% of updated pages remain readable and usable at common desktop width and phone-sized browser width without horizontal scrolling for normal content.
- **SC-007**: At least 90% of reviewed prototype-to-page comparisons are judged visually consistent by the project team, with any differences documented as workflow or data-driven deviations.
- **SC-008**: No reviewed page exposes candidate personal data, scores, feedback, compensation, offer, or onboarding details to an unauthorized role.

## Assumptions

- The approved Stitch-generated screen set in `stitch_screens/` is the visual reference for this feature.
- Existing SRIM workflows, controllers, routes, validation behavior, and data relationships remain the functional source of truth.
- This feature is a UI polish and usability phase, not a new workflow or schema expansion phase.
- The constitution's Tailwind CSS Amendment authorizes use of the Tailwind-based visual design while preserving the Vanilla PHP monolithic MVC architecture.
- Modern desktop and mobile browser support is sufficient for the academic demo scope.
- Visual verification will use project-team review against the downloaded reference screens plus manual role-based workflow checks.
