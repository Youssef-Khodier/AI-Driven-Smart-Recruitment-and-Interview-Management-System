# Quickstart: Technical Assessment Management

## Prerequisites

- Existing SRIM Laravel application is installed.
- Existing authentication, RBAC, departments, job requisitions, candidates, and applications are available.
- At least one Candidate has an application that HR can move to `ASSESSMENT` status.

## Verification Commands

```bash
composer test
```

If focused test groups are added during implementation, run the relevant assessment tests in addition to the full suite.

## Implementation Verification Notes

- `php artisan route:list` completed successfully after assessment routes were added.
- `php artisan view:cache` completed successfully, then compiled views were cleared with `php artisan view:clear`.
- `php -l` passed for PHP files under `app/`, `database/`, and `tests/`.
- `php artisan test --filter=Assessment` could not execute in this environment because the configured test connection uses SQLite and the PHP SQLite PDO driver is unavailable (`could not find driver`).

## Manual Demo Path

1. Sign in as HR Admin.
2. Open an existing job requisition.
3. Create a technical assessment with a positive duration.
4. Add at least 10 questions across MCQ, theory/free-text, and coding-as-text question types.
5. Confirm invalid question data shows field-level validation messages.
6. Move or prepare a candidate application so its status is `ASSESSMENT`.
7. Sign in as that Candidate.
8. Start the assessment from the application flow.
9. Confirm questions appear in randomized order and the timer is visible.
10. Answer several questions, refresh the page, and confirm saved answers remain.
11. Switch away from the assessment page and return, then submit before the deadline.
12. Confirm the result page shows a simulated score label.
13. Repeat with a short-duration attempt and submit after the deadline.
14. Confirm the attempt is expired, further edits are blocked, and the score uses only answers saved before the deadline.
15. Sign in as HR Admin and open assessment results for the job.
16. Confirm HR can review candidate, status, simulated score, timing, saved answer evidence, and focus-loss count.
17. Confirm another Candidate cannot view this candidate's attempt or proctoring events.

## Acceptance Evidence

- HR creates a 10-question assessment in under 5 minutes.
- Candidate completes a 10-question assessment in one continuous session.
- Late submission is marked expired in all tested cases.
- Simulated score labels are visible on Candidate and HR pages.
- Focus-loss events appear in HR review with timestamps.
- HR can review 50 attempts for one job in under 30 seconds.
- Unauthorized role access is denied for authoring and other candidates' attempts.

## Known Limitations for This Phase

- No real code execution or hidden test cases.
- No plagiarism detection.
- No webcam, microphone, screen recording, or lockdown browser.
- No retakes or cool-down score reuse.
- No email assessment links or external assessment provider integration.
