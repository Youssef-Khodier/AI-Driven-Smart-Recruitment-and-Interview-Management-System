# Implementation Plan: Interview Coordination Workflows

**Branch**: `011-interview-coordination` | **Date**: 2026-05-06 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/011-interview-coordination/spec.md`

## Summary

Complete the SRIM interview coordination vertical slice with HR-managed interview scheduling, balanced panel recommendations, workload and schedule-conflict ranking, interview briefing snapshots, simulated refresh-based live coding workspace, HR-approved technical-issue extensions, observer shadowing boundaries, and audit records for all scheduling, assignment, extension, and workspace changes. Delivery remains a framework-free Vanilla PHP monolithic MVC flow with server-rendered PHP templates, `routes/web.php` browser routes, MySQL via PDO, native sessions, CSRF checks, server-side validation, and role guards.

## Technical Context

**Language/Version**: PHP 8.1+ framework-free Vanilla PHP MVC  
**Primary Dependencies**: PDO, native sessions, CSRF tokens, server-side validation, middleware-style role guards, authorization policies, Tailwind CSS already approved for styling  
**Storage**: MySQL 8+ through PDO; plain SQL schema and migration files; local database records only for interview sessions, panel assignments, assignment recommendations, briefing snapshots, simulated coding workspace state/history, extension requests, and audit events  
**Testing**: Manual acceptance testing via server-rendered HR, Interviewer, Candidate, and observer page workflows; targeted PHP syntax checks; policy/repository/service tests for panel recommendation, conflict detection, extension approval, workspace access, and audit recording where practical  
**Target Platform**: Server-rendered web application in modern browsers  
**Project Type**: Framework-free Vanilla PHP monolithic MVC web application; no REST API or separated frontend  
**Performance Goals**: HR scheduling and interview detail pages load in under 2 seconds for demo data; panel recommendations for up to 50 active staff complete in under 2 seconds; live coding workspace save and refresh completes within one normal page request; audit history for at least 100 interview events loads in under 2 seconds  
**Constraints**: PHP templates, `routes/web.php`, MySQL, PDO, sessions, CSRF, server-side validation, RBAC policies, local refresh-based simulated coding workspace, no external calendar integration for this feature  
**Scale/Scope**: 3-person academic delivery; one reviewable interview-coordination vertical slice aligned to `Diagrams/`; no real-time collaboration service, websocket server, REST API, SPA, external calendar booking, video integration, or real compiler/sandbox

## Baseline Materials Review

- **SRS / Use Case Trace**: `Diagrams/SRS/SRS-SRIM final ver1.docx` section 4 UC-13 Interviewer Availability Conflict Resolver, UC-14 Multi-Representative Panel Builder, UC-15 Automated Interview Briefing Generator, UC-16 Live Coding Environment Sync, UC-17 Interviewer Shadowing Logic, UC-18 Session Extension Protocol, UC-19 Interviewer Load Balancer, and nonfunctional System Audit Trail.
- **Database / ERD Trace**: Baseline tables `users`, `departments`, `candidates`, `applications`, `assessments`, `candidate_assessments`, `submissions`, `interviews`, `interviewers_assignment`, `interview_feedback`, `notifications`, and existing `interview_audit_records`; additions/extensions for staff panel capabilities, recommendation snapshots, briefing snapshots, live coding workspace state/history, extension requests, assignment override reasons, and extended audit action coverage.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` interview stage after passed technical test; `Diagrams/Acrivity Diagram/Activity 3.pdf` automated interview pack generation; `Diagrams/Acrivity Diagram/Activity 4.pdf` feedback continuation after interview; `Diagrams/Class Diagram/Class Diagram.drawio` HRStaff, TechnicalInterviewer, Interview, Feedback, Application, Candidate; `Diagrams/Object Diagram/Object Diagram.pdf` interview object state before/after scheduled and completed sessions; `Diagrams/Use-case Diagram/Usecase.pdf` interview coordination use cases and observer actor.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio` maps this feature to HR Admin Portal, Interviewer Portal, Candidate Portal, Interview Module, Feedback and Recommendation Module, Auth and RBAC, MySQL, and Notification and Audit. Implementation extends existing `HrInterviewController`, `InterviewerInterviewController`, `InterviewRepository`, `InterviewAuditRepository`, interview policies, interview enums, interview views, and `routes/web.php` route naming patterns.
- **Scope Changes**: UC-16 describes instant real-time live coding synchronization and UC-13 references Google Calendar. This plan intentionally keeps live coding simulated through server-rendered form refreshes and keeps schedule conflict detection local to SRIM interview records. This follows the active spec, constitution, and user request.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL schema/migration files and PDO-backed repositories/controller queries.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff/observer where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed where candidate interview data is touched.
- [x] Live coding, calendar booking, notifications, and advanced collaboration are marked simulated/local unless explicitly in scope.
- [x] Acceptance criteria are testable by PHP checks, policy/repository tests where practical, or documented server-rendered page demo flows.
- [x] Peer review is scheduled before implementation begins.

## Project Structure

### Documentation (this feature)

```text
specs/011-interview-coordination/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── route-map.md
├── contracts/
│   └── interview-coordination-web.md
└── tasks.md                 # Generated later by /speckit.tasks
```

### Source Code (repository root)

```text
app/
├── Controllers/
│   ├── HrInterviewController.php
│   └── InterviewerInterviewController.php
├── Enums/
│   ├── InterviewAssignmentRole.php
│   ├── InterviewAuditAction.php
│   ├── InterviewStatus.php
│   └── UserRole.php
├── Policies/
│   ├── InterviewFeedbackPolicy.php
│   └── InterviewPolicy.php
├── Repositories/
│   ├── InterviewAuditRepository.php
│   └── InterviewRepository.php
└── Services/
    └── InterviewPanelRecommendationService.php   # Add only if recommendation logic is reusable enough to separate

