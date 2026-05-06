# Implementation Plan: Feedback Governance Analytics

**Branch**: `012-feedback-governance-analytics` | **Date**: 2026-05-06 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/012-feedback-governance-analytics/spec.md`

## Summary

Complete the SRIM feedback governance and evaluation analytics vertical slice with governed official feedback aggregation, interviewer harshness normalization, serious concern flag escalation, candidate post-interview sentiment, in-app consensus/debrief records, HR-maintained competency benchmarks, competency gap visualization, final recommendation governance, notifications, RBAC, and immutable audit coverage. Delivery remains a framework-free Vanilla PHP MVC monolith with server-rendered PHP templates, `routes/web.php` browser routes, MySQL via PDO repositories, native sessions, CSRF checks, server-side validation, and explicit policies.

## Technical Context

**Language/Version**: PHP 8.1+ framework-free Vanilla PHP MVC  
**Primary Dependencies**: PDO, native sessions, CSRF tokens, server-side validation, middleware-style role guards, authorization policies, Tailwind CSS already approved for styling  
**Storage**: MySQL 8+ through PDO; plain SQL schema and migration files; local records for feedback governance, normalization snapshots, serious concern flags, candidate sentiment, debrief records, competency benchmarks, gap snapshots, notifications, and audit events  
**Testing**: Manual acceptance testing through server-rendered HR, Interviewer, Candidate, and observer workflows; targeted PHP syntax checks; policy, repository, and service tests where practical for normalization thresholds, RBAC, duplicate prevention, blocking rules, and audit writes  
**Target Platform**: Server-rendered web application in modern browsers  
**Project Type**: Framework-free Vanilla PHP monolithic MVC web application; no REST API or separated frontend  
**Performance Goals**: HR evaluation report loads in under 2 seconds for demo data; normalization for up to 20 official panel submissions and 12 months of comparable history completes in one normal page request; competency visualizer renders in under 2 seconds; audit history for at least 100 governance events loads in under 2 seconds  
**Constraints**: PHP templates, `routes/web.php`, MySQL, PDO repositories, sessions, CSRF, server-side validation, RBAC policies, in-system notifications, in-app debrief records only, no REST API, no SPA, no external calendar scheduling, no background worker dependency  
**Scale/Scope**: 3-person academic delivery; one reviewable feedback-governance vertical slice aligned to `Diagrams/`; local deterministic analytics and decision support only

## Baseline Materials Review

- **SRS / Use Case Trace**: `Diagrams/SRS/SRS-SRIM final ver1.docx` UC-20 Multi-Dimensional Feedback Aggregator, UC-21 Score Normalization Algorithm, UC-22 Candidate Red-Flag Escalation, UC-23 Consensus Meeting Automator, UC-24 Competency Gap Visualizer, UC-25 Hiring Recommendation State-Machine, and nonfunctional System Audit Trail, RBAC, privacy, and Post-Interview Sentiment Logger requirements.
- **Database / ERD Trace**: Baseline and current tables `users`, `candidates`, `job_requisitions`, `applications`, `interviews`, `interviewers_assignment`, `interview_feedback`, `final_evaluations`, `notifications`, and existing `interview_audit_records`; additions/extensions planned for serious concern flags, candidate sentiment, debrief records, competency benchmarks, normalized evaluation snapshots, gap snapshots, and expanded audit coverage.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 4.pdf` feedback aggregation, red-flag check, HR recommendation review, override reasons, final status update; `Diagrams/Class Diagram/Class Diagram.drawio` HRStaff, TechnicalInterviewer, Candidate, Interview, Feedback, Application; `Diagrams/Object Diagram/Object Diagram.pdf` interview and feedback before/after states; `Diagrams/Use-case Diagram/Usecase.pdf` feedback and compliance actors and use cases.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio` maps this feature to HR Admin Portal, Interviewer Portal, Candidate Portal, Feedback & Recommendation Module, Notification & Audit, Auth & RBAC, and MySQL. Implementation extends existing feedback/evaluation controllers, repositories, policies, notifications, audit reporting, and server-rendered views.
- **Scope Changes**: The SRS phrase "Consensus Meeting Automator" is narrowed to an in-app debrief record with participants, consensus, dissent, outcome, rationale, and next action. No external calendar scheduling is introduced. This is a constitution-compliant scope refinement from the clarified spec.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL schema/migration files and PDO-backed repositories.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff/observer where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed because candidate evaluation data is touched.
- [x] AI, proctoring, background checks, job board sync, calendar, and email are not introduced; notifications remain in-system and debriefs remain in-app.
- [x] Acceptance criteria are testable by PHP checks, policy/repository/service tests where practical, or documented server-rendered page demo flows.
- [x] Peer review is scheduled before implementation begins.

## Project Structure

### Documentation (this feature)

```text
specs/012-feedback-governance-analytics/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── route-map.md
├── contracts/
│   └── feedback-governance-web.md
└── tasks.md                 # Generated later by /speckit.tasks
```

### Source Code (repository root)

```text
app/
├── Controllers/
│   ├── CandidateInterviewController.php
│   ├── HrAuditLogController.php
│   ├── HrFinalEvaluationController.php
│   ├── HrInterviewController.php
│   ├── HrReportController.php
│   └── InterviewerInterviewController.php
├── Enums/
│   ├── FeedbackGovernanceAuditAction.php
│   ├── FinalEvaluationRecommendation.php
│   ├── InterviewAssignmentRole.php
│   └── UserRole.php
├── Policies/
│   ├── AuditLogPolicy.php
│   ├── FinalEvaluationPolicy.php
│   ├── InterviewFeedbackPolicy.php
│   ├── InterviewPolicy.php
│   └── ReportPolicy.php
├── Repositories/
│   ├── AuditLogRepository.php
│   ├── FeedbackGovernanceRepository.php
│   ├── FinalEvaluationRepository.php
│   ├── InterviewAuditRepository.php
│   ├── InterviewFeedbackRepository.php
│   ├── InterviewRepository.php
│   ├── NotificationRepository.php
│   └── ReportRepository.php
└── Services/
    └── FeedbackNormalizationService.php

