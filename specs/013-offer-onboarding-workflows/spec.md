# Feature Specification: Offer Onboarding Workflows

**Feature Branch**: `013-offer-onboarding-workflows`  
**Created**: 2026-05-06  
**Status**: Draft  
**Input**: User description: "Complete offer and onboarding workflows in server-rendered Vanilla PHP. HR admins can calculate offer packages using role level, base salary, bonus, and stock rules; generate a versioned digital offer letter from an approved template; track offer expiry and negotiation revisions; attribute referral rewards; run simulated background checks; and provide candidates with a pre-onboarding welcome portal for day-one documents. Offer expiry and background checks remain manually triggered through HR Run Checks unless a constitution-approved scheduler is added."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.docx` UC-26 through UC-32, Section 1.4 initial scope note, and nonfunctional RBAC/privacy/audit sections; `Diagrams/document.md` functions 29 through 35 and functions 36 through 40; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 5.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Acrivity Diagram/Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`; current `specs/012-feedback-governance-analytics/plan.md`; `.specify/memory/constitution.md`.
- **SRS / Use Case IDs**: UC-26 Offer Package Calculator, UC-27 Digital Offer-Letter Generator, UC-28 Offer Validity Timer, UC-29 Counter-Offer Negotiation Tracker, UC-30 Referral Reward Attribution, UC-31 Background Check Integration (Simulated), UC-32 Template Versioning Manager, plus Role-Based Access Control, System Audit Trail, Data Retention and Right to be Forgotten.
- **Baseline Entities**: `users`, `candidates`, `job_requisitions`, `applications`, `final_evaluations`, `offers`, `onboarding`, `notifications`, offer template/version records, negotiation revision records, referral reward records, simulated background check records, onboarding document records, and audit events.
- **Baseline Workflow**: Extends the post-evaluation flow where HR marks a candidate as Hire or Strong Hire, extends an offer, calculates compensation, generates an offer record and digital letter, tracks acceptance or expiry, runs background verification, and moves the candidate into onboarding with day-one document preparation.
- **Scope Decision**: Implements the baseline Offers & Onboarding scope and the user-requested manual HR Run Checks refinement. The SRS Section 1.4 note listed offer letter generation as out of scope for the initial release, but later SRS use cases, diagrams, and the user request explicitly include offer letters, offer validity, referral attribution, background checks, template versioning, and onboarding. This specification treats the feature as a team-approved later-phase completion of that module. Offer expiry and simulated background checks are not automatic scheduler behavior; they are manually triggered by HR through HR Run Checks unless the constitution is amended to approve a scheduler.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates.
- **Routing**: Browser web routes and form submissions only; no REST API contract or separated frontend.
- **Data Access**: MySQL through PDO-backed repositories and plain SQL schema or migration files.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and policies.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Calculate and Prepare Offer Package (Priority: P1)

As an HR Admin, I need to calculate an offer package from the approved candidate decision, role level, base salary, bonus rules, and stock rules so that offers are consistent, reviewable, and ready for candidate delivery.

**Why this priority**: A governed offer package is the prerequisite for offer letters, negotiations, referrals, background checks, and onboarding.

**Independent Test**: Can be tested by selecting a candidate with a Hire or Strong Hire recommendation, entering role and compensation inputs, reviewing calculated totals and rule explanations, saving the package, and confirming the candidate application is ready for offer generation.

**Acceptance Scenarios**:

1. **Given** a candidate application has a final Hire or Strong Hire recommendation, **When** HR enters role level, base salary, bonus inputs, stock inputs, offer type, proposed start date, and expiry window, **Then** the system calculates total compensation, shows the rule basis, validates allowed values, and lets HR save a draft offer package.
2. **Given** required compensation inputs are missing, negative, or outside allowed policy ranges, **When** HR attempts to calculate or save the package, **Then** the system rejects the action with clear validation messages and no offer is sent to the candidate.
3. **Given** a candidate already has an active sent or accepted offer for the same application, **When** HR attempts to create a duplicate initial offer, **Then** the system prevents duplicate active offers and directs HR to the negotiation or revision workflow.

