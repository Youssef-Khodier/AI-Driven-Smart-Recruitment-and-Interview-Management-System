# Research: Compliance Reporting Maintenance

## Decision: Keep Operational Automation Manual Through HR Run Checks

**Rationale**: The feature explicitly prohibits scheduler scope unless separately approved, and the constitution favors small reviewable academic delivery. A manual Run Checks page lets HR Admins trigger missing-feedback reminders, offer expiry follow-up, simulated background-check follow-up, onboarding follow-up, and archive eligibility checks while keeping behavior visible, auditable, and demo-friendly.

**Alternatives considered**: A scheduler or background worker was rejected because it violates scope. Page-load side effects were rejected because they hide operational changes from HR and are harder to audit. External email reminders were rejected because external email delivery is out of scope.

## Decision: Use Deterministic Bottleneck Thresholds

**Rationale**: Bottleneck detection should be explainable to HR Admins and easy to test. Stage age and stage share thresholds can be computed from existing application status history and current application state. Defaults follow the spec assumptions: 7 days in active stages, 24 hours after completed interviews for missing feedback, 24 hours before offer expiry, 48 hours for pending simulated background checks, and due date for onboarding tasks.

**Alternatives considered**: Statistical anomaly detection was rejected as too opaque and unnecessary for academic demo data. Configurable per-requisition thresholds were deferred because the current request does not require HR threshold management.

## Decision: D&I Reports Are Aggregate-Only With Small-Group Suppression

**Rationale**: Optional demographic fields are sensitive. Reporting only grouped totals and suppressing groups with fewer than 3 candidates satisfies the spec's privacy requirement while still letting HR inspect high-level applicant and outcome trends. Non-disclosure remains a first-class "Not provided" category so totals remain accurate without pressuring candidates.

**Alternatives considered**: Individual demographic display was rejected because it creates privacy and hiring-bias risk. Excluding non-disclosing candidates was rejected because it distorts report totals. Hard-coded binary categories were rejected because demographic disclosure should support controlled, inclusive values plus non-disclosure.

## Decision: Archive Means Hide From Active Queues, Not Hard Delete

**Rationale**: The feature requires database integrity archive actions and audit history, while the constitution requires privacy-aware retention and erasure behavior. Soft archive markers preserve traceability and aggregate reporting while removing closed/rejected records from active workflows.

**Alternatives considered**: Hard deletion was rejected because it would break audit and reporting continuity. Moving rows into separate archive tables was rejected because it increases relationship complexity for this phase and risks divergence from baseline ERD entities.

## Decision: Track Run Check Batches and Findings

**Rationale**: HR Admins need counts, affected records, skipped records, duplicate escalation prevention, and actor/timestamp history. A batch plus findings model makes each run reviewable and supports idempotency by tying findings to notification references and archive recommendations.

**Alternatives considered**: Flash-only summaries were rejected because they disappear and cannot support audit evidence. Writing notifications without run records was rejected because HR cannot review what was skipped or already active.

## Decision: Use Existing Notification Records for Escalations

**Rationale**: The application already has in-system notifications with reference metadata and duplicate prevention. Extending notification types and making run-check findings reference notifications is simpler and preserves the existing notification center.

**Alternatives considered**: Separate escalation inbox tables were rejected as redundant. External email was rejected as out of scope.

## Decision: Use Existing Audit Surfaces Plus Compliance-Specific Audit Actions

**Rationale**: The current application has account, feedback governance, screening, requisition governance, interview, and post-offer audit records. Compliance-specific actions can be stored locally with actor, action, affected entity, old/new values, reason, and timestamp while preserving module boundaries.

**Alternatives considered**: A single global event store was rejected because it would be a broad cross-cutting refactor. No audit writes were rejected because archive and sensitive report access require evidence.
