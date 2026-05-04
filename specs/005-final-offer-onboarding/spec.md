# Feature Specification: Final Offer Onboarding

**Feature Branch**: `005-final-offer-onboarding`  
**Created**: 2026-05-04  
**Status**: Draft  
**Input**: User description: "Build final evaluation, offer, and onboarding workflows. HR admins can aggregate assessment and interview feedback into a final recommendation, create offer packages, track offer status and expiry, and create onboarding records after accepted offers."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.docx` sections 1.2-1.4, 3.2-3.5, 4, and 5.2-5.5; `Diagrams/document.md`; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 2.pdf`; `Diagrams/Acrivity Diagram/Activity 3.pdf`; `Diagrams/Acrivity Diagram/Activity 4.pdf`; `Diagrams/Acrivity Diagram/Activity 5.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Acrivity Diagram/Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`; `.specify/memory/constitution.md`; `specs/004-interview-scheduling-feedback/plan.md`.
- **SRS / Use Case IDs**: UC-20 Multi-Dimensional Feedback Aggregator, UC-22 Candidate Red-Flag Escalation as review context, UC-25 Hiring Recommendation State-Machine, UC-26 Offer Package Calculator, UC-27 Digital Offer-Letter Generator as simulated document context only, UC-28 Offer Validity Timer, UC-29 Counter-Offer Negotiation Tracker as later-scope context only, UC-31 Background Check Integration as later simulated context only, UC-32 Template Versioning Manager as later-scope context only.
- **Baseline Entities**: `applications`, `candidates`, `users`, `job_requisitions`, `assessments`, `candidate_assessments`, `submissions`, `interviews`, `interview_feedback`, `final_evaluations`, `offers`, `onboarding`, and `notifications` for status visibility context.
- **Baseline Workflow**: Candidate applies, completes assessments, completes interviews, interviewers submit feedback, HR reviews aggregated evidence, HR records a final recommendation, HR creates and sends an offer for hire recommendations, offer status is tracked through decision and expiry, and HR creates onboarding after an offer is accepted.
- **Scope Decision**: Matches the baseline offer-and-onboarding phase with a Vanilla PHP monolith delivery constraint. This feature allows one replacement offer after a rejected or expired offer while still allowing only one active offer at a time. External email delivery, legal e-signature, background-check integration, counter-offer approvals beyond that single replacement, referral rewards, score normalization, and automated consensus meetings are out of scope unless a later specification adds them.

## Clarifications

### Session 2026-05-05

- Q: How should the aggregate evaluation score be calculated? → A: Equal normalized average of assessment score and interview overall score; if one side is missing, use available evidence and flag partial evidence.
- Q: Which application status transitions should this feature enforce? → A: `No Hire`/`Strong No Hire` -> `REJECTED`; sent offer -> `OFFER`; accepted offer -> `HIRED`; rejected/expired offer -> `REJECTED`.
- Q: Should HR be able to create a replacement offer after an offer is rejected or expired? → A: Allow one replacement offer after rejection or expiry, while keeping only one active offer at a time.
- Q: How should candidate offer responses be captured in this feature? → A: Candidate can accept or reject their own offer from the candidate portal.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates.
- **Routing**: Browser routes and form submissions only; no machine-facing service contract or separated frontend.
- **Data Access**: MySQL through PDO-backed models or repositories and plain SQL schema files.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and authorization policies.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Record Final Evaluation (Priority: P1)

An HR Admin opens an application that has completed assessment and interview evidence, reviews the aggregated scores, missing-feedback warnings, submitted comments, and any red-flag indicators, then records the final hiring recommendation.

**Why this priority**: The final evaluation is the decision point that turns assessment and interview evidence into a clear hiring outcome.

**Independent Test**: Can be fully tested by using an application with completed assessment and interview feedback, recording a final recommendation, and verifying the application is ready for offer or rejection follow-up.

**Acceptance Scenarios**:

