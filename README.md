# AI-Driven Smart Recruitment and Interview Management System

SRIM is implemented as a framework-free Vanilla PHP monolithic MVC application with a direct `app/Models` folder, server-rendered PHP templates, web routes, native sessions, CSRF protection, server-side validation, PDO-backed model access, SQL database setup, middleware-style guards, services, and policies.

The visible in-app navigation is intentionally pruned to the grouped workflows required by the 42 mapped functions: Recruitment, Assessments, Interviews, Feedback, Offers & Onboarding, and Administration & Compliance.

## Requirements

- PHP 8.2 or newer.
- MySQL 8 or compatible database.

## Setup

1. Copy `.env.example` to `.env` and update database values.
2. Import the provided SQL database file into MySQL. The SQL file should include the schema and demo data.
3. Start the local server from the project root with `php -S 127.0.0.1:8000`.

## Seeded Demo Accounts

All seeded demo accounts use password `password`.

- HR Admin: `hr.admin@example.com` by default, or the email configured in `FIRST_HR_ADMIN_EMAIL`
- Department Head / HR approver: `dana.head@example.com`
- Technical Interviewer: `omar.interviewer@example.com`
- Shadow Interviewer: `mona.shadow@example.com`
- Candidate: `lina.candidate@example.com`
- Additional candidate data: `karim.candidate@example.com`, `sara.candidate@example.com`

The first HR admin is still controlled by these `.env` values:

- `FIRST_HR_ADMIN_NAME`, default `SRIM HR Admin`
- `FIRST_HR_ADMIN_EMAIL`, default `hr.admin@example.com`
- `FIRST_HR_ADMIN_PASSWORD`, default `password`

Change the password before any shared demo or deployment.

## Demo Script

1. Log in as `hr.admin@example.com` and open `Recruitment` from the top navigation.
2. Open a requisition, then use the requisition detail page for approvals, publishing, versions, applications, screening, shortlist, duplicates, assessments, and assessment results.
3. From eligible applications, schedule interviews, review panel recommendations, refresh briefings, open workspace snapshots, process extensions, and mark interviews complete.
4. Open `Feedback` to review governance flags, debrief readiness, competency gaps, candidate sentiment, and final evaluations.
5. Open `Offers` to create offers, generate letters, review revision chains, manage referrals, run background checks, and create onboarding after acceptance.
6. Open `Compliance` and `Administration` to run notification checks, archive closed or rejected records, review diversity data, audit logs, retention actions, notifications, and user access.
7. Log in as `lina.candidate@example.com` to browse jobs, apply, start assessments, review interview sentiment, accept or reject offers, and complete onboarding tasks.
8. Log in as `omar.interviewer@example.com` or `mona.shadow@example.com` to open `My Interviews`, review briefings, use the coding workspace, request extensions, and submit or observe feedback according to role.

The seeded academic demo includes departments, requisitions, screening configuration, candidates, applications across statuses, assessments with difficulty rules, expected code outputs, plagiarism common answers, interviews, panel assignments, feedback, offers, referrals, background checks, onboarding task progress, notifications, audit records, template versions, and demographic data for diversity reporting.
