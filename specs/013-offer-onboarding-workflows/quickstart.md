# Quickstart: Offer Onboarding Workflows

## Prerequisites

- Use an HR Admin account and a Candidate account.
- Ensure the candidate has an application with a final recommendation of Hire or Strong Hire.
- Ensure at least one approved active offer letter template exists.
- Ensure compensation rules exist for the role level and offer type used in the demo.

## Manual Demo Flow

### 1. HR Calculates Offer Package

1. Sign in as HR Admin.
2. Open the candidate's final evaluation or offer-eligible application.
3. Select Create Offer.
4. Enter role level, offer type, base salary, bonus, stock, start date, and expiry date.
5. Save the draft offer.
6. Confirm the offer shows calculated total compensation, rule basis, draft status, and audit history.

Expected result: HR can save a draft only for a Hire or Strong Hire application, and invalid compensation inputs are rejected.

### 2. HR Generates and Sends Versioned Letter

1. From the draft offer page, generate the offer letter from the approved active template.
2. Preview the generated letter.
3. Confirm the template version and generated content snapshot are visible.
4. Send the offer.

Expected result: Candidate receives an in-system notification and can view only their own current offer.

### 3. Candidate Requests Negotiation

1. Sign in as the candidate.
2. Open the current offer.
3. Submit a negotiation request with a changed compensation value or start date and a message.
4. Sign back in as HR Admin.
5. Review the negotiation request, approve or reject it with rationale, and generate a revised offer if approved.

Expected result: Prior revisions remain audit-visible, only the latest sent revision is actionable, and superseded offers cannot be accepted.

### 4. Candidate Accepts Current Offer

1. Sign in as the candidate.
2. Open the current actionable offer.
3. Accept the offer and acknowledge required consent.
4. Confirm the candidate sees an accepted status and a message that HR checks may still be pending.

Expected result: Offer status changes to accepted, HR is notified, and onboarding is not completed until required HR Run Checks are resolved.

### 5. HR Runs Manual Checks

1. Sign in as HR Admin.
2. Open HR Run Checks for offers.
3. Run the expiry check and confirm only overdue unsigned offers are eligible.
4. Run simulated background checks for accepted offers awaiting clearance.
5. Choose Cleared for the accepted candidate.

Expected result: Expiry and background-check outcomes happen only from HR Run Checks. Simulated background-check records are labeled simulated and audit-recorded.

### 6. HR Reviews Referral Attribution

1. Open the accepted and cleared candidate's offer or onboarding record.
2. Confirm referral attribution is created when a valid referrer exists.
3. Hold, reject, or correct the attribution with rationale if needed.

Expected result: Referral reward eligibility is visible to HR with referrer, milestone, status, and audit history. No payroll payout occurs.

### 7. Candidate Completes Welcome Portal Documents

1. Sign in as the candidate.
2. Open the welcome portal.
3. Review role, start date, onboarding status, and document checklist.
4. Submit required document items or confirmations.
5. Sign in as HR Admin and review submitted items.
6. Accept items or request correction with candidate-visible messages.

Expected result: Candidate can complete required items only for their own onboarding record. HR can mark documents complete when all required items are accepted.

## Verification Checklist

- Offer package calculation completes in under 3 minutes.
- Generated offer letter records template version and offer revision.
- Candidate cannot view another candidate's offer or onboarding portal.
- Candidate cannot accept expired or superseded revisions.
- Normal offer page loads do not expire offers automatically.
- HR Run Checks records expiry and background-check outcomes with audit events.
- Background-check outcomes are labeled simulated.
- Onboarding remains blocked for unresolved or failed background-check outcomes.
- Referral attribution records eligibility but does not trigger payroll payout.
- Required onboarding document completion changes are audit-recorded.

## Targeted Technical Checks

- Run PHP syntax checks on changed controllers, policies, repositories, services, and enums.
- Verify route names in `routes/web.php` resolve for HR and candidate flows.
- Verify policy checks deny interviewer, junior staff, and unrelated candidate access to offer/onboarding data.
- Verify database migration applies cleanly to the current schema.
- Verify audit records are written for each state-changing action.
