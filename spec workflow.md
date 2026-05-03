# Spec Workflow

## Project Approach

This project will use Spec Kit with a phased, spec-driven workflow. Each phase is treated as one Spec Kit feature/spec and must be completed, reviewed, and verified before starting the next phase.

The application will be built as a Laravel PHP monolithic full-stack MVC application. Backend and frontend will live in the same Laravel project using routes, controllers, Blade templates, Eloquent models, migrations, form requests, policies, middleware, sessions, and server-rendered pages.

The project will not use REST APIs or a separated frontend.

## Standard Laravel Plan Prompt

Use this in `/speckit.plan` for every phase:

```text
The application uses Laravel PHP as a monolithic full-stack MVC application. Backend and frontend are implemented in the same Laravel project using routes, controllers, Blade templates, Eloquent models, migrations, form requests, policies/middleware, and server-rendered pages. Do not build REST APIs or a separated frontend. Use MySQL as the database. Use Laravel authentication, sessions, CSRF protection, validation, and role-based middleware/policies. AI features, proctoring, plagiarism detection, job-board sync, background checks, and code execution are simulated unless explicitly scoped otherwise.
```

## Recommended Stack

- Laravel 11 or 12
- PHP 8.2+
- MySQL 8+
- Blade templates
- Laravel Breeze for authentication if allowed
- Tailwind CSS for faster UI development if allowed
- Eloquent models and migrations
- Form Requests for server-side validation
- Laravel policies and middleware for role-based access control
- PHPUnit or Pest for tests

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
/speckit.constitution Create principles for a 3-person academic software engineering project: clear specs before code, small phased delivery, Laravel monolithic MVC architecture, MySQL-backed data model, Blade server-rendered frontend, role-based access control, testable acceptance criteria, privacy-aware candidate data handling, simulated AI/proctoring where needed, and mandatory peer review before implementation.
```

## Phase 1: Foundation, Auth, Roles, Layouts

Goal: create the Laravel foundation and role-specific access.

```text
/speckit.specify Build the Laravel foundation for the AI-Driven Smart Recruitment and Interview Management System. The system is a monolithic Laravel MVC app with Blade pages, not an API. Users can register or be created with roles: HR admin, technical interviewer, and candidate. Users authenticate with sessions, access role-specific dashboards, and are restricted by role-based middleware or policies.
```

## Phase 2: Jobs and Applications

Goal: allow HR admins to manage jobs and candidates to apply.

```text
/speckit.specify Build job requisition and candidate application management in Laravel Blade. HR admins can create, edit, submit, approve, open, and close job requisitions. Candidates can manage their profile, browse open jobs, apply once per job, and track application statuses. The system calculates a simulated match score from job requirements and candidate profile fields.
```

## Phase 3: Assessments

Goal: support technical assessments and simulated proctoring.

```text
/speckit.specify Build technical assessment management as server-rendered Laravel pages. HR admins can create assessments and questions for jobs. Candidates can start timed assessments, receive randomized questions, submit answers, and receive simulated scores. The system tracks focus-loss events as simulated proctoring data and expires assessments when time runs out.
```

## Phase 4: Interviews and Feedback

Goal: support scheduling, interviewer assignments, and feedback.

```text
/speckit.specify Build interview scheduling and feedback workflows in Laravel. HR admins can schedule interviews for applications, assign interviewers and observers, and avoid obvious scheduling conflicts. Interviewers can view assigned interviews, see candidate and assessment briefing details, and submit feedback scores and comments using Blade forms.
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

- Person 1: Laravel backend logic, Eloquent models, migrations, controllers, middleware, and policies.
- Person 2: Blade UI, layouts, forms, dashboards, Tailwind styling, and user flows.
- Person 3: Spec Kit specs, tests, validation, QA, documentation, diagrams, and review.

Rotate ownership by phase so everyone contributes to specification, implementation, and review during the project.

## Collaboration Rules

- Start every phase from a clear Spec Kit spec.
- Do not implement features that are not in the current phase spec.
- Keep AI-related features simulated unless the phase explicitly says otherwise.
- Use Laravel server-rendered pages instead of APIs.
- Use MySQL as the source of truth.
- Require peer review before marking a phase complete.
- Keep each phase small enough to finish, test, and review before moving forward.
