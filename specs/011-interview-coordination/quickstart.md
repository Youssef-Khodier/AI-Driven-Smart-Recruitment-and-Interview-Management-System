# Quickstart: Interview Coordination Workflows

## Preconditions

- Database is loaded with the current `database/schema.sql` plus the `011_interview_coordination_workflows.sql` migration when implemented.
- At least one candidate application is in `INTERVIEW` status.
- Active staff accounts exist for HR Admin, Interviewer, and Junior Staff/observer.
- At least one interviewer has senior technical capability for technical or panel interview recommendations.
- The candidate has profile/resume data and, ideally, a completed assessment attempt so briefing snapshots can show assessment context.

## Manual Demo Flow

1. Sign in as HR Admin.
2. Open an eligible application and choose schedule interview.
3. Enter interview type `PANEL` or `TECHNICAL`, a future date/time, and a positive duration.
4. Generate panel recommendations.
5. Verify recommendations show role fit, workload counts, conflict status, and reasons.
6. Accept the recommended HR representative, senior technical interviewer, interviewer, and optional observer.
7. Save the interview and confirm the HR detail page shows assignments and briefing snapshot status.
8. Sign in as the assigned interviewer and open the interview briefing.
9. Open the simulated coding workspace, add interviewer prompt or notes, and save.
10. Sign in as the assigned candidate and open the candidate interview workspace.
11. Save candidate code and run notes.
12. Refresh the interviewer workspace and confirm the latest candidate content appears.
13. From the interviewer session, submit a technical-issue extension request with requested minutes and reason.
14. Sign back in as HR Admin, review the extension request, approve a positive number of minutes, and confirm the updated session time appears.
15. Open the interview audit view or detail page and confirm scheduling, assignment, briefing, workspace, and extension events are recorded.

## Negative Flow Checks

- Try scheduling an interview for an application not in `INTERVIEW` status; expect rejection.
- Try assigning the same staff user twice; expect validation error.
- Try assigning a conflicted staff member without override reason; expect validation error or warning that blocks save.
- Try signing in as an unassigned interviewer and opening the workspace; expect 403.
- Try signing in as observer and submitting official feedback; expect 403.
- Try candidate access to another candidate's interview workspace; expect 403.
- Try approving an extension that creates a new participant conflict; expect HR warning and required acknowledgement.

## Verification Commands

```bash
php -l app/Controllers/HrInterviewController.php
php -l app/Controllers/InterviewerInterviewController.php
php -l app/Repositories/InterviewRepository.php
php -l app/Repositories/InterviewAuditRepository.php
php -l app/Policies/InterviewPolicy.php
php -l routes/web.php
```

## Evidence Checklist

- HR screenshot or notes showing recommendation reasons and balanced panel.
- Interviewer and candidate screenshots or notes showing refresh-based workspace save and latest content.
- HR screenshot or notes showing extension approval and updated session time.
- Audit screenshot or notes showing one event for each required action class.
- Notes showing observer cannot submit official feedback and unassigned access is blocked.

## Manual Acceptance Notes

- [US1] HR balanced-panel manual demo: 
- [US2] Recommendation ranking manual demo:
- [US5] Audit-history manual demo:
- [US3] Simulated workspace manual demo:
- [US4] Extension approval manual demo:

