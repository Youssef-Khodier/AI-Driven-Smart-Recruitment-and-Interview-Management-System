# Route Map: Compliance Reporting Maintenance

## Existing Routes To Extend

| Route | Controller | View | Change |
|-------|------------|------|--------|
| `GET /hr/reports/pipeline` | `HrReportController::pipeline` | `views/hr/reports/pipeline.php` | Add filters, stage age, conversion rates, time-to-hire, and bottleneck labels. |
| `GET /hr/reports/time-to-hire` | `HrReportController::timeToHire` | `views/hr/reports/time-to-hire.php` | Keep as supporting report or link into enhanced pipeline analytics. |
| `POST /hr/checks/run` | `HrComplianceCheckController::run` | Redirect currently | Replace or wrap with new run-check index/detail flow while preserving manual trigger behavior. |
| `GET /notifications` | `NotificationController::index` | `views/notifications/index.php` | Display new escalation notification types and reference labels. |
| `GET /candidate/profile` | `CandidateController::profile` | `views/candidate/profile.php` | Show optional demographic disclosure controls if profile ownership is confirmed. |

## New HR Report Routes

| Route | Controller | View | Purpose |
|-------|------------|------|---------|
| `GET /hr/reports/diversity` | `HrReportController::diversity` | `views/hr/reports/diversity.php` | Aggregate D&I report with privacy suppression and "Not provided" totals. |

## New Run Check Routes

| Route | Controller | View | Purpose |
|-------|------------|------|---------|
| `GET /hr/run-checks` | `HrComplianceCheckController::index` | `views/hr/run-checks/index.php` | List recent HR-triggered maintenance batches. |
| `POST /hr/run-checks` | `HrComplianceCheckController::store` | Redirect | Run selected checks and redirect to batch details. |
| `GET /hr/run-checks/{id}` | `HrComplianceCheckController::show` | `views/hr/run-checks/show.php` | Show findings, duplicate skips, notifications, archive recommendations, and blockers. |

## New Archive Routes

| Route | Controller | View | Purpose |
|-------|------------|------|---------|
| `GET /hr/archive` | `HrDataRetentionController::archiveIndex` or `HrComplianceCheckController::archiveIndex` | `views/hr/archive/index.php` | Review archive recommendations and history. |
| `GET /hr/archive/{entityType}/{id}` | `HrDataRetentionController::archiveShow` | `views/hr/archive/show.php` | Show archive eligibility, blockers, and audit history. |
| `POST /hr/archive/{entityType}/{id}/approve` | `HrDataRetentionController::approveArchive` | Redirect | Revalidate and apply archive action with reason. |

## New Candidate Disclosure Route

| Route | Controller | View | Purpose |
|-------|------------|------|---------|
| `POST /candidate/profile/demographics` | `CandidateController::updateDemographics` | Redirect | Save or withdraw optional demographic disclosure for the signed-in candidate. |

## Authorization Summary

HR Admin only: reports, D&I audit, run checks, archive review, archive approval, sensitive archive views.  
Technical Interviewer only: their own missing-feedback escalation notifications and assigned interviews.  
Candidate only: their own demographic disclosure and profile.  
Junior Staff/observer: no compliance reports, run checks, archive approvals, or sensitive archive views unless a future policy explicitly grants read-only training access.
