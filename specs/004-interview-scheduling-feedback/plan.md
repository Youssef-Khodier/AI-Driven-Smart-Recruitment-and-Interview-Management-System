# Implementation Plan: Interview Scheduling Feedback

**Branch**: `004-interview-scheduling-feedback` | **Date**: 2026-05-04 | **Spec**: [spec.md](./spec.md)  
**Input**: Feature specification from `specs/004-interview-scheduling-feedback/spec.md`

## Summary

Build the SRIM interview scheduling and feedback vertical slice for HR Admin scheduling of `INTERVIEW`-status applications, panel and observer assignment, stored-schedule conflict blocking, assigned interviewer briefing views, completed-interview feedback submission, observer read-only access, and audit traceability for schedule and feedback changes. Delivery targets the framework-free Vanilla PHP monolithic MVC application with server-rendered PHP templates, `routes/web.php` form workflows, PDO-backed persistence, SQL schema updates, policies/guards, native sessions, CSRF, server-side validation, and PHP syntax/test or documented manual evidence.

## Technical Context

**Language/Version**: PHP 8.2+ with no runtime framework dependency  
**Primary Dependencies**: Vanilla PHP MVC, server-rendered PHP templates, PDO, existing router/controller/view core, middleware-style guards, authorization policies, native sessions, CSRF, server-side validation, existing RBAC foundation, existing applications/job/assessment data, and Composer script evidence  
**Storage**: MySQL via `database/schema.sql` and PDO-backed data access; add interview, assignment, feedback, and audit tables aligned with `Diagrams/Database/schema.sql`; no file upload, external document storage, real calendar storage, or email delivery is in scope  
**Testing**: `composer test`, PHP syntax checks, targeted PHP test/manual web-flow evidence for HR and Interviewer/Observer flows, policy checks for assigned-only access, validation checks for scheduling and feedback forms, model/query checks for conflict detection and feedback completion  
**Target Platform**: Server-rendered web application in modern Chrome, Firefox, and Edge browsers  
**Project Type**: Vanilla PHP monolithic MVC web application; no REST API, SPA, separated frontend, mobile-native project, or runtime framework dependency  
**Performance Goals**: HR can schedule a valid interview with one interviewer and one observer in under 3 minutes; 100% of tested overlapping stored-schedule conflicts are blocked before save; assigned interviewers can open their next briefing in under 2 minutes; completed-interview feedback can be submitted in under 3 minutes; HR feedback status is visible immediately after submission during acceptance testing  
**Constraints**: Server-rendered PHP pages, `routes/web.php`, form submissions, redirects, MySQL, sessions, CSRF, server-side validation, active-account guards, role/assignment policies, no machine-facing service contract, no separated frontend, no runtime framework dependency, no external calendar/email/video/live-coding integration, no automated score normalization, no final recommendation automation, no feedback revision workflow  
**Scale/Scope**: 3-person academic delivery; one working interviews-and-feedback vertical slice aligned to baseline SRIM UC-13, UC-14, UC-15, UC-17, and UC-20, sized around 50 interviews per job and a small panel of official interviewers plus observers for acceptance evidence

## Baseline Materials Review

- **SRS / Use Case Trace**: SRS sections 1.2-1.4, 3.2, 3.4, 4, and 5.2-5.3; UC-13 Interviewer Availability Conflict Resolver, UC-14 Multi-Representative Panel Builder, UC-15 Automated Interview Briefing Generator, UC-17 Interviewer Shadowing Logic, UC-19 Interviewer Load Balancer as later-scope context, UC-20 Multi-Dimensional Feedback Aggregator, UC-23 Consensus Meeting Automator as later-scope context, and UC-25 Hiring Recommendation State-Machine as downstream context only.
- **Database / ERD Trace**: `Diagrams/Database/schema.sql`, `schema-erd.svg`, and README baseline entities `applications`, `users`, `candidates`, `job_requisitions`, `assessments`, `candidate_assessments`, `submissions`, `interviews`, `interviewers_assignment`, `interview_feedback`, `final_evaluations`, and `notifications`; implementation schema currently lacks the interview tables and will add them plus `interview_audit_records` for clarified traceability.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` application-to-interview flow; `Activity 3.pdf` briefing generation flow; `Activity 4.pdf` feedback aggregation and HR review flow; `Activity 6.pdf` login, role detection, and assigned dashboard access; `Diagrams/Class Diagram/Class Diagram.drawio` TechnicalInterviewer, Interview, Feedback, Application, Candidate, Assessment, and HRStaff concepts; `Diagrams/Object Diagram/Object Diagram.pdf` scheduled interview and feedback example.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio` maps this feature to HR Admin Portal, Interviewer Portal, Auth & RBAC, Interview Module, Feedback & Recommendation Module, Notification & Audit, and MySQL Database inside the single SRIM platform.
- **Scope Changes**: None requiring constitution amendment. External Google Calendar, SMTP/email, live coding sync, automatic load balancing, consensus meeting automation, score normalization, and final hiring recommendations remain deferred or simulated as explicitly out of scope.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL schema files and PDO-backed models/repositories.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff/observer where relevant.
- [x] Candidate privacy, feedback privacy, and audit-relevant schedule/feedback changes are addressed.
- [x] AI, proctoring, background checks, job board sync, calendar, and email are out of scope or simulated unless explicitly added later.
- [x] Acceptance criteria are testable by PHP tests or documented server-rendered page demo flows.
- [x] Peer review is required before implementation begins.

