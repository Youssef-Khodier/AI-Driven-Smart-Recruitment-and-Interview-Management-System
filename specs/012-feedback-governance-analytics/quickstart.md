# Quickstart: Feedback Governance Analytics

## Prerequisites

- Apply existing schema and migrations through `011_interview_coordination_workflows.sql`.
- Apply the planned `012_feedback_governance_analytics.sql` migration after implementation.
- Seed or create users for HR Admin, Technical Interviewer, Candidate, and observer/junior staff as needed.
- Create an approved/open job requisition, application, completed interview, and official interviewer assignments.

## Manual Demo Flow

1. Log in as HR Admin.
2. Maintain job competency benchmarks for the target job at `/hr/jobs/{jobId}/competency-benchmarks`.
3. Log in as each assigned official interviewer.
4. Submit official interview feedback with scores and comments.
5. For one assigned interviewer, create a serious concern flag and confirm HR receives an in-system notification.
6. Log in as HR Admin and open `/hr/evaluations/{applicationId}/governance`.
7. Confirm the evaluation shows raw scores, normalization eligibility, fallback reasons, missing feedback status if applicable, open flag blockers, and audit trail.
8. Resolve the serious concern flag with rationale.
9. Confirm final decision actions are no longer blocked when all official feedback is complete.
10. Complete the in-app debrief record with participants, consensus, dissent notes when needed, final recommendation, rationale, and next action.
11. Confirm the competency gap visualizer shows each benchmark, candidate score, and severity using 90%/75% thresholds.
12. Log in as the candidate and submit post-interview sentiment for their completed interview.
13. Return as HR and confirm sentiment is visible separately from official scores and excluded from calculations.
14. Open `/hr/reports/feedback-governance` and confirm aggregate counts for open flags, normalization fallbacks, sentiment, debrief completion, and benchmark gaps.
15. Open the HR audit log and confirm governance events are visible with actor, timestamp, action, affected record, and rationale where required.

## Targeted Verification

- Run PHP syntax checks on touched PHP files.
- Verify policy checks for HR-only governance pages, assigned interviewer flags, candidate-owned sentiment, and observer restrictions.
- Verify repository/service tests where practical for normalization threshold, raw-score fallback, debrief duplicate prevention, serious flag blockers, benchmark gap severity, and audit writes.
- Verify invalid CSRF or missing required fields do not persist partial governance state.

## Expected Outcomes

- HR can review governed evaluation results within 2 minutes of final official feedback submission.
- Serious concern flags are auditable and block final decision actions until HR resolution.
- Candidate sentiment is stored once per completed interview and does not affect scoring.
- Debrief records are created exactly once after all official feedback is submitted.
- Competency gap labels use meeting at at least 90% of benchmark, minor gap at 75-89%, and major gap below 75%.

## Known Limits For This Phase

- No external calendar scheduling for debriefs.
- No email or SMS delivery; notifications are in-system only.
- No REST API or SPA visualizer.
- Normalization uses local SRIM historical feedback only.
