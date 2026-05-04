# AI-Driven Smart Recruitment and Interview Management System

SRIM is implemented as a framework-free Vanilla PHP monolithic MVC application with server-rendered PHP templates, web routes, native sessions, CSRF protection, server-side validation, PDO-backed models, SQL schema/seed scripts, middleware-style guards, and policies.

## Requirements

- PHP 8.2 or newer.
- MySQL 8 or compatible database.

## Setup

1. Copy `.env.example` to `.env` and update database values.
2. Import the database schema with `mysql -u root -p < database/schema.sql`.
3. Seed departments and the first active HR admin with `php scripts/seed.php`.
4. Start the local server with `php -S 127.0.0.1:8000 -t public`.

## Seeded HR Admin

The first HR admin is controlled by these `.env` values:

- `FIRST_HR_ADMIN_NAME`, default `SRIM HR Admin`
- `FIRST_HR_ADMIN_EMAIL`, default `hr.admin@example.com`
- `FIRST_HR_ADMIN_PASSWORD`, default `password`

Change the password before any shared demo or deployment.

## Verification

Run syntax verification with:

```bash
php scripts/check.php
```

The phase-one demo covers candidate registration/login, HR user creation and access changes, interviewer dashboard access, logout, inactive-account denial, last-active-HR-admin protection, and audit record creation.
