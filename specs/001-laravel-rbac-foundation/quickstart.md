# Quickstart: Laravel RBAC Foundation

This guide describes the expected first-phase setup and verification path after implementation tasks are generated and completed.

## Prerequisites

- PHP 8.2+.
- Composer.
- MySQL 8+.
- Node/npm only if the selected Laravel scaffold requires asset compilation for the Blade layout.

## Setup

1. Install dependencies with `composer install`.
2. Create `.env` from the project example and configure MySQL connection settings.
3. Generate the application key with `php artisan key:generate`.
4. Run migrations with `php artisan migrate`.
5. Seed controlled setup data with `php artisan db:seed`, including the initial active HR admin account.
6. Start the app with `php artisan serve`.

## Demo Flow

1. Open the public registration page and register a candidate with name, email, password, and phone.
2. Confirm the candidate reaches the candidate dashboard and cannot open HR or interviewer pages.
3. Sign in as the seeded HR admin.
4. Create a technical interviewer account and confirm an audit record is produced.
5. Create or update a candidate account role/status as HR and confirm an audit record is produced.
6. Sign in as the technical interviewer and confirm only the interviewer dashboard is accessible.
7. Deactivate a non-HR user and confirm they cannot sign in or continue protected access.
8. Attempt to deactivate or downgrade the last active HR admin and confirm the action is denied.

## Verification Commands

Run the relevant tests after implementation:

```bash
php artisan test
```

Expected test coverage:

- Candidate registration validation and duplicate email handling.
- Login success/failure for active and inactive accounts.
- Role dashboard redirects and cross-role denial.
- HR account creation authorization and validation.
- HR role/status change authorization, validation, and last-active-HR-admin safeguard.
- Candidate profile ownership restrictions.
- Audit record creation for HR account administration.

## Completion Evidence

- Feature tests pass.
- Manual Blade-page demo flow is captured in reviewer notes or screenshots.
- No REST API routes or separated frontend app are introduced.
- Peer reviewer signs off on diagram traceability, RBAC, validation, privacy, and audit coverage.
