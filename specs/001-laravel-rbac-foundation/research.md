# Research: Laravel RBAC Foundation

## Decision: Use Laravel 12.x with PHP 8.2+

**Rationale**: Laravel 12.x is the current stable Laravel line for a new 2026 project and keeps the academic system on maintained framework defaults. PHP 8.2+ satisfies modern Laravel support and provides a realistic baseline for local development.

**Alternatives considered**: Laravel 10/11 for older environment compatibility; rejected because this repository has no existing Laravel scaffold or pinned runtime, so a new first-phase scaffold should use the current stable line. A non-Laravel stack was rejected by the constitution.

## Decision: Implement a single Laravel monolith at repository root

**Rationale**: The constitution requires a monolithic Laravel MVC app with Blade pages, MySQL, sessions, CSRF, middleware, policies, and server-side validation. The repository currently contains planning artifacts and diagrams but no Laravel scaffold, so phase 1 should create the application foundation in the root rather than nesting separate backend/frontend projects.

**Alternatives considered**: Separate frontend/backend projects, REST API backend, or SPA frontend; rejected by the constitution and user input. A nested Laravel subdirectory was rejected because the workspace root is the shared project root and future commands should operate directly against the app.

## Decision: Use baseline `users.role` values for RBAC in phase 1

**Rationale**: The ERD schema defines role values directly on `users`, and the first phase needs only three roles: HR admin, technical interviewer, and candidate. A single role field keeps authorization simple, testable, and aligned with the diagrams.

**Alternatives considered**: Spatie permissions or many-to-many role/permission tables; deferred because granular permission management is not required for this first phase and would add avoidable complexity. Fully hard-coded role checks without policies/middleware were rejected because the constitution requires middleware or policies.

## Decision: Use Blade web forms and Laravel session authentication

**Rationale**: The feature explicitly requires a monolithic Blade app, not an API. Session authentication, CSRF-protected forms, redirects, Form Request validation, password hashing, and role-gated dashboards satisfy the SRS Activity 6 login flow and the constitution.

**Alternatives considered**: Token/JWT authentication, OAuth-only login, and API-first auth; rejected because they are unnecessary and conflict with the first-phase browser-session scope.

## Decision: Seed the first active HR admin account

**Rationale**: A controlled seeded HR admin gives the team a deterministic way to administer phase 1 without exposing public HR registration or building a special one-time setup workflow.

**Alternatives considered**: One-time setup page and manual database account creation; rejected because they either expand the user-facing scope or reduce reproducibility for tests and demos.

## Decision: Candidate self-registration requires name, email, password, and phone

**Rationale**: The clarified spec selected phone capture for phase 1. Storing phone with the candidate profile aligns with the baseline `candidates.phone` field and keeps the public form still small enough to complete quickly.

**Alternatives considered**: Name/email/password only; rejected by clarification. Full candidate profile at registration; rejected because it would expand scope beyond the foundation and slow first-time registration.

## Decision: Audit HR account creation and role/status changes

**Rationale**: The SRS identifies audit trail expectations, and role/status changes are privileged actions that materially affect access. Recording who changed what, when, and for which account provides evidence without implementing full compliance reporting.

**Alternatives considered**: Audit login/logout only or no audit records; rejected because those would not trace the privileged account changes most relevant to RBAC risk.

## Decision: Document UI workflow contracts instead of API contracts

**Rationale**: The project exposes Blade pages and form submissions to browser users, not REST endpoints. A web workflow contract gives task generation and testing enough structure without creating an API surface.

**Alternatives considered**: OpenAPI contracts; rejected because no REST API contract is allowed. No contracts; rejected because form routes, role access, redirects, and validation behavior are user-facing interfaces for this MVC app.
