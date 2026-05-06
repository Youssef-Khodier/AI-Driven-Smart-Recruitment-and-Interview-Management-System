# Feature Specification: Advanced Job Requisition Governance

**Feature Branch**: `009-requisition-governance`  
**Created**: 2026-05-06  
**Status**: Draft  
**Input**: User description: "Complete advanced job requisition governance in server-rendered Vanilla PHP. HR admins can submit requisitions through a multi-tier approval workflow, department heads can approve or reject within their department, the system records template versions for job descriptions and rubrics, and HR can simulate publishing approved job posts to external job boards. Job-board sync is simulated with local records showing target platform, payload summary, sync status, and timestamp. All changes must be auditable and must not introduce external API calls."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.pdf` sections 3.2 and 3.4; `Diagrams/document.md` design functions #3 (Job Requisition Approval Workflow), #7 (External Job-Board Sync Manager), #39 (System Audit Trail), #40 (Template Versioning Manager); `Diagrams/Database/schema.sql` tables `job_requisitions`, `departments`, `users`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio.pdf`.
- **SRS / Use Case IDs**: UC-3 Job Requisition Approval Workflow (multi-tier department-head approval), design function #7 External Job-Board Sync Manager (simulated publishing), design function #40 Template Versioning Manager (job description and rubric versioning), design function #39 System Audit Trail.
- **Baseline Entities**: Job Requisitions, Departments, Users (HR Admin with department-head role context), new entities for Approval Workflow Steps, Template Versions, Job Board Sync Records, and Audit Log entries.
- **Baseline Workflow**: HR Admin creates/edits requisition → submits for approval → approval request routed to department head → department head approves or rejects → approved requisition may be published to simulated job boards → all state changes recorded in audit trail.
- **Scope Decision**: Extends the baseline requisition lifecycle from spec 002 by adding multi-tier department-head approval (beyond the single HR-approves-HR model), template versioning for job descriptions and rubrics, and simulated job-board publishing. This is a team-approved scope extension consistent with design functions #3, #7, #39, and #40 in the baseline documents.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates.
- **Routing**: Web routes and form submissions only; no REST API contract.
- **Data Access**: MySQL through PDO and plain SQL schema files.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and authorization policies.

## Clarifications

### Session 2026-05-06

- Q: Can a single HR Admin user be designated as department head for more than one department? → A: No — each user can head at most one department.
- Q: Does "multi-tier" mean a single department-head approval tier or a sequential chain of multiple approvers? → A: Single department-head tier only. "Multi-tier" refers to the staged workflow gates (draft → pending → approved), not sequential human approvers.
- Q: Is the "rubric" a new editable field on the job requisition or the existing `requirements` field? → A: Rubric maps to the existing `requirements` column. No new column is needed; template versioning snapshots `description` + `requirements` together.

## User Scenarios & Testing *(mandatory)*

### User Story 1 – Department-Head Requisition Approval Workflow (Priority: P1)

An HR Admin creates or edits a job requisition and submits it for approval. The system routes the approval request to the department head associated with the requisition's department. The department head reviews the requisition within their department scope and approves or rejects it with optional comments. If rejected, the requisition returns to the HR Admin for revision. If approved, the requisition becomes available for opening or simulated publishing. The approval is a single-tier gate (department head only); "multi-tier" refers to the staged workflow transitions (draft → pending → approved), not a sequential chain of multiple human approvers.

**Why this priority**: The approval workflow is the governance backbone. Without multi-tier approval, requisitions lack the departmental oversight required by the baseline design function UC-3, and no downstream features (publishing, versioning) can operate on a properly governed requisition.

**Independent Test**: Can be fully tested by signing in as an HR Admin, creating a requisition for a specific department, submitting it for approval, then signing in as the department head for that department to approve or reject. Verify that department heads cannot approve requisitions outside their department, and that the full approval chain is recorded.

**Acceptance Scenarios**:

