# Implementation Plan: Final Offer Onboarding

**Branch**: `005-final-offer-onboarding` | **Date**: 2026-05-05 | **Spec**: [spec.md](./spec.md)  
**Input**: Feature specification from `specs/005-final-offer-onboarding/spec.md`

## Summary

Build the SRIM final evaluation, offer, and onboarding vertical slice for HR Admin aggregation of assessment and interview evidence into a final recommendation, offer package creation and one-time replacement after rejection/expiry, candidate self-service offer accept/reject before expiry, application status transitions, accepted-offer onboarding creation, and audit traceability. Delivery targets the framework-free Vanilla PHP monolithic MVC application with server-rendered PHP templates, `routes/web.php` browser/form workflows, PDO-backed persistence, SQL schema updates, policies/guards, native sessions, CSRF, server-side validation, and PHP syntax/test or documented manual evidence.

## Technical Context

**Language/Version**: PHP 8.2+ with no runtime framework dependency  
**Primary Dependencies**: Vanilla PHP MVC, server-rendered PHP templates, PDO, existing router/controller/view core, middleware-style guards, authorization policies, native sessions, CSRF, server-side validation, existing RBAC foundation, existing applications/job/assessment/interview/feedback data, and Composer script evidence  
**Storage**: MySQL via `database/schema.sql` and PDO-backed data access; add final evaluation, offer, onboarding, and audit persistence aligned with `Diagrams/Database/schema.sql`, with a documented multiplicity adjustment for the clarified one replacement offer; no external document storage, real email delivery, e-signature provider, background-check provider, or public offer API is in scope  
**Testing**: `composer test`, PHP syntax checks, targeted PHP test/manual web-flow evidence for HR final evaluation/offer/onboarding flows and candidate offer response flows, policy checks for candidate-owned offer access, validation checks for score aggregation, offer expiry, one-active-offer enforcement, and onboarding eligibility  
**Target Platform**: Server-rendered web application in modern Chrome, Firefox, and Edge browsers  
**Project Type**: Vanilla PHP monolithic MVC web application; no REST API, SPA, separated frontend, mobile-native project, or runtime framework dependency  
**Performance Goals**: HR can review evidence and record a final recommendation in under 4 minutes; HR can create and send a complete offer in under 3 minutes; candidate can accept or reject a sent offer in under 2 minutes; expired sent offers are blocked from acceptance in 100% of tested cases; HR can create onboarding for an accepted offer in under 2 minutes  
**Constraints**: Server-rendered PHP pages, `routes/web.php`, form submissions, redirects, MySQL, sessions, CSRF, server-side validation, active-account guards, role/ownership policies, no machine-facing service contract, no separated frontend, no runtime framework dependency, no real email/e-signature/background-check integration, no advanced score normalization, no unlimited counter-offer workflow  
**Scale/Scope**: 3-person academic delivery; one working final-evaluation-to-onboarding vertical slice aligned to baseline SRIM UC-20, UC-25, UC-26, UC-28, and limited UC-29 replacement-offer context, sized around 50 offer-eligible applications per job and one original plus one replacement offer per application for acceptance evidence

## Baseline Materials Review

