# Route Map: Final Offer Onboarding

## HR Final Evaluation

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/hr/applications/{id}/final-evaluation` | `hr.final-evaluations.show` | `HrFinalEvaluationController::show` | Review evidence and final evaluation form |
| POST | `/hr/applications/{id}/final-evaluation` | `hr.final-evaluations.store` | `HrFinalEvaluationController::store` | Save final recommendation |

## HR Offers

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/hr/offers` | `hr.offers.index` | `HrOfferController::index` | Track all offers and expiry state |
| GET | `/hr/applications/{id}/offers/create` | `hr.offers.create` | `HrOfferController::create` | Create original or allowed replacement offer |
| POST | `/hr/applications/{id}/offers` | `hr.offers.store` | `HrOfferController::store` | Store draft offer |
| GET | `/hr/offers/{id}` | `hr.offers.show` | `HrOfferController::show` | View offer package, response, and onboarding eligibility |
| POST | `/hr/offers/{id}/send` | `hr.offers.send` | `HrOfferController::send` | Mark complete draft as sent |

## Candidate Offers

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/candidate/offers/{id}` | `candidate.offers.show` | `CandidateOfferController::show` | View own sent offer |
| POST | `/candidate/offers/{id}/accept` | `candidate.offers.accept` | `CandidateOfferController::accept` | Accept own unexpired sent offer |
| POST | `/candidate/offers/{id}/reject` | `candidate.offers.reject` | `CandidateOfferController::reject` | Reject own unexpired sent offer |

## HR Onboarding

| Method | Path | Route Name | Controller Action | Purpose |
|--------|------|------------|-------------------|---------|
| GET | `/hr/onboarding` | `hr.onboarding.index` | `HrOnboardingController::index` | Track onboarding records |
| GET | `/hr/offers/{id}/onboarding/create` | `hr.onboarding.create` | `HrOnboardingController::create` | Create onboarding for accepted offer |
| POST | `/hr/offers/{id}/onboarding` | `hr.onboarding.store` | `HrOnboardingController::store` | Store onboarding record |
| GET | `/hr/onboarding/{id}` | `hr.onboarding.show` | `HrOnboardingController::show` | View onboarding progress |
| PUT | `/hr/onboarding/{id}` | `hr.onboarding.update` | `HrOnboardingController::update` | Update onboarding progress |

## Navigation Entry Points

- HR application/show or application list should link to final evaluation when an application has assessment or interview evidence.
- HR final evaluation page should link to offer creation when recommendation is `STRONG_HIRE` or `HIRE`.
- HR offer show page should link to onboarding creation when offer is accepted and no onboarding exists.
- Candidate application or dashboard should link to own sent offer when an offer exists.

## Guard Expectations

- HR routes require active HR Admin role.
- Candidate offer routes require active Candidate role and ownership of the offer's application.
- Mutating routes require CSRF validation.
- Expiry enforcement runs before rendering sent offers and before accepting/rejecting a sent offer.
