# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. SRIM is constitutionally constrained to a Laravel monolithic
  MVC application with Blade server-rendered pages. Do not plan REST APIs,
  separated frontend apps, mobile apps, or SPA delivery unless the constitution
  is amended first.
-->

**Language/Version**: PHP [version] with Laravel [version] or NEEDS CLARIFICATION  
**Primary Dependencies**: Laravel MVC, Blade, Eloquent, middleware, policies, sessions, CSRF or NEEDS CLARIFICATION  
**Storage**: MySQL via Laravel migrations and Eloquent models; file storage for resumes/documents if in scope  
**Testing**: Laravel PHPUnit/Pest feature, policy, validation, and model tests or NEEDS CLARIFICATION  
**Target Platform**: Server-rendered web application in modern browsers  
**Project Type**: Laravel monolithic MVC web application; no REST API or separated frontend  
**Performance Goals**: [domain-specific SRIM target, e.g., dashboard/page response targets or NEEDS CLARIFICATION]  
**Constraints**: Blade pages, `routes/web.php`, MySQL, sessions, CSRF, server-side validation, RBAC policies  
**Scale/Scope**: 3-person academic delivery; phased SRIM modules aligned to `Diagrams/`

## Baseline Materials Review

<!--
  REQUIRED before Phase 0 research: confirm the current feature was checked
  against the project materials in Diagrams/. Treat these files as the source
  of truth unless the team records an explicit scope change.
-->

- **SRS / Use Case Trace**: [SRS section, UC number, or NEEDS CLARIFICATION]
- **Database / ERD Trace**: [schema tables and relationships affected]
- **Activity / Class / Object Trace**: [relevant diagram files and flows]
- **Architecture Trace**: [Laravel monolith mapping to SRIM modules]
- **Scope Changes**: [None, or documented team-approved change]

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [ ] Relevant `Diagrams/` materials were read and traced in this plan.
- [ ] Feature uses Laravel monolithic MVC with Blade pages and `routes/web.php` flows.
- [ ] No REST API, separated frontend, SPA, or mobile-native scope is introduced.
- [ ] MySQL schema changes use Laravel migrations and Eloquent relationships.
- [ ] Controllers, middleware, policies, sessions, CSRF, and server-side validation are planned where applicable.
- [ ] RBAC is specified for HR Admin, Technical Interviewer, Candidate, and Junior Staff where relevant.
- [ ] Candidate privacy, retention/erasure, and audit-relevant changes are addressed when candidate data is touched.
- [ ] AI, proctoring, background checks, job board sync, calendar, and email are marked simulated unless explicitly in scope.
- [ ] Acceptance criteria are testable by Laravel tests or documented Blade-page demo flows.
- [ ] Peer review is scheduled before implementation begins.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── route-map.md         # Phase 1 output: web routes, controllers, Blade views
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace or trim the placeholder tree below with concrete
  Laravel paths for this feature. Do not introduce backend/frontend splits,
  REST API folders, or mobile/API projects.
-->

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Policies/
└── Services/              # Optional, only for reusable domain logic

database/
├── migrations/
└── seeders/

resources/views/
├── layouts/
└── [feature]/

routes/
└── web.php

tests/
├── Feature/
└── Unit/
```

**Structure Decision**: [Document the selected structure and reference the real
directories captured above]

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |
