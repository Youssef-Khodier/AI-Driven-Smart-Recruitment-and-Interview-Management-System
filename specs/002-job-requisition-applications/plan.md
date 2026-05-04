# Implementation Plan: Job Requisition and Candidate Applications

**Branch**: `002-job-applications` | **Date**: 2026-05-04 | **Spec**: [spec.md](./spec.md)  
**Input**: Feature specification from `specs/002-job-requisition-applications/spec.md`

## Summary

Build the SRIM recruitment pipeline vertical slice for HR-managed job requisitions, candidate profile completion, open-job browsing, one application per candidate per job, candidate application tracking, HR application review, audit-relevant status history, stale edit protection, and a deterministic simulated match score. The implementation stays inside the existing Laravel monolithic MVC application using Blade pages, web form submissions, Eloquent models, policies, middleware, CSRF protection, server-side validation, MySQL migrations, and PHPUnit feature/model/policy tests.

## Technical Context

**Language/Version**: PHP 8.2+ with Laravel 12.x stable  
**Primary Dependencies**: Laravel MVC, Blade, Eloquent, middleware, policies, sessions, CSRF, Form Request validation, password/session authentication from the RBAC foundation, and PHPUnit via `php artisan test`  
**Storage**: MySQL via Laravel migrations and Eloquent models; candidate resumes remain URL/reference strings in this phase with no file upload/storage scope  
**Testing**: Laravel PHPUnit feature tests for HR and candidate web flows, policy tests for role boundaries, validation tests for forms and duplicate applications, model relationship tests, and status-history tests  
**Target Platform**: Server-rendered web application in modern Chrome, Firefox, and Edge browsers  
**Project Type**: Laravel monolithic MVC web application; no REST API, SPA, separated frontend, or mobile-native project  
**Performance Goals**: HR requisition lifecycle completion under 3 minutes; candidate profile-to-application flow under 4 minutes; duplicate application and closed-job blocks in 100% of tested cases; simulated score visible within 3 seconds for at least 95% of complete applications; HR can identify top-scoring applicants among 100 applications in under 10 seconds  
**Constraints**: Blade pages, `routes/web.php`, form submissions, redirects, MySQL, sessions, CSRF, server-side validation, active-account middleware, role middleware/policies, no external job board sync, no email, no real resume parsing, no real AI/NLP, no assessment/interview/offer/onboarding implementation  
**Scale/Scope**: 3-person academic delivery; one working vertical slice for job requisitions and applications with acceptance coverage around 100 applications per requisition

## Baseline Materials Review

- **SRS / Use Case Trace**: SRS sections 1.3, 3.2, 3.4, 4, and 5.3; UC-1 Automated Screening Triage, UC-2 Dynamic Skill-Weighting Engine, UC-3 Job Requisition Approval Workflow, UC-4 Application Deduplication Logic, and UC-5 AI-Ranked Shortlisting as later-scope context only.
- **Database / ERD Trace**: `Diagrams/Database/schema.sql` and `schema-erd.svg` baseline entities `users`, `departments`, `candidates`, `job_requisitions`, and `applications`; feature-specific additions include candidate skill keywords and status-history records for requisitions and applications.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` apply and screening flow; `Activity 6.pdf` login, role detection, dashboards, and permission application; `Diagrams/Class Diagram/Class Diagram.drawio.pdf` JobRequisition, Application, Candidate, HRStaff, and AIScreeningEngine concepts; `Diagrams/Object Diagram/Object Diagram.pdf` requisition, candidate/application, and screening examples.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio.pdf` maps to Candidate Portal, HR Admin Portal, Auth & RBAC, Screening Engine, MySQL Database, and existing session-backed Laravel application core.
- **Scope Changes**: None. External job boards, email, real AI/NLP parsing, assessments, interviews, offers, onboarding, and analytics remain out of scope for this feature.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses Laravel monolithic MVC with Blade pages and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use Laravel migrations and Eloquent relationships.
- [x] Controllers, middleware, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed when candidate data is touched.
- [x] AI, proctoring, background checks, job board sync, calendar, and email are marked simulated or out of scope.
- [x] Acceptance criteria are testable by Laravel tests or documented Blade-page demo flows.
- [x] Peer review is scheduled before implementation begins.

