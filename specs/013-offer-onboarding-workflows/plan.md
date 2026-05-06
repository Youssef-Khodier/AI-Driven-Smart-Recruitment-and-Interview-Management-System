# Implementation Plan: Offer Onboarding Workflows

**Branch**: `013-offer-onboarding-workflows` | **Date**: 2026-05-06 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/013-offer-onboarding-workflows/spec.md`

## Summary

Complete the SRIM offer and onboarding vertical slice with governed offer package calculation, versioned digital offer letters, negotiation revisions, manual HR Run Checks for expiry and simulated background checks, referral reward attribution, candidate welcome portal document readiness, RBAC, validation, notifications, and audit history. Delivery extends the existing framework-free Vanilla PHP MVC post-offer files using server-rendered PHP templates, `routes/web.php` browser routes, MySQL via PDO repositories, native sessions, CSRF checks, server-side validation, explicit policies, and plain SQL schema changes.

## Technical Context

**Language/Version**: PHP 8.1+ framework-free Vanilla PHP MVC  
**Primary Dependencies**: PDO, native sessions, CSRF tokens, server-side validation, middleware-style role guards, authorization policies, Tailwind CSS already approved for styling  
**Storage**: MySQL 8+ through PDO; plain SQL schema and migration files; local records for offer rules, offer template versions, generated offer letters, negotiation revisions, manual HR Run Checks, simulated background checks, referral reward attributions, onboarding document items, notifications, and post-offer audit events  
**Testing**: Manual acceptance testing through server-rendered HR and Candidate workflows; targeted PHP syntax checks; policy, repository, and service tests where practical for offer eligibility, duplicate active offers, manual expiry, simulated background-check gates, referral attribution, onboarding documents, RBAC, and audit writes  
**Target Platform**: Server-rendered web application in modern browsers  
**Project Type**: Framework-free Vanilla PHP monolithic MVC web application; no REST API or separated frontend  
**Performance Goals**: HR offer package and letter pages load in under 2 seconds for demo data; HR Run Checks for at least 100 offers/background-check candidates completes in under 3 seconds; candidate offer and welcome portal pages load in under 2 seconds; audit history for at least 100 post-offer events loads in under 2 seconds  
**Constraints**: PHP templates, `routes/web.php`, MySQL, PDO repositories, sessions, CSRF, server-side validation, RBAC policies, in-system notifications, simulated background checks only, manual HR Run Checks only, no scheduler, no background worker dependency, no REST API, no SPA, no external payroll/background-check/email dependency  
**Scale/Scope**: 3-person academic delivery; one reviewable offers-and-onboarding vertical slice aligned to `Diagrams/`; local deterministic compensation and simulated verification workflows only

## Baseline Materials Review

- **SRS / Use Case Trace**: `Diagrams/SRS/SRS-SRIM final ver1.docx` UC-26 Offer Package Calculator, UC-27 Digital Offer-Letter Generator, UC-28 Offer Validity Timer, UC-29 Counter-Offer Negotiation Tracker, UC-30 Referral Reward Attribution, UC-31 Background Check Integration (Simulated), UC-32 Template Versioning Manager, plus nonfunctional RBAC, privacy, retention, and System Audit Trail requirements. The SRS initial-release scope note excluded offer letter generation, but later SRS use cases and diagrams include it; this phase treats offer letters as a later approved module completion.
- **Database / ERD Trace**: Baseline and current tables `users`, `candidates`, `job_requisitions`, `applications`, `final_evaluations`, `offers`, `onboarding`, `notifications`, and existing `post_offer_audit_records`; additions/extensions planned for compensation rules, template versions, generated offer letters, negotiation revisions, manual run-check records, simulated background checks, referral reward attributions, onboarding document items, and expanded audit coverage.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` end-to-end flow from accepted interview performance to offer extended; `Diagrams/Acrivity Diagram/Activity 5.pdf` offer package calculation and HR package review; `Diagrams/Acrivity Diagram/Activity 6.pdf` RBAC dashboard/access boundaries; `Diagrams/Acrivity Diagram/Activity 7.pdf` offer send, validity, candidate signing, HR response review, and manual HR follow-up; `Diagrams/Class Diagram/Class Diagram.drawio` HRStaff, Candidate, Application, OfferPackage, Onboarding; `Diagrams/Object Diagram/Object Diagram.pdf` offer package and onboarding before/after states.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio` maps this feature to Candidate Portal, HR Admin Portal, Auth & RBAC, Offer & Onboarding Module, Notification & Audit, MySQL, and document storage. Implementation extends existing offer/onboarding controllers, repositories, policies, views, notification, and audit modules.
- **Scope Changes**: Manual HR Run Checks explicitly replace automatic offer expiry and automatic background-check execution. Background checks remain simulated and local. Referral reward attribution records eligibility and HR state only; payroll payout is outside this feature. External email, external background-check provider calls, and a scheduler/background worker remain out of scope unless the constitution is amended.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL schema/migration files and PDO-backed repositories.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff/observer where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed because offer, compensation, background-check, referral, and onboarding data is touched.
- [x] Background checks are simulated; offer expiry and background-check processing remain manually triggered through HR Run Checks; no scheduler, job board sync, calendar, external payroll, or external email integration is introduced.
- [x] Acceptance criteria are testable by PHP checks, policy/repository/service tests where practical, or documented server-rendered page demo flows.
- [x] Peer review is scheduled before implementation begins.

## Project Structure

### Documentation (this feature)

```text
specs/013-offer-onboarding-workflows/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── route-map.md
├── contracts/
│   └── offer-onboarding-web.md
└── tasks.md                 # Generated later by /speckit.tasks
```

### Source Code (repository root)

```text
app/
├── Controllers/
│   ├── CandidateOfferController.php
│   ├── CandidateOnboardingController.php
│   ├── HrOfferController.php
│   ├── HrOnboardingController.php
│   └── HrRunChecksController.php
├── Enums/
│   ├── BackgroundCheckOutcome.php
│   ├── BackgroundCheckStatus.php
│   ├── OfferLetterStatus.php
│   ├── OfferNegotiationStatus.php
│   ├── OfferRunCheckType.php
│   ├── OnboardingDocumentStatus.php
│   ├── ReferralRewardStatus.php
│   └── PostOfferAuditAction.php
├── Policies/
│   ├── OfferPolicy.php
│   └── OnboardingPolicy.php
├── Repositories/
│   ├── OfferRepository.php
│   ├── OnboardingRepository.php
│   ├── NotificationRepository.php
│   └── PostOfferAuditRepository.php
└── Services/
    ├── OfferLetterTemplateService.php
    ├── OfferPackageCalculator.php
    └── SimulatedBackgroundCheckService.php

