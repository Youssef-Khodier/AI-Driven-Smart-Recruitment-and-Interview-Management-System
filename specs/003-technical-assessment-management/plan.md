# Implementation Plan: Technical Assessment Management

**Branch**: `002-job-applications` | **Date**: 2026-05-04 | **Spec**: [spec.md](./spec.md)  
**Input**: Feature specification from `specs/003-technical-assessment-management/spec.md`

## Summary

Build the SRIM assessment and simulated proctoring vertical slice for HR-authored job assessments, MCQ/theory/coding-as-text question management, candidate assessment attempts for applications in `ASSESSMENT` status, randomized question snapshots, continuous answer saving, timeout expiry, deterministic simulated scoring, and HR review of scores plus focus-loss events. Delivery remains inside the existing Laravel monolithic MVC application using Blade pages, `routes/web.php`, form submissions, Eloquent models, migrations, policies, middleware, sessions, CSRF protection, server-side validation, and PHPUnit feature/model/policy tests.

## Technical Context

**Language/Version**: PHP 8.2+ with Laravel 12.x stable  
**Primary Dependencies**: Laravel MVC, Blade, Eloquent, middleware, policies, sessions, CSRF, Form Request validation, existing RBAC foundation, existing requisition/application models, and PHPUnit via `php artisan test`  
**Storage**: MySQL via Laravel migrations and Eloquent models; no file upload, external storage, real code execution storage, webcam media, or document storage is in scope  
**Testing**: Laravel PHPUnit feature tests for HR and Candidate web flows, policy tests for role boundaries, validation tests for assessment/question forms, model relationship/state tests, timeout/scoring tests, and simulated proctoring event tests  
**Target Platform**: Server-rendered web application in modern Chrome, Firefox, and Edge browsers  
**Project Type**: Laravel monolithic MVC web application; no REST API, SPA, separated frontend, or mobile-native project  
**Performance Goals**: HR can create a 10-question assessment in under 5 minutes; candidate can complete a 10-question assessment in one session with at least 95% completion during acceptance testing; timeout enforcement passes in 100% of tested late submissions; HR can review 50 attempts for one job in under 30 seconds; simulated score is visible immediately after final submit or expiry review in at least 95% of tested cases  
**Constraints**: Blade pages, `routes/web.php`, form submissions or narrowly scoped same-page progressive enhancement only, redirects, MySQL, sessions, CSRF, server-side validation, active-account middleware, role middleware/policies, no REST API, no real AI grading, no real code execution, no plagiarism engine, no webcam/video proctoring, no email links, no retakes or cool-down reuse in this phase  
**Scale/Scope**: 3-person academic delivery; one working assessment vertical slice aligned to baseline SRIM assessment/proctoring use cases and sized around 50 attempts per job and 10+ questions per assessment for acceptance evidence

## Baseline Materials Review

- **SRS / Use Case Trace**: SRS sections 1.3, 3.2, 3.4, 4, and 5.2-5.3; UC-7 Proctored Environment Controller; UC-8 Randomized Question-Bank Generator; UC-9 Code-Execution Output Validator as simulated scoring context only; UC-10 Plagiarism Detection Logic and UC-12 Assessment Cool-down Manager as later-scope context only.
- **Database / ERD Trace**: `Diagrams/Database/schema.sql`, `schema-erd.svg`, and README baseline entities `job_requisitions`, `applications`, `assessments`, `questions`, `candidate_assessments`, and `submissions`; feature-specific additions include attempt question snapshots and assessment integrity events, plus an `application_id` link on candidate attempts for unambiguous eligibility and HR reporting.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` application to technical test flow; `Activity 2.pdf` candidate code submission flow used only as simulated text-question context; `Activity 6.pdf` login, role detection, dashboards, and permission application; `Diagrams/Class Diagram/Class Diagram.drawio.pdf` Assessment, Candidate, Application, JobRequisition, HRStaff, and Candidate takeAssessment concepts; `Diagrams/Object Diagram/Object Diagram.pdf` assessment example with time limit and score.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio.pdf` maps to Candidate Portal, HR Admin Portal, Auth & RBAC, Assessment & Proctoring, MySQL Database, and the existing session-backed Laravel application core.
- **Scope Changes**: None requiring constitution amendment. The implementation extends the baseline schema for attempt snapshots and integrity events to satisfy clarified evidence requirements while keeping real code execution, webcam/video proctoring, plagiarism detection, dynamic difficulty, retakes, cool-down reuse, email, and external integrations out of scope.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses Laravel monolithic MVC with Blade pages and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use Laravel migrations and Eloquent relationships.
- [x] Controllers, middleware, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed when candidate data is touched.
- [x] AI, proctoring, background checks, job board sync, calendar, and email are marked simulated unless explicitly in scope.
- [x] Acceptance criteria are testable by Laravel tests or documented Blade-page demo flows.
- [x] Peer review is scheduled before implementation begins.

