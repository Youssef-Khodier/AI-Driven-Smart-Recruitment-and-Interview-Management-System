# Implementation Plan: Advanced Job Requisition Governance

**Branch**: `009-requisition-governance` | **Date**: 2026-05-06 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/009-requisition-governance/spec.md`

## Summary

Extends the SRIM job requisition lifecycle with department-head approval governance, template versioning for job descriptions and requirements, simulated job-board publishing, and a comprehensive governance audit trail. All changes use the existing Vanilla PHP MVC monolith with server-rendered templates, MySQL via PDO, and no external API calls. The department-head approval is a single-tier gate (department head for the requisition's department), template versions snapshot `description` + `requirements` on submit, and job-board sync is fully simulated with local database records.

## Technical Context

**Language/Version**: PHP 8.1+ (framework-free Vanilla PHP MVC)  
**Primary Dependencies**: PDO, native sessions, CSRF tokens, server-side validation, middleware-style guards, authorization policies  
**Storage**: MySQL 8+ via PDO and plain SQL schema files; TEXT columns for versioned content  
**Testing**: Manual acceptance testing via documented server-rendered page workflows; PHP unit tests for policies and services where practical  
**Target Platform**: Server-rendered web application in modern browsers  
**Project Type**: Vanilla PHP monolithic MVC web application; no REST API or separated frontend  
**Performance Goals**: All governance page loads complete within standard web response times (<2s); audit log queries paginated for scalability  
**Constraints**: PHP templates, `routes/web.php`, MySQL, sessions, CSRF, server-side validation, RBAC policies  
**Scale/Scope**: 3-person academic delivery; phased SRIM modules aligned to `Diagrams/`

## Baseline Materials Review

- **SRS / Use Case Trace**: UC-3 (Job Requisition Approval Workflow), design functions #3, #7, #39, #40 from `Diagrams/document.md`
- **Database / ERD Trace**: `job_requisitions`, `departments`, `users` tables; new tables: `requisition_approval_steps`, `requisition_template_versions`, `job_board_platforms`, `job_board_sync_records`, `requisition_governance_audit`; column addition: `users.is_department_head`
- **Activity / Class / Object Trace**: `Diagrams/Acrivity Diagram/Activity 1.pdf` (requisition lifecycle); `Diagrams/Class Diagram/Class Diagram.drawio.pdf` (user-department relationship)
- **Architecture Trace**: Vanilla PHP monolith; new `HrGovernanceController` follows existing controller patterns (`HrController`, `HrScreeningController`); new `GovernanceRepository` follows existing repository patterns; new `GovernancePolicy` follows existing policy patterns
- **Scope Changes**: Team-approved extension — department-head approval replaces the existing HR-approves-HR model for governance purposes; REJECTED status added to requisition lifecycle

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Relevant `Diagrams/` materials were read and traced in this plan.
- [x] Feature uses Vanilla PHP monolithic MVC with server-rendered PHP templates and `routes/web.php` flows.
- [x] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [x] MySQL schema changes use plain SQL migration files and PDO.
- [x] Controllers, middleware-style guards, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [x] RBAC is specified for HR Admin (with department-head authority) and implicitly excludes Candidate and Interviewer roles from governance features.
- [x] Candidate privacy is not directly affected (governance operates on requisitions, not candidate data); audit log access restricted to HR Admin.
- [x] Job board sync is explicitly labeled as simulated; no external API calls.
- [x] Acceptance criteria are testable by documented PHP template page demo flows.
- [x] Peer review is scheduled before implementation begins.

## Project Structure

### Documentation (this feature)

```text
specs/009-requisition-governance/
├── plan.md                # This file
├── spec.md                # Feature specification
├── research.md            # Phase 0: technical research decisions
├── data-model.md          # Phase 1: schema design & entity definitions
├── route-map.md           # Phase 1: routes, controllers, views, policies
├── quickstart.md          # Phase 1: setup & testing guide
└── tasks.md               # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
app/
├── Controllers/
│   ├── HrGovernanceController.php    # NEW: all governance actions
│   └── HrController.php             # MODIFIED: submit + re-approval hooks
├── Core/                             # UNCHANGED
├── Enums/
│   ├── JobRequisitionStatus.php      # MODIFIED: add REJECTED
│   ├── GovernanceAuditAction.php     # NEW
│   ├── SyncStatus.php                # NEW
│   └── ApprovalDecision.php          # NEW
├── Policies/
│   ├── GovernancePolicy.php          # NEW: governance authorization
│   └── JobRequisitionPolicy.php      # MODIFIED: REJECTED transitions
├── Repositories/
│   ├── GovernanceRepository.php      # NEW: governance data access
│   └── AuditLogRepository.php        # MODIFIED: add UNION ALL leg
└── Services/
    └── TemplateVersionDiffService.php # NEW: version comparison logic

database/
└── migrations/
    └── 009_governance_tables.sql      # NEW: all schema changes

routes/
└── web.php                            # MODIFIED: ~12 new routes

views/hr/
├── governance/
│   ├── approval-queue.php             # NEW
│   ├── approve-form.php               # NEW
│   ├── version-history.php            # NEW
│   ├── version-show.php               # NEW
│   ├── version-compare.php            # NEW
│   ├── publish-form.php               # NEW
│   ├── sync-history.php               # NEW
│   ├── governance-audit.php           # NEW
│   └── department-heads.php           # NEW
├── requisitions/
│   ├── show.php                       # MODIFIED: governance links
│   └── index.php                      # MODIFIED: REJECTED status
└── dashboard.php                      # MODIFIED: pending approvals widget
```

**Structure Decision**: Follows the established SRIM pattern of domain-specific controllers (`HrGovernanceController`), single-concern repositories (`GovernanceRepository`), policy classes (`GovernancePolicy`), and organized view directories (`views/hr/governance/`). No new architectural patterns introduced.

## Complexity Tracking

No constitution violations to justify.