1. **Given** an application has assessment scores and submitted interview feedback, **When** an HR Admin opens the final evaluation view, **Then** the system shows the available evidence, an aggregate score, feedback completeness state, and recommendation options.
2. **Given** an HR Admin selects `Hire` or `Strong Hire`, **When** they save the final evaluation with required decision notes, **Then** the system records the recommendation and marks the application as eligible for offer creation.
3. **Given** required interview feedback is missing, **When** an HR Admin reviews the final evaluation, **Then** the system flags the missing evidence and requires HR to acknowledge the partial evidence before saving a final decision.

---

### User Story 2 - Create Offer Package (Priority: P2)

An HR Admin creates an offer package for an application with a hire recommendation, enters compensation details and offer validity, reviews the package, and moves it from draft to sent when ready.

**Why this priority**: Offer creation is the primary downstream action after a positive final recommendation and is required before onboarding can begin.

**Independent Test**: Can be tested by creating a complete offer for an application with a hire recommendation and verifying the offer is linked to that application with the expected draft or sent status.

**Acceptance Scenarios**:

1. **Given** an application has a final recommendation of `Hire` or `Strong Hire` and no existing offer, **When** an HR Admin submits valid offer details, **Then** the system creates a draft offer package for that application.
2. **Given** an HR Admin enters a negative compensation amount or an expiry date that is not in the future, **When** they submit the offer package, **Then** the system rejects the package with clear correction messages.
3. **Given** a draft offer package is complete, **When** an HR Admin marks it as sent, **Then** the system records the sent status, sent time, and expiry deadline.

---

### User Story 3 - Track Offer Status and Expiry (Priority: P3)

An HR Admin monitors sent offers while candidates accept or reject their own unexpired offers from the candidate portal, and HR can identify offers that are accepted, rejected, expired, or still awaiting response.

**Why this priority**: HR must know which offers need follow-up and which accepted offers are ready for onboarding.

**Independent Test**: Can be tested by logging in as the candidate for a sent offer, accepting or rejecting it before expiry, and verifying the response state is visible in the HR workflow.

**Acceptance Scenarios**:

1. **Given** a sent offer has not reached its expiry deadline, **When** the candidate accepts the offer from their own candidate portal, **Then** the offer is marked accepted with an accepted time and becomes eligible for onboarding.
2. **Given** a sent offer has not reached its expiry deadline, **When** the candidate rejects the offer from their own candidate portal, **Then** the offer is marked rejected and onboarding remains unavailable.
3. **Given** a sent offer is past its expiry deadline and has no accepted or rejected response, **When** HR opens or refreshes offer tracking, **Then** the offer is shown as expired and cannot be accepted without a new or revised offer.

---

### User Story 4 - Create Onboarding Record (Priority: P4)

An HR Admin creates an onboarding record only after an offer is accepted, sets a planned start date, and tracks whether onboarding is pending, in progress, or completed.

**Why this priority**: Onboarding completes the recruitment lifecycle after an accepted offer and provides HR with a handoff record for day-one readiness.

**Independent Test**: Can be tested by accepting an offer, creating one onboarding record, and verifying onboarding status and document-completion tracking are visible to HR.

**Acceptance Scenarios**:

1. **Given** an offer is accepted and has no onboarding record, **When** an HR Admin enters a valid start date, **Then** the system creates an onboarding record with pending status.
2. **Given** an offer is not accepted, **When** an HR Admin attempts to create onboarding, **Then** the system blocks onboarding creation and explains that only accepted offers can be onboarded.
3. **Given** onboarding exists for an accepted offer, **When** HR updates its status or document-completion state, **Then** the system shows the latest onboarding progress without creating a duplicate record.

---

### Edge Cases