---

### User Story 2 - Generate and Send Versioned Offer Letter (Priority: P1)

As an HR Admin, I need to generate a digital offer letter from an approved template version and send it for candidate review so that the candidate receives a consistent, auditable offer document.

**Why this priority**: The offer letter is the candidate-facing commitment and must preserve the approved template version used at generation time.

**Independent Test**: Can be tested by approving an offer package, selecting the active approved offer template, generating the letter, sending it to the candidate, and confirming the candidate sees only their own offer details and response options.

**Acceptance Scenarios**:

1. **Given** HR has saved a complete offer package and an approved active offer letter template exists, **When** HR generates the offer letter, **Then** the letter contains candidate, role, compensation, start date, expiry, and policy details and records the exact template version used.
2. **Given** the active template is missing, deprecated, unapproved, or lacks required placeholders, **When** HR attempts to generate the letter, **Then** generation is blocked and HR is shown what must be corrected before sending.
3. **Given** the offer letter has been sent, **When** the candidate opens their offer portal, **Then** they can view the offer letter, see the expiry date, accept, decline, or request negotiation, and cannot access another candidate's offer.

---

### User Story 3 - Track Negotiations and Offer Expiry (Priority: P1)

As an HR Admin, I need to track candidate counter-offers, HR revisions, approvals, declines, and manually run expiry checks so that negotiation history and offer status are controlled without relying on an unapproved scheduler.

**Why this priority**: Negotiations and expiry directly affect candidate commitments, compliance, and pipeline status.

**Independent Test**: Can be tested by sending an offer, recording a candidate counter-offer, creating a revision, sending the revised letter, and manually running HR Run Checks after the expiry date to update overdue unsigned offers.

**Acceptance Scenarios**:

1. **Given** a candidate requests a compensation or start-date change, **When** HR records a negotiation revision, **Then** the system stores the requested changes, HR decision, rationale, revised package values when applicable, revision number, actor, and timestamp.
2. **Given** HR approves a revised package, **When** HR generates and sends the revised offer letter, **Then** the prior sent offer is preserved as superseded, the new letter has a new revision and template version reference, and the candidate sees only the currently actionable offer.
3. **Given** a sent offer is unsigned and its expiry date has passed, **When** HR runs HR Run Checks, **Then** the system identifies overdue unsigned offers and allows HR to mark them expired with audit history; the offer is not expired by any background scheduler.

---

### User Story 4 - Run Simulated Background Checks (Priority: P2)

As an HR Admin, I need to manually trigger and record simulated background checks after a conditional offer is accepted so that candidates are cleared or routed for review before onboarding proceeds.

**Why this priority**: Background verification is a compliance gate before onboarding completion, but the project scope requires a simulated and manually triggered workflow.

**Independent Test**: Can be tested by accepting an offer as a candidate, running HR Run Checks, selecting a simulated check outcome, and confirming the candidate is either cleared for onboarding or locked for HR review.

**Acceptance Scenarios**:

1. **Given** a candidate has accepted a conditional offer and provided required consent details, **When** HR triggers a simulated background check through HR Run Checks, **Then** the system records the check request, simulated provider/status label, outcome, reviewed-by actor, timestamp, and next action.
2. **Given** the simulated background check outcome is cleared, **When** HR confirms the outcome, **Then** the candidate becomes eligible for pre-onboarding and the offer/onboarding history records the clearance.
3. **Given** the simulated background check outcome is review required or failed, **When** HR records the outcome and rationale, **Then** onboarding progression is blocked until HR resolves the review and the candidate sees a clear status without sensitive internal notes.

---

### User Story 5 - Attribute Referral Rewards (Priority: P2)

As an HR Admin, I need to attribute referral rewards to the recorded referrer for a hired candidate so that referral credit is traceable and reward eligibility is visible at the correct hiring milestone.

**Why this priority**: Referral attribution affects internal reward fairness and must be recorded before candidate onboarding history is finalized.

