# Implementation Plan: Laravel RBAC Foundation

**Branch**: `001-laravel-rbac-foundation` | **Date**: 2026-05-03 | **Spec**: [spec.md](./spec.md)  
**Input**: Feature specification from `specs/001-laravel-rbac-foundation/spec.md`

## Summary

Build the first SRIM vertical slice: a Laravel monolithic MVC web application foundation with session authentication, candidate self-registration, HR-created user accounts, role-specific dashboards, role/status access restrictions, seeded initial HR admin, candidate phone capture, and audit records for HR account administration. The plan intentionally excludes REST APIs, SPA delivery, full user profile editing, account deletion, password reset, email verification, SSO, MFA, recruitment pipeline features, assessments, interviews, offers, onboarding, AI, proctoring, and external integrations.

## Technical Context

**Language/Version**: PHP 8.2+ with Laravel 12.x stable  
**Primary Dependencies**: Laravel MVC, Blade, Eloquent, web middleware, authorization policies/gates, sessions, CSRF, Form Request validation, password hashing, database seeders  
**Storage**: MySQL via Laravel migrations and Eloquent models; no file storage in this phase  
**Testing**: Laravel feature tests for web flows, validation tests, authorization/policy tests, model relationship tests, and seeder smoke tests  
**Target Platform**: Server-rendered web application in modern Chrome, Firefox, and Edge browsers  
**Project Type**: Laravel monolithic MVC web application; no REST API or separated frontend  
**Performance Goals**: Candidate registration completes under 2 minutes; HR account creation completes under 90 seconds; 95% of successful sign-ins reach the correct dashboard within 3 seconds; all tested role-boundary violations are denied  
**Constraints**: Blade pages, `routes/web.php`, form submissions, redirects, MySQL, sessions, CSRF, server-side validation, RBAC middleware/policies, least-privilege candidate data exposure, audit records for HR account administration  
**Scale/Scope**: 3-person academic first phase; establishes foundation for later SRIM modules without implementing recruitment, assessment, interview, offer, onboarding, AI, proctoring, or external integration scope

## Baseline Materials Review

- **SRS / Use Case Trace**: SRS sections 1.4, 3.2, 3.4, 5.3, and glossary; RBAC nonfunctional requirement; foundational support for UC-1 through UC-32 by establishing authenticated actors and access boundaries.
- **Database / ERD Trace**: `users`, `departments`, and `candidates` from `Diagrams/Database/schema.sql` and `Diagrams/Database/schema-erd.svg`; phase-specific addition of account administration audit records while preserving baseline entities.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 6.pdf` login, credential verification, role detection, dashboard loading, and permission application; `Diagrams/Class Diagram/Class Diagram.drawio` User, Role, Permission, HRStaff, TechnicalInterviewer, Candidate; `Diagrams/Object Diagram/Object Diagram.pdf` Role/User/HR/Candidate examples.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio` maps to Candidate Portal, HR Admin Portal, Interviewer Portal, Shared Auth UI, Identity & Access Module, MySQL Core Recruitment Data, and active sessions.
- **Scope Changes**: None. This plan follows the clarified first-phase scope and records the audit table as a foundation detail needed by the SRS audit trail expectation.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses Laravel monolithic MVC with Blade pages and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use Laravel migrations and Eloquent relationships.
- [x] Controllers, middleware, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed when candidate data is touched.
- [x] AI, proctoring, background checks, job board sync, calendar, and email are marked out of scope for this phase.
- [x] Acceptance criteria are testable by Laravel tests or documented Blade-page demo flows.
- [x] Peer review is scheduled before implementation begins.

Gate result: PASS. No constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/001-laravel-rbac-foundation/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── route-map.md
├── contracts/
│   └── web-workflows.md
└── tasks.md             # Created later by /speckit.tasks
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Candidate/
│   │   ├── Hr/
│   │   └── Interviewer/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Policies/
└── Support/             # Optional, only for small reusable helpers

database/
├── migrations/
└── seeders/

resources/views/
├── auth/
├── candidate/
├── hr/
├── interviewer/
└── layouts/

routes/
└── web.php

tests/
├── Feature/
└── Unit/
```

**Structure Decision**: Create or use a single Laravel application at the repository root. Keep identity, dashboards, user administration, and audit behavior in standard Laravel MVC paths. Do not introduce `routes/api.php` contracts, frontend applications, mobile projects, or service boundaries.

## Phase 0: Research Summary

Research output: [research.md](./research.md)

Resolved decisions:

- Use Laravel 12.x with PHP 8.2+ unless the local environment requires a newer compatible PHP patch version.
- Use a single `users.role` value aligned to the baseline schema rather than a separate roles/permissions package for phase 1.
- Use Laravel web authentication/session primitives with Blade forms, CSRF, Form Requests, middleware, and policies.
- Seed the initial active HR admin account in controlled setup data.
- Store candidate phone in the candidate profile during registration.
- Record audit rows for HR account creation, role changes, and active-status changes.

## Phase 1: Design Summary

Design outputs:

- [data-model.md](./data-model.md)
- [contracts/web-workflows.md](./contracts/web-workflows.md)
- [route-map.md](./route-map.md)
- [quickstart.md](./quickstart.md)

Implementation boundaries:

- Candidate registration creates an active `users` row and a matching `candidates` row with phone.
- HR user administration creates users and changes role/status only; full profile editing and deletion are explicitly deferred.
- Candidate, HR admin, and technical interviewer dashboards may show empty-state cards for later modules but must enforce role-specific access now.
- Role and status checks apply on every protected page/form action, not only at login.
- Audit records cover privileged HR account administration actions for later review.

## Post-Design Constitution Check

- [x] Design artifacts preserve diagram traceability and baseline entities.
- [x] Web workflow contracts are Blade/form contracts, not REST API contracts.
- [x] Data model uses MySQL/Eloquent-compatible entities and relationships.
- [x] RBAC and candidate privacy are represented in routes, data model, and acceptance flows.
- [x] Audit-relevant HR account administration changes are included.
- [x] No AI/proctoring/integration scope is introduced.
- [x] Quickstart includes test/demo evidence before implementation is considered complete.

Post-design gate result: PASS. No constitution violations.

## Complexity Tracking

No constitution violations or complexity exceptions are required.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

## Phase 2 Preview

Task generation should produce small, independently reviewable tasks for scaffold/setup, migrations/models/seeders, auth flows, role middleware/policies, dashboards, HR account administration, audit recording, and feature tests. Do not start implementation until `/speckit.tasks` creates the task list and peer review is complete.