- HR attempts to create a final evaluation before any assessment or interview evidence exists.
- HR records a final recommendation while required interviewer feedback is incomplete.
- HR attempts to create a second final evaluation for the same application.
- HR attempts to create an offer for a `No Hire`, `Strong No Hire`, ineligible, actively offered, or already-replaced application.
- Offer compensation values are missing, negative, malformed, or inconsistent with the selected offer type.
- Offer expiry is missing for a sent offer, in the past, or earlier than the sent time.
- A sent offer expires before the candidate records a response.
- HR attempts to create onboarding from an expired, rejected, or draft offer.
- A candidate attempts to accept or reject another candidate's offer.
- A candidate attempts to accept or reject their own offer after expiry or after a response was already recorded.
- HR attempts to create duplicate onboarding records for the same accepted offer.
- Candidate personal data, assessment results, interview feedback, final decisions, offer details, and onboarding data must remain hidden from unauthorized roles.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow HR Admins to review assessment evidence, interview feedback, feedback completeness, and red-flag indicators for an application before recording a final evaluation.
- **FR-002**: System MUST calculate and display an aggregate evaluation score by normalizing assessment scores and interview overall scores to a common 0-100 scale, averaging assessment evidence and interview evidence equally, and clearly identifying any missing evidence included in the evaluation context.
- **FR-003**: System MUST allow HR Admins to record exactly one final evaluation per application with an aggregate score, recommendation, decision notes, status, evaluator, and recorded time.
- **FR-004**: System MUST support final recommendation states of `Strong Hire`, `Hire`, `No Hire`, and `Strong No Hire`.
- **FR-005**: System MUST require HR Admin acknowledgement before saving a final evaluation when expected interview feedback is incomplete.
- **FR-006**: System MUST mark applications with `Hire` or `Strong Hire` final recommendations as eligible for offer creation and mark `No Hire` or `Strong No Hire` recommendations as ineligible for offer creation.
- **FR-007**: System MUST allow HR Admins to create an offer package only when the application has an offer-eligible final recommendation, has no existing active offer, and has not already used its one allowed replacement after a rejected or expired offer.
- **FR-008**: System MUST require each offer package to include offer type, compensation amount, optional bonus, optional stock or equity value, status, and expiry deadline before it can be sent.
- **FR-009**: System MUST validate offer package input, including required fields, non-negative compensation values, allowed offer status, and future expiry for sent offers.
- **FR-010**: System MUST allow HR Admins to save an offer as draft, mark a complete draft as sent, and record the sent time and expiry deadline when sent.
- **FR-011**: System MUST show HR Admins offer status values of draft, sent, accepted, rejected, and expired, including the relevant sent, accepted, rejected, or expired timing where available.
- **FR-012**: System MUST prevent accepted offers from being changed back to draft or sent, and MUST allow at most one replacement offer after a rejected or expired offer while preserving the prior offer history.
- **FR-013**: System MUST mark sent offers as expired when the expiry deadline has passed and no accepted or rejected response has been recorded.
- **FR-014**: System MUST allow the candidate who owns the application to accept or reject their own unexpired sent offer and preserve the response time.
- **FR-015**: System MUST update the related application status so `No Hire` and `Strong No Hire` final recommendations set the application to `REJECTED`, sent offers set it to `OFFER`, accepted offers set it to `HIRED`, and rejected or expired offers set it to `REJECTED`.
- **FR-016**: System MUST allow HR Admins to create exactly one onboarding record for an accepted offer.
- **FR-017**: System MUST prevent onboarding creation for draft, sent, rejected, or expired offers.
- **FR-018**: System MUST allow HR Admins to set onboarding start date, onboarding status, and document-completion state.
- **FR-019**: System MUST validate onboarding input, including accepted offer eligibility, non-duplicate onboarding, allowed onboarding status, and valid start date.
- **FR-020**: System MUST preserve traceability for final evaluation decisions, offer status changes, expiry changes, accepted or rejected responses, and onboarding status changes with actor, action, timestamp, and changed fields.
- **FR-021**: System MUST keep score normalization, counter-offer negotiations, legal e-signature, real email delivery, background checks, referral rewards, and automated onboarding tasks outside this feature scope.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST be limited to reviewing recruitment evidence, recording final evaluations, managing offers, tracking offer responses and expiry, and creating onboarding records for applications in the recruitment workflow.
- **RP-002**: Technical Interviewer access MUST NOT include final evaluation editing, offer package details, compensation data, or onboarding management unless a later approved scope grants it.
- **RP-003**: Candidate access MUST be limited to their own application status and their own sent offer response action; candidates MUST NOT see interviewer-only comments, internal recommendation notes, compensation for other candidates, or other candidates' offers.
- **RP-004**: Junior Staff or observer access MUST NOT include final evaluations, offers, compensation details, or onboarding records.
- **RP-005**: Candidate PII, assessment scores, interview feedback, final evaluation notes, compensation details, offer status, and onboarding records MUST be hidden from unauthorized roles.
- **RP-006**: Final recommendation changes, offer status changes, compensation changes, expiry changes, and onboarding status changes MUST remain traceable for HR audit review.