1. **Given** an HR Admin with a complete Draft requisition for a department, **When** the HR Admin submits it for approval, **Then** the requisition status changes to Pending Approval and the department head of that department can see it in their approval queue.
2. **Given** a Pending Approval requisition, **When** the department head for the matching department approves it with optional comments, **Then** the requisition status changes to Approved and the approval is recorded with actor, timestamp, comments, and decision.
3. **Given** a Pending Approval requisition, **When** the department head rejects it with a reason, **Then** the requisition status changes to Rejected and the HR Admin can view the rejection reason and revise the requisition.
4. **Given** a Pending Approval requisition for Department A, **When** a department head of Department B attempts to approve it, **Then** the action is denied because the department head is not authorized for that department.
5. **Given** a rejected requisition, **When** the HR Admin edits and resubmits it, **Then** a new approval cycle begins and the full history of previous submissions and rejections is preserved.

---

### User Story 2 – Template Versioning for Job Descriptions and Rubrics (Priority: P2)

The system records versioned snapshots of job description content and associated screening criteria (the `requirements` field, referred to as "rubric" in versioning context) each time a requisition is submitted for approval or formally changed. HR Admins can view the version history of any requisition's description and requirements, compare versions, and the active version is always identifiable.

**Why this priority**: Template versioning ensures auditability and consistency in what was approved versus what is published. It prevents undocumented post-approval changes to job requirements, which is critical for fair and transparent recruitment.

**Independent Test**: Can be fully tested by creating a requisition, submitting it (capturing version 1), editing the description after rejection, resubmitting (capturing version 2), and verifying that both versions are retrievable with correct timestamps and content diffs.

**Acceptance Scenarios**:

1. **Given** an HR Admin submits a requisition for approval, **When** the submission is processed, **Then** the system creates a versioned snapshot of the job description and requirements content with a version number, timestamp, and the actor who triggered the version.
2. **Given** a requisition with multiple versions, **When** an HR Admin views the version history, **Then** all versions are listed chronologically with version number, creation date, and author.
3. **Given** a requisition with at least two versions, **When** an HR Admin selects two versions to compare, **Then** the system displays a side-by-side or inline difference view showing what changed between versions.
4. **Given** an approved requisition, **When** any change is made to the job description or requirements content, **Then** the system creates a new version automatically, and the previous approved version remains accessible.

---

### User Story 3 – Simulated Job-Board Publishing (Priority: P3)

HR Admins can simulate publishing approved and opened job requisitions to external job boards. The system creates local records that represent what would be sent to each target platform, including platform name, payload summary, sync status, and timestamp. No actual external HTTP requests are made; all synchronization is represented through local database records.

**Why this priority**: Job-board sync simulation completes the requisition publishing lifecycle and demonstrates the integration pattern for external job boards without requiring real external API dependencies, which aligns with the academic simulation constraint.

**Independent Test**: Can be fully tested by approving and opening a requisition, initiating a simulated publish to one or more target platforms, and verifying local sync records show correct platform name, payload preview, status, and timestamp.

**Acceptance Scenarios**:

1. **Given** an Open requisition, **When** an HR Admin initiates a simulated publish and selects one or more target platforms from a predefined list, **Then** the system creates a local sync record for each selected platform with status "Queued."
2. **Given** a queued sync record, **When** the simulated sync processes, **Then** the record status transitions to "Published" with a completion timestamp and a payload summary showing the job title, department, description excerpt, and requirements sent.
3. **Given** existing sync records for a requisition, **When** an HR Admin views the sync history, **Then** all records are listed with platform name, payload summary, sync status, and timestamps.
4. **Given** a requisition that has been closed, **When** an HR Admin initiates a simulated unpublish, **Then** the system creates sync records with status "Unpublished" for each previously published platform.
5. **Given** any sync operation, **When** the operation completes, **Then** no external HTTP requests or API calls are made; all data remains in the local database.

---

### User Story 4 – Comprehensive Audit Trail (Priority: P1)

Every governance action—approval decisions, status transitions, template version changes, and job-board sync operations—is recorded in an immutable audit log. Authorized users can review the audit trail filtered by requisition, actor, action type, and date range.

**Why this priority**: Auditability is a cross-cutting governance requirement. Without it, the approval workflow, versioning, and publishing features lack accountability and traceability, violating both the SRS audit requirements and the constitution's mandate for audit-relevant change records.

**Independent Test**: Can be fully tested by performing a sequence of governance actions (create, submit, approve, version, publish) and verifying that each action appears in the audit log with correct actor, timestamp, action type, previous value, and new value.

**Acceptance Scenarios**:

