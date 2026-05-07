# AI-Driven Smart Recruitment and Interview Management System

SRIM is a Vanilla PHP MVC web application for managing recruitment from job requisition to candidate onboarding. It supports HR admins, candidates, interviewers, and junior staff through one connected workflow: recruitment, screening, assessments, interviews, feedback governance, offers, onboarding, reporting, compliance, notifications, and user administration.

The project is framework-free. It uses custom routing, controllers, models, policies, services, server-rendered PHP views, native sessions, CSRF protection, PDO database access, and SQL setup files.

## Main Features

- Role-based access for HR admins, candidates, interviewers, and junior staff.
- Job requisition creation, approval, publishing, version history, and governance audit.
- Candidate job browsing, applications, application tracking, and profile management.
- Screening configuration, score recalculation, shortlist generation, triage, duplicate detection, and screening audit.
- Assessment creation, question management, candidate assessment attempts, proctoring events, scoring, and result review.
- Interview scheduling, panel recommendations, briefing refresh, shared workspace, extension requests, and interview audit.
- Interviewer feedback, feedback governance, competency gaps, candidate sentiment, and final evaluation.
- Offer creation, package calculation, offer letters, candidate accept/reject actions, and negotiation support.
- Referrals, simulated background checks, onboarding task tracking, compliance checks, data retention, diversity reports, audit logs, notifications, and user access management.

## Requirements

- PHP 8.2 or newer.
- MySQL 8 or a compatible MySQL database.
- XAMPP with Apache and MySQL enabled.

## Project Setup

1. Put the project folder inside XAMPP `htdocs`.
2. Open the XAMPP Control Panel.
3. Start `Apache` and `MySQL`.
4. Copy `.env.example` to `.env`.
5. Update the database settings in `.env`. For a default local XAMPP setup, these are usually:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=srim
DB_USERNAME=root
DB_PASSWORD=
```

6. Open phpMyAdmin from XAMPP.
7. Create a database named `srim`, or use the name configured in `DB_DATABASE`.
8. Import `srim.sql` into that database if you want the complete demo database with seeded data.
9. If you only need the schema, import `database/schema.sql` and then apply the migration files in `database/migrations` in order.
10. Open the project in the browser through Apache.

Example local URL:

```text
http://localhost/srim
```

If your folder name is different, replace `srim` with your actual folder name under `htdocs`.

## Environment Values

Important `.env` values:

| Key | Purpose |
| --- | --- |
| `APP_NAME` | Application name shown in configuration. |
| `APP_ENV` | Local, testing, or production environment label. |
| `APP_DEBUG` | Shows detailed errors when enabled. Keep disabled outside local development. |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | MySQL connection settings. |
| `FIRST_HR_ADMIN_NAME` | Default first HR admin name. |
| `FIRST_HR_ADMIN_EMAIL` | Default first HR admin email. |
| `FIRST_HR_ADMIN_PASSWORD` | Default first HR admin password. |

Change seeded passwords before any real shared deployment.

## Seeded Demo Accounts

All seeded demo accounts use password `password`.

| Role | Email |
| --- | --- |
| HR Admin | `hr.admin@example.com` |
| Department Head / HR Approver | `dana.head@example.com` |
| Technical Interviewer | `omar.interviewer@example.com` |
| Shadow Interviewer / Junior Staff | `mona.shadow@example.com` |
| Candidate | `lina.candidate@example.com` |
| Additional Candidate | `karim.candidate@example.com` |
| Additional Candidate | `sara.candidate@example.com` |

## Main Navigation By Role

HR Admin:

- Dashboard
- Recruitment
- Interviews
- Feedback
- Offers
- Compliance
- Administration
- Notifications

Candidate:

- Dashboard
- My Profile
- Open Jobs
- My Applications
- Onboarding
- Notifications

Interviewer / Junior Staff:

- Dashboard
- My Interviews
- Notifications

## Recommended Demo Flow

1. Log in as `hr.admin@example.com`.
2. Open Recruitment and review requisitions, applications, screening, shortlist, duplicates, assessments, and assessment results.
3. Open Interviews and review scheduling, panel assignments, briefing, workspace, extensions, completion, and audit.
4. Open Feedback and review governance flags, final evaluation, competency gaps, and candidate sentiment.
5. Open Offers and review offers, letters, referrals, background checks, and onboarding creation.
6. Open Compliance and Administration to review checks, diversity reports, data retention, audit logs, notifications, and users.
7. Log in as `lina.candidate@example.com`.
8. Review profile, open jobs, applications, assessments, interviews, sentiment, offers, onboarding, and notifications.
9. Log in as `omar.interviewer@example.com`.
10. Review assigned interviews, open the workspace, request an extension when needed, and submit feedback.

For the full team testing checklist, use `TEAM_TEST_SCENARIO.md`.

## Project Documentation

- `PROJECT_FOLDER_GUIDE.md` explains each root file, main folder, and application file group.
- `TEAM_TEST_SCENARIO.md` gives the team a practical end-to-end test flow.
- `Diagrams` contains SRS, ERD, architecture, class, activity, object, and use-case documentation.
- `Diagrams/Database/README.md` documents the database diagram artifacts.

## Important Files

| File | Purpose |
| --- | --- |
| `index.php` | App entry point. |
| `bootstrap/app.php` | Loads config, sessions, database, routes, and returns the app instance. |
| `bootstrap/autoload.php` | Custom class autoloader. |
| `routes/web.php` | All web routes and route names. |
| `database/schema.sql` | Main database schema. |
| `srim.sql` | Full demo database import file. |
| `views/layouts/app.php` | Main shared HTML layout and navigation. |

## Development Notes

- This project does not depend on Laravel or another PHP framework.
- Composer is present for PHP version metadata, but application classes are loaded by `bootstrap/autoload.php`.
- Keep secrets in `.env`; do not commit local credentials.
- Runtime files belong in `storage/app` and runtime logs belong in `storage/logs`.
- Public requests are routed through `index.php` and `routes/web.php`.

## Troubleshooting

If the app cannot connect to the database:

- Check that MySQL is running.
- Confirm `.env` database values match your local MySQL settings.
- Confirm the database exists.
- Re-import `srim.sql` if demo tables or demo users are missing.

If login fails:

- Confirm the demo database was imported.
- Use password `password` for seeded accounts.
- Check whether `FIRST_HR_ADMIN_EMAIL` or `FIRST_HR_ADMIN_PASSWORD` was changed in `.env`.

If pages show raw PHP or database errors:

- Confirm PHP 8.2+ is running.
- Confirm database migrations/schema are complete.
- Check `storage/logs` if logs are available.
