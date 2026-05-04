# Data Model: Final Offer Onboarding

## Entity: Application

**Purpose**: Candidate's application for a job and the anchor for final evaluation, offer, and onboarding eligibility.

**Existing fields used**: `application_id`, `candidate_id`, `job_id`, `status`, `match_score`, `applied_at`, `created_at`, `updated_at`.

**Relationships**:

- Belongs to one Candidate.
- Belongs to one Job Requisition.
- Has many Candidate Assessments.
- Has many Interviews and Interview Feedback through interviews.
- Has zero or one Final Evaluation.
- Has up to two Offer Packages in this feature: original plus one replacement.

**Validation and state rules**:

- Final evaluation may be recorded when at least one assessment score or one submitted interview feedback record exists.
- `No Hire` and `Strong No Hire` final recommendations set status to `REJECTED`.
- Sent offer sets status to `OFFER`.
- Accepted offer sets status to `HIRED`.
- Rejected or expired offer sets status to `REJECTED`.
- Each status transition writes an application status history row.

## Entity: Assessment Evidence

**Purpose**: Existing assessment attempt evidence used in final evaluation aggregation.

**Existing fields used**: `ca_id`, `application_id`, `candidate_id`, `assessment_id`, `status`, `score`, `start_time`, `end_time`.

**Relationships**:

- Belongs to Application.
- Belongs to Candidate.
- Belongs to Assessment.

**Validation and aggregation rules**:

- Only submitted/completed assessment attempts with a non-null score count toward aggregate final evaluation evidence.
- Assessment score is treated as a 0-100 value for aggregation.
- Missing assessment evidence is allowed only with HR acknowledgement of partial evidence.

## Entity: Interview Feedback

**Purpose**: Existing structured interviewer feedback used in final evaluation aggregation.

**Existing fields used**: `feedback_id`, `interview_id`, `interviewer_id`, `technical_score`, `communication_score`, `culture_fit_score`, `overall_score`, `comments`, `submitted_at`.

**Relationships**:

- Belongs to Interview.
- Belongs to User as interviewer.
- Contributes to Application final evaluation through Interview.

**Validation and aggregation rules**:

- Only submitted official feedback counts toward final evaluation evidence.
- Interview `overall_score` is normalized from the feedback scale to 0-100 before aggregation.
- Missing required interview feedback is allowed only with HR acknowledgement of partial evidence.

## Entity: Final Evaluation

**Purpose**: HR-owned final hiring decision for an application.

**Fields**:

- `evaluation_id`: unique identifier.
- `application_id`: required, unique application reference.
- `aggregate_score`: nullable decimal 0-100 value calculated from available evidence.
- `recommendation`: required; one of `STRONG_HIRE`, `HIRE`, `NO_HIRE`, `STRONG_NO_HIRE`.
- `status`: required decision state, initially `EVALUATED` or completed equivalent for saved decisions.
- `decision_notes`: required HR rationale text.
- `partial_evidence_acknowledged`: boolean; true when HR saves with missing assessment or interview evidence.
- `evaluated_by`: required HR Admin user reference.
- `created_at`, `updated_at`: timestamps.

**Relationships**:

- Belongs to one Application.
- Created by one HR Admin User.
- Has many Post-Offer Audit Records through application context.

**Validation rules**:

- One final evaluation per application.
- At least one evidence source must exist.
- Recommendation must be one of the allowed final recommendation values.
- Decision notes are required.
- Partial evidence requires explicit acknowledgement.

## Entity: Offer Package

**Purpose**: Compensation and employment proposal linked to an offer-eligible application.

**Fields**:

- `offer_id`: unique identifier.
- `application_id`: required application reference.
- `offer_sequence`: required integer, `1` for original offer and `2` for the single allowed replacement.
- `replaces_offer_id`: nullable reference to the rejected or expired offer being replaced.
- `offer_type`: required; one of `FULL_TIME`, `CONTRACT`, `INTERN`.
- `ctc`: required non-negative compensation amount.
- `bonus`: required non-negative amount, default `0`.
- `stock_options`: required non-negative amount, default `0`.
- `status`: required; one of `DRAFT`, `SENT`, `ACCEPTED`, `REJECTED`, `EXPIRED`.
- `expiry_date`: required before send; must be in the future when sent.
- `sent_at`: nullable timestamp set when offer is sent.
- `accepted_at`: nullable timestamp set when candidate accepts.
- `rejected_at`: nullable timestamp set when candidate rejects.
- `expired_at`: nullable timestamp set when expiry is enforced.
- `created_by`: required HR Admin user reference.
- `created_at`, `updated_at`: timestamps.

