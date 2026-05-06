# Web Contract: Compliance Reporting Maintenance

This feature exposes server-rendered browser pages and form submissions only. It does not define REST APIs or a separated frontend contract.

## HR Pipeline Throughput Report

**Route**: `GET /hr/reports/pipeline`  
**Actor**: HR Admin  
**Inputs**: Optional date range, requisition id, department id, stage filter.  
**Result**: Server-rendered report showing stage counts, conversion rates, average stage age, time-to-hire, bottleneck labels, and empty-state messaging.  
**Validation**: Invalid date ranges or unknown filters return the page with validation errors and no misleading metrics.

## HR D&I Audit Report

**Route**: `GET /hr/reports/diversity`  
**Actor**: HR Admin  
**Inputs**: Optional date range, requisition id, department id, outcome, demographic category.  
**Result**: Server-rendered aggregate report showing privacy-safe counts, suppressed groups, "Not provided" totals, and report scope.  
**Validation**: Groups with fewer than 3 candidates are suppressed or combined. Non-HR users receive access denied.

## Candidate Demographic Disclosure

**Route**: `POST /candidate/profile/demographics`  
**Actor**: Candidate  
**Inputs**: Optional demographic category values, consent/withdraw choice, CSRF token.  
**Result**: Redirect to candidate profile with a success or validation message.  
**Validation**: Values must be blank, withdrawn, or from approved category lists. Candidates can only update their own disclosure.

## HR Run Checks Index

**Route**: `GET /hr/run-checks`  
**Actor**: HR Admin  
**Inputs**: Optional date range, check type, status.  
**Result**: Server-rendered list of recent check batches with counts, actor, time, status, and link to details.

## Execute HR Run Checks

**Route**: `POST /hr/run-checks`  
**Actor**: HR Admin  
**Inputs**: Check type, optional requisition/date scope, CSRF token.  
**Result**: Redirect to the run-check details page with findings, notification counts, duplicate skips, archive recommendations, and blocked items.  
**Validation**: Check type and filters are required/validated. The operation is idempotent for duplicate open escalations.

## HR Run Check Details

**Route**: `GET /hr/run-checks/{id}`  
**Actor**: HR Admin  
**Inputs**: Run check id.  
**Result**: Server-rendered detail page showing findings grouped by type, severity, entity, responsible user, recommended action, notification status, and archive recommendation status.

## Archive Review

**Route**: `GET /hr/archive`  
**Actor**: HR Admin  
**Inputs**: Optional entity type, status, requisition, date range.  
**Result**: Server-rendered archive recommendation and archive history list.

## Approve Archive Action

**Route**: `POST /hr/archive/{entityType}/{id}/approve`  
**Actor**: HR Admin  
**Inputs**: Entity type, entity id, reason, CSRF token.  
**Result**: Redirect to archive detail or list with success/blocked message.  
**Validation**: Eligibility is recalculated before approval. Pending work blocks archive. Reason is required.

## Archive Detail

**Route**: `GET /hr/archive/{entityType}/{id}`  
**Actor**: HR Admin  
**Inputs**: Entity type and id.  
**Result**: Server-rendered detail page showing archive status, eligibility history, affected records, audit events, and active-work blockers if any.

## Notification Center

**Route**: `GET /notifications`  
**Actor**: Authenticated responsible users  
**Inputs**: Existing notification filters/pagination.  
**Result**: Existing notification list includes new escalation types for feedback, offers, simulated background checks, onboarding tasks, and archive follow-up.
