# Web Workflow Contracts: Final Offer Onboarding

These are server-rendered browser/page and form contracts for the Vanilla PHP monolith. They are not REST API contracts.

## Shared Rules

- All mutating forms require an authenticated session and valid CSRF token.
- HR routes require active HR Admin access unless otherwise stated.
- Candidate offer response routes require active Candidate access and ownership of the application tied to the offer.
- Validation failures re-render the server-rendered form with field-level errors and preserve safe entered values.
- Unauthorized access returns the existing forbidden response flow.
- Successful mutations redirect to the relevant show/index page with a flash message.

## HR Final Evaluation Workflows

### View Final Evaluation Evidence

```text
GET /hr/applications/{id}/final-evaluation
Route name: hr.final-evaluations.show
Controller: HrFinalEvaluationController::show
```

**Preconditions**:

- User is active HR Admin.
- Application exists.

**Page contract**:

- Shows candidate summary, job title, application status, assessment evidence, interview feedback evidence, missing evidence warnings, aggregate score preview, final recommendation options, and decision notes form.
- If final evaluation already exists, shows saved recommendation and offer eligibility state.

### Save Final Evaluation

```text
POST /hr/applications/{id}/final-evaluation
Route name: hr.final-evaluations.store
Controller: HrFinalEvaluationController::store
```

**Form fields**:

- `recommendation`: `STRONG_HIRE`, `HIRE`, `NO_HIRE`, or `STRONG_NO_HIRE`.
- `decision_notes`: required text.
- `partial_evidence_acknowledged`: required when assessment or interview evidence is missing.

**Validation rules**:

- One final evaluation per application.
- At least one scored assessment or submitted interview feedback exists.
- Missing evidence requires acknowledgement.
- Recommendation value is allowed.

**Success result**:

- Saves final evaluation with aggregate score.
- Writes audit record.
- Updates application status to `REJECTED` for `NO_HIRE` or `STRONG_NO_HIRE`.
- Redirects to the final evaluation page.

## HR Offer Workflows

### Offer Index

```text
GET /hr/offers
Route name: hr.offers.index
Controller: HrOfferController::index
```

**Page contract**:

- Lists draft, sent, accepted, rejected, and expired offers with candidate, job, expiry, response, and onboarding eligibility.
- Enforces expiry display before rendering sent offers.

### Create Offer Form

```text
GET /hr/applications/{id}/offers/create
Route name: hr.offers.create
Controller: HrOfferController::create
```

**Preconditions**:

- Application has `STRONG_HIRE` or `HIRE` final evaluation.
- No active draft or sent offer exists.
- Application has not already used its one replacement offer.

**Page contract**:

- Shows candidate and job summary, final recommendation, prior rejected/expired offer context if creating a replacement, and offer form.

### Store Draft Offer

```text
POST /hr/applications/{id}/offers
Route name: hr.offers.store
Controller: HrOfferController::store
```

**Form fields**:

- `offer_type`: `FULL_TIME`, `CONTRACT`, or `INTERN`.
- `ctc`: required non-negative amount.
- `bonus`: optional non-negative amount.
- `stock_options`: optional non-negative amount.
- `expiry_date`: required before send; may be saved on draft for completeness.

**Success result**:

- Creates original or allowed replacement draft offer.
- Writes audit record.
- Redirects to offer show page.

### Show Offer

```text
GET /hr/offers/{id}
Route name: hr.offers.show
Controller: HrOfferController::show
```

**Page contract**:

- Shows offer package, status, expiry, candidate response, replacement eligibility, onboarding eligibility, and audit-relevant timestamps.
- Enforces expiry display before rendering sent offers.

### Send Offer

```text
POST /hr/offers/{id}/send
Route name: hr.offers.send
Controller: HrOfferController::send
```

**Validation rules**:

- Offer is draft.
- Offer is complete.
- Expiry date is in the future.
- No other active offer exists for the application.

**Success result**:

- Sets offer status to `SENT`, records sent time, sets application status to `OFFER`, writes status history and audit record.

## Candidate Offer Workflows

### View Candidate Offer

```text
GET /candidate/offers/{id}
Route name: candidate.offers.show
Controller: CandidateOfferController::show
```

**Preconditions**:

- User is the candidate who owns the offer's application.
- Offer is visible to candidate only after sent.

**Page contract**:

- Shows own offer type, compensation fields, expiry deadline, status, and accept/reject actions only when offer is sent and unexpired.
- Does not show internal HR notes, interviewer comments, or final evaluation notes.

### Accept Offer

```text
POST /candidate/offers/{id}/accept
Route name: candidate.offers.accept
Controller: CandidateOfferController::accept
```

**Validation rules**:

- Candidate owns the offer.
- Offer is `SENT`.
- Offer expiry has not passed.
- No prior response exists.

**Success result**:

- Sets offer status to `ACCEPTED`, records accepted time, sets application status to `HIRED`, writes status history and audit record, redirects to candidate offer page.

### Reject Offer

```text
POST /candidate/offers/{id}/reject
Route name: candidate.offers.reject
Controller: CandidateOfferController::reject
```

**Validation rules**:

- Candidate owns the offer.
- Offer is `SENT`.
- Offer expiry has not passed.
- No prior response exists.

**Success result**:

- Sets offer status to `REJECTED`, records rejected time, sets application status to `REJECTED`, writes status history and audit record, redirects to candidate offer page.

## HR Onboarding Workflows

### Onboarding Index

```text
GET /hr/onboarding
Route name: hr.onboarding.index
Controller: HrOnboardingController::index
```

**Page contract**:

- Lists accepted offers with onboarding state, start date, and document-completion state.

### Create Onboarding Form

```text
GET /hr/offers/{id}/onboarding/create
Route name: hr.onboarding.create
Controller: HrOnboardingController::create
```

**Preconditions**:

- Offer is accepted.
- No onboarding record exists for the offer.

### Store Onboarding

```text
POST /hr/offers/{id}/onboarding
Route name: hr.onboarding.store
Controller: HrOnboardingController::store
```

**Form fields**:

- `start_date`: optional valid date.
- `status`: `PENDING`, `IN_PROGRESS`, or `COMPLETED`.
- `documents_completed`: boolean.

**Success result**:

- Creates onboarding record, writes audit record, redirects to onboarding show page.

### Show Onboarding

```text
GET /hr/onboarding/{id}
Route name: hr.onboarding.show
Controller: HrOnboardingController::show
```

### Update Onboarding

```text
PUT /hr/onboarding/{id}
Route name: hr.onboarding.update
Controller: HrOnboardingController::update
```

**Validation rules**:

- HR Admin only.
- Status value is allowed.
- Start date is valid when provided.

**Success result**:

- Updates status/start date/document completion, writes audit record, redirects to onboarding show page.