Gate result: PASS. No constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/004-interview-scheduling-feedback/
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
├── Controllers/
│   ├── HrInterviewController.php          # New HR scheduling, reschedule, cancel, complete, review feedback
│   └── InterviewerInterviewController.php # New assigned list, briefing, feedback form/submit
├── Enums/
│   ├── InterviewAssignmentRole.php        # New panel lead, interviewer, observer
│   ├── InterviewAuditAction.php           # New schedule, reschedule, cancel, complete, feedback submit
│   ├── InterviewStatus.php                # New scheduled, completed, cancelled
│   └── UserRole.php                       # Existing; add JUNIOR_STAFF for baseline observer actor
├── Policies/
│   ├── InterviewPolicy.php                # New HR and assigned-panel access rules
│   └── InterviewFeedbackPolicy.php        # New official feedback and observer denial rules
├── Repositories/
│   ├── InterviewAuditRepository.php       # New audit persistence
│   ├── InterviewFeedbackRepository.php    # New feedback persistence and completion queries
│   └── InterviewRepository.php            # New scheduling, conflict, briefing, and assignment queries
└── Core/
    └── Validator.php                      # Existing validation helper used by new controllers

database/
└── schema.sql                             # Add interview tables and seed/demo-safe constraints

views/
├── hr/
│   └── interviews/
│       ├── form.php
│       ├── index.php
│       └── show.php
└── interviewer/
    └── interviews/
        ├── feedback.php
        ├── index.php
        └── show.php

routes/
└── web.php                                # Add HR and interviewer browser/form routes

scripts/
└── check.php                              # Existing Composer test target; extend as needed for feature checks
```

**Structure Decision**: Extend the existing Vanilla PHP MVC app at the repository root. Keep HR scheduling pages under `views/hr/interviews`, interviewer/observer pages under `views/interviewer/interviews`, browser flows in `routes/web.php`, and persistence logic in small PDO repositories because this feature requires repeated conflict, assignment, briefing, and feedback-completion queries. Do not introduce `routes/api.php`, public JSON contracts, a separated frontend, framework schema tooling, or runtime framework dependencies.

## Phase 0: Research Summary

Research output: [research.md](./research.md)

Resolved decisions:

- Use only server-rendered page and form workflows for scheduling, assignment, briefing, status transitions, and feedback.
- Model interview eligibility as `applications.status = INTERVIEW` only.
- Use a hard block for conflicts where a new time interval overlaps any non-cancelled interview for the same application or any selected panel member.
- Store assignments separately from interviews and enforce one assignment per user per interview.
- Compute briefing content from existing application, candidate, job, assessment attempt, and submission data rather than storing generated briefing files.
- Allow official feedback only after interview status is `COMPLETED` and only once per official interviewer or panel lead.
- Treat Junior Staff and observer assignments as read-only participants whose entries never count toward official feedback completion.
- Block rescheduling once any official feedback exists for the interview.
- Store audit records for schedule, reschedule, cancel, complete, and feedback submit actions with actor, action, timestamp, and changed fields.

## Phase 1: Design Summary

Design outputs:

- [data-model.md](./data-model.md)
- [contracts/web-workflows.md](./contracts/web-workflows.md)
- [route-map.md](./route-map.md)
- [quickstart.md](./quickstart.md)

Implementation boundaries:

- HR Admins schedule, reschedule, cancel, complete, and review interviews for `INTERVIEW` applications.
- HR Admins assign official interviewers, panel leads, and observers; at least one official scorer is required.
- Stored conflicts are checked for the application and all selected panel users before save.
- Assigned official interviewers and observers can view only their own assigned interviews and briefing pages.
- Briefing pages show partial-data notices when resumes, assessment attempts, scores, or submitted answers are missing.
- Official feedback records include technical, communication, culture fit, overall scores, comments, author, and submitted time.
- Feedback does not change application status, final evaluation, offers, or recommendations in this feature.

## Post-Design Constitution Check

- [x] Design artifacts preserve diagram traceability and baseline entities.
- [x] Web workflow contracts are server-rendered page/form contracts, not REST API contracts.
- [x] Data model uses MySQL/PDO-compatible entities and relationships.
- [x] RBAC and candidate privacy are represented in route contracts, policies, model ownership rules, and validation expectations.
- [x] Audit-relevant schedule, status, assignment, and feedback changes are included.
- [x] External calendar/email/live-coding integrations remain out of scope.
- [x] Quickstart includes test/demo evidence before implementation is considered complete.

Post-design gate result: PASS. No constitution violations.

## Complexity Tracking

No constitution violations or complexity exceptions are required.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

## Phase 2 Preview

Task generation should produce small, independently reviewable tasks for SQL schema updates, enums/repositories/policies, HR interview scheduling and status routes, conflict validation, panel assignment handling, interviewer and observer assigned lists, briefing views, completed-interview feedback forms, feedback completion indicators, audit records, PHP syntax/test checks, and documented manual demo evidence. Do not start implementation until `/speckit.tasks` creates the task list and peer review is complete.