**Independent Test**: Can be tested by hiring a referred candidate, confirming the referrer is recorded, accepting and clearing the offer, and verifying HR can see the reward eligibility state and audit trail.

**Acceptance Scenarios**:

1. **Given** a candidate application includes a valid referrer, **When** the candidate accepts an offer and is cleared for onboarding, **Then** the system attributes referral reward eligibility to the referrer with candidate, job, milestone, status, and audit details.
2. **Given** no referrer is recorded for the candidate application, **When** the candidate reaches the referral attribution milestone, **Then** the system records that no reward is applicable and does not create a payable reward.
3. **Given** HR corrects or rejects a referral attribution due to an invalid referrer or policy issue, **When** HR saves the change with a reason, **Then** the previous attribution remains visible in audit history and the reward state reflects the HR decision.

---

### User Story 6 - Complete Pre-Onboarding Welcome Portal (Priority: P2)

As a candidate with a cleared accepted offer, I need a pre-onboarding welcome portal where I can review day-one information and submit required documents so that I arrive prepared for my start date.

**Why this priority**: The welcome portal completes the offer-to-hire handoff and reduces manual HR follow-up before day one.

**Independent Test**: Can be tested by clearing a candidate's accepted offer, opening the candidate welcome portal, uploading or confirming required documents, and confirming HR can review document completion status.

**Acceptance Scenarios**:

1. **Given** a candidate has accepted the current offer and is cleared for onboarding, **When** they open the welcome portal, **Then** they see their role, start date, onboarding status, required day-one document checklist, and instructions for each item.
2. **Given** the candidate submits required onboarding document information, **When** all required items pass validation, **Then** the portal marks the document checklist complete and HR can view the completion status.
3. **Given** background check review is unresolved or failed, **When** the candidate attempts to complete onboarding documents, **Then** the portal prevents final onboarding completion and displays a respectful status message without exposing internal review details.

---

### Edge Cases

