# Quickstart: Compliance Reporting Maintenance

## Prerequisites

- Use branch `014-compliance-reporting-maintenance`.
- Review `specs/014-compliance-reporting-maintenance/spec.md` and `plan.md` before implementation.
- Confirm the local database includes application status history, completed interviews, offers, onboarding records, notifications, and audit tables from earlier phases.

## Manual Demo Path

1. Sign in as an active HR Admin.
2. Open `GET /hr/reports/pipeline` and filter by a requisition with applications in multiple stages.
3. Verify stage counts, conversion rates, average stage age, time-to-hire, and bottleneck labels appear.
4. Add or update optional demographic disclosure for several demo candidates, including at least one "Not provided" case.
5. Open `GET /hr/reports/diversity` and verify aggregate counts, "Not provided" totals, and suppression for groups smaller than 3.
6. Prepare overdue demo data: one completed interview missing feedback after 24 hours, one sent offer near expiry or expired, one pending simulated background check older than 48 hours, and one overdue onboarding task.
7. Open `GET /hr/run-checks`, run all checks, then open the generated batch details.
8. Verify findings show affected records, responsible users, created notifications, duplicate skips, and archive recommendations.
9. Run the same checks again and verify duplicate open escalations are not created.
10. Close a requisition or mark an application rejected with no pending work, run archive eligibility checks, and approve archive with a reason.
11. Verify the archived record disappears from active queues, appears in archive views, and has audit evidence.

## Verification Commands

```bash
php -l app/Controllers/HrReportController.php
php -l app/Controllers/HrComplianceCheckController.php
php -l app/Repositories/ReportRepository.php
php -l app/Repositories/ComplianceMaintenanceRepository.php
php -l app/Services/ComplianceRunCheckService.php
php -l app/Services/ArchiveEligibilityService.php
php -l app/Services/DiversityReportSuppressor.php
```

## Acceptance Evidence

- Screenshot or notes for pipeline bottleneck report with filters.
- Screenshot or notes for D&I report showing suppression and "Not provided" totals.
- Run-check detail page showing no duplicate notifications after repeated runs.
- Archive approval page showing eligibility revalidation and audit summary.
- Access-denied checks for non-HR users attempting HR compliance pages.

## Known Scope Boundaries

- No scheduler or background worker runs maintenance automatically.
- No external email is sent; escalations are in-system notifications only.
- Background-check findings remain simulated/local.
- Archive actions do not hard-delete candidate or requisition history.
- D&I reports never show individual demographic values.
