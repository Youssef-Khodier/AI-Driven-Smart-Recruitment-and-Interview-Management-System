# Route Map: Offer Onboarding Workflows

## Existing Routes To Keep and Refine

| Route | Controller | View | Planned Change |
|-------|------------|------|----------------|
| `GET /hr/offers` | `HrOfferController@index` | `views/hr/offers/index.php` | Remove automatic expiry side effects; show HR Run Checks links and offer status summary. |
| `GET /hr/applications/{id}/offers/create` | `HrOfferController@create` | `views/hr/offers/form.php` | Add role level, base salary, rule-based bonus/stock fields, start date, and template readiness. |
| `POST /hr/applications/{id}/offers` | `HrOfferController@store` | Redirect | Save calculated draft with calculation snapshot and audit event. |
| `GET /hr/offers/{id}` | `HrOfferController@show` | `views/hr/offers/show.php` | Remove read-time expiry; display revisions, generated letter, run-check state, background-check gate, referral state, and audit trail. |
| `POST /hr/offers/{id}/send` | `HrOfferController@send` | Redirect | Require generated letter snapshot and no active current offer conflict. |
| `GET /candidate/offers/{id}` | `CandidateOfferController@show` | `views/candidate/offers/show.php` | Remove read-time expiry; show generated letter snapshot, current revision only, response/negotiation controls. |
| `POST /candidate/offers/{id}/accept` | `CandidateOfferController@accept` | Redirect | Record signature/consent fields and wait for HR Run Checks before onboarding readiness. |
| `POST /candidate/offers/{id}/reject` | `CandidateOfferController@reject` | Redirect | Preserve decline reason and audit event. |
| `GET /hr/onboarding` | `HrOnboardingController@index` | `views/hr/onboarding/index.php` | Add readiness, background-check status, and document completion filters. |
| `GET /hr/offers/{id}/onboarding/create` | `HrOnboardingController@create` | `views/hr/onboarding/form.php` | Gate onboarding creation on accepted current offer and cleared simulated background check. |
| `POST /hr/offers/{id}/onboarding` | `HrOnboardingController@store` | Redirect | Create onboarding record and default document checklist. |
| `GET /hr/onboarding/{id}` | `HrOnboardingController@show` | `views/hr/onboarding/show.php` | Show checklist, review state, candidate-safe correction messages, and audit events. |
| `PUT /hr/onboarding/{id}` | `HrOnboardingController@update` | Redirect | Preserve manual HR updates but prevent completed state when required documents/background-check gates are unresolved. |

## New HR Routes

| Route | Controller | View | Purpose |
|-------|------------|------|---------|
| `POST /hr/offers/{id}/letter/generate` | `HrOfferController@generateLetter` | Redirect | Generate versioned offer letter snapshot from approved template. |
| `GET /hr/offers/{id}/letter` | `HrOfferController@letter` | `views/hr/offers/letter.php` | Preview generated offer letter and template version. |
| `GET /hr/offers/{id}/negotiations` | `HrOfferController@negotiations` | `views/hr/offers/negotiation.php` | Review negotiation history and open candidate requests. |
| `POST /hr/offers/{id}/negotiations/{revision_id}` | `HrOfferController@decideNegotiation` | Redirect | Approve, reject, hold, or revise a candidate counter-offer. |
| `GET /hr/run-checks/offers` | `HrRunChecksController@offers` | `views/hr/run-checks/offers.php` | Show manual expiry/background-check check dashboard. |
| `POST /hr/run-checks/offers/expiry` | `HrRunChecksController@expireOffers` | Redirect | Manually mark overdue unsigned offers expired. |
| `POST /hr/run-checks/background-checks` | `HrRunChecksController@backgroundChecks` | Redirect | Manually record simulated background-check outcomes. |
| `GET /hr/onboarding/{id}/documents` | `HrOnboardingController@documents` | `views/hr/onboarding/documents.php` | Review candidate day-one document checklist. |
| `POST /hr/onboarding/{id}/documents/{document_item_id}/review` | `HrOnboardingController@reviewDocument` | Redirect | Accept or request correction for submitted document item. |

## New Candidate Routes

| Route | Controller | View | Purpose |
|-------|------------|------|---------|
| `POST /candidate/offers/{id}/negotiate` | `CandidateOfferController@negotiate` | Redirect | Submit counter-offer or requested start-date change for the current sent offer. |
| `GET /candidate/onboarding/{id}/welcome` | `CandidateOnboardingController@welcome` | `views/candidate/onboarding/welcome.php` | Candidate welcome portal after accepted and cleared offer. |
| `GET /candidate/onboarding/{id}/documents` | `CandidateOnboardingController@documents` | `views/candidate/onboarding/documents.php` | View document checklist and submission status. |
| `POST /candidate/onboarding/{id}/documents/{document_item_id}` | `CandidateOnboardingController@submitDocument` | Redirect | Submit or confirm one required day-one document item. |

## Authorization Map

- HR Admin: full post-offer management through offer, run-check, background-check, referral, onboarding, document review, notification, and audit screens.
- Candidate: own current offer response, own negotiation request, own welcome portal, and own onboarding document submissions.
- Technical Interviewer: no offer, compensation, background-check, referral, or onboarding document access by default.
- Junior Staff/Observer: no offer, compensation, background-check, referral, or onboarding document access by default.

## Manual Check Boundary

- `OfferRepository::enforceExpiryForOffer()` style read-time expiry must not be called from normal list/show/candidate view actions after this feature.
- Expiry changes must be made by `HrRunChecksController@expireOffers` only.
- Simulated background-check creation/outcome changes must be made by HR Run Checks or explicit HR review actions only.