- If a candidate's final recommendation is No Hire, Strong No Hire, incomplete, or blocked by unresolved governance flags, HR cannot send an offer for that application.
- If role level or compensation rules are missing for a job, HR can save a draft only when policy allows manual review, and the offer cannot be sent until HR records an approved compensation basis.
- If an offer template changes after a letter is generated, the generated letter retains the original template version and does not silently change.
- If a candidate accepts a superseded offer revision link, the system rejects the response and points the candidate to the current actionable offer.
- If a candidate requests negotiation after the offer is expired, HR must create a new revision or close the negotiation; the expired offer is not reactivated silently.
- If HR Run Checks is run multiple times for the same expired or background-check-eligible record, the system prevents duplicate status changes and shows the existing result.
- If referral attribution points to an inactive, invalid, or candidate user, HR must resolve the attribution before marking a reward eligible.
- If onboarding document uploads or entries are invalid, too large, unsupported, duplicated, or missing required consent, the portal rejects them safely and preserves previously accepted items.
- If an authenticated user attempts an action outside their role or candidate ownership, access is denied and offer, compensation, background check, referral, and onboarding data remain hidden.
- If candidate data is later retained, anonymized, or erased under privacy rules, offer and onboarding records must preserve compliance history without exposing unnecessary PII to unauthorized users.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow HR Admins to create an offer package only for candidate applications with an approved Hire or Strong Hire final recommendation and no unresolved blocking evaluation condition.
- **FR-002**: System MUST calculate offer package totals from role level, base salary, bonus rules, stock rules, offer type, proposed start date, and expiry window, and show HR the calculated total compensation and rule basis before saving.
- **FR-003**: System MUST validate offer inputs before saving or sending, including required fields, non-negative compensation values, valid dates, valid offer type, allowed status transitions, and one active actionable offer per application.
- **FR-004**: System MUST allow HR to save draft offer packages, approve them for sending, and preserve package values used for each sent offer letter revision.
- **FR-005**: System MUST generate a digital offer letter only from an approved active template and MUST record the exact template version, generated content snapshot, offer revision, actor, and timestamp.
- **FR-006**: System MUST block offer letter generation when required candidate, job, compensation, start-date, expiry, or template fields are missing or invalid.
- **FR-007**: System MUST allow candidates to view, accept, decline, or request negotiation for only their own current actionable offer and MUST clearly display the offer expiry date and current revision.
- **FR-008**: System MUST track negotiation revisions, including candidate request, HR response, compensation/start-date changes, approval or rejection, rationale, revision number, status, actor, and timestamp.
- **FR-009**: System MUST preserve prior offer revisions and mark superseded revisions as non-actionable when a new revision is sent.
- **FR-010**: System MUST provide HR Run Checks for HR Admins to manually identify sent unsigned offers whose expiry date has passed and mark them expired with audit history; no offer expiry may occur through an unapproved scheduler.
- **FR-011**: System MUST allow HR Admins to manually trigger simulated background checks through HR Run Checks only after a candidate accepts a conditional offer and required consent details are available.
- **FR-012**: System MUST label background checks as simulated, record the simulated check status and outcome, and allow HR to mark outcomes as cleared, review required, failed, or cancelled with rationale when required.
- **FR-013**: System MUST block final onboarding progression when a simulated background check is review required, failed, missing, or otherwise unresolved, while showing the candidate a clear non-sensitive status.
- **FR-014**: System MUST create or update onboarding readiness when a candidate has accepted the current offer and HR has recorded any required simulated background check clearance.
- **FR-015**: System MUST provide a candidate welcome portal for eligible candidates to view role, start date, onboarding status, instructions, and day-one document checklist items.
- **FR-016**: System MUST allow candidates to submit or confirm required day-one document items and MUST validate required fields, ownership, duplicate submissions, file/document constraints where applicable, and completion status.
- **FR-017**: System MUST allow HR Admins to review onboarding document completion status, mark items accepted or needing correction, and provide candidate-visible correction messages that do not expose internal notes.
- **FR-018**: System MUST attribute referral reward eligibility when a referred candidate accepts the current offer and is cleared for onboarding, including referrer, candidate application, job, milestone, reward status, and audit details.
- **FR-019**: System MUST allow HR Admins to correct, reject, or hold referral attribution with a reason while preserving the previous attribution in audit history.
- **FR-020**: System MUST update candidate application and offer states consistently across offer sent, accepted, declined, expired, superseded, background check cleared/review/failed, onboarding started, and onboarding documents completed milestones.
- **FR-021**: System MUST notify relevant HR users and candidates in-system when offer letters are sent, candidate responses are received, negotiation revisions are created, HR Run Checks changes statuses, background check outcomes are recorded, onboarding document corrections are requested, or onboarding readiness changes.
- **FR-022**: System MUST provide audit history for offer package calculation, package approval, letter generation, template version used, sent offers, candidate responses, negotiation revisions, HR Run Checks outcomes, background check actions, referral attribution changes, onboarding document actions, and status changes.
- **FR-023**: System MUST ensure audit records include actor, role, timestamp, affected candidate/application/offer/onboarding record, action type, previous state when applicable, new state, and stated reason when applicable.
- **FR-024**: System MUST prevent unauthorized access to candidate compensation, offer letters, negotiation details, background check details, referral reward details, onboarding documents, and audit records.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST include creating, reviewing, sending, revising, expiring, and auditing offers; manually running HR Run Checks; recording simulated background check outcomes; reviewing onboarding documents; and managing referral attribution.
- **RP-002**: Technical Interviewer access MUST NOT include candidate compensation, offer letters, negotiation details, referral reward details, background check details, or onboarding documents unless a separate approved role policy grants access.
- **RP-003**: Candidate access MUST be limited to their own current actionable offer, their own offer response history visible to them, their own welcome portal, and their own onboarding document checklist and submission status.
- **RP-004**: Junior Staff or observer access MUST NOT include offer, background check, referral reward, or onboarding document data by default.
- **RP-005**: Candidate PII, compensation details, offer letters, background check status, referral attribution, onboarding documents, and audit details MUST be hidden from unauthorized roles and from unrelated candidates.
- **RP-006**: Simulated background check outcomes MUST be labeled as simulated and reviewable by HR before they affect onboarding progression.