1. **Given** any requisition status change (Draft → Pending Approval → Approved → Open → Closed or Rejected), **When** the transition occurs, **Then** an audit record is created with requisition ID, actor, action type, old status, new status, timestamp, and optional comments.
2. **Given** a template version is created, **When** the version is saved, **Then** an audit record links the version event to the requisition, actor, and timestamp.
3. **Given** a simulated job-board sync operation, **When** the sync record is created or updated, **Then** an audit record captures the operation type, target platform, sync status, and actor.
4. **Given** an authorized HR Admin, **When** they access the audit log for a requisition, **Then** all audit entries for that requisition are displayed in reverse chronological order with filtering by action type and date range.
5. **Given** a non-authorized user, **When** they attempt to access the audit log, **Then** access is denied without disclosing audit data.

---

### Edge Cases

- If a requisition's department has no designated department head, the system displays an error when submission is attempted and prevents the requisition from entering the approval queue.
- If a department head is deactivated while they have pending approvals, those requisitions remain in Pending Approval status and an HR Admin is alerted to reassign or handle the approval.
- If an HR Admin attempts to publish a requisition that is not in Open status, the publish action is blocked with a clear message.
- If a requisition is edited after approval but before opening, the system requires re-approval and creates a new template version.
- If two HR Admins simultaneously attempt to submit or edit the same requisition, the system uses optimistic concurrency control to prevent stale overwrites.
- If a sync record for a platform already exists with "Published" status and the user attempts to publish again to the same platform, the system blocks the duplicate and shows the existing record.
- If the template version comparison is requested for a requisition with only one version, the system shows the single version without a diff view.
- If an HR Admin attempts to approve their own requisition submission, approval is denied.
- Invalid, missing, or expired form inputs are rejected with clear server-side validation messages.
- Candidate users and interviewers have no access to governance workflows, approval queues, template versions, sync records, or audit logs.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow HR Admins to submit complete Draft requisitions for department-head approval, routing the approval request to the designated department head of the requisition's assigned department.
- **FR-002**: System MUST allow department heads to view a queue of requisitions pending their approval, limited to requisitions within their own department.
- **FR-003**: System MUST allow department heads to approve or reject pending requisitions with optional comments, recording the decision, actor, timestamp, and reason.
- **FR-004**: System MUST transition rejected requisitions back to a revisable state where the HR Admin can edit and resubmit them.
- **FR-005**: System MUST prevent department heads from approving requisitions outside their assigned department.
- **FR-006**: System MUST prevent the HR Admin who submitted a requisition from also approving it as department head for the same requisition.
- **FR-007**: System MUST create a versioned snapshot of job description and requirements content each time a requisition is submitted for approval.
- **FR-008**: System MUST create a new template version automatically when the job description or requirements content of an approved requisition is modified.
- **FR-009**: System MUST allow HR Admins to view the full version history of any requisition's job description and requirements, showing version number, date, and author.
- **FR-010**: System MUST allow HR Admins to compare any two versions of a requisition's job description or requirements, displaying differences clearly.
- **FR-011**: System MUST allow HR Admins to simulate publishing an Open requisition to one or more target platforms selected from a predefined list of simulated job boards.
- **FR-012**: System MUST create local sync records for each simulated publish with fields for target platform, payload summary, sync status, and timestamp.
- **FR-013**: System MUST transition sync record status through Queued → Published lifecycle stages without making any external HTTP requests or API calls.
- **FR-014**: System MUST allow HR Admins to simulate unpublishing a closed requisition, creating sync records with "Unpublished" status.
- **FR-015**: System MUST allow HR Admins to view sync history for any requisition, showing all sync records with platform name, payload summary, status, and timestamps.
- **FR-016**: System MUST record an audit log entry for every requisition status transition, approval decision, template version creation, and job-board sync operation.
- **FR-017**: System MUST store audit entries with requisition ID, actor, action type, old value, new value, timestamp, and optional comments.
- **FR-018**: System MUST allow authorized HR Admins to view and filter the audit log by requisition, actor, action type, and date range.
- **FR-019**: System MUST validate all requisition fields (title, department, description, requirements) before allowing submission for approval.
- **FR-020**: System MUST use optimistic concurrency control to prevent stale overwrites when multiple users edit the same requisition simultaneously.
- **FR-021**: System MUST block publishing actions for requisitions that are not in Open status.
- **FR-022**: System MUST prevent duplicate sync records for the same requisition and platform when a "Published" record already exists.
- **FR-023**: System MUST require re-approval and create a new template version when an approved requisition's description or requirements is edited before opening.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST include creating and editing requisitions, submitting for approval, publishing to simulated boards, viewing template versions, viewing sync records, and viewing audit logs for governance workflows.
- **RP-002**: Department head access MUST be limited to approving or rejecting requisitions within their own department and viewing approval history for their department's requisitions.
- **RP-003**: Candidate access MUST be limited to viewing Open requisitions and their own applications; candidates MUST NOT see approval workflows, template versions, sync records, or audit logs.
- **RP-004**: Technical Interviewer access MUST NOT include requisition governance, approval, publishing, versioning, or audit log features.
- **RP-005**: Audit log entries and governance metadata MUST be hidden from unauthorized roles.
- **RP-006**: Department heads MUST NOT access requisitions, approval queues, or audit data from departments other than their own.