database/
├── schema.sql
└── migrations/
    └── 012_feedback_governance_analytics.sql

routes/
└── web.php

views/
├── candidate/
│   └── interviews/
│       └── sentiment.php
├── hr/
│   ├── audit-log/
│   │   └── index.php
│   ├── evaluations/
│   │   ├── benchmarks.php
│   │   ├── debrief.php
│   │   ├── flags.php
│   │   ├── governance.php
│   │   └── show.php
│   └── reports/
│       └── feedback-governance.php
└── interviewer/
    └── interviews/
        ├── feedback.php
        └── flag.php
```

**Structure Decision**: Extend the existing interview feedback, final evaluation, report, notification, and audit modules instead of introducing a separate analytics subsystem. Add a focused `FeedbackGovernanceRepository` for new governance tables and a small `FeedbackNormalizationService` because normalization is reusable across report generation, final evaluation, and audit explanation. Keep all user interactions as server-rendered web routes and form submissions.

## Phase 0 Research Summary

Research decisions are documented in [research.md](research.md). All planning unknowns are resolved: normalization uses 5 comparable prior official submissions in 12 months, score scale is normalized consistently from existing 0-10 feedback to 0-100 evaluation outputs, serious concern flags block final decision actions but not remaining feedback, debriefs are in-app records only, benchmarks are HR-maintained, and audit events are retained in governance-specific audit records.

## Phase 1 Design Summary

Data design is documented in [data-model.md](data-model.md). Server-rendered web contracts are documented in [contracts/feedback-governance-web.md](contracts/feedback-governance-web.md). Route/controller/view mapping is documented in [route-map.md](route-map.md). Manual demo and verification steps are documented in [quickstart.md](quickstart.md).

## Post-Design Constitution Check

- [x] Phase 1 design preserves diagram traceability and documents the in-app debrief scope refinement.
- [x] Phase 1 design remains a Vanilla PHP MVC monolith using `routes/web.php` and server-rendered templates.
- [x] Phase 1 design uses MySQL/PDO and plain SQL migration files only.
- [x] Phase 1 design protects candidate feedback, flags, sentiment, normalized scores, recommendations, debrief outcomes, and audit details with role-based policies.
- [x] Phase 1 design avoids REST APIs, separated frontend, SPA, external calendar scheduling, external email delivery, and background workers.
- [x] Phase 1 outputs include testable manual flows and targeted PHP validation points.
- [x] Peer review remains required before implementation tasks begin.

## Complexity Tracking

No constitution violations to justify. The copied template referenced Laravel, Blade, Eloquent, and migrations, but the active constitution supersedes that outdated template language with framework-free Vanilla PHP MVC, PHP templates, PDO repositories, and plain SQL schema files.
