# Quickstart: Advanced Assessment Integrity and Adaptive Testing

## Prerequisites

- SRIM is running as the existing framework-free Vanilla PHP MVC application.
- MySQL schema includes baseline recruitment and assessment tables.
- Test users exist for HR Admin and Candidate roles.
- At least one open job requisition and one candidate application exist.

## Setup

1. Apply the schema changes from `database/migrations/010_assessment_integrity_adaptive_testing.sql` if the current database does not already include them.
2. Confirm `database/schema.sql` includes the assessment integrity tables and columns for fresh installs.
3. Sign in as HR Admin.
4. Open a job requisition and create or edit an assessment.

## Manual Demo Flow

### 1. Configure Question Rules And Cooldown

1. Go to HR assessment create/edit.
2. Set duration, cooldown months, and counts for Easy, Medium, and Hard.
3. Save the assessment.
4. Confirm the assessment detail page shows the configured counts and cooldown.

Expected result: valid rules save; invalid counts show validation errors; existing attempts remain unchanged after edits.

### 2. Add Questions And Local Integrity References

1. Add enough active questions to satisfy each configured difficulty count.
2. For a coding question, add one or more hidden expected outputs.
3. Add one or more common-answer records.
4. Save the question.

Expected result: HR can review reference records; candidate pages never expose hidden expected outputs or common answers.

### 3. Start Candidate Attempt

1. Sign in as Candidate.
2. Open the application that requires the assessment.
3. Start the assessment.

Expected result: a randomized question snapshot is created according to configured counts. If the bank is insufficient, the attempt is blocked and HR is alerted.

### 4. Verify Timer Heartbeat And Expiry

1. Open the candidate attempt page.
2. Save at least one answer.
3. Let heartbeat updates persist remaining time.
4. Force or wait for expiry.

Expected result: remaining time is saved, but the server-side deadline controls expiry. Answers saved after expiry do not affect score.

### 5. Submit Coding Answer With Simulated Validation

1. Enter an answer and a stated output for a coding question.
2. Submit the assessment.
3. Sign back in as HR Admin and open the candidate attempt review.

Expected result: HR sees simulated output match and simulated plagiarism similarity. Results are labeled simulated. Similarity at or above 80% is an HR review flag only.

### 6. Verify Adaptive Suggestion

1. Create or seed at least five completed attempts for the assessment.
2. Use average scores <= 50%, >= 80%, and between the two bands in separate checks.
3. Open the HR assessment detail page.

Expected result: HR sees easier, harder, or unchanged suggestions according to the score bands.

### 7. Verify Cooldown

1. Complete or expire a candidate attempt.
2. Try starting the same assessment again before the cooldown ends.
3. Adjust the system data or wait until the cooldown period has elapsed and retry.

Expected result: retake is blocked with a next eligible date during cooldown and allowed after cooldown.

## Targeted Verification Commands

Run syntax checks for touched PHP files:

```bash
php -l app/Controllers/AssessmentController.php
php -l app/Services/SimulatedAssessmentScorer.php
php -l routes/web.php
```

Run view syntax checks for touched assessment templates:

```bash
php -l views/hr/assessments/form.php
php -l views/hr/assessments/show.php
php -l views/hr/assessments/attempt.php
php -l views/hr/assessment-questions/form.php
php -l views/candidate/assessments/show.php
php -l views/candidate/assessments/result.php
```

## Evidence Checklist

- HR assessment rule configuration screenshot or notes.
- Candidate randomized attempt screenshot or notes.
- Heartbeat/expiry behavior notes.
- HR review page showing simulated labels.
- Cooldown blocked-retake screenshot or notes.
- PHP syntax-check output.
