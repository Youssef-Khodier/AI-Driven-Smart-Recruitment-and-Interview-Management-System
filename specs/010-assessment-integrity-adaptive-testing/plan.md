# Implementation Plan: Advanced Assessment Integrity and Adaptive Testing

**Branch**: `009-requisition-governance` | **Date**: 2026-05-06 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/010-assessment-integrity-adaptive-testing/spec.md`

## Summary

Complete the SRIM assessment integrity vertical slice with HR-managed difficulty-tier question rules, randomized candidate attempt snapshots, timer heartbeat persistence with server-deadline expiry, local simulated output validation, local simulated plagiarism similarity, score-based adaptive difficulty suggestions, and configurable assessment retake cooldowns. Delivery remains a framework-free Vanilla PHP monolithic MVC flow with server-rendered PHP templates, `routes/web.php` browser routes, MySQL via PDO, native sessions, CSRF checks, server-side validation, and role guards.

## Technical Context

**Language/Version**: PHP 8.1+ framework-free Vanilla PHP MVC  
**Primary Dependencies**: PDO, native sessions, CSRF tokens, server-side validation, middleware-style role guards, authorization policies, Tailwind CSS already approved for styling  
**Storage**: MySQL 8+ through PDO; plain SQL schema and migration files; local database records only for hidden expected outputs, common answers, simulated validation, and simulated plagiarism results  
**Testing**: Manual acceptance testing via server-rendered HR and Candidate page workflows; targeted PHP syntax checks; PHP unit/service tests for scorer, cooldown, rule validation, and policy logic where practical  
**Target Platform**: Server-rendered web application in modern browsers  
**Project Type**: Framework-free Vanilla PHP monolithic MVC web application; no REST API or separated frontend  
**Performance Goals**: HR assessment pages and candidate assessment pages load in under 2 seconds for demo data; attempt start randomizes up to 50 questions in under 2 seconds; heartbeat save completes within one normal heartbeat interval; scoring for one attempt completes in under 2 seconds for up to 50 submissions  
**Constraints**: PHP templates, `routes/web.php`, MySQL, PDO, sessions, CSRF, server-side validation, RBAC policies, simulated/local-only output validation and plagiarism detection  
**Scale/Scope**: 3-person academic delivery; one reviewable assessment-integrity vertical slice aligned to `Diagrams/`; no real compiler, sandbox, external plagiarism service, REST API, SPA, or separated frontend

## Baseline Materials Review

- **SRS / Use Case Trace**: `Diagrams/SRS/SRS-SRIM final ver1.docx` section 4 UC-7 Proctored Environment Controller, UC-8 Randomized Question-Bank Generator, UC-9 Code-Execution Output Validator, UC-10 Plagiarism Detection Logic, UC-11 Dynamic Difficulty Adjustment, UC-12 Assessment Cool-down Manager.
- **Database / ERD Trace**: Baseline tables `users`, `candidates`, `applications`, `assessments`, `questions`, `candidate_assessments`, `candidate_assessment_questions`, `submissions`, and `assessment_integrity_events`; additions/extensions for `assessment_question_rules`, `question_expected_outputs`, `assessment_common_answers`, `assessments.cooldown_months`, `candidate_assessments.remaining_seconds`, `candidate_assessments.last_heartbeat_at`, `submissions.code_output`, `submissions.output_matched`, and `submissions.plagiarism_score`.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` assessment stage in recruitment lifecycle; `Diagrams/Acrivity Diagram/Activity 2.pdf` code submission and expected-output comparison flow; `Diagrams/Class Diagram/Class Diagram.drawio` Candidate and Assessment classes; `Diagrams/Object Diagram/Object Diagram.pdf` assessment object state before/after application submission; `Diagrams/Use-case Diagram/Usecase.pdf` assessment/proctoring includes and extensions.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio` maps the feature to Candidate Portal, HR Admin Portal, Assessment Module, Proctoring Module, Auth & RBAC, MySQL, and Notification/Audit. Implementation follows existing `AssessmentController`, `SimulatedAssessmentScorer`, assessment views, and `routes/web.php` route naming patterns.
- **Scope Changes**: The SRS UC-9 wording describes real compilation/execution, but this feature explicitly keeps code-output validation simulated and local. This is allowed by `Diagrams/document.md` design function #11, the constitution's simulated advanced-feature guidance, and the user request.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL schema/migration files and PDO-backed repositories/controller queries.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed where candidate assessment data is touched.
- [x] Code-output validation, plagiarism detection, and proctoring/integrity outcomes are marked simulated unless explicitly in scope.
- [x] Acceptance criteria are testable by PHP checks, service/policy tests where practical, or documented server-rendered page demo flows.
- [x] Peer review is scheduled before implementation begins.

## Project Structure

### Documentation (this feature)

```text
specs/010-assessment-integrity-adaptive-testing/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── route-map.md
├── contracts/
│   └── assessment-integrity-web.md
├── checklists/
│   └── requirements.md
└── tasks.md                 # Generated later by /speckit.tasks
```

### Source Code (repository root)

```text
app/
├── Controllers/
│   └── AssessmentController.php
├── Core/
│   ├── Database.php
│   ├── Request.php
│   ├── Response.php
│   └── Session.php
├── Enums/
│   ├── AssessmentAttemptStatus.php
│   ├── AssessmentQuestionType.php
│   ├── AssessmentType.php
│   └── UserRole.php
└── Services/
    └── SimulatedAssessmentScorer.php

