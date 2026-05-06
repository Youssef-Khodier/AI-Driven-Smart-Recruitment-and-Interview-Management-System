# Web Contract: Offer Onboarding Workflows

This contract documents server-rendered browser routes and form interactions. It is not a REST API contract.

## HR Offer Routes

### List Offers

**Route**: `GET /hr/offers`  
**Controller**: `HrOfferController@index`  
**View**: `views/hr/offers/index.php`  
**Access**: HR Admin only  
**Behavior**: Shows offers with candidate, job, current status, active revision, expiry, background-check gate, and next action. Must not auto-expire offers during page load.

### Create Offer Package

**Route**: `GET /hr/applications/{application_id}/offers/create`  
**Controller**: `HrOfferController@create`  
**View**: `views/hr/offers/form.php`  
**Access**: HR Admin only  
**Preconditions**: Application has Hire or Strong Hire final recommendation and no unresolved blocking evaluation condition.

### Store Offer Package

**Route**: `POST /hr/applications/{application_id}/offers`  
**Controller**: `HrOfferController@store`  
**Access**: HR Admin only  
**Form fields**: `role_level`, `offer_type`, `base_salary`, `bonus_rule_id`, `bonus_amount`, `stock_rule_id`, `stock_options`, `start_date`, `expiry_date`, `manual_override_reason` when applicable, CSRF token.  
**Success**: Creates draft offer package and audit event.  
**Failure**: Redirects with validation message and no partial sent offer.

### Generate Offer Letter

**Route**: `POST /hr/offers/{offer_id}/letter/generate`  
**Controller**: `HrOfferController@generateLetter`  
**Access**: HR Admin only  
**Form fields**: `template_id`, optional `preview_notes`, CSRF token.  
**Success**: Creates digital offer letter snapshot with template version and generated body.  
**Failure**: Blocks generation when template or required placeholders are invalid.

### Preview Offer Letter

**Route**: `GET /hr/offers/{offer_id}/letter`  
**Controller**: `HrOfferController@letter`  
**View**: `views/hr/offers/letter.php`  
**Access**: HR Admin only  
**Behavior**: Shows generated letter snapshot, template version, package totals, and send eligibility.

### Send Offer

**Route**: `POST /hr/offers/{offer_id}/send`  
**Controller**: `HrOfferController@send`  
**Access**: HR Admin only  
**Preconditions**: Offer is draft, has generated valid letter, expiry is future, and no active sent offer already exists.  
**Success**: Marks offer and letter sent, creates notification, records audit event.

### Record Negotiation Decision

**Route**: `POST /hr/offers/{offer_id}/negotiations/{revision_id}`  
**Controller**: `HrOfferController@decideNegotiation`  
**Access**: HR Admin only  
**Form fields**: `decision`, `hr_rationale`, revised compensation/start-date fields when approved, CSRF token.  
**Success**: Updates negotiation revision; approved revisions create a new draft offer package or revised sent offer flow.

## HR Run Checks Routes

### Show Run Checks

**Route**: `GET /hr/run-checks/offers`  
**Controller**: `HrRunChecksController@offers`  
**View**: `views/hr/run-checks/offers.php`  
**Access**: HR Admin only  
**Behavior**: Shows manually runnable checks for expired unsigned offers and eligible accepted offers awaiting simulated background checks.

### Run Offer Expiry Check

**Route**: `POST /hr/run-checks/offers/expiry`  
**Controller**: `HrRunChecksController@expireOffers`  
**Access**: HR Admin only  
**Form fields**: optional selected `offer_ids[]`, `reason`, CSRF token.  
**Success**: Marks selected overdue unsigned offers expired, records run-check result rows and audit events.  
**Constraint**: This is the only workflow that changes offer status to expired.

### Run Simulated Background Checks

