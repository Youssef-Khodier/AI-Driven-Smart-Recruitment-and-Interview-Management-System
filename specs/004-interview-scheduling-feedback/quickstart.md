# Quickstart: Interview Scheduling Feedback

## Prerequisites

- PHP 8.2+ available on the command line.
- MySQL database configured through the existing `.env` values.
- Existing Vanilla PHP MVC foundation, RBAC, requisitions/applications, and assessment management features available.

## Setup

1. Install dependencies if needed: `composer install`
2. Prepare local configuration from `.env.example` if needed.
3. Load schema and demo data after implementation updates: `composer db:schema` and `composer db:seed`
4. Run verification: `composer test`

## Demo Flow

1. Log in as HR Admin.
2. Confirm a candidate application has status `INTERVIEW`.
3. Open the HR interview schedule form for that application.
4. Schedule a future interview with one official interviewer and one observer.
5. Attempt a second overlapping interview for the same interviewer and confirm the save is blocked.
6. Log in as the assigned official interviewer.
7. Open assigned interviews and view the candidate/job/assessment briefing.
8. Log in as HR Admin and mark the interview `COMPLETED`.
9. Log in as the official interviewer and submit technical, communication, culture fit, overall scores, and comments.
10. Log in as HR Admin and confirm feedback completion state and submitted feedback are visible.
11. Log in as the observer and confirm the briefing is viewable but official feedback submission is denied or unavailable.

## Acceptance Evidence

### US1 Verification Checklist
- [x] HR Admin can schedule an interview for an INTERVIEW application.
- [x] One official interviewer and one observer can be assigned.
- [x] Interview saves successfully.
- [x] Attempting to save an overlapping interview is blocked by conflict detection.
- [x] HR Admin can view the scheduled interview in the list.

### US2 Verification Checklist
- [x] Assigned interviewer sees only their assigned interviews in the list.
- [x] Assigned interviewer can open the briefing and see candidate, job, and assessment data.
- [x] Missing assessment data is indicated gracefully.
- [x] Unassigned interviewers are denied access.

### US3 Verification Checklist
- [x] HR Admin can mark a scheduled interview as COMPLETED.
- [x] Official assigned interviewer can submit valid scores and comments for a completed interview.
- [x] Duplicate feedback submission is blocked.
- [x] Feedback state updates immediately for HR.

### US4 Verification Checklist
- [x] JUNIOR_STAFF user account can be created by HR.
- [x] Observer access displays "Observer access - training only".
- [x] Observers cannot submit official feedback (link hidden, POST denied).

### Test Results
- US1 implementation tests (`composer test`): Passed
- US2 implementation tests (`composer test`): Passed
- US3 implementation tests (`composer test`): Passed
- US4 implementation tests (`composer test`): Passed
- All validation, authorization, and syntax checks passed successfully.

- HR scheduling with one interviewer and one observer completes in under 3 minutes.
- Overlap for same application or selected staff is blocked before save.
- Assigned interviewer opens briefing in under 2 minutes.
- Feedback submission after completion succeeds in under 3 minutes.
- Candidate, unassigned interviewer, inactive user, and observer official-feedback attempts are denied.
- Audit records show actor, action, timestamp, and changed fields for schedule/status/feedback actions.

## Known Out Of Scope

- External calendar booking.
- Email invitations and reminders.
- Video links and live coding synchronization.
- Automatic load balancing.
- Score normalization and final hiring recommendation automation.
- Feedback revision workflow.
