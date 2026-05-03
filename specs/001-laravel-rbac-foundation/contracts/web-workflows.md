# Web Workflow Contracts: Laravel RBAC Foundation

These are browser-facing Blade page and form contracts. They are not REST API contracts.

## Public Candidate Registration

- **Actor**: Visitor.
- **Entry Page**: Candidate registration page.
- **Submission**: Web form submission with CSRF protection.
- **Required Inputs**: `name`, `email`, `password`, `password_confirmation`, `phone`.
- **Success Result**: Active candidate account and candidate profile are created; user is signed in or prompted to sign in; candidate dashboard is reachable.
- **Validation Failures**: Missing fields, invalid email, duplicate email, weak/mismatched password, invalid phone format.
- **Authorization Rules**: Public registration always creates candidate role only; submitted role values are ignored or rejected.

## Login

- **Actor**: HR admin, technical interviewer, candidate.
- **Entry Page**: Login page.
- **Submission**: Web form submission with CSRF protection.
- **Required Inputs**: `email`, `password`.
- **Success Result**: Active authenticated session starts; user is redirected to dashboard for current role.
- **Failure Result**: Unknown account, invalid credentials, and inactive accounts do not create a session and show a safe error message.
- **Authorization Rules**: Current role and active status are checked before redirecting to dashboard.

## Logout

- **Actor**: Authenticated user.
- **Entry Page**: Any authenticated layout with logout control.
- **Submission**: Web form submission with CSRF protection.
- **Success Result**: Session ends and protected pages require login.

## HR Account Creation

- **Actor**: Authenticated HR admin.
- **Entry Page**: HR user administration page.
- **Submission**: Web form submission with CSRF protection.
- **Required Inputs**: `name`, `email`, `password`, `password_confirmation`, `role`, `status`.
- **Optional Inputs**: `department_id`; `phone` when creating a candidate account.
- **Success Result**: New user account is created; candidate profile is created when role is candidate; account creation audit record is written.
- **Validation Failures**: Missing fields, duplicate email, invalid role, invalid status, invalid department, invalid candidate phone.
- **Authorization Rules**: Non-HR users are denied before any data changes.

## HR Role/Status Change

- **Actor**: Authenticated HR admin.
- **Entry Page**: HR user administration page.
- **Submission**: Web form submission with CSRF protection.
- **Required Inputs**: `role` or `status` change for an existing user.
- **Success Result**: User role/status is updated; audit record is written; updated access applies on the user's next protected action.
- **Validation Failures**: Invalid role/status, target user missing, attempted removal or deactivation of the last active HR admin.
- **Authorization Rules**: Non-HR users are denied before any data changes.

## Role Dashboards

- **Actor**: Authenticated active user.
- **Pages**: HR dashboard, interviewer dashboard, candidate dashboard.
- **Success Result**: User sees only the dashboard matching their current role.
- **Denied Result**: Cross-role dashboard requests are denied and restricted data is not rendered.
- **Privacy Rule**: Candidate dashboard and profile entry points are scoped to the authenticated candidate only.
