# Spec Workflow

## Project Approach

This project will use Spec Kit with a phased, spec-driven workflow. Each phase is treated as one Spec Kit feature/spec and must be completed, reviewed, and verified before starting the next phase.

The application will be built as a framework-free Vanilla PHP monolithic full-stack MVC application. Backend and frontend will live in the same PHP project using routes, controllers, PHP templates, PDO-backed models, SQL schema files, validation classes, policies, middleware-style guards, sessions, and server-rendered pages.

The project will not use REST APIs or a separated frontend.

## Standard Vanilla PHP Plan Prompt

Use this in `/speckit.plan` for every phase:

```text
The application uses framework-free Vanilla PHP as a monolithic full-stack MVC application. Backend and frontend are implemented in the same PHP project using routes, controllers, PHP templates, PDO-backed models, SQL schema files, validation, policies/middleware-style guards, native sessions, CSRF protection, and server-rendered pages. Do not build REST APIs or a separated frontend. Use MySQL as the database. AI features, proctoring, plagiarism detection, job-board sync, background checks, and code execution are simulated unless explicitly scoped otherwise.
```

## Recommended Stack

- PHP 8.2+
- MySQL 8+
- Server-rendered PHP templates
- Native PHP sessions and password hashing
- PDO models/repositories and SQL schema files
- Plain PHP validation
- Plain PHP policies and middleware-style guards for role-based access control
- Syntax checks and optional dev-only PHPUnit tests

## Spec Kit Command Flow

Run this sequence for each phase:

```text
/speckit.specify
/speckit.clarify
/speckit.plan
/speckit.tasks
/speckit.analyze
/speckit.implement
```

Only move to the next phase after the current phase is implemented, reviewed, and verified.

## Phase 0: Project Constitution

Create the project rules before implementation begins.

```text
/speckit.constitution Create principles for a 3-person academic software engineering project. The project must use framework-free Vanilla PHP as a monolithic MVC application with server-rendered PHP templates, MySQL through PDO, SQL schema files, controllers, middleware-style guards, policies, sessions, CSRF protection, and server-side validation. Do not use REST APIs, runtime framework dependencies, or a separated frontend. Before creating any spec, plan, or task list, read and respect the existing project materials in the Diagrams folder, including the SRS, database schema, ERD, use-case diagram, activity diagrams, class diagram, object diagram, and system architecture diagram. Treat these files as the baseline source of truth unless the team explicitly changes scope. Enforce clear specs before code, small phased delivery, role-based access control, testable acceptance criteria, privacy-aware candidate data handling, simulated AI/proctoring where needed, and mandatory peer review before implementation.
```

## Phase 1: Foundation, Auth, Roles, Layouts

Goal: create the Vanilla PHP MVC foundation and role-specific access.

```text
/speckit.specify Build the Vanilla PHP MVC foundation for the AI-Driven Smart Recruitment and Interview Management System. The system is a monolithic server-rendered PHP MVC app, not an API. Users can register or be created with roles: HR admin, technical interviewer, and candidate. Users authenticate with sessions, access role-specific dashboards, and are restricted by role-based middleware or policies.
```

## Phase 2: Jobs and Applications

Goal: allow HR admins to manage jobs and candidates to apply.

```text
/speckit.specify Build job requisition and candidate application management in server-rendered Vanilla PHP. HR admins can create, edit, submit, approve, open, and close job requisitions. Candidates can manage their profile, browse open jobs, apply once per job, and track application statuses. The system calculates a simulated match score from job requirements and candidate profile fields.
```

## Phase 3: Assessments

Goal: support technical assessments and simulated proctoring.

```text
/speckit.specify Build technical assessment management as server-rendered Vanilla PHP pages. HR admins can create assessments and questions for jobs. Candidates can start timed assessments, receive randomized questions, submit answers, and receive simulated scores. The system tracks focus-loss events as simulated proctoring data and expires assessments when time runs out.
```

## Phase 4: Interviews and Feedback

Goal: support scheduling, interviewer assignments, and feedback.

```text
/speckit.specify Build interview scheduling and feedback workflows in Vanilla PHP. HR admins can schedule interviews for applications, assign interviewers and observers, and avoid obvious scheduling conflicts. Interviewers can view assigned interviews, see candidate and assessment briefing details, and submit feedback scores and comments using server-rendered forms.
```

## Phase 5: Evaluation, Offers, Onboarding

Goal: complete the hiring lifecycle.

```text
/speckit.specify Build final evaluation, offer, and onboarding workflows. HR admins can aggregate assessment and interview feedback into a final recommendation, create offer packages, track offer status and expiry, and create onboarding records after accepted offers.
```

## Phase 6: Notifications, Reports, Compliance

Goal: add reporting, reminders, audit history, and compliance support.

```text
/speckit.specify Build notifications, recruitment reports, audit logs, and basic compliance features. The system sends in-app notifications for missing feedback, offer expiry, and application status changes. HR admins can view pipeline reports, time-to-hire summaries, audit history, and candidate data retention actions.
```

## Team Split For 3 People

- Person 1: Vanilla PHP backend logic, PDO models/repositories, SQL schema, controllers, middleware-style guards, and policies.
- Person 2: PHP template UI, layouts, forms, dashboards, styling, and user flows.
- Person 3: Spec Kit specs, tests, validation, QA, documentation, diagrams, and review.

Rotate ownership by phase so everyone contributes to specification, implementation, and review during the project.

## Collaboration Rules

- Start every phase from a clear Spec Kit spec.
- Do not implement features that are not in the current phase spec.
- Keep AI-related features simulated unless the phase explicitly says otherwise.
- Use Vanilla PHP server-rendered pages instead of APIs.
- Use MySQL as the source of truth.
- Require peer review before marking a phase complete.
- Keep each phase small enough to finish, test, and review before moving forward.
