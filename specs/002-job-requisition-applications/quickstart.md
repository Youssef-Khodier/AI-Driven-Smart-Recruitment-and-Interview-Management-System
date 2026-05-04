# Quickstart: Job Requisition and Candidate Applications

## Prerequisites

- Laravel foundation and RBAC feature are implemented and passing.
- At least two active HR Admin accounts exist to test different-admin approval.
- At least one active Candidate account exists with a matching Candidate Profile.
- At least one Department exists.

## Setup

1. Install dependencies if needed: `composer install`
2. Prepare environment if needed: copy `.env.example` to `.env` and run `php artisan key:generate`
3. Run migrations and seeders: `php artisan migrate:fresh --seed`
4. Start the application for manual demo: `php artisan serve`

## Automated Verification

Run the full test suite:

```bash
php artisan test
```

Expected coverage after implementation:

- HR authorization and active-account enforcement for requisition pages and forms.
- Candidate authorization and ownership enforcement for jobs and applications.
- Requisition create, edit, submit, approve, open, close workflows.
- Different-HR approval rule and self-approval denial.
- Stale requisition edit conflict handling.
- Candidate profile validation including comma-separated skill keywords.
- Candidate open-job browsing and closed/non-open job hiding.
- Candidate apply-once rule and duplicate application blocking.
- Simulated match score persistence and advisory label visibility.
- HR applicant review and application status update history.

## Manual Demo Path

1. Sign in as HR Admin A.
2. Create a Draft requisition with title, department, description, and requirements.
3. Edit the Draft and confirm stale-edit protection by attempting to save from an older edit form after another save.
4. Submit the Draft for approval and confirm it is Pending Approval.
5. Attempt approval as HR Admin A and confirm self-approval is denied.
6. Sign in as HR Admin B and approve the requisition.
7. Open the Approved requisition.
8. Sign in as Candidate and complete profile fields, including a comma-separated skills or keywords list.
9. Browse open jobs and apply to the Open requisition.
10. Confirm application status is Applied and the simulated score is labeled advisory.
11. Attempt to apply to the same job again and confirm the duplicate is blocked.
12. Sign in as HR Admin and review applicants for the requisition.
13. Change the application status to Screening, then Interview or Rejected, and confirm status history is recorded.
14. Sign in as Candidate and confirm the exact pipeline status is visible only for their own application.
15. Close the requisition and confirm new applications are blocked while existing application tracking remains visible.

## Evidence Checklist

- Passing `php artisan test` output.
- Screenshots or notes for HR requisition lifecycle pages.
- Screenshots or notes for candidate profile, job browsing, application, duplicate block, and tracking pages.
- Screenshots or notes proving self-approval denial and candidate privacy denial.
- Known limitations list confirming external job boards, email, real resume parsing, real AI/NLP, assessments, interviews, offers, onboarding, and analytics are out of scope.

## Implementation Evidence Notes

- Implemented as Laravel web routes, controllers, Form Requests, policies, Eloquent models, migrations, and Blade views; no REST API or separated frontend was introduced.
- Blade mutating forms include CSRF tokens; shared layout renders validation errors and success messages.
- HR and candidate views include empty states for no requisitions, no open jobs, no applicants, and no candidate applications.
- Simulated match scores are labeled advisory in candidate job detail, candidate application tracking, and HR applicant review pages.
- Automated tests and `vendor/bin/pint` could not be executed in this environment because Composer is not installed and `vendor/autoload.php` is missing.
- PHP syntax checks were executed for changed and newly added PHP/Blade files with `php -l`; no syntax errors were reported.

## Known Limitations

- External job boards, email, real resume parsing, real AI/NLP, assessments, interviews, offers, onboarding, and analytics remain out of scope for this phase.
- Candidate resumes remain URL/reference strings; file upload and storage are intentionally not implemented.
- Peer-review gate tasks remain open until a separate human reviewer confirms diagram traceability, RBAC, privacy, migrations, and acceptance coverage.
