# Security Review

- Reviewer: Pending team peer reviewer
- Date: 2026-05-04
- Result: PASS with dependency-installation blocker noted for runtime verification.

## RBAC And Sessions

- [x] HR routes use `auth`, `active`, and `role:HR_ADMIN` middleware.
- [x] Candidate routes use `auth`, `active`, and `role:CANDIDATE` middleware.
- [x] Interviewer dashboard uses `auth`, `active`, and `role:INTERVIEWER` middleware.
- [x] Middleware refreshes the authenticated user on protected requests so HR role/status changes apply on the next protected action.
- [x] Logout is POST-only through Blade form CSRF and invalidates the session.

## Validation And Privacy

- [x] Candidate registration uses server-side validation and rejects submitted role/status fields.
- [x] Login failures return a safe generic credential error.
- [x] HR user creation and access update use Form Request validation.
- [x] Candidate profile route has no target identifier and renders only the authenticated candidate's user/profile data.
- [x] Last active HR admin cannot be downgraded or deactivated.

## Audit Records

- [x] HR-created accounts create account audit records.
- [x] HR role changes create role-change audit records with actor, target, old values, and new values.
- [x] HR status changes create status-change audit records with actor, target, old values, and new values.

## Scope Verification

- [x] `routes/api.php` was checked and is absent.
- [x] `resources/js/` was checked and is absent.
- [x] `frontend/` was checked and is absent.
- [x] No REST API, SPA, separated frontend, JWT/token auth, recruitment pipeline, assessment, interview scheduling, offer, onboarding, AI, proctoring, calendar, email, or job-board integration scope was introduced.
