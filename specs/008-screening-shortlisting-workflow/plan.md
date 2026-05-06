# Implementation Plan: Screening & Shortlisting Workflow

**Branch**: `008-screening-shortlisting-workflow` | **Date**: 2026-05-05 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/008-screening-shortlisting-workflow/spec.md`

## Summary

Implement the screening, shortlisting, and candidate deduplication workflow for SRIM.
HR Admins configure per-requisition skill weights and triage thresholds, trigger simulated
match-score recalculation against candidate profile evidence, generate AI-ranked (simulated)
shortlists, run automated triage from APPLIED to SCREENING/ASSESSMENT/INTERVIEW/REJECTED,
and detect and resolve duplicate candidate profiles—all through form-based, auditable,
RBAC-protected server-rendered pages in the existing Vanilla PHP MVC monolith.

## Technical Context

**Language/Version**: PHP 8.2+ (framework-free Vanilla PHP MVC)
**Primary Dependencies**: Custom MVC core (`App\Core\*`), PDO via `App\Core\Database`, server-rendered PHP templates, native sessions, CSRF via `App\Core\Csrf`, `App\Core\Validator`, enum-backed value objects (`App\Enums\*`), policy classes (`App\Policies\*`), repository classes (`App\Repositories\*`), service classes (`App\Services\*`)
**Storage**: MySQL 8+ via PDO; plain SQL schema in `database/schema.sql`; no ORM
**Testing**: PHPUnit with manual demo-path verification for server-rendered workflows
**Target Platform**: Server-rendered web application in modern browsers (XAMPP local dev)
**Project Type**: Vanilla PHP monolithic MVC web application; no REST API or separated frontend
**Performance Goals**: Score recalculation + shortlist view for up to 100 applicants in < 10 seconds (SC-003); configuration save < 5 minutes user-facing (SC-001)
**Constraints**: PHP templates in `views/`, `routes/web.php`, MySQL/PDO, sessions, CSRF, server-side validation, RBAC policies
**Scale/Scope**: 3-person academic delivery; phased SRIM modules aligned to `Diagrams/`

## Baseline Materials Review

- **SRS / Use Case Trace**: Baseline functions 1 (Automated Screening Triage), 2 (Dynamic Skill-Weighting Engine), 4 (Application Deduplication Logic), 5 (AI-Ranked Shortlisting Simulated), 36 (RBAC), 39 (System Audit Trail)
- **Database / ERD Trace**: `applications` (match_score column exists; adding match_score_breakdown JSON), `candidate_merge_log` (extending with decision_type, confidence_category), `candidates` (profile fields used as evidence), `job_requisitions` (requirements field). New tables: `screening_configs`, `screening_skills`, `screening_thresholds`, `screening_audit_records`
- **Activity / Class / Object Trace**: Activity diagrams 1–7 cover the recruitment pipeline; this feature adds the screening/shortlisting step between APPLIED and ASSESSMENT/INTERVIEW. Class diagram covers User, Candidate, Application, JobRequisition relationships.
- **Architecture Trace**: Vanilla PHP monolith: new controller(s) under `app/Controllers/`, new repositories under `app/Repositories/`, new policy under `app/Policies/`, new service under `app/Services/`, new views under `views/hr/screening/`, routes added to `routes/web.php`.
- **Scope Changes**: None. All AI functionality remains simulated per constitution.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses Vanilla PHP monolithic MVC with PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL in `database/schema.sql`.
- [x] Controllers, CSRF, policies, sessions, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff where relevant.
- [x] Candidate privacy, retention/erasure, and audit-relevant changes are addressed when candidate data is touched.
- [x] AI, proctoring, background checks, job board sync, calendar, and email are marked simulated unless explicitly in scope.
- [x] Acceptance criteria are testable by PHPUnit tests or documented page demo flows.
- [x] Peer review is scheduled before implementation begins.

## Project Structure

### Documentation (this feature)

```text
specs/008-screening-shortlisting-workflow/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── route-map.md         # Phase 1 output: web routes, controllers, views
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Controllers/
│   └── HrScreeningController.php      # All screening, shortlist, triage, duplicate actions
├── Core/                               # Existing MVC core (no changes expected)
├── Enums/
│   ├── ScreeningAuditAction.php        # NEW: audit action types for screening
│   └── DuplicateDecisionType.php       # NEW: MERGE, IGNORE, DEFER
├── Policies/
│   └── ScreeningPolicy.php             # NEW: RBAC for screening actions
├── Repositories/
│   ├── ScreeningConfigRepository.php   # NEW: screening_configs CRUD
│   ├── ScreeningAuditRepository.php    # NEW: screening audit records
│   └── DuplicateRepository.php         # NEW: duplicate detection + merge log
└── Services/
    ├── SimulatedMatchScorer.php         # EXISTING: will be enhanced with weighted scoring
    ├── ScreeningScoreService.php        # NEW: orchestrates recalculation + shortlist
    └── DuplicateDetectionService.php   # NEW: duplicate detection logic

database/
└── schema.sql                          # MODIFIED: new tables + altered columns

views/hr/screening/
├── config.php                          # Configure skills/weights/thresholds
├── shortlist.php                       # View ranked shortlist for a requisition
├── triage-confirm.php                  # Confirm triage action
├── triage-results.php                  # Show triage results + audit
├── duplicates.php                      # List duplicate suggestions
├── duplicate-resolve.php              # Merge/ignore/defer decision form
└── audit.php                           # Screening-specific audit trail

routes/
└── web.php                             # MODIFIED: add screening routes
```

**Structure Decision**: Follows the established SRIM pattern of one controller per domain
area (like `HrInterviewController`, `HrOfferController`), with supporting repositories,
services, policies, and enums. Views are nested under `views/hr/screening/` consistent
with existing `views/hr/{feature}/` convention.

## Complexity Tracking

> No constitution violations detected. All screening/shortlisting work uses the established
> Vanilla PHP MVC patterns with server-rendered pages, PDO, RBAC policies, CSRF, and
> server-side validation.
