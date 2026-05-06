# Phase 0 Research: Offer Onboarding Workflows

## Decision: Extend Existing Post-Offer MVC Slice

**Rationale**: The repository already contains `HrOfferController`, `CandidateOfferController`, `HrOnboardingController`, `OfferRepository`, `OnboardingRepository`, `OfferPolicy`, `OnboardingPolicy`, and `PostOfferAuditRepository`. Extending these keeps the feature small, reviewable, and aligned with the monolithic MVC architecture.

**Alternatives considered**: A new offer subsystem was rejected because it would duplicate existing routes and records. A REST API was rejected because the constitution requires browser routes and form submissions.

## Decision: Deterministic HR-Maintained Compensation Rules

**Rationale**: Offer packages need predictable calculation using role level, base salary, bonus, and stock rules. HR-maintained rules support demo-ready behavior, validation, and auditable manual override rationale without external compensation integrations.

**Alternatives considered**: Hard-coded values were rejected because HR cannot review or adjust them. External compensation benchmarking was rejected as out of scope and unnecessary for the academic slice.

## Decision: Snapshot Generated Offer Letters

**Rationale**: Each generated letter must preserve the exact approved template version and generated content used for a candidate. This prevents later template edits from silently changing sent offers and supports compliance review.

**Alternatives considered**: Rendering letters dynamically from the latest template was rejected because it breaks versioned evidence. Storing only a file path was rejected because the generated content needs to remain reviewable in-system.

## Decision: Immutable Negotiation Revisions with One Actionable Offer

**Rationale**: Counter-offers and HR revisions must preserve prior states while keeping the candidate experience unambiguous. A revision log with one current actionable revision satisfies auditability and prevents candidates from accepting superseded offers.

**Alternatives considered**: Updating the same offer in place was rejected because it loses negotiation history. Allowing multiple active offers was rejected because candidate responses could conflict.

## Decision: Manual HR Run Checks for Expiry

**Rationale**: The user explicitly requires expiry to remain manually triggered unless a constitution-approved scheduler is added. Current automatic expiry behavior from normal offer page loads should be moved behind an explicit HR Run Checks action.

**Alternatives considered**: A scheduler or background worker was rejected because the constitution does not approve one. Expiring offers on read was rejected because it creates hidden workflow side effects outside HR Run Checks.

## Decision: Simulated Background Checks Only

**Rationale**: The SRS labels background checks as simulated and the constitution requires simulated advanced integrations unless explicitly approved. The workflow should record consent status, simulated outcome, HR rationale, and onboarding gate state without sending real data externally.

**Alternatives considered**: External provider API calls and webhooks were rejected as out of scope. Automatic background-check processing was rejected because checks must be manually triggered through HR Run Checks.

## Decision: Referral Eligibility, Not Payroll Payout

**Rationale**: SRIM can attribute the referrer and track reward eligibility at hiring/onboarding milestones, but actual payroll payout would require a payroll integration outside the diagrams and constitution scope.

**Alternatives considered**: Direct payroll payout was rejected. Ignoring referrals was rejected because UC-30 and the user request require attribution.

## Decision: Onboarding Document Checklist with HR Review States

**Rationale**: A checklist model supports candidate-friendly day-one preparation, validation, HR correction requests, and completion tracking while avoiding overbuilt document management.

**Alternatives considered**: A single `documents_completed` boolean was rejected because it cannot support individual document validation or correction. External document signing/storage providers were rejected as unnecessary for this phase.

## Decision: Post-Offer Audit Expansion

**Rationale**: Offer calculation, templates, letters, negotiations, run checks, background checks, referral attribution, onboarding documents, and status changes affect sensitive candidate and compensation data. Audit events must include actor, timestamp, entity, previous state, new state, and rationale where required.

**Alternatives considered**: Relying only on updated timestamps was rejected because it cannot explain who changed what or why. A separate external audit tool was rejected as out of scope.
