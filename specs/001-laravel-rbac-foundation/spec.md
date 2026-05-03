# Feature Specification: Laravel RBAC Foundation

**Feature Branch**: `001-laravel-rbac-foundation`  
**Created**: 2026-05-03  
**Status**: Draft  
**Input**: User description: "Build the Laravel foundation for the AI-Driven Smart Recruitment and Interview Management System. The system is a monolithic Laravel MVC app with Blade pages, not an API. Users can register or be created with roles: HR admin, technical interviewer, and candidate. Users authenticate with sessions, access role-specific dashboards, and are restricted by role-based middleware or policies."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.pdf` sections 1.4, 3.2, 3.4, 5.3, and glossary; `Diagrams/document.md` Core Modules and System Administration & Compliance functions; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/System Architecture/system-architecture.drawio`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`.
- **SRS / Use Case IDs**: RBAC nonfunctional requirement in SRS section 5.3; Role-Based Access Control use case in the use-case diagram; Activity 6 login and role-dashboard flow; foundational support for UC-1 through UC-32 by establishing authenticated actors and access boundaries.
- **Baseline Entities**: `users`, `departments`, and `candidates`; role values HR Admin, Technical Interviewer, and Candidate; account status values Active and Inactive.
- **Baseline Workflow**: User submits login credentials, system verifies credentials, detects role, loads the matching dashboard, applies permissions, and denies invalid or unauthorized access.
- **Scope Decision**: Matches baseline. This feature creates only the identity, session authentication, role dashboard, and access-control foundation needed before recruitment, assessment, interview, offer, onboarding, AI, and integration workflows are built.

## Laravel Delivery Constraints *(mandatory)*

- **Delivery Mode**: Laravel monolithic MVC with Blade server-rendered pages.
- **Routing**: Web routes and form submissions only; no REST API contract.
- **Data Access**: MySQL through Eloquent models and migrations.
- **Security**: Sessions, CSRF protection, server-side validation, middleware, and policies.

## Clarifications

### Session 2026-05-03

- Q: How should the first HR admin account be established for phase 1? → A: Seed the first HR admin account during setup.
- Q: What HR user-management scope belongs in phase 1? → A: Include create users plus change role/status.
- Q: What fields are required for candidate self-registration in phase 1? → A: Name, email, password, phone.
- Q: What actions require audit records in phase 1? → A: Audit HR account and role/status changes.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Candidate Self-Registration and Login (Priority: P1)

A candidate creates an account with name, email, password, and phone number, signs in, and lands on a candidate dashboard where only their own recruitment activity and profile entry points are visible.

**Why this priority**: Candidate identity is the minimum public entry point for the recruitment lifecycle and establishes privacy boundaries for candidate data.

**Independent Test**: Can be fully tested by registering a new candidate, logging in with the new credentials, and confirming the candidate dashboard loads without exposing HR or interviewer features.

**Acceptance Scenarios**:

1. **Given** a visitor with a unique email address, **When** they submit valid candidate registration details including name, email, password, and phone number, **Then** the system creates an active candidate account, signs them in or prompts them to sign in, and shows the candidate dashboard.
2. **Given** a registered candidate, **When** they enter valid credentials, **Then** the system starts an authenticated session and redirects them to the candidate dashboard.
3. **Given** a candidate is signed in, **When** they request an HR admin or interviewer page, **Then** access is denied and no restricted user or candidate data is displayed.

---

### User Story 2 - HR Admin Creates and Manages User Accounts (Priority: P2)

An HR admin signs in, opens the HR dashboard, creates accounts for HR admins, technical interviewers, and candidates, and changes existing users' role or active status when needed.

**Why this priority**: Staff accounts must be controlled by authorized HR users so privileged roles are not self-assigned by public visitors.

**Independent Test**: Can be fully tested by signing in as an HR admin, creating one technical interviewer and one candidate account, changing one user's role or status, and confirming each account receives the expected dashboard and access restrictions after login.

**Acceptance Scenarios**:

1. **Given** an authenticated HR admin, **When** they submit valid details for a new technical interviewer, **Then** the system creates an active interviewer account and the interviewer can access only the interviewer dashboard after login.
2. **Given** an authenticated HR admin, **When** they submit valid details for another HR admin, **Then** the system creates a privileged account and records the selected role and status.
3. **Given** an authenticated HR admin, **When** they change a user's role or active status, **Then** the system applies the updated access level on the user's next protected action.
4. **Given** a non-HR user, **When** they attempt to create users or change role/status, **Then** the system denies the action and does not change account data.

---

### User Story 3 - Technical Interviewer Role Dashboard (Priority: P3)

A technical interviewer signs in and sees an interviewer dashboard focused on assigned interview work, with no access to HR-wide administration or unrelated candidate profiles.

**Why this priority**: Interviewer access is essential before interview and feedback features can be safely added, and it enforces the baseline rule that interviewers only view assigned candidates.

**Independent Test**: Can be fully tested by logging in as a technical interviewer and verifying the interviewer dashboard is shown while HR admin and candidate-only pages remain inaccessible.

**Acceptance Scenarios**:

1. **Given** an active technical interviewer account, **When** the interviewer signs in, **Then** the system loads the interviewer dashboard rather than HR or candidate dashboards.
2. **Given** an authenticated technical interviewer, **When** they request candidate-only profile pages or HR account-management pages, **Then** access is denied and the user remains authenticated without seeing restricted content.

---

### User Story 4 - Session and Account State Protection (Priority: P4)

Authenticated users can securely sign out, and inactive or invalid accounts cannot continue to access protected areas.

**Why this priority**: Session termination and inactive-account handling prevent stale access after role changes, account deactivation, or shared-device use.

**Independent Test**: Can be fully tested by logging in, signing out, attempting to revisit protected pages, and attempting login with inactive or invalid accounts.

**Acceptance Scenarios**:

1. **Given** any authenticated user, **When** they sign out, **Then** their session ends and protected pages require a new login.
2. **Given** an inactive account, **When** the user attempts to sign in, **Then** the system denies access with a clear message and does not create an authenticated session.
3. **Given** a user's role has changed since their last session, **When** they next authenticate, **Then** the system applies the current role and sends them to the current role's dashboard.

### Edge Cases

- A visitor attempts to register with an email address already used by another account.
- A public visitor attempts to self-register as HR admin or technical interviewer.
- A user submits missing, invalid, weak, mismatched, or expired form input.
- An authenticated user manually enters a URL for another role's dashboard.
- A user is deactivated while they have an active session.
- A role is changed after login and the user attempts to keep using the previous role's pages.
- An HR admin attempts to deactivate or downgrade the only available HR admin account.
- Candidate-profile setup fails after account creation and must not leave the candidate with access to another candidate's data.
- An HR admin creates an account or changes a user's role/status and the change must remain traceable for later review.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow public visitors to register a candidate account using name, unique email address, password, and phone number.
- **FR-002**: System MUST prevent public self-registration for HR admin and technical interviewer roles; privileged accounts MUST be created only by an authorized HR admin.
- **FR-003**: System MUST allow an HR admin to create active or inactive accounts for HR admins, technical interviewers, and candidates.
- **FR-004**: System MUST require each user account to have one valid role: HR admin, technical interviewer, or candidate.
- **FR-005**: System MUST require unique email addresses across all user accounts.
- **FR-006**: System MUST validate registration, login, account creation, and account update input before account data changes are accepted.
- **FR-007**: System MUST authenticate users with credentials and create a browser session only when the account is active and credentials are valid.
- **FR-008**: System MUST deny login for inactive accounts, unknown accounts, and accounts with invalid credentials without exposing whether sensitive account details are correct.
- **FR-009**: System MUST terminate the authenticated session when a user signs out.
- **FR-010**: System MUST redirect authenticated users to the dashboard that matches their current role.
- **FR-011**: System MUST provide separate dashboard entry points for HR admins, technical interviewers, and candidates.
- **FR-012**: System MUST restrict every protected page and form action by the user's current role before showing data or accepting changes.
- **FR-013**: System MUST deny access when an authenticated user attempts an action outside their role and MUST keep restricted data hidden.
- **FR-014**: System MUST create or maintain candidate profile data for accounts with the candidate role so future application and assessment flows can attach to the candidate identity.
- **FR-015**: System MUST support optional department association for staff accounts where HR needs to organize users by department.
- **FR-016**: System MUST show user-friendly validation and authorization messages without revealing passwords, password hashes, or other users' private account data.
- **FR-017**: System MUST ensure the only-HR-admin safeguard prevents removal or deactivation of the last active HR admin account.
- **FR-018**: System MUST apply the current stored role and account status on each new authentication and protected access check.
- **FR-019**: System MUST provide an initial active HR admin account through controlled setup data so phase 1 can be administered without public privilege assignment.
- **FR-020**: System MUST allow an HR admin to change an existing user's role or active status, while excluding full profile editing and deletion from phase 1.
- **FR-021**: System MUST record audit information for HR admin account creation, role changes, and active-status changes.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST be limited to account administration, HR dashboard visibility, and foundation-level candidate profile visibility needed to manage users; later recruitment data remains governed by its own feature scope.
- **RP-002**: Technical Interviewer access MUST be limited to the interviewer dashboard and, when later interview data exists, only candidates and interviews assigned to that interviewer.
- **RP-003**: Candidate access MUST be limited to their own profile, applications, assessments, offers, status, and dashboard entry points.
- **RP-004**: Junior Staff or observer access is outside this feature; no junior staff role receives access in this foundation unless a later feature adds it.
- **RP-005**: Candidate PII, resumes, scores, feedback, and offer details MUST be hidden from unauthorized roles.
- **RP-006**: Simulated AI/proctoring decisions are outside this feature; when later introduced, they MUST be labeled as simulated and reviewable by an authorized role.

### Key Entities *(include if feature involves data)*

- **User Account**: A person who can authenticate, identified by name, email, credential, role, account status, and optional department association.
- **Role**: The user's authorization category: HR admin, technical interviewer, or candidate. The role determines dashboard access and permitted actions.
- **Candidate Profile**: Candidate-specific profile attached to a candidate user account, including phone number in phase 1, so future application, assessment, offer, and onboarding records can be linked to the correct person.
- **Department**: Organizational grouping used to associate HR and interviewer accounts with business units where needed.
- **Authenticated Session**: The user's active signed-in state that must end on logout, invalid credentials, inactive account handling, or session expiry.
- **Audit Record**: A traceable record of privileged account administration, including who performed the action, which account was affected, what changed, and when it occurred.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A new candidate can complete account registration and reach the candidate dashboard in under 2 minutes during acceptance testing.
- **SC-002**: An HR admin can create a staff or candidate account and confirm the assigned role in under 90 seconds.
- **SC-003**: 100% of tested attempts to access another role's dashboard or account-management action are denied without exposing restricted data.
- **SC-004**: 95% of successful sign-ins reach the correct role dashboard within 3 seconds in the target browser environment.
- **SC-005**: At least 90% of demo participants can identify their role-specific dashboard and sign out without assistance.
- **SC-006**: No candidate personal information is visible to candidates other than the account owner in role-boundary acceptance tests.
- **SC-007**: 100% of tested HR account creation, role-change, and active-status-change actions produce a traceable audit record.

## Assumptions

- Public self-registration is limited to candidate accounts to prevent public privilege escalation.
- HR admins are responsible for creating technical interviewer accounts and additional HR admin accounts.
- The first HR admin account is seeded through controlled setup data before normal HR account management is available.
- Password reset, email verification, single sign-on, and multi-factor authentication are not part of this foundation unless added in a later feature.
- Full user profile editing and account deletion are outside phase 1; this phase supports account creation plus role and active-status changes.
- Dashboards in this foundation may show empty states or entry points for future recruitment modules until those modules are implemented.
- Browser support follows the SRS target of modern Chrome, Firefox, and Edge versions.
