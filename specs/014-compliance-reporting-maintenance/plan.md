# Implementation Plan: Compliance Reporting Maintenance

**Branch**: `014-compliance-reporting-maintenance` | **Date**: 2026-05-06 | **Spec**: [spec.md](spec.md)  
**Input**: Feature specification from `specs/014-compliance-reporting-maintenance/spec.md`

## Summary

Complete SRIM compliance reporting and operational maintenance by enhancing HR pipeline analytics with bottleneck detection, adding aggregate D&I audit reports from optional demographic fields, adding manual HR Run Checks for escalations and archive eligibility, and preserving auditable archive actions. Delivery extends the existing framework-free Vanilla PHP MVC reports, compliance checks, notifications, data retention, offer/onboarding, and audit surfaces using server-rendered PHP templates, `routes/web.php` browser routes, MySQL via PDO repositories, native sessions, CSRF checks, server-side validation, explicit policies, and plain SQL schema changes.

## Technical Context

**Language/Version**: PHP 8.1+ framework-free Vanilla PHP MVC  
**Primary Dependencies**: PDO, native sessions, CSRF tokens, server-side validation, middleware-style role guards, authorization policies, Tailwind CSS already approved for styling  
**Storage**: MySQL 8+ through PDO; plain SQL schema and migration files; local records for optional demographic fields, report access audit events, HR Run Check batches/findings, escalation notification metadata, archive eligibility decisions, archive actions, and archive status markers on requisition/application records  
**Testing**: Manual acceptance testing through server-rendered HR, Interviewer, Candidate, and notification workflows; targeted PHP syntax checks; policy, repository, and service tests where practical for reports, suppression rules, run-check duplicate prevention, archive eligibility, RBAC, validation, and audit writes  
**Target Platform**: Server-rendered web application in modern browsers  
**Project Type**: Framework-free Vanilla PHP monolithic MVC web application; no REST API or separated frontend  
**Performance Goals**: HR throughput and D&I reports render in under 3 seconds for academic demo data; HR Run Checks for at least 25 flagged records complete in under 5 minutes including review/action; Run Check processing for 100 candidate/application records completes in under 3 seconds; archive views for 100 archived records render in under 3 seconds  
**Constraints**: PHP templates, `routes/web.php`, MySQL, PDO repositories, sessions, CSRF, server-side validation, RBAC policies, in-system notifications only, optional demographic data only, aggregate D&I reporting only, manual HR Run Checks only, no scheduler, no background worker dependency, no REST API, no SPA, no external email/background-check/compliance provider dependency  
**Scale/Scope**: 3-person academic delivery; one reviewable compliance-and-maintenance vertical slice aligned to `Diagrams/`; local deterministic report and maintenance workflows only

## Baseline Materials Review

- **SRS / Use Case Trace**: `Diagrams/SRS/SRS-SRIM final ver1.docx` compliance/reporting baseline plus `Diagrams/document.md` functions 6 Pipeline Throughput Analytics, 37 Data Retention & Privacy, 38 Diversity & Inclusion Audit Reporter, 39 System Audit Trail, 41 Database Integrity Manager, and 42 Automated Notification Escalator. Related offer/onboarding traces include Offer Validity Timer, Background Check Integration (Simulated), and Pre-Onboarding Welcome Portal.
- **Database / ERD Trace**: Baseline and current tables `users`, `departments`, `candidates`, `job_requisitions`, `applications`, `application_status_histories`, `candidate_assessments`, `interviews`, `interviewers_assignment`, `interview_feedback`, `final_evaluations`, `offers`, `onboarding`, `notifications`, `account_audit_records`, `post_offer_audit_records`, and existing feedback/governance audit tables. Additions/extensions planned for candidate demographic fields, compliance run checks, run-check findings, archive actions, and archive status metadata.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` end-to-end application through offer/rejection flow; `Activity 4.pdf` feedback aggregation and HR review; `Activity 6.pdf` role-based dashboard/access boundaries; `Activity 7.pdf` offer expiry/manual HR follow-up; `Diagrams/Class Diagram/Class Diagram.drawio` HRStaff, Candidate, Application, Interview, Feedback, OfferPackage, Onboarding; `Diagrams/Object Diagram/Object Diagram.pdf` application, assessment, offer, and onboarding states.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio` maps this feature to HR Admin Portal, Interviewer Portal, Candidate Portal, Auth & RBAC, Candidate Management, Feedback & Recommendation, Offer & Onboarding, Notification & Escalation, Audit & Compliance, MySQL, and document storage. Implementation extends existing `HrReportController`, `HrComplianceCheckController`, repositories, policies, notifications, and views rather than introducing a new application boundary.
- **Scope Changes**: No baseline feature expansion beyond the explicit request. The only refinement is that all operational automation is manual HR-triggered Run Checks. External email, external background-check providers, external compliance systems, schedulers, background workers, REST APIs, and separated frontends remain out of scope unless the constitution is amended.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL schema/migration files and PDO-backed repositories.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff/observer where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed because reports, demographic fields, feedback, offers, onboarding, and archive data are touched.
- [x] Background checks remain simulated; offer expiry, background-check follow-up, onboarding follow-up, archive eligibility, and missing-feedback reminders remain manually triggered through HR Run Checks; no scheduler, job board sync, calendar, external email, or external provider integration is introduced.
- [x] Acceptance criteria are testable by PHP checks, policy/repository/service tests where practical, or documented server-rendered page demo flows.
- [x] Peer review is scheduled before implementation begins.