database/
├── schema.sql
└── migrations/
    └── 010_assessment_integrity_adaptive_testing.sql

routes/
└── web.php

views/
├── candidate/assessments/
│   ├── show.php
│   └── result.php
└── hr/
    ├── assessment-questions/form.php
    └── assessments/
        ├── attempt.php
        ├── form.php
        ├── index.php
        ├── results.php
        └── show.php
```

**Structure Decision**: Extend the existing assessment controller, scorer service, route file, SQL schema, and server-rendered assessment views. Do not introduce a REST controller, background worker, external compiler, third-party plagiarism service, framework dependency, or separated frontend. Use narrowly scoped browser enhancement only for the timer heartbeat while retaining normal form submissions and server-side authority.

## Phase 0 Research Summary

Research decisions are documented in [research.md](research.md). All technical unknowns from the plan context are resolved: simulated validation remains local, heartbeat is server-deadline authoritative, insufficient question banks block attempt start, adaptive suggestions use the clarified score bands, and completed integrity results are snapshot-preserved.

## Phase 1 Design Summary

Data design is documented in [data-model.md](data-model.md). Server-rendered web contracts are documented in [contracts/assessment-integrity-web.md](contracts/assessment-integrity-web.md). Route/controller/view mapping is documented in [route-map.md](route-map.md). Manual demo and verification steps are documented in [quickstart.md](quickstart.md).

## Post-Design Constitution Check

- [x] Phase 1 design preserves diagram traceability and documents the UC-9 simulation scope decision.
- [x] Phase 1 design remains a Vanilla PHP MVC monolith using `routes/web.php` and server-rendered templates.
- [x] Phase 1 design uses MySQL/PDO and plain SQL migrations only.
- [x] Phase 1 design protects hidden expected outputs, common answers, candidate submissions, timer state, and simulated integrity results with role-based access.
- [x] Phase 1 design avoids REST APIs, separated frontend, real code execution, and external plagiarism services.
- [x] Phase 1 outputs include testable manual flows and targeted PHP validation points.
- [x] Peer review remains required before implementation tasks begin.

## Complexity Tracking

No constitution violations to justify. The copied template referenced Laravel, Blade, Eloquent, and migrations, but the active constitution supersedes that outdated template language with framework-free Vanilla PHP MVC, PHP templates, PDO, and plain SQL schema files.
