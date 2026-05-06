# Data Model: Offer Onboarding Workflows

## Existing Baseline Entities

### User

Represents HR Admins, Technical Interviewers, Candidates, and existing user accounts that may be referrers.

**Key fields**: `user_id`, `department_id`, `name`, `email`, `role`, `status`, timestamps.

**Relationships**: Candidate users map to `candidates`; HR users create offers, run checks, review documents, resolve background checks, and record audit events; referrer users may be tied to referral attribution.

### Candidate

Represents candidate profile data for a user.

**Key fields**: `candidate_id`, `phone`, `current_title`, `years_experience`, `location`, `resume_url`, timestamps.

**Relationships**: Has applications; reaches onboarding through `application -> offer -> onboarding`.

### Application

Represents a candidate's application for a job requisition.

**Key fields**: `application_id`, `candidate_id`, `job_id`, `status`, `match_score`, timestamps.

**Relationships**: Has one final evaluation; may have offer packages and revisions; may have referral attribution.

### Final Evaluation

Represents HR's final candidate recommendation for an application.

**Key fields**: `evaluation_id`, `application_id`, `aggregated_score`, `recommendation`, `status`, `created_at`.

**Relationships**: Gates offer eligibility. Only Hire and Strong Hire recommendations without unresolved blockers are offer-eligible.

### Offer

Baseline offer table currently stores package and status values.

**Existing fields**: `offer_id`, `application_id`, `offer_type`, `ctc`, `bonus`, `stock_options`, `status`, `expiry_date`, `sent_at`, `accepted_at`.

**Planned extensions**: `role_level`, `base_salary`, `total_compensation`, `start_date`, `offer_sequence`, `revision_number`, `replaces_offer_id`, `created_by`, `approved_by`, `approved_at`, `expired_at`, `rejected_at`, `superseded_at`, `calculation_snapshot`, `manual_override_reason`, timestamps.

**Validation rules**: Application must be offer-eligible; compensation values must be non-negative; expiry must be present before sending and be in the future at send time; only one draft or sent actionable offer per application; superseded offers cannot receive candidate responses.

**State transitions**: `DRAFT -> SENT -> ACCEPTED`; `SENT -> REJECTED`; `SENT -> EXPIRED` only through HR Run Checks; `SENT -> SUPERSEDED` when a revised offer is sent; terminal states do not return to actionable without a new revision.

## New or Expanded Entities

### Compensation Rule

Represents HR-maintained offer calculation rules by role level and offer type.

**Fields**: `rule_id`, `role_level`, `offer_type`, `base_salary_min`, `base_salary_max`, `bonus_type`, `bonus_value`, `stock_type`, `stock_value`, `effective_from`, `effective_to`, `status`, `created_by`, `updated_by`, timestamps.

**Relationships**: Used by Offer Package calculation; referenced in offer calculation snapshots.

**Validation rules**: Role level and offer type are required; ranges must be non-negative and min cannot exceed max; active rules cannot have overlapping effective windows for the same role level and offer type.

### Offer Template Version

Represents approved offer-letter templates and their lifecycle.

**Fields**: `template_id`, `template_key`, `version_number`, `title`, `body_template`, `required_placeholders`, `status`, `approved_by`, `approved_at`, `deprecated_at`, timestamps.

**Relationships**: Referenced by generated offer letters.

**Validation rules**: Only approved active templates may generate letters; required placeholders must be resolvable from candidate, job, offer, and onboarding data; deprecated templates remain available for historical letters but not new generation.

**State transitions**: `DRAFT -> APPROVED -> DEPRECATED`; rejected drafts can remain non-actionable for audit.

### Digital Offer Letter

Represents the generated candidate-facing offer letter snapshot.

**Fields**: `letter_id`, `offer_id`, `application_id`, `template_id`, `template_version_number`, `revision_number`, `generated_subject`, `generated_body`, `status`, `generated_by`, `generated_at`, `sent_by`, `sent_at`, `candidate_responded_at`.

**Relationships**: Belongs to one offer revision and one template version.

**Validation rules**: Offer must be complete before generation; one current generated letter per actionable revision; sent letters cannot be edited in place.

**State transitions**: `GENERATED -> SENT -> ACCEPTED/DECLINED/SUPERSEDED/EXPIRED` following the parent offer state.

### Negotiation Revision

Represents candidate counter-offers and HR responses.

**Fields**: `revision_id`, `application_id`, `offer_id`, `previous_offer_id`, `requested_by`, `requested_changes`, `candidate_message`, `hr_decision`, `hr_rationale`, `resulting_offer_id`, `status`, `created_at`, `decided_by`, `decided_at`.

**Relationships**: May create a new offer revision; linked to candidate and HR audit events.

**Validation rules**: Candidate can request negotiation only for their current sent offer; HR decision requires rationale for rejection, hold, or material changes; only one open negotiation per application.

**State transitions**: `REQUESTED -> APPROVED -> REVISED_SENT`; `REQUESTED -> REJECTED`; `REQUESTED -> WITHDRAWN`; `REQUESTED -> CLOSED`.

### HR Run Check

Represents a manual HR-triggered operational check.

