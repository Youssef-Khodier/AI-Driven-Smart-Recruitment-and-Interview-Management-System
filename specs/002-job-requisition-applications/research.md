# Phase 0 Research: Job Requisition and Candidate Applications

## Decision: Continue with Laravel 12 and PHP 8.2+

**Rationale**: The repository is already a Laravel 12 application requiring PHP `^8.2`, and the RBAC foundation has working middleware, controllers, models, Blade views, and tests. Continuing this stack satisfies the constitution and avoids unnecessary migration risk.

**Alternatives considered**: A separated frontend, REST API, or service split was rejected because the constitution requires a Laravel monolithic MVC application with Blade pages and web form submissions.

## Decision: Use baseline job and application entities

**Rationale**: The SRS, ERD, database schema, class diagram, and object diagram all identify JobRequisition and Application as core recruitment entities. Adding these as Laravel models and migrations extends the approved baseline instead of inventing a parallel recruitment structure.

**Alternatives considered**: Storing applications as candidate metadata was rejected because it would not support one application per job, per-requisition applicant review, status history, or later assessment/interview relationships.

## Decision: Add manual candidate skill keywords

**Rationale**: The clarified spec requires a comma-separated skills or keywords list for simulated scoring, while real resume parsing is out of scope. Adding a candidate-owned profile field makes the scoring input explicit, testable, and editable without external integrations.

**Alternatives considered**: Resume-only scoring was rejected because it depends on parsing that is out of scope. A fully managed skill taxonomy was rejected because it creates unnecessary administration scope for this phase.

## Decision: Use explicit lifecycle statuses and policies

**Rationale**: Requisition and application status values are central acceptance criteria. Explicit enum-like values make transition rules, validation, filtering, and tests clear. Policies and active-account/role middleware enforce HR-only lifecycle management and candidate-only ownership access.

**Alternatives considered**: Free-text statuses were rejected because they make test assertions and role-safe transitions ambiguous. A generic workflow engine was rejected because the feature needs only a small fixed set of states.

## Decision: Require a different HR Admin for approval

**Rationale**: The clarified spec requires a different active HR Admin to approve a submitted requisition. This preserves the approval-gate value from UC-3 while remaining feasible within the existing HR Admin role model.

**Alternatives considered**: Self-approval was rejected because it weakens audit and approval semantics. Department-head or finance approval chains were rejected as out of scope for this academic phase.

## Decision: Use deterministic simulated match scoring in support code

**Rationale**: The score is simulated, advisory, and must be reproducible in tests. A small support class can calculate the score from persisted job requirements and candidate profile fields using the clarified weights: skills overlap 70%, title match 15%, and experience match 15%.

**Alternatives considered**: Real AI/NLP scoring was rejected because it is out of scope and would introduce external dependency risk. Random scoring was rejected because it cannot support reliable tests or HR trust.

## Decision: Persist score at application time

**Rationale**: The spec says profile edits must not silently change historical application results. Storing the score on the application preserves the evidence available to HR at submission time and supports repeatable candidate tracking.

**Alternatives considered**: Calculating scores dynamically on every page view was rejected because later profile edits could change historical application evidence without audit.

## Decision: Use dedicated status-history tables

**Rationale**: Existing account audit records are specific to account administration. Requisition and application status changes need actor, old status, new status, timestamp, and optional reason tied directly to recruitment entities. Dedicated history tables keep audit intent clear and simpler than a polymorphic audit abstraction.

**Alternatives considered**: Reusing account audit records was rejected because target records are not user accounts. A generic polymorphic audit table was rejected as unnecessary abstraction for this phase.

## Decision: Detect stale HR requisition edits

**Rationale**: The clarified spec requires blocking a save when a requisition changed after the HR Admin loaded the edit page. Submitting the last-seen update timestamp with the form is sufficient for this Blade workflow and avoids accidental overwrites.

**Alternatives considered**: Last-write-wins was rejected because it can erase another HR Admin's changes. Full record locking was rejected because it adds operational complexity not required by the feature.

## Decision: Keep external integrations out of scope

**Rationale**: The feature can be demonstrated with local Blade pages, forms, and MySQL-backed workflows. External job boards, email notifications, real resume parsing, assessments, interviews, offers, and onboarding are explicitly excluded in the spec.

**Alternatives considered**: Simulated email/job-board side effects were rejected for this phase because they would distract from core requisition and application management.
