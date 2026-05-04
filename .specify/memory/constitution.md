<!--
Sync Impact Report
Version change: 1.0.0 -> 2.0.0
Modified principles:
- II. Laravel Monolithic MVC Only -> II. Vanilla PHP Monolithic MVC Only
Added sections:
- Runtime Replacement Amendment
Removed sections:
- None
Templates requiring updates:
- Pending follow-up: .specify/templates/* if future Spec Kit commands are used
- Updated: AGENTS.md and project workflow documentation
Follow-up TODOs: None
-->
# AI-Driven Smart Recruitment & Interview Management System Constitution

## Core Principles

### I. Diagram-Baseline Scope Control
All specs, plans, task lists, and implementation decisions MUST start from the existing
materials in `Diagrams/`: the SRS, database schema, ERD, use-case diagram, activity
diagrams, class diagram, object diagram, and system architecture diagram. These files
are the baseline source of truth for SRIM scope, actors, workflows, entities, and
module boundaries unless the team explicitly records a scope change. Any conflict
between a new request and the diagrams MUST be documented before code is planned.

Rationale: The project is an academic software engineering project with pre-existing
requirements and design artifacts. Traceability to those artifacts is required to keep
implementation aligned with the approved analysis and design work.

### II. Vanilla PHP Monolithic MVC Only
The system MUST be implemented as one framework-free Vanilla PHP monolithic MVC
application using server-rendered PHP templates and `routes/web.php` browser flows.
The application MUST use MySQL through PDO, plain PHP models/repositories, SQL schema
files, controllers, middleware-style guards, policies, native sessions, CSRF
protection, and server-side validation. The team MUST NOT implement REST APIs for
internal feature delivery, MUST NOT create a separated frontend application, MUST NOT
use a SPA architecture, and MUST NOT add a runtime framework dependency.

Rationale: A monolithic Vanilla PHP MVC design fits the project's approved
implementation direction while still covering the required software engineering
concepts: MVC, persistence, authorization, validation, and secure web workflows.

### III. Role-Based Security and Privacy
Every feature MUST enforce role-based access for HR Admin, Technical Interviewer,
Candidate, and Junior Staff where the baseline diagrams require that actor. Vanilla
PHP guards and policies MUST protect candidate profiles, applications, resumes,
assessment attempts, submissions, interview feedback, final evaluations, offers, and
onboarding data. Candidate personal data MUST be handled with privacy-aware defaults:
least-privilege access, server-side validation, CSRF protection, session-based
authentication, audit-relevant change records, and retention or erasure behavior when
the scope includes it.

Rationale: The SRS identifies RBAC, GDPR-style retention, right to erasure, audit
trail, proctoring integrity, and candidate privacy as nonfunctional requirements. These
controls are mandatory because recruitment data includes sensitive personal and
evaluation information.

### IV. Clear Specs and Testable Acceptance
No implementation work MAY start until the feature has a clear specification reviewed
by a peer. Each spec MUST identify the baseline diagram or SRS use case it implements,
the in-scope actors, the relevant server-rendered web flow, the affected data entities,
privacy and RBAC expectations, and testable acceptance criteria. Acceptance criteria
MUST be verifiable through PHP tests, policy/validation tests, or a documented manual
server-rendered page workflow when automation is not practical for the academic demo.

Rationale: Clear specs before code reduce rework for a small team and make grading,
peer review, and demonstration objective.

### V. Small Phased Academic Delivery
The 3-person team MUST deliver SRIM in small, reviewable phases. Each phase MUST
produce a working vertical slice that can be demonstrated independently and MUST name
an owner and reviewer. Recommended phase order is: Vanilla PHP MVC foundation and
database, authentication and RBAC, job requisitions and applications, assessments with
simulated AI/proctoring, interviews and feedback, offers and onboarding, then analytics
or compliance refinements. Simulated AI, proctoring, background checks, external job
board sync, calendar, and email integrations MUST be clearly labeled as simulated
unless the team explicitly commits to real integration scope.

Rationale: Small phases match a 3-person academic schedule, make peer review feasible,
and keep simulated advanced features honest without blocking the core recruitment
workflow.

## Technology Constraints

The canonical runtime architecture is a single Vanilla PHP MVC application with
server-rendered PHP templates. Internal interactions MUST use standard web routes,
form submissions, redirects, sessions, policies, validation, and controller actions.
JSON responses are allowed only for narrowly scoped progressive enhancement inside the
same monolith and MUST NOT become a public or internal REST API contract.

The canonical data architecture is MySQL through PDO, plain SQL schema files, and
model/repository relationships derived from `Diagrams/Database/schema.sql` and
`schema-erd.svg`. Schema changes MUST preserve the baseline entities unless the team
records an explicit scope change. Required baseline entities include users,
departments, candidates, job requisitions, applications, assessments, questions,
candidate assessments, submissions, interviews, interviewer assignments, feedback,
final evaluations, offers, onboarding, notifications, and candidate merge logs when
deduplication is in scope.

Security and privacy controls MUST use framework-free PHP mechanisms first: native
sessions, CSRF token checks, `password_hash`/`password_verify`, authorization policies,
server-side validation, explicit allow-lists for persisted fields, and database
constraints. Candidate-facing pages MUST never expose another candidate's data.
Interviewers MUST only access assigned candidates and interviews. HR Admin access MUST
be broad but still audited for score, status, feedback, offer, and candidate data
changes.

## Runtime Replacement Amendment

The team approved replacing the existing Laravel runtime with a framework-free Vanilla
PHP MVC runtime. Existing SRIM scope, actors, workflows, MySQL entities, role-based
privacy boundaries, and simulated AI/proctoring labels remain unchanged. Laravel
framework code, Blade templates, Eloquent models, migrations, Form Requests, and
Artisan commands are migration sources only and MUST be replaced by framework-free
equivalents before the rewrite is considered complete.

## Development Workflow

Each feature MUST follow this order: read the relevant `Diagrams/` materials, write or
update the spec, review the spec, write or update the implementation plan, create a
small task list, peer review the plan and tasks, then implement. Code written before
spec and peer review is non-compliant unless it is limited to a throwaway spike that is
not merged or submitted.

For each feature, the team MUST assign three responsibilities across the 3-person team:
spec owner, implementer, and peer reviewer. The same person MAY own the spec and
implementation for a small feature, but the reviewer MUST be a different person. Peer
review MUST verify diagram traceability, Vanilla PHP monolith compliance, RBAC,
validation, privacy handling, schema/model correctness, and acceptance criteria
coverage.

Each phase MUST end with an evidence checkpoint: passing relevant PHP tests or a
documented manual demo path, reviewed screenshots or notes for server-rendered
workflows, and a short list of known limitations. New phases MUST not depend on
incomplete hidden work from another phase.

## Governance

This constitution governs all SRIM specs, plans, tasks, and implementation work. When
process or technology guidance conflicts, this constitution takes precedence. When
feature scope conflicts, the `Diagrams/` materials take precedence until the team
records and approves a scope change.

Amendments MUST be proposed in writing with the reason, impacted principles, affected
templates, and migration impact for existing specs or code. Amendments require approval
from at least two of the three team members before implementation guidance changes.

Versioning follows semantic versioning. MAJOR increments remove or redefine core
governance in a backward-incompatible way. MINOR increments add principles, sections,
or materially expanded required practices. PATCH increments clarify wording without
changing required behavior.

Compliance MUST be reviewed at every spec, plan, task, peer review, and demo
checkpoint. Any violation MUST be documented with either a correction before
implementation or a team-approved exception that explains why the simpler compliant
alternative was not used.

**Version**: 2.0.0 | **Ratified**: 2026-05-03 | **Last Amended**: 2026-05-04
