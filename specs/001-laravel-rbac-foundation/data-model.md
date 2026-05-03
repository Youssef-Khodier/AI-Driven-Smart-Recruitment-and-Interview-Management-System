# Data Model: Laravel RBAC Foundation

## Entity: Department

Represents an organizational department used to group HR admin and technical interviewer accounts.

### Fields

- `department_id`: Unique identifier.
- `name`: Required unique department name, maximum 120 characters.
- `description`: Optional department description.
- `parent_department_id`: Optional parent department reference.
- `created_at`, `updated_at`: Timestamps.

### Relationships

- A department may have one parent department.
- A department may have many user accounts.

### Validation Rules

- Name is required and unique.
- Parent department, if present, must reference an existing department and must not create invalid hierarchy cycles.

## Entity: User Account

Represents a person who can authenticate and access one role-specific dashboard.

### Fields

- `user_id`: Unique identifier.
- `department_id`: Optional department reference for staff accounts.
- `name`: Required display name, maximum 160 characters.
- `email`: Required unique email address, maximum 180 characters.
- `password_hash`: Required hashed credential; raw passwords are never stored.
- `role`: Required value: `HR_ADMIN`, `INTERVIEWER`, or `CANDIDATE`.
- `status`: Required value: `ACTIVE` or `INACTIVE`; defaults to `ACTIVE`.
- `created_at`, `updated_at`: Timestamps.

### Relationships

- A user may belong to one department.
- A candidate user has exactly one candidate profile.
- A user may perform many account audit actions.

### Validation Rules

- Email must be unique and valid.
- Password must satisfy the selected Laravel password validation defaults for phase 1.
- Role must be one of the three phase-1 roles.
- Public registration may only create `CANDIDATE` users.
- HR admin account creation may create any phase-1 role.
- The last active HR admin must not be deactivated or changed away from HR admin.

### State Transitions

```text
ACTIVE -> INACTIVE: HR admin deactivates account, except last active HR admin.
INACTIVE -> ACTIVE: HR admin reactivates account.
role A -> role B: HR admin changes role, except changes that remove the last active HR admin.
```

## Entity: Candidate Profile

Represents candidate-specific contact/profile data attached to a candidate user.

### Fields

- `candidate_id`: Same identifier as the linked candidate user account.
- `phone`: Required for phase-1 candidate self-registration.
- `current_title`: Optional; deferred for later profile completion.
- `years_experience`: Defaults to 0; deferred for later profile completion.
- `location`: Optional; deferred for later profile completion.
- `resume_url`: Optional; deferred for later profile completion.
- `created_at`, `updated_at`: Timestamps.

### Relationships

- Belongs to exactly one `User Account` with role `CANDIDATE`.
- Future application, assessment, offer, and onboarding records link through this candidate identity.

### Validation Rules

- Candidate profile must be created when a candidate user is created.
- Phone is required for public candidate registration in phase 1.
- Candidate pages must only expose the authenticated candidate's own profile.

## Entity: Authenticated Session

Represents a user's active signed-in browser session.

### Fields

- Session identifier managed by the framework.
- Authenticated user reference.
- Session creation and expiry metadata managed by the framework.

### Relationships

- Belongs to one authenticated user at a time.

### Validation Rules

- Sessions are created only for active accounts with valid credentials.
- Sessions end on sign-out.
- Protected pages and form actions must re-check current user role and status.

## Entity: Account Audit Record

Represents traceability for privileged HR account administration actions.

### Fields

- `audit_id`: Unique identifier.
- `actor_user_id`: Required HR admin who performed the action.
- `target_user_id`: Required affected user account.
- `action`: Required value such as `USER_CREATED`, `ROLE_CHANGED`, or `STATUS_CHANGED`.
- `old_values`: Optional structured snapshot of relevant changed values before the action.
- `new_values`: Required structured snapshot of relevant changed values after the action.
- `created_at`: Timestamp when the action occurred.

### Relationships

- Belongs to one actor user account.
- Belongs to one target user account.

### Validation Rules

- Audit record must be created for HR account creation, role changes, and status changes.
- Audit record must identify actor, target, action, changed values, and timestamp.
- Audit records are append-only in normal application workflows.

## Derived Authorization Rules

- HR admin can access HR dashboard and phase-1 user administration.
- HR admin can create users and change role/status, subject to last-active-HR-admin protection.
- Technical interviewer can access interviewer dashboard only.
- Candidate can access candidate dashboard and their own candidate profile only.
- Inactive users cannot authenticate or continue using protected pages.