## Project Structure

### Documentation (this feature)

```text
specs/014-compliance-reporting-maintenance/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── route-map.md
├── contracts/
│   └── compliance-reporting-web.md
├── checklists/
│   └── requirements.md
└── tasks.md                 # Generated later by /speckit.tasks
```

### Source Code (repository root)

```text
app/
├── Controllers/
│   ├── CandidateController.php
│   ├── HrComplianceCheckController.php
│   ├── HrDataRetentionController.php
│   ├── HrReportController.php
│   └── NotificationController.php
├── Enums/
│   ├── ArchiveActionStatus.php
│   ├── ComplianceAuditAction.php
│   ├── ComplianceRunCheckType.php
│   └── NotificationType.php
├── Policies/
│   ├── DataRetentionPolicy.php
│   ├── NotificationPolicy.php
│   └── ReportPolicy.php
├── Repositories/
│   ├── ComplianceMaintenanceRepository.php
│   ├── DataRetentionRepository.php
│   ├── NotificationRepository.php
│   ├── OfferRepository.php
│   ├── OnboardingRepository.php
│   └── ReportRepository.php
└── Services/
    ├── ArchiveEligibilityService.php
    ├── ComplianceRunCheckService.php
    └── DiversityReportSuppressor.php

database/
├── schema.sql
└── migrations/
    └── 014_compliance_reporting_maintenance.sql

routes/
└── web.php

views/
├── candidate/
│   └── profile.php
├── hr/
│   ├── archive/
│   │   ├── index.php
│   │   └── show.php
│   ├── reports/
│   │   ├── diversity.php
│   │   ├── pipeline.php
│   │   └── time-to-hire.php
│   └── run-checks/
│       ├── index.php
│       └── show.php
└── notifications/
    └── index.php
```

**Structure Decision**: Extend existing `HrReportController`, `HrComplianceCheckController`, `ReportRepository`, `NotificationRepository`, `ReportPolicy`, `DataRetentionRepository`, `OfferRepository`, and `OnboardingRepository` rather than replacing current reports, compliance checks, notifications, data retention, or post-offer flows. Add narrowly scoped services for reusable eligibility, suppression, and run-check orchestration logic where controller/repository code would otherwise become difficult to test. Keep all user interactions as server-rendered forms and pages.

## Phase 0 Research Summary

Research decisions are documented in [research.md](research.md). All planning unknowns are resolved: D&I data is optional and aggregate-only with small-group suppression, bottlenecks use deterministic age/share thresholds, Run Checks remain manual and idempotent, archive actions mark records as archived rather than deleting them, audit events use local audit records, and background-check escalation remains simulated/local.

## Phase 1 Design Summary

Data design is documented in [data-model.md](data-model.md). Server-rendered web contracts are documented in [contracts/compliance-reporting-web.md](contracts/compliance-reporting-web.md). Route/controller/view mapping is documented in [route-map.md](route-map.md). Manual demo and verification steps are documented in [quickstart.md](quickstart.md). No public API or separate frontend contract is introduced.

## Post-Design Constitution Check

- [x] Phase 1 design preserves diagram traceability for reports, compliance, notifications, archive maintenance, feedback, offer, and onboarding flows.
- [x] Phase 1 design remains a Vanilla PHP MVC monolith using `routes/web.php` and server-rendered templates.
- [x] Phase 1 design uses MySQL/PDO and plain SQL migration files only.
- [x] Phase 1 design protects demographic fields, aggregate reports, feedback obligations, offer/onboarding status, archive records, and audit details with role-based policies.
- [x] Phase 1 design avoids REST APIs, separated frontend, SPA, external email, external background-check providers, external compliance systems, schedulers, and background workers.
- [x] Phase 1 outputs include testable manual flows and targeted PHP validation points.
- [x] Peer review remains required before implementation tasks begin.

## Complexity Tracking

No constitution violations to justify.