### Key Entities *(include if feature involves data)*

- **Approval Workflow Step**: Records each approval or rejection decision for a requisition, including the requisition ID, approver (department head), decision (approved/rejected), comments, and timestamp. Supports the multi-tier approval chain history.
- **Template Version**: A versioned snapshot of a requisition's job description (`description`) and screening criteria (`requirements`), with version number, requisition ID, description body, requirements body, author, and creation timestamp. "Rubric" in versioning context refers to the `requirements` field. Enables comparison and historical review.
- **Job Board Sync Record**: A local-only record representing a simulated publish or unpublish operation, with requisition ID, target platform name, payload summary, sync status (Queued, Published, Unpublished, Failed), and timestamps. No external requests are made.
- **Governance Audit Log Entry**: An immutable record of every governance action, with requisition ID, actor, action type (status_change, approval_decision, version_created, sync_operation), old value, new value, timestamp, and optional comments.
- **Department Head Assignment**: A one-to-one association between an HR Admin user and a single department that grants them department-head approval authority. Each user can head at most one department, and each department has at most one designated head.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can submit a requisition for approval and have the department head approve or reject it within 2 minutes during a guided acceptance test.
- **SC-002**: 100% of requisitions submitted for approval are visible only to the department head of the matching department in acceptance testing.
- **SC-003**: 100% of cross-department approval attempts are denied in acceptance testing.
- **SC-004**: Every requisition submission and content change creates a retrievable template version with correct version number, timestamp, and author in acceptance testing.
- **SC-005**: HR Admins can compare any two template versions and identify differences within 30 seconds during a guided demo.
- **SC-006**: Simulated publishing creates local sync records for all selected platforms with correct payload summary, status, and timestamp, with zero external HTTP requests during acceptance testing.
- **SC-007**: 100% of governance actions (approvals, rejections, status changes, version events, sync operations) produce an audit log entry with correct actor, action, and timestamp.
- **SC-008**: HR Admins can filter the audit log by requisition, action type, and date range and retrieve relevant entries within 5 seconds during demo testing.
- **SC-009**: 100% of unauthorized access attempts to governance features (by candidates, interviewers, or wrong-department heads) are denied without data disclosure.

## Assumptions

- The existing RBAC foundation (spec 001) and job requisition lifecycle (spec 002) are implemented and available, including session-based authentication, role enforcement, and the base requisition CRUD and status lifecycle.
- Department heads are HR Admin users who have been assigned department-head authority for exactly one department (one user per department, one department per user). The assignment mechanism is part of this feature.
- The predefined list of simulated job boards includes representative platform names (e.g., "LinkedIn Jobs," "Indeed," "Glassdoor," "Internal Careers Page") and is configurable by HR Admins.
- Simulated sync operations are instantaneous—records transition from Queued to Published immediately within the same request since no real external processing occurs.
- Rubric content refers to the existing `requirements` field on job requisitions, which captures screening or evaluation criteria. Template versioning snapshots `description` + `requirements` together; no separate rubric column is added.
- The audit log for this feature is append-only and does not support deletion or modification of audit entries.
- Email or in-app notification of approval requests is handled by the notifications feature (spec 007) and is not duplicated here; however, the approval queue serves as the primary awareness mechanism.
- The optimistic concurrency control approach from spec 002 (version/timestamp check on save) is reused for this feature.
