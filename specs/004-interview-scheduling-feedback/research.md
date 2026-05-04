# Research: Interview Scheduling Feedback

## Decision: Use Server-Rendered Web Workflows Only

**Rationale**: The constitution requires a framework-free Vanilla PHP monolithic MVC application using `routes/web.php`, templates, sessions, CSRF, controller actions, and redirects. Scheduling and feedback are form-based HR/interviewer workflows that fit normal server-rendered pages.

**Alternatives considered**: REST endpoints, SPA screens, calendar webhooks, and client-side scheduling widgets were rejected because they add interfaces and dependencies outside this feature's approved scope.

## Decision: Interview Eligibility Is Application Status `INTERVIEW`

**Rationale**: The clarified spec states only applications with status `INTERVIEW` can be scheduled. This aligns with the baseline lifecycle after assessment pass and before offer/final evaluation.

**Alternatives considered**: Allowing `ASSESSMENT` or any active application was rejected because it would let HR skip pipeline stages and complicate acceptance tests.

## Decision: Block Stored Schedule Conflicts

**Rationale**: The feature requires avoiding conflicts, and clarification chose a hard block. Conflict detection will compare requested start/end against non-cancelled stored interviews for the same application and all selected panel users.

**Alternatives considered**: Warning-only saves and HR override were rejected because they produce inconsistent schedules and weaken testability.

## Decision: Keep Interview Briefings Derived, Not Stored Files

**Rationale**: Briefings are a view over existing candidate, job, application, assessment, attempt, and submission records. Deriving the briefing avoids document generation scope while still satisfying interviewer preparation needs and partial-data flags.

**Alternatives considered**: Persisted briefing packs and generated files were rejected because file storage and template versioning are out of scope.

## Decision: Separate Assignments From Feedback

**Rationale**: Baseline tables already separate `interviewers_assignment` and `interview_feedback`. Keeping them separate supports observer read-only access, official scorer counts, and one-feedback-per-official-interviewer rules.

**Alternatives considered**: Storing panel users directly on `interviews` was rejected because panels can contain multiple official interviewers and observers.

## Decision: Feedback Requires `COMPLETED` Interview Status

**Rationale**: Clarification requires official feedback only after an interview is marked completed. This prevents premature scoring and gives a deterministic validation rule.

**Alternatives considered**: Allowing feedback after scheduled start time or anytime for assigned interviewers was rejected because it can record evaluations before the session actually happened.

## Decision: Observer and Junior Staff Access Is Assignment-Bound and Read-Only

**Rationale**: UC-17 requires shadowing that does not affect official scoring. Assignment-bound access allows constrained Junior Staff accounts and interviewer accounts assigned as observers to view only assigned interviews while preventing official feedback submission.

**Alternatives considered**: Global observer access and observer scoring were rejected because they violate least privilege and evaluation integrity.

## Decision: Add Interview Audit Records

**Rationale**: Clarification requires actor, action, timestamp, and changed fields for schedule and feedback traceability. A dedicated audit record keeps evidence consistent across create, reschedule, cancel, complete, and feedback submit actions.

**Alternatives considered**: Relying only on timestamps was rejected because it does not identify changed fields. Full before/after snapshots plus reasons were rejected as larger than required for this phase.

## Decision: Defer External Calendar, Email, Load Balancing, and Recommendations

**Rationale**: The spec explicitly excludes external calendar booking, email dispatch, live coding, automated load balancing, score normalization, consensus meetings, and final recommendations. The implementation should remain a working in-system vertical slice.

**Alternatives considered**: Integrating Google Calendar or SMTP now was rejected because the constitution requires simulated/deferred integrations unless explicitly in scope.