**Route**: `POST /hr/run-checks/background-checks`  
**Controller**: `HrRunChecksController@backgroundChecks`  
**Access**: HR Admin only  
**Form fields**: selected `offer_ids[]`, `outcome`, `rationale` when needed, CSRF token.  
**Success**: Creates or updates simulated background-check records and onboarding gate state.

## Candidate Offer Routes

### View Current Offer

**Route**: `GET /candidate/offers/{offer_id}`  
**Controller**: `CandidateOfferController@show`  
**View**: `views/candidate/offers/show.php`  
**Access**: Candidate owns the offer's application  
**Behavior**: Shows current actionable offer details, generated letter, expiry, and response controls. Superseded and expired offers are read-only or hidden according to policy.

### Accept Offer

**Route**: `POST /candidate/offers/{offer_id}/accept`  
**Controller**: `CandidateOfferController@accept`  
**Access**: Candidate owns the current sent offer  
**Form fields**: `signature_name`, `consent_acknowledged`, CSRF token.  
**Success**: Marks offer accepted, records audit event, notifies HR, and waits for required HR Run Checks before onboarding readiness.

### Decline Offer

**Route**: `POST /candidate/offers/{offer_id}/reject`  
**Controller**: `CandidateOfferController@reject`  
**Access**: Candidate owns the current sent offer  
**Form fields**: optional `decline_reason`, CSRF token.  
**Success**: Marks offer rejected, records audit event, notifies HR.

### Request Negotiation

**Route**: `POST /candidate/offers/{offer_id}/negotiate`  
**Controller**: `CandidateOfferController@negotiate`  
**Access**: Candidate owns the current sent offer  
**Form fields**: `requested_base_salary`, `requested_bonus`, `requested_stock_options`, `requested_start_date`, `message`, CSRF token.  
**Success**: Creates negotiation revision, marks offer pending HR review if applicable, notifies HR.

## Candidate Onboarding Routes

### Welcome Portal

**Route**: `GET /candidate/onboarding/{onboarding_id}/welcome`  
**Controller**: `CandidateOnboardingController@welcome`  
**View**: `views/candidate/onboarding/welcome.php`  
**Access**: Candidate owns the onboarding record  
**Behavior**: Shows role, start date, onboarding readiness, background-check-safe status, and document checklist.

### Submit Onboarding Document Item

**Route**: `POST /candidate/onboarding/{onboarding_id}/documents/{document_item_id}`  
**Controller**: `CandidateOnboardingController@submitDocument`  
**Access**: Candidate owns the onboarding record and item  
**Form fields**: document-specific value or file, consent/confirmation checkbox when required, CSRF token.  
**Success**: Saves submitted item for HR review or marks confirmation complete.

## HR Onboarding Routes

### List Onboarding Records

**Route**: `GET /hr/onboarding`  
**Controller**: `HrOnboardingController@index`  
**View**: `views/hr/onboarding/index.php`  
**Access**: HR Admin only

### Review Onboarding Documents

**Route**: `GET /hr/onboarding/{onboarding_id}/documents`  
**Controller**: `HrOnboardingController@documents`  
**View**: `views/hr/onboarding/documents.php`  
**Access**: HR Admin only

### Decide Document Item

**Route**: `POST /hr/onboarding/{onboarding_id}/documents/{document_item_id}/review`  
**Controller**: `HrOnboardingController@reviewDocument`  
**Access**: HR Admin only  
**Form fields**: `decision`, `correction_message` when needed, internal `review_note`, CSRF token.  
**Success**: Updates item review state, candidate-safe correction message, audit event, and onboarding completion when all required items are accepted.

## Cross-Cutting Contract Rules

- Every mutating form requires a valid CSRF token.
- Every route enforces role and ownership policy checks before reading sensitive data.
- Candidate-facing pages never show HR-only rationale, internal background-check details, referral reward details, or unrelated candidate data.
- All state-changing actions write post-offer audit events.
- Offer expiry and simulated background checks are changed only through HR Run Checks routes.