### Key Entities *(include if feature involves data)*

- **Offer Package**: Compensation proposal for a candidate application, including role level, offer type, base salary, bonus, stock options, total compensation, start date, expiry, status, and approval context.
- **Offer Template Version**: Approved offer-letter template version with status, effective version, required placeholders, and deprecation state used to generate offer letters consistently.
- **Digital Offer Letter**: Candidate-facing generated offer document tied to an offer package, template version, revision number, generated content snapshot, sent status, and candidate response state.
- **Negotiation Revision**: Structured record of a candidate counter-offer or HR revision with requested changes, HR decision, rationale, resulting package values, revision number, and status.
- **HR Run Check Result**: Manually triggered HR review outcome for offer expiry and simulated background-check eligibility/status changes, including run actor, affected records, outcomes, and audit details.
- **Simulated Background Check**: Manual, simulated verification record for a candidate with consent status, simulated provider/status label, outcome, rationale, HR reviewer, and onboarding gate result.
- **Referral Reward Attribution**: Referrer credit record tied to a candidate application and hiring milestone, including referrer identity, reward eligibility state, HR adjustments, and audit history.
- **Onboarding Record**: Candidate onboarding readiness record tied to an accepted and cleared offer, including onboarding status, start date, document completion state, and HR review status.
- **Onboarding Document Item**: Required day-one document or confirmation item visible in the candidate welcome portal, including item type, instructions, submission status, HR review state, and correction message when needed.
- **Audit Event**: Immutable workflow event describing who performed an offer or onboarding action, what changed, when it changed, and why it changed when a reason is required.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR can calculate, review, and save a complete offer package for an eligible candidate in under 3 minutes during user acceptance testing.
- **SC-002**: 100% of generated offer letters record the template version, offer revision, generated timestamp, and candidate application they belong to.
- **SC-003**: Candidates can view and respond to their current actionable offer in under 2 minutes without seeing any other candidate's offer data.
- **SC-004**: 100% of negotiation revisions preserve the prior offer state and identify the active candidate-actionable revision after HR sends a revised offer.
- **SC-005**: HR Run Checks identifies all sent unsigned offers past expiry in the test dataset and updates selected offers to expired without any automatic scheduler action.
- **SC-006**: 100% of simulated background check outcomes are labeled simulated and either clear the candidate for onboarding or block onboarding with HR-visible rationale and candidate-safe status.
- **SC-007**: At least 90% of eligible candidates can complete required welcome portal document checklist items in under 5 minutes during acceptance testing.
- **SC-008**: 100% of referral reward attributions for referred cleared hires show the referrer, candidate application, reward eligibility state, milestone, and HR audit history.
- **SC-009**: Unauthorized users are prevented from viewing or changing offer, compensation, background check, referral, onboarding document, and audit data in all role-based access checks.
- **SC-010**: In HR acceptance review, at least 85% of reviewers agree that the offer and onboarding history is clear enough to explain the candidate's current status and next action.

## Assumptions

- Existing authentication, role assignment, candidate application, final evaluation, notification, and audit foundations are reused and extended.
- Eligible offers begin only after HR records a final Hire or Strong Hire decision for the application.
- Compensation rules are maintained by HR or project seed data and can be reviewed by HR before sending an offer.
- Offer letter templates have an approval state; generated letters preserve the template version used even if a later version is approved.
- Candidate offer responses occur inside the candidate portal; external email delivery is not required for this feature unless a later plan explicitly includes it.
- HR Run Checks is the manual operational workflow for offer expiry review and simulated background-check actions. No scheduler, background worker, or automatic expiry/background-check trigger is in scope.
- Background checks are simulated and do not send real candidate data to an external provider.
- Referral reward attribution records eligibility and HR review state; actual payroll payout is outside this feature unless separately approved.
- Onboarding document handling follows SRIM privacy and retention rules, and candidate-facing messages avoid exposing internal background-check or referral-review notes.
