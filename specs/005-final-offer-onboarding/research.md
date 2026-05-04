# Research: Final Offer Onboarding

## Decision: Use Server-Rendered Page/Form Workflows Only

**Rationale**: SRIM is constitutionally constrained to a framework-free Vanilla PHP monolith with server-rendered PHP templates and `routes/web.php` flows. HR final evaluation, offer management, candidate offer response, and onboarding are all human-facing workflows that fit standard GET pages, POST/PUT forms, redirects, sessions, CSRF, and validation.

**Alternatives considered**: REST endpoints for offer status changes were rejected because they would create a machine-facing contract not needed for this slice. A separated candidate offer frontend was rejected because it violates the approved monolith delivery mode.

## Decision: Aggregate Score Uses Equal Normalized Evidence

**Rationale**: Clarification selected an equal normalized average of assessment score evidence and interview overall score evidence. This is transparent, testable, and easy for HR/demo reviewers to understand. If assessment or interview evidence is missing, the system uses available evidence and flags the final evaluation as partial evidence before HR saves the recommendation.

**Alternatives considered**: A fixed 40/60 assessment-interview weighting was rejected because no baseline weighting rule exists. Manual-only aggregate score entry was rejected because the feature explicitly asks HR to aggregate assessment and interview feedback. Omitting aggregate score was rejected because the baseline `final_evaluations.aggregated_score` entity exists.

## Decision: Recommendation Controls Offer Eligibility

**Rationale**: `Strong Hire` and `Hire` represent positive final recommendations and unlock offer creation. `No Hire` and `Strong No Hire` represent terminal negative decisions and set the application to `REJECTED`. This aligns with UC-25 and the existing `ApplicationStatus` enum.

**Alternatives considered**: Allowing offers from negative recommendations was rejected as a business-rule contradiction. Leaving application status unchanged was rejected because the clarified workflow requires visible downstream state transitions.

## Decision: Enforce Explicit Application Status Transitions

**Rationale**: The clarification established that no-hire recommendations set `REJECTED`, sent offers set `OFFER`, accepted offers set `HIRED`, and rejected or expired offers set `REJECTED`. Recording status history keeps HR decisions auditable and preserves pipeline visibility.

**Alternatives considered**: Setting `OFFER` immediately after a hire recommendation was rejected because HR may draft or abandon an offer before sending. Tracking only offer status was rejected because the broader application pipeline needs a single current state.

## Decision: Allow One Replacement Offer, One Active Offer

**Rationale**: The clarification allows one replacement offer after a rejected or expired offer, while enforcing only one active draft or sent offer at a time. This provides limited negotiation/reissue capability without implementing full UC-29 counter-offer negotiation.

**Alternatives considered**: No replacement offers was rejected by user clarification. Unlimited replacements were rejected because they would require a full negotiation history and approval workflow beyond this phase.

## Decision: Candidate Self-Service Offer Response

**Rationale**: The clarification requires candidates to accept or reject their own unexpired sent offers from the candidate portal. Ownership checks, active sessions, CSRF, and server-side validation are sufficient for the demo scope while preserving candidate privacy.

**Alternatives considered**: HR-recorded responses were rejected by user clarification. Allowing both HR and candidate to record responses was rejected because it adds override and conflict-resolution rules not required for this feature.

## Decision: Lazy Expiry Enforcement on Offer Views and Mutations

**Rationale**: A sent offer expires based on its recorded expiry deadline when HR or the candidate opens offer tracking or submits a status-changing form. This meets acceptance criteria without adding a background worker or scheduler to the Vanilla PHP app.

**Alternatives considered**: Always-on background processing was rejected because it adds operational complexity not needed for the academic slice. Manual expiry by HR was rejected because UC-28 requires automatic expiry behavior.

## Decision: Onboarding Starts Only After Accepted Offer

**Rationale**: The baseline ERD links onboarding to offers, and the spec requires onboarding records after accepted offers. One onboarding record per accepted offer prevents duplicate day-one handoff records.

**Alternatives considered**: Creating onboarding at offer send was rejected because candidates may reject or let offers expire. Candidate-owned onboarding task management was deferred because the spec only requires HR-created onboarding records and basic progress tracking.

## Decision: Post-Offer Audit Records Capture Critical Changes

**Rationale**: The constitution requires audit-relevant candidate, score, status, feedback, offer, and onboarding changes to be traceable. A post-offer audit record with actor, action, timestamp, changed fields, and related application/offer/onboarding identifiers covers final evaluations, offer status changes, candidate responses, expiry, and onboarding changes.

**Alternatives considered**: Relying only on `application_status_histories` was rejected because compensation and onboarding changes need traceability beyond application status. Separate audit tables per entity were rejected as unnecessary for a small academic slice.