Gate result: PASS. No constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/003-technical-assessment-management/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── route-map.md
├── contracts/
│   └── web-workflows.md
├── checklists/
│   └── requirements.md
└── tasks.md             # Created later by /speckit.tasks
```

### Source Code (repository root)

```text
app/
├── Enums/
│   ├── ApplicationStatus.php                  # Existing; use Assessment status case
│   ├── AssessmentAttemptStatus.php            # New
│   ├── AssessmentQuestionType.php              # New
│   └── AssessmentType.php                      # New if enum clarity is needed
├── Http/
│   ├── Controllers/
│   │   ├── Candidate/
│   │   │   └── AssessmentController.php        # Start, show, save answer, submit, expired view
│   │   └── Hr/
│   │       ├── AssessmentController.php        # Assessment CRUD and results
│   │       └── AssessmentQuestionController.php # Question CRUD
│   └── Requests/
│       ├── Candidate/
│       │   ├── SaveAssessmentAnswerRequest.php
│       │   └── SubmitAssessmentRequest.php
│       └── Hr/
│           ├── StoreAssessmentRequest.php
│           ├── StoreAssessmentQuestionRequest.php
│           ├── UpdateAssessmentQuestionRequest.php
│           └── UpdateAssessmentRequest.php
├── Models/
│   ├── Assessment.php
│   ├── AssessmentIntegrityEvent.php
│   ├── CandidateAssessment.php
│   ├── CandidateAssessmentQuestion.php
│   ├── Question.php
│   └── Submission.php
├── Policies/
│   ├── AssessmentPolicy.php
│   └── CandidateAssessmentPolicy.php
└── Support/
    └── SimulatedAssessmentScorer.php

database/
├── migrations/
└── seeders/

resources/views/
├── candidate/
│   └── assessments/
│       ├── expired.blade.php
│       ├── result.blade.php
│       └── show.blade.php
├── hr/
│   ├── assessments/
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── form.blade.php
│   │   ├── results.blade.php
│   │   └── show.blade.php
│   └── assessment-questions/
│       ├── create.blade.php
│       ├── edit.blade.php
│       └── form.blade.php
└── layouts/

routes/
└── web.php

tests/
├── Feature/
│   ├── Candidate/
│   └── Hr/
└── Unit/
```

**Structure Decision**: Extend the existing Laravel application at the repository root. Keep HR assessment pages under `resources/views/hr`, candidate attempt pages under `resources/views/candidate`, browser flows in `routes/web.php`, and domain models/policies in standard Laravel paths. Use `app/Support/SimulatedAssessmentScorer.php` for deterministic simulated scoring because the existing project already uses `app/Support` for reusable domain helpers. Do not introduce `routes/api.php`, public JSON contracts, a separated frontend, or external integration contracts.

## Phase 0: Research Summary

Research output: [research.md](./research.md)

Resolved decisions:

- Use Laravel 12.x and PHP 8.2+ with current RBAC conventions.
- Use Blade pages and web form submissions, with optional same-page progressive enhancement only for continuous answer save and focus-loss logging.
- Extend baseline assessment tables through Laravel migrations rather than replacing existing job, application, candidate, or user tables.
- Add `application_id` to candidate assessment attempts for eligibility, privacy checks, and HR job-level reporting.
- Add attempt question snapshots to preserve question text, answer choices, points, and randomized order at attempt start.
- Use deterministic simulated scoring: MCQ exact answer matching and keyword/reference overlap for theory/free-text and coding-as-text answers; no code execution or AI grading.
- Use server-side deadline checks on every attempt action to enforce expiry, while the visible timer is only a candidate aid.
- Store focus-loss and focus-return as simulated integrity events linked to the candidate attempt.
- Use one attempt per candidate per assessment for this phase; retakes and cool-down reuse are deferred.

## Phase 1: Design Summary

Design outputs:

- [data-model.md](./data-model.md)
- [contracts/web-workflows.md](./contracts/web-workflows.md)
- [route-map.md](./route-map.md)
- [quickstart.md](./quickstart.md)

Implementation boundaries:

- HR Admins manage assessments and questions for existing job requisitions.
- Candidates can start assessments only when their application is in `ASSESSMENT` status.
- Attempts snapshot question evidence at start and randomize question order once per attempt.
- Candidate answers are continuously saved before final submit, then finalized at submit or scored on expiry using saved answers before the deadline.
- Scores and proctoring events are always labeled simulated and advisory.
- Focus-loss tracking records browser focus/visibility events only; no webcam, microphone, screen recording, or lockdown-browser scope.
- Technical Interviewer and Junior Staff receive no new authoring access; interviewer assessment summary access remains deferred to interview preparation scope.

## Post-Design Constitution Check

- [x] Design artifacts preserve diagram traceability and baseline entities.
- [x] Web workflow contracts are Blade/form contracts, not REST API contracts.
- [x] Data model uses MySQL/Eloquent-compatible entities and relationships.
- [x] RBAC and candidate privacy are represented in route contracts, policies, model ownership rules, and tests.
- [x] Audit-relevant assessment authoring, scoring, timeout, and simulated proctoring evidence are included.
- [x] Simulated scoring and proctoring are clearly labeled; real AI/code execution/video proctoring/integrations remain out of scope.
- [x] Quickstart includes test/demo evidence before implementation is considered complete.

Post-design gate result: PASS. No constitution violations.

## Complexity Tracking

No constitution violations or complexity exceptions are required.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

## Phase 2 Preview

Task generation should produce small, independently reviewable tasks for migrations/enums/models, policies, HR assessment CRUD, HR question CRUD, candidate start/show/save/submit/expiry flows, deterministic scoring, focus-loss event capture, HR results review, Blade views, route wiring, seed/demo data, and Laravel tests. Do not start implementation until `/speckit.tasks` creates the task list and peer review is complete.