database/
├── schema.sql
└── migrations/
    └── 013_offer_onboarding_workflows.sql

routes/
└── web.php

views/
├── candidate/
│   ├── offers/
│   │   └── show.php
│   └── onboarding/
│       ├── documents.php
│       └── welcome.php
└── hr/
    ├── offers/
    │   ├── form.php
    │   ├── index.php
    │   ├── letter.php
    │   ├── negotiation.php
    │   └── show.php
    ├── onboarding/
    │   ├── documents.php
    │   ├── form.php
    │   ├── index.php
    │   └── show.php
    └── run-checks/
        └── offers.php
```

**Structure Decision**: Extend existing `HrOfferController`, `CandidateOfferController`, `HrOnboardingController`, `OfferRepository`, `OnboardingRepository`, `OfferPolicy`, `OnboardingPolicy`, and `PostOfferAuditRepository` rather than replacing the current post-offer module. Add focused services only where reusable logic is clearer than controller code: compensation calculation, template rendering/version validation, and simulated background-check outcome generation. Add a small HR Run Checks controller/view to remove current page-load expiry side effects and make expiry/background-check operations explicitly manual.

## Phase 0 Research Summary

Research decisions are documented in [research.md](research.md). All planning unknowns are resolved: role-level compensation uses HR-maintained deterministic rules with manual override rationale, offer letters snapshot the approved template version and generated body, negotiations use immutable revisions with one actionable revision, expiry and simulated background checks run only through HR Run Checks, referral rewards record eligibility rather than payroll payout, onboarding documents use checklist metadata and HR review states, and all sensitive post-offer data is protected by existing role policies and audit records.

## Phase 1 Design Summary

Data design is documented in [data-model.md](data-model.md). Server-rendered web contracts are documented in [contracts/offer-onboarding-web.md](contracts/offer-onboarding-web.md). Route/controller/view mapping is documented in [route-map.md](route-map.md). Manual demo and verification steps are documented in [quickstart.md](quickstart.md). No public API or separate frontend contract is introduced.

## Post-Design Constitution Check

- [x] Phase 1 design preserves diagram traceability and documents the later-phase offer-letter scope refinement.
- [x] Phase 1 design remains a Vanilla PHP MVC monolith using `routes/web.php` and server-rendered templates.
- [x] Phase 1 design uses MySQL/PDO and plain SQL migration files only.
- [x] Phase 1 design protects compensation, offer letters, negotiations, simulated background-check outcomes, referral attribution, onboarding documents, and audit details with role-based policies.
- [x] Phase 1 design avoids REST APIs, separated frontend, SPA, external payroll, external background-check providers, external email delivery, schedulers, and background workers.
- [x] Phase 1 outputs include testable manual flows and targeted PHP validation points.
- [x] Peer review remains required before implementation tasks begin.

## Complexity Tracking

No constitution violations to justify. The copied template referenced Laravel, Blade, Eloquent, and migrations, but the active constitution supersedes that outdated template language with framework-free Vanilla PHP MVC, PHP templates, PDO repositories, and plain SQL schema files.
