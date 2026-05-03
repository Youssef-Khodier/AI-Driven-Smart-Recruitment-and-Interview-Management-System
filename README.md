# AI-Driven Smart Recruitment and Interview Management System

SRIM is implemented as a Laravel monolithic MVC application with Blade pages, web routes, sessions, CSRF protection, server-side validation, Eloquent models, migrations, seeders, middleware, and policies.

## Requirements

- PHP 8.2 or newer.
- Composer.
- MySQL 8 or compatible database.

## Setup

1. Install dependencies with `composer install`.
2. Copy `.env.example` to `.env` and update database values.
3. Generate the app key with `php artisan key:generate`.
4. Run migrations with `php artisan migrate`.
5. Seed departments and the first active HR admin with `php artisan db:seed`.
6. Start the local server with `php artisan serve`.

## Seeded HR Admin

The first HR admin is controlled by these `.env` values:

- `FIRST_HR_ADMIN_NAME`, default `SRIM HR Admin`
- `FIRST_HR_ADMIN_EMAIL`, default `hr.admin@example.com`
- `FIRST_HR_ADMIN_PASSWORD`, default `password`

Change the password before any shared demo or deployment.

## Verification

Run the Laravel feature test suite with:

```bash
php artisan test
```

The phase-one demo covers candidate registration/login, HR user creation and access changes, interviewer dashboard access, logout, inactive-account denial, last-active-HR-admin protection, and audit record creation.