database/
├── schema.sql
└── migrations/
    └── 011_interview_coordination_workflows.sql

routes/
└── web.php

views/
├── candidate/
│   └── interviews/
│       └── show.php
├── hr/
│   └── interviews/
│       ├── audit.php
│       ├── extension.php
│       ├── form.php
│       ├── index.php
│       ├── recommendations.php
│       ├── show.php
│       └── workspace.php
└── interviewer/
    └── interviews/
        ├── index.php
        ├── show.php
        ├── workspace.php
        └── feedback.php
```

**Structure Decision**: Extend the existing interview controller, repository, policy, enum, route, SQL schema, and server-rendered interview views. Add a small recommendation service only if controller/repository code becomes too large; otherwise keep recommendation logic near the interview repository for the smallest correct implementation. Do not introduce a REST controller, websocket server, background worker, external calendar adapter, real-time collaboration dependency, real compiler/sandbox, SPA, or separated frontend.

## Phase 0 Research Summary

Research decisions are documented in [research.md](research.md). All planning unknowns are resolved: panel recommendations remain deterministic and local, workload uses upcoming scheduled assignments, schedule conflicts use local interview ranges, live coding is refresh-based, extension approvals remain HR-only, observer access is training-only, and audit events are appended for all coordination changes.

## Phase 1 Design Summary

Data design is documented in [data-model.md](data-model.md). Server-rendered web contracts are documented in [contracts/interview-coordination-web.md](contracts/interview-coordination-web.md). Route/controller/view mapping is documented in [route-map.md](route-map.md). Manual demo and verification steps are documented in [quickstart.md](quickstart.md).

## Post-Design Constitution Check

- [x] Phase 1 design preserves diagram traceability and documents the UC-16/UC-13 simulation scope decisions.
- [x] Phase 1 design remains a Vanilla PHP MVC monolith using `routes/web.php` and server-rendered templates.
- [x] Phase 1 design uses MySQL/PDO and plain SQL migrations only.
- [x] Phase 1 design protects candidate interview details, briefing snapshots, workspace content, extension requests, and audit details with role-based access.
- [x] Phase 1 design avoids REST APIs, separated frontend, websockets, external calendar booking, real-time collaboration services, and real code execution.
- [x] Phase 1 outputs include testable manual flows and targeted PHP validation points.
- [x] Peer review remains required before implementation tasks begin.

## Complexity Tracking

No constitution violations to justify. The copied template referenced Laravel, Blade, Eloquent, and migrations, but the active constitution supersedes that outdated template language with framework-free Vanilla PHP MVC, PHP templates, PDO, and plain SQL schema files.