- **SRS / Use Case Trace**: SRS sections 1.2-1.4, 3.2-3.5, 4, and 5.2-5.5; UC-20 Multi-Dimensional Feedback Aggregator, UC-22 Candidate Red-Flag Escalation as review context, UC-25 Hiring Recommendation State-Machine, UC-26 Offer Package Calculator, UC-27 Digital Offer-Letter Generator as simulated context, UC-28 Offer Validity Timer, UC-29 Counter-Offer Negotiation Tracker as limited replacement-offer context, UC-31 Background Check Integration as later simulated context, and UC-32 Template Versioning Manager as later-scope context.
- **Database / ERD Trace**: `Diagrams/Database/schema.sql`, `schema-erd.svg`, and README baseline entities `applications`, `users`, `candidates`, `job_requisitions`, `assessments`, `candidate_assessments`, `submissions`, `interviews`, `interview_feedback`, `final_evaluations`, `offers`, `onboarding`, and `notifications`; implementation schema currently lacks final evaluation, offer, and onboarding tables and will add them plus post-offer audit records.
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` interview-to-offer flow; `Activity 4.pdf` feedback aggregation and HR recommendation review flow; `Activity 5.pdf` offer package calculation/review flow; `Activity 6.pdf` login, role detection, and permission application; `Activity 7.pdf` offer send, validity timer, candidate signing decision, and HR review flow; `Diagrams/Class Diagram/Class Diagram.drawio` HRStaff, Application, Assessment, Feedback, OfferPackage, Onboarding, Candidate, and User concepts; `Diagrams/Object Diagram/Object Diagram.pdf` offer/onboarding example.
- **Architecture Trace**: `Diagrams/System Architecture/system-architecture.drawio` maps this feature to Candidate Portal, HR Admin Portal, Auth & RBAC, Feedback & Recommendation Module, Offer & Onboarding Module, Notification & Audit, and MySQL Database inside the single SRIM platform.
- **Scope Changes**: No constitution amendment is required. The clarified one replacement offer adjusts the baseline ERD's one-to-one application-offer relationship to one application with at most one active offer and at most one replacement offer, preserving the baseline `OfferPackage` entity and using UC-29 as limited context. Real email, legal e-signature, background checks, referral rewards, unlimited negotiations, and template versioning remain deferred or simulated.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL schema files and PDO-backed models/repositories.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff/observer where relevant.
- [x] Candidate privacy, final evaluation privacy, compensation privacy, onboarding privacy, and audit-relevant changes are addressed.
- [x] AI, proctoring, background checks, job board sync, calendar, and email are out of scope or simulated unless explicitly added later.
- [x] Acceptance criteria are testable by PHP tests or documented server-rendered page demo flows.
- [x] Peer review is required before implementation begins.

Gate result: PASS. No constitution violations.

## Project Structure

### Documentation (this feature)

```text
specs/005-final-offer-onboarding/
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
│   ├── HrFinalEvaluationController.php     # New HR evidence aggregation and final recommendation workflows
│   ├── HrOfferController.php               # New HR offer draft/send/replacement/status tracking workflows
│   ├── CandidateOfferController.php        # New candidate-owned offer view and accept/reject workflows
│   └── HrOnboardingController.php          # New accepted-offer onboarding workflows
├── Enums/
│   ├── FinalEvaluationRecommendation.php   # New strong hire, hire, no hire, strong no hire
│   ├── FinalEvaluationStatus.php           # New evaluated/completed-style decision state
│   ├── OfferStatus.php                     # New draft, sent, accepted, rejected, expired
│   ├── OfferType.php                       # New full-time, contract, intern
│   ├── OnboardingStatus.php                # New pending, in progress, completed
│   └── PostOfferAuditAction.php            # New evaluation, offer, response, expiry, onboarding actions
├── Policies/
│   ├── FinalEvaluationPolicy.php           # New HR-only decision rules
│   ├── OfferPolicy.php                     # New HR offer management and candidate-owned response rules
│   └── OnboardingPolicy.php                # New HR-only accepted-offer onboarding rules
├── Repositories/
│   ├── FinalEvaluationRepository.php       # New evidence aggregation and decision persistence
│   ├── OfferRepository.php                 # New offer lifecycle, expiry, active/replacement checks
│   ├── OnboardingRepository.php            # New onboarding persistence and duplicate checks
│   └── PostOfferAuditRepository.php        # New audit persistence
└── Core/
    └── Validator.php                       # Existing validation helper used by new controllers

database/
└── schema.sql                              # Add final evaluation, offer, onboarding, and post-offer audit tables/constraints

views/
├── candidate/
│   └── offers/
│       └── show.php
└── hr/
    ├── evaluations/
    │   └── show.php
    ├── offers/
    │   ├── form.php
    │   ├── index.php
    │   └── show.php
    └── onboarding/
        ├── form.php
        ├── index.php
        └── show.php