**Fields**: `run_check_id`, `run_type`, `run_by`, `started_at`, `completed_at`, `summary`, `affected_count`, `status`.

**Relationships**: Has result rows for expired offers and simulated background-check candidates.

**Validation rules**: Only HR Admin can run; run type must be explicit; each affected record must record before/after state and action taken.

### HR Run Check Result

Represents the outcome of one record inspected during a manual run.

**Fields**: `result_id`, `run_check_id`, `entity_type`, `entity_id`, `application_id`, `offer_id`, `previous_state`, `new_state`, `outcome`, `reason`, `created_at`.

**Relationships**: Links HR Run Check to offers or background checks and audit events.

**Validation rules**: Duplicate status changes for already expired or already resolved records are skipped and recorded as no-op results.

### Simulated Background Check

Represents a manual, simulated post-offer verification gate.

**Fields**: `background_check_id`, `application_id`, `offer_id`, `candidate_id`, `consent_recorded`, `status`, `outcome`, `simulated_provider_label`, `requested_by`, `requested_at`, `reviewed_by`, `reviewed_at`, `rationale`, `candidate_safe_message`.

**Relationships**: Gates Onboarding readiness; linked to HR Run Checks and audit events.

**Validation rules**: Accepted current offer required; consent required before triggering; outcomes must be labeled simulated; failed or review-required outcomes block onboarding progression.

**State transitions**: `NOT_REQUESTED -> REQUESTED -> CLEARED`; `REQUESTED -> REVIEW_REQUIRED`; `REQUESTED -> FAILED`; `REVIEW_REQUIRED -> CLEARED/FAILED/CANCELLED`.

### Referral Reward Attribution

Represents referral reward eligibility and HR review.

**Fields**: `referral_attribution_id`, `application_id`, `candidate_id`, `referrer_user_id`, `milestone`, `status`, `reward_amount`, `eligibility_reason`, `hr_decision_reason`, `created_at`, `updated_by`, `updated_at`.

**Relationships**: Belongs to application and referrer user; audit events track corrections.

**Validation rules**: Referrer must be an active non-candidate user unless HR records a rejection reason; no payable eligibility record is created when no referrer exists; reward payout is not executed by SRIM.

**State transitions**: `NOT_APPLICABLE`; `PENDING_REVIEW -> ELIGIBLE`; `PENDING_REVIEW -> REJECTED`; `ELIGIBLE -> ON_HOLD`; `ON_HOLD -> ELIGIBLE/REJECTED`.

### Onboarding Record

Represents onboarding readiness for an accepted and cleared offer.

**Existing fields**: `onboarding_id`, `offer_id`, `status`, `start_date`, `documents_completed`, `created_at`.

**Planned extensions**: `created_by`, `updated_at`, `background_check_id`, `welcome_visible_at`, `completed_at`, `completion_notes`.

**Relationships**: Has many onboarding document items; belongs to offer.

**Validation rules**: Current accepted offer required; simulated background check must be cleared when required; one onboarding record per offer.

**State transitions**: `PENDING -> IN_PROGRESS -> COMPLETED`; `IN_PROGRESS -> BLOCKED` when background-check review is unresolved; `BLOCKED -> IN_PROGRESS` after HR clearance.

### Onboarding Document Item

Represents a required day-one document or confirmation checklist item.

**Fields**: `document_item_id`, `onboarding_id`, `candidate_id`, `document_type`, `title`, `instructions`, `is_required`, `status`, `submitted_value`, `stored_file_path`, `submitted_at`, `reviewed_by`, `reviewed_at`, `correction_message`.

**Relationships**: Belongs to onboarding record and candidate.

**Validation rules**: Candidate can submit only their own items; required items must be accepted before documents complete; rejected items require candidate-visible correction message; invalid, duplicate, unsupported, or oversized submissions are rejected safely.

**State transitions**: `PENDING -> SUBMITTED -> ACCEPTED`; `SUBMITTED -> NEEDS_CORRECTION -> SUBMITTED`; optional items may remain `NOT_REQUIRED`.

### Post-Offer Audit Event

Represents immutable audit coverage for offer and onboarding workflows.

**Fields**: `audit_id`, `application_id`, `offer_id`, `onboarding_id`, `actor_user_id`, `actor_role`, `entity_type`, `entity_id`, `action`, `old_values`, `new_values`, `reason`, `created_at`.

**Relationships**: References post-offer entities and actor user where available.

**Validation rules**: Required for package calculation, approval, letter generation, send, candidate response, revision, expiry run check, background check, referral attribution, onboarding document review, and status transitions; records are append-only.

## Cross-Entity Invariants

- One application can have multiple historical offer revisions but only one actionable draft or sent offer.
- A candidate can respond only to their own current sent offer.
- Offer expiry is changed only by HR Run Checks, not by normal page views.
- Background-check status is simulated and changed only by HR Run Checks or HR review actions.
- Onboarding readiness requires accepted current offer and any required cleared simulated background check.
- Generated letters retain template version and generated content even after template deprecation.
- Candidate-facing pages never expose HR-only rationale, internal background-check notes, unrelated candidate data, or referral reward details.