### Key Entities *(include if feature involves data)*

- **Application**: Candidate's application for a job; links candidate, job, assessment evidence, interviews, final evaluation, offer, and downstream onboarding eligibility.
- **Assessment Evidence**: Existing assessment attempt, score, and submission summary used as part of final evaluation evidence.
- **Interview Feedback**: Structured interviewer scores and comments used as part of final evaluation evidence.
- **Final Evaluation**: HR-owned hiring decision for an application; includes aggregate score, recommendation, decision notes, status, evaluator, and recorded time.
- **Offer Package**: Compensation and employment proposal linked to an offer-eligible application; includes type, compensation components, status, sent time, response time, expiry deadline, and whether it is the original or the single allowed replacement.
- **Onboarding Record**: Post-acceptance handoff record linked to an accepted offer; includes start date, onboarding status, and document-completion state.
- **User**: Authenticated HR Admin, Technical Interviewer, Candidate, or observer-capable staff account governed by role permissions.
- **Audit Record**: Traceability record for final evaluation, offer, and onboarding changes; includes actor, action, timestamp, and changed fields.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can review evidence and record a final recommendation for one application in under 4 minutes during acceptance testing.
- **SC-002**: 100% of tested applications with `Hire` or `Strong Hire` recommendations are eligible for offer creation, and 100% of tested `No Hire` or `Strong No Hire` applications are blocked from offer creation.
- **SC-003**: HR Admins can create and send a complete offer package in under 3 minutes after a hire recommendation is saved.
- **SC-004**: 100% of tested sent offers past their expiry deadline are shown as expired before the candidate can record an acceptance.
- **SC-005**: HR Admins can create an onboarding record for an accepted offer in under 2 minutes, and duplicate onboarding attempts are blocked in 100% of tested cases.
- **SC-006**: At least 90% of demo reviewers can identify each candidate's current post-interview state, final recommendation, offer status, and onboarding state without asking the implementation team for explanation.
- **SC-007**: 100% of tested unauthorized attempts by candidates, interviewers, observers, inactive users, or unauthenticated users to access restricted final evaluation, compensation, or onboarding data are denied.

## Assumptions

- Applications become eligible for final evaluation after the prior assessment and interview workflow has produced at least one assessment score or one submitted interview feedback record.
- Final evaluation uses an equal normalized average of assessment evidence and interview overall score evidence; interviewer-harshness adjustment and advanced score normalization are deferred.
- HR Admins make the final recommendation and may proceed on partial evidence after explicit acknowledgement, because missing feedback can occur in the baseline workflow.
- Offer letter generation, signing links, email delivery, legal template management, and background checks are simulated or handled outside this feature for the academic demo.
- Candidate offer acceptance or rejection is captured through the candidate portal for the candidate's own unexpired sent offer.
- A sent offer expires based on its recorded expiry deadline when HR opens or reviews offer tracking; always-on background processing is not required for this specification.
- Onboarding starts only after an offer is accepted; onboarding task checklists beyond start date, status, and document-completion state are deferred.