Gate result: PASS. No constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/002-job-requisition-applications/
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
│   ├── ApplicationStatus.php
│   └── JobRequisitionStatus.php
├── Http/
│   ├── Controllers/
│   │   ├── Candidate/
│   │   │   ├── ApplicationController.php
│   │   │   ├── JobController.php
│   │   │   └── ProfileController.php      # Extend existing profile workflow
│   │   └── Hr/
│   │       ├── ApplicationController.php
│   │       └── JobRequisitionController.php
│   └── Requests/
│       ├── Candidate/
│       │   ├── StoreApplicationRequest.php
│       │   └── UpdateProfileRequest.php
│       └── Hr/
│           ├── StoreJobRequisitionRequest.php
│           ├── UpdateApplicationStatusRequest.php
│           └── UpdateJobRequisitionRequest.php
├── Models/
│   ├── Application.php
│   ├── ApplicationStatusHistory.php
│   ├── JobRequisition.php
│   └── JobRequisitionStatusHistory.php
├── Policies/
│   ├── ApplicationPolicy.php
│   └── JobRequisitionPolicy.php
└── Support/
    └── SimulatedMatchScorer.php

database/
├── migrations/
└── seeders/

resources/views/
├── candidate/
│   ├── applications/
│   ├── jobs/
│   └── profile.blade.php                  # Extend existing profile page
├── hr/
│   ├── applications/
│   └── requisitions/
└── layouts/

routes/
└── web.php

tests/
├── Feature/
│   ├── Candidate/
│   └── Hr/
└── Unit/
```

**Structure Decision**: Extend the existing Laravel application at the repository root. Keep HR pages under `resources/views/hr`, candidate pages under `resources/views/candidate`, browser flows in `routes/web.php`, and domain models/policies in standard Laravel paths. Use `app/Support/SimulatedMatchScorer.php` for the deterministic score calculation because the existing project already uses `app/Support` for small reusable domain helpers. Do not introduce `routes/api.php`, a service boundary, a separated frontend, or external integration contracts.

## Phase 0: Research Summary

Research output: [research.md](./research.md)

Resolved decisions:

- Use Laravel 12.x with PHP 8.2+ and the current RBAC foundation conventions.
- Add baseline-aligned `job_requisitions` and `applications` models/migrations rather than replacing existing `users`, `departments`, or `candidates` entities.
- Add a manual comma-separated candidate `skill_keywords` field for this phase because real resume parsing is out of scope.
- Use explicit status enums for requisitions and applications, with policies enforcing valid transitions and self-approval prevention.
- Use deterministic simulated scoring in `app/Support`, weighted as skills overlap 70%, title match 15%, and experience match 15%.
- Use separate status-history records for requisitions and applications to support audit review without overloading account-audit records.
- Use stale-edit detection based on the requisition update timestamp submitted with HR edit forms.

## Phase 1: Design Summary

Design outputs:

- [data-model.md](./data-model.md)
- [contracts/web-workflows.md](./contracts/web-workflows.md)
- [route-map.md](./route-map.md)
- [quickstart.md](./quickstart.md)

Implementation boundaries:

- HR Admins manage the requisition lifecycle: Draft, Pending Approval, Approved, Open, and Closed.
- Requisition approval requires a different active HR Admin from the creator.
- Candidates can browse and apply only to Open requisitions.
- Candidates can apply once per job and see exact pipeline statuses for their own applications.
- Application match scores are simulated, advisory, calculated at application time, and not silently recalculated after profile edits.
- Candidate profile skill input is a required comma-separated skills or keywords list.
- HR application status changes and requisition status changes are recorded for audit review.
- Technical Interviewer and Junior Staff receive no new feature access in this phase.

## Post-Design Constitution Check

- [x] Design artifacts preserve diagram traceability and baseline entities.
- [x] Web workflow contracts are Blade/form contracts, not REST API contracts.
- [x] Data model uses MySQL/Eloquent-compatible entities and relationships.
- [x] RBAC and candidate privacy are represented in route contracts, policies, model ownership rules, and tests.
- [x] Audit-relevant requisition and application status changes are included.
- [x] Simulated scoring is clearly labeled and no real AI/NLP, proctoring, integration, or email scope is introduced.
- [x] Quickstart includes test/demo evidence before implementation is considered complete.

Post-design gate result: PASS. No constitution violations.

## Complexity Tracking

No constitution violations or complexity exceptions are required.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

## Phase 2 Preview

Task generation should produce small, independently reviewable tasks for migrations/enums/models, candidate profile skill keywords, HR requisition CRUD/status transitions, candidate job browsing and applications, simulated scoring, HR applicant review/status updates, status-history recording, stale-edit protection, Blade views, policies, route wiring, and Laravel tests. Do not start implementation until `/speckit.tasks` creates the task list and peer review is complete.
