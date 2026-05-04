# Quickstart: Final Offer Onboarding

## Prerequisites

- PHP 8.2+ available on the command line.
- Composer dependencies installed if the local environment uses Composer scripts.
- MySQL database configured for SRIM.
- Existing application data includes at least one application with completed assessment or interview feedback evidence.

## Setup

1. Review the specification and plan:

   ```bash
   # Read these files in your editor
   specs/005-final-offer-onboarding/spec.md
   specs/005-final-offer-onboarding/plan.md
   ```

2. Apply schema changes after implementation tasks add them:

   ```bash
   composer run db:schema
   composer run db:seed
   ```

3. Run the project checks:

   ```bash
   composer test
   ```

## Manual Demo Flow

### HR Records Final Evaluation

1. Log in as an active HR Admin.
2. Open an application with assessment score evidence or submitted interview feedback.
3. Open the final evaluation page.
4. Verify the page shows available evidence, missing evidence warnings, aggregate score, and recommendation choices.
5. Save `Hire` or `Strong Hire` with decision notes.
6. Verify the final evaluation is saved and the application becomes offer-eligible.

### HR Creates and Sends Offer

1. From the final evaluation or application page, open offer creation.
2. Enter offer type, compensation, optional bonus/stock, and a future expiry date.
3. Save a draft offer.
4. Send the offer.
5. Verify the offer status is `SENT`, sent time is visible, and application status is `OFFER`.

### Candidate Accepts or Rejects Own Offer

1. Log in as the candidate who owns the application.
2. Open the candidate offer page.
3. Verify only the candidate's own sent offer is visible.
4. Accept the offer before expiry.
5. Verify the offer status is `ACCEPTED`, accepted time is visible, and application status is `HIRED`.

### HR Creates Onboarding

1. Log in as HR Admin.
2. Open the accepted offer.
3. Create onboarding with start date, status, and document-completion state.
4. Verify onboarding appears in the HR onboarding list and duplicate creation is blocked.

### Replacement Offer Path

1. Create and send an offer for a hire recommendation.
2. Log in as the candidate and reject the offer, or set up a sent offer that is past expiry and open it to enforce expiry.
3. Log in as HR Admin and create one replacement offer.
4. Verify another replacement is blocked after the replacement is rejected or expired.

## Required Evidence Before Completion

- `composer test` passes or failures are documented with cause.
- HR final evaluation save is demonstrated with complete and partial evidence cases.
- Offer creation blocks ineligible recommendations and duplicate active offers.
- Candidate ownership checks deny access to another candidate's offer.
- Expired offer acceptance is blocked.
- Accepted offer allows onboarding and duplicate onboarding is blocked.
- Audit records are visible through repository/test evidence or documented database evidence.
