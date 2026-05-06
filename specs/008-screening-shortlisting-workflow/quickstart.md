# Quickstart: Screening & Shortlisting Workflow

**Feature**: `008-screening-shortlisting-workflow`
**Date**: 2026-05-05

## Prerequisites

- XAMPP running with Apache + MySQL
- SRIM database initialized via `database/schema.sql`
- At least one HR Admin user, one or more Candidates with profiles, and one APPROVED/OPEN requisition with APPLIED applications
- Mock seed data: `php scripts/mock_seed.php` (if available)

## Setup Steps

### 1. Apply Schema Changes

Run the SQL migration from `data-model.md` against your local MySQL:

```sql
-- Run in MySQL console or phpMyAdmin:
SOURCE h:/Apps/XAMPPP/htdocs/srim/specs/008-screening-shortlisting-workflow/data-model.md
-- Or copy the SQL Migration section from data-model.md and execute directly
```

Alternatively, update `database/schema.sql` with the new tables and re-import the full schema.

### 2. Verify New Files

After implementation, the following files should exist:

```
app/Controllers/HrScreeningController.php
app/Enums/ScreeningAuditAction.php
app/Enums/DuplicateDecisionType.php
app/Policies/ScreeningPolicy.php
app/Repositories/ScreeningConfigRepository.php
app/Repositories/ScreeningAuditRepository.php
app/Repositories/DuplicateRepository.php
app/Services/ScreeningScoreService.php
app/Services/DuplicateDetectionService.php
views/hr/screening/config.php
views/hr/screening/shortlist.php
views/hr/screening/triage-confirm.php
views/hr/screening/triage-results.php
views/hr/screening/duplicates.php
views/hr/screening/duplicate-resolve.php
views/hr/screening/audit.php
```

### 3. Verify Routes

New routes added to `routes/web.php`:

| Method | Path | Action | Name |
|--------|------|--------|------|
| GET | `/hr/requisitions/{id}/screening` | Show/edit screening config | `hr.screening.config` |
| POST | `/hr/requisitions/{id}/screening` | Save screening config | `hr.screening.config.store` |
| POST | `/hr/requisitions/{id}/screening/recalculate` | Recalculate match scores | `hr.screening.recalculate` |
| GET | `/hr/requisitions/{id}/shortlist` | View ranked shortlist | `hr.screening.shortlist` |
| GET | `/hr/requisitions/{id}/triage` | Show triage confirmation | `hr.screening.triage` |
| POST | `/hr/requisitions/{id}/triage` | Execute triage | `hr.screening.triage.execute` |
| GET | `/hr/requisitions/{id}/duplicates` | View duplicate suggestions | `hr.screening.duplicates` |
| POST | `/hr/requisitions/{id}/duplicates/{mergeId}` | Record duplicate decision | `hr.screening.duplicates.resolve` |
| GET | `/hr/requisitions/{id}/screening/audit` | View screening audit trail | `hr.screening.audit` |

## Demo Walkthrough

### Configure Screening Rules (User Story 1)

1. Log in as HR Admin
2. Navigate to **HR → Requisitions → [select an APPROVED/OPEN requisition]**
3. Click **"Configure Screening"** link
4. Add skills (e.g., PHP: 30%, MySQL: 25%, React: 20%, Docker: 25%)
5. Configure thresholds (e.g., 0–39 → REJECTED, 40–59 → SCREENING, 60–79 → ASSESSMENT, 80–100 → INTERVIEW)
6. Submit → configuration is saved with audit entry

### Recalculate Scores & View Shortlist (User Story 2)

1. From the screening config page, click **"Recalculate Scores"**
2. System processes all APPLIED candidates against the weighted skills
3. View the **"Shortlist"** page showing candidates ranked by score
4. Each candidate row shows score breakdown, missing evidence, and ranking position
5. Page clearly labels results as **"Simulated AI-Ranked Shortlist"**

### Run Triage (User Story 3)

1. From the shortlist page, click **"Run Triage"**
2. Review the triage preview showing which candidates will move to which status
3. Confirm → APPLIED applications are moved per threshold bands
4. View triage results with audit trail

### Detect & Resolve Duplicates (User Story 4)

1. Navigate to **HR → Requisitions → [requisition] → Check Duplicates**
2. Review duplicate suggestions with matching evidence and confidence
3. For each suggestion, choose **Merge**, **Ignore**, or **Defer** with a reason
4. Decision is recorded in the audit log

### Review Audit Trail (User Story 5)

1. Navigate to **HR → Requisitions → [requisition] → Screening Audit**
2. Filter by action type, date range, or candidate
3. Review configuration changes, score recalculations, triage actions, and duplicate decisions

## Verification Checklist

- [ ] Screening config form validates weights sum to 100%
- [ ] Invalid configurations show field-level errors
- [ ] Match scores are 0–100 with per-skill breakdown visible
- [ ] Shortlist page shows "Simulated" label
- [ ] Triage only moves APPLIED applications
- [ ] Triage audit entries include before/after status, score, and threshold rule
- [ ] Duplicate suggestions show matching evidence and confidence
- [ ] Merge/ignore/defer decisions require a reason
- [ ] Non-HR users are blocked from all screening pages
- [ ] Candidates cannot see scores, rankings, or duplicate info
- [ ] No duplicate form submissions create duplicate records