routes/
└── web.php                                 # Add HR and candidate browser/form routes

scripts/
└── check.php                               # Existing Composer test target; extend as needed for feature checks
```

**Structure Decision**: Extend the existing Vanilla PHP MVC app at the repository root. Keep HR decision, offer, and onboarding pages under `views/hr`, candidate offer response pages under `views/candidate/offers`, browser flows in `routes/web.php`, and persistence logic in small PDO repositories because this feature needs repeated evidence aggregation, status-transition, replacement-offer, expiry, and ownership checks. Do not introduce `routes/api.php`, public JSON contracts, a separated frontend, framework schema tooling, or runtime framework dependencies.

## Phase 0: Research Summary

Research output: [research.md](./research.md)

Resolved decisions:

- Use only server-rendered page and form workflows for final evaluation, offer management, candidate offer response, and onboarding.
- Compute aggregate final evaluation score as an equal normalized average of assessment score evidence and interview overall score evidence; use available evidence and flag partial evidence when one side is missing.
- Treat `Strong Hire` and `Hire` as offer-eligible recommendations, and `No Hire` and `Strong No Hire` as rejection recommendations.
- Enforce application status transitions: no-hire recommendations set `REJECTED`, sent offers set `OFFER`, accepted offers set `HIRED`, and rejected/expired offers set `REJECTED`.
- Allow one original offer plus at most one replacement after rejection or expiry, with only one active draft/sent offer at a time.
- Capture candidate acceptance or rejection through the candidate portal for the candidate's own unexpired sent offer.
- Evaluate offer expiry on HR/candidate offer views and status-changing form submissions; no always-on background worker is required for this academic slice.
- Create onboarding only after accepted offers, one onboarding record per accepted offer.
- Store audit records for final evaluation save, offer create/send/replacement, candidate response, expiry, and onboarding create/update actions with actor, action, timestamp, and changed fields.

## Phase 1: Design Summary

Design outputs:

- [data-model.md](./data-model.md)
- [contracts/web-workflows.md](./contracts/web-workflows.md)
- [route-map.md](./route-map.md)
- [quickstart.md](./quickstart.md)

Implementation boundaries:

- HR Admins review evidence and record one final evaluation per application.
- Final evaluation evidence is read from existing application, candidate, job, assessment attempt, interview, and feedback data.
- HR Admins can draft and send offers only for offer-eligible recommendations, with at most one active offer and one replacement after rejection/expiry.
- Candidates can view and accept/reject only their own unexpired sent offers; they cannot view internal evaluation notes or other candidates' compensation.
- Offer expiry is enforced before candidate response and HR onboarding actions.
- HR Admins create and update onboarding only after offer acceptance.
- All final evaluation, offer, candidate response, expiry, and onboarding changes are auditable.

## Post-Design Constitution Check

- [x] Design artifacts preserve diagram traceability and baseline entities, with the replacement-offer multiplicity adjustment documented.
- [x] Web workflow contracts are server-rendered page/form contracts, not REST API contracts.
- [x] Data model uses MySQL/PDO-compatible entities and relationships.
- [x] RBAC and candidate privacy are represented in route contracts, policies, model ownership rules, and validation expectations.
- [x] Audit-relevant final evaluation, offer, response, expiry, and onboarding changes are included.
- [x] External email/e-signature/background-check integrations remain out of scope or simulated.
- [x] Quickstart includes test/demo evidence before implementation is considered complete.

Post-design gate result: PASS. No constitution violations.

## Complexity Tracking

No constitution violations or complexity exceptions are required.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

## Phase 2 Preview

Task generation should produce small, independently reviewable tasks for SQL schema updates, enums/repositories/policies, HR final evaluation pages and routes, evidence aggregation, application status histories, HR offer draft/send/replacement/status routes, candidate offer view and accept/reject routes, expiry enforcement, HR onboarding routes, post-offer audit records, PHP syntax/test checks, and documented manual demo evidence. Do not start implementation until `/speckit.tasks` creates the task list and peer review is complete.