**Relationships**:

- Belongs to Application.
- Replacement offer optionally belongs to the prior Offer Package it replaces.
- Has zero or one Onboarding Record if accepted.

**Validation and state rules**:

- Application must have a `Hire` or `Strong Hire` final evaluation.
- At most one active `DRAFT` or `SENT` offer per application.
- At most two offers per application: original plus one replacement after rejection or expiry.
- Replacement requires the prior offer to be `REJECTED` or `EXPIRED`.
- Draft offer may become sent when complete and expiry is future.
- Sent offer may become accepted or rejected only by the owning candidate before expiry.
- Sent offer becomes expired when expiry date has passed and no response exists.
- Accepted offers cannot return to draft or sent.

## Entity: Onboarding Record

**Purpose**: Post-acceptance handoff record for day-one readiness.

**Fields**:

- `onboarding_id`: unique identifier.
- `offer_id`: required, unique accepted offer reference.
- `status`: required; one of `PENDING`, `IN_PROGRESS`, `COMPLETED`.
- `start_date`: nullable planned start date.
- `documents_completed`: boolean default `false`.
- `created_by`: required HR Admin user reference.
- `created_at`, `updated_at`: timestamps.

**Relationships**:

- Belongs to one accepted Offer Package.
- Candidate is derived through offer -> application -> candidate.

**Validation and state rules**:

- One onboarding record per accepted offer.
- Offer must be `ACCEPTED`.
- Status must be one of the allowed onboarding statuses.
- Start date must be a valid date when provided.
- Duplicate onboarding creation for the same accepted offer is blocked.

## Entity: Post-Offer Audit Record

**Purpose**: Traceability record for final evaluation, offer, response, expiry, and onboarding changes.

**Fields**:

- `audit_id`: unique identifier.
- `application_id`: required application reference.
- `offer_id`: nullable offer reference.
- `onboarding_id`: nullable onboarding reference.
- `actor_user_id`: required user who performed the action; candidate for candidate response, HR Admin for HR actions, system-equivalent actor for expiry if implemented.
- `action`: required action value such as `FINAL_EVALUATION_SAVE`, `OFFER_CREATE`, `OFFER_SEND`, `OFFER_REPLACE`, `OFFER_ACCEPT`, `OFFER_REJECT`, `OFFER_EXPIRE`, `ONBOARDING_CREATE`, `ONBOARDING_UPDATE`.
- `changed_fields`: nullable structured list of changed fields and old/new values.
- `created_at`: timestamp.

**Relationships**:

- Belongs to Application.
- Optionally belongs to Offer Package.
- Optionally belongs to Onboarding Record.
- Belongs to User as actor.

**Validation rules**:

- Actor, action, application, and timestamp are required.
- Offer actions require `offer_id`.
- Onboarding actions require `onboarding_id`.
- Changed fields should include status, score, recommendation, compensation, expiry, response, start date, or document-completion changes when those values change.

## State Transition Summary

### Final Evaluation

```text
Not Evaluated -> EVALUATED
```

Saved recommendations are not revised in this feature; corrections require later controlled revision scope.

### Application

```text
INTERVIEW -> REJECTED  (No Hire or Strong No Hire final evaluation)
INTERVIEW -> OFFER     (Offer sent)
REJECTED -> OFFER      (Replacement offer sent after rejected/expired offer, if allowed)
OFFER -> HIRED         (Candidate accepts sent offer)
OFFER -> REJECTED      (Candidate rejects or offer expires)
```

### Offer Package

```text
DRAFT -> SENT
SENT -> ACCEPTED
SENT -> REJECTED
SENT -> EXPIRED
REJECTED -> replaced by one new DRAFT offer
EXPIRED -> replaced by one new DRAFT offer
```

### Onboarding Record

```text
PENDING -> IN_PROGRESS -> COMPLETED
PENDING -> COMPLETED
```
