# Feature Specification: Interview Coordination Workflows

**Feature Branch**: `011-interview-coordination`  
**Created**: 2026-05-06  
**Status**: Draft  
**Input**: User description: "Complete interview coordination workflows in server-rendered Vanilla PHP. HR admins can automatically build a balanced interview panel using active HR, senior technical interviewers, interviewers, and observers. The system recommends assignments using workload counts and schedule conflicts. Interview sessions include a simulated live coding workspace stored in the database and refreshed through server-rendered forms. HR can approve a session extension for technical issues, and all scheduling, assignment, extension, and live coding changes are audited."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.docx` section 4 use cases UC-13 through UC-19 and nonfunctional System Audit Trail; `Diagrams/document.md` design functions #15 through #21 and #39; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 2.pdf`; `Diagrams/Acrivity Diagram/Activity 3.pdf`; `Diagrams/Acrivity Diagram/Activity 4.pdf`; `Diagrams/Acrivity Diagram/Activity 5.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Acrivity Diagram/Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`; `.specify/memory/constitution.md`; `specs/010-assessment-integrity-adaptive-testing/plan.md`.
- **SRS / Use Case IDs**: UC-13 Interviewer Availability Conflict Resolver, UC-14 Multi-Representative Panel Builder, UC-15 Automated Interview Briefing Generator, UC-16 Live Coding Environment Sync, UC-17 Interviewer Shadowing Logic, UC-18 Session Extension Protocol, UC-19 Interviewer Load Balancer; design functions #15 Interviewer Availability Conflict Resolver, #16 Multi-Representative Panel Builder, #17 Automated Interview Briefing Generator, #18 Live Coding Environment Sync, #19 Interviewer Shadowing Logic, #20 Session Extension Protocol, #21 Interviewer Load Balancer, #39 System Audit Trail.
- **Baseline Entities**: `users`, `departments`, `candidates`, `applications`, `assessments`, `candidate_assessments`, `interviews`, `interviewers_assignment`, `interview_feedback`, `notifications`, plus local records needed for interview availability, briefing snapshots, assignment recommendations, simulated live coding workspace state, extension requests, and audit events.
- **Baseline Workflow**: Candidate applies, passes technical assessment, reaches the interview stage, HR schedules an interview, the system recommends an available and balanced panel, interviewers conduct the session with structured context and a simulated coding workspace, observers may shadow without influencing evaluation, HR reviews technical-issue extension requests, and later feedback/evaluation workflows continue from the completed session.
- **Scope Decision**: Matches the approved interview coordination baseline with one explicit constraint: UC-16 describes instant real-time live coding synchronization, but this feature provides a simulated live coding workspace refreshed through normal server-rendered form submissions. This keeps the feature aligned with the Vanilla PHP monolithic delivery model and avoids external real-time collaboration services.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP pages.
- **Routing**: Browser page routes and form submissions only; no REST API contract or separated frontend application.
- **Data Access**: MySQL through PDO and plain SQL schema files.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and authorization policies.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - HR Schedules a Balanced Panel (Priority: P1)

An HR Admin schedules an interview for an application in the interview stage and asks the system to recommend a balanced panel using active HR representatives, senior technical interviewers, interviewers, and optional observers.

**Why this priority**: Interview coordination cannot proceed until HR can create a session and lock in a valid panel without manually checking every staff member.

**Independent Test**: Can be fully tested by moving an application to interview stage, entering a proposed interview date and duration, generating panel recommendations, and saving one recommended panel.

**Acceptance Scenarios**:

1. **Given** an HR Admin is scheduling an interview for an eligible application, **When** they request panel recommendations for a date and duration, **Then** the system returns active staff candidates grouped by HR representative, senior technical interviewer, interviewer, and observer eligibility.
2. **Given** enough active staff are available, **When** HR accepts a recommended panel, **Then** the interview is scheduled and the selected panel assignments are locked to the session.
3. **Given** the required panel mix cannot be satisfied, **When** HR requests recommendations, **Then** the system marks the panel as incomplete and explains which required panel role is missing.
4. **Given** HR manually changes a recommended assignment, **When** the replacement creates a schedule conflict or weakens the required panel mix, **Then** the system warns HR before saving.

---

### User Story 2 - System Recommends Low-Conflict Assignments (Priority: P1)

HR sees assignment recommendations ranked by workload and schedule conflicts so interviews are distributed fairly and avoid double-booking.

**Why this priority**: The user request explicitly depends on workload-aware and conflict-aware assignment recommendations to reduce coordination overhead.

**Independent Test**: Can be fully tested by preparing staff with different existing interview counts and overlapping schedules, then confirming recommendations prefer available staff with lower workload.

**Acceptance Scenarios**:

1. **Given** multiple active interviewers can fill the same panel role, **When** recommendations are generated, **Then** staff with fewer upcoming assigned interviews are ranked ahead of equally qualified staff with heavier workloads.
2. **Given** an active staff member already has an overlapping scheduled interview, **When** recommendations are generated, **Then** that staff member is excluded or clearly marked as conflicted and not recommended by default.
3. **Given** multiple staff have equal workload and no schedule conflicts, **When** recommendations are generated, **Then** the system uses a consistent tie-breaker and displays the reason for the recommendation.
4. **Given** HR saves an assignment, **When** another interview already overlaps for the same staff member, **Then** the system prevents the double-booking unless HR records an explicit override reason.

---

### User Story 3 - Interview Participants Use Simulated Coding Workspace (Priority: P2)

During a technical interview, the candidate and assigned interviewers use a simulated coding workspace where code text, prompt notes, and run notes are saved and refreshed through page form submissions.

**Why this priority**: Live coding is a core technical interview activity, but it can be delivered as a reliable simulated workflow after scheduling and assignment are working.

**Independent Test**: Can be fully tested by opening a scheduled technical interview as the assigned candidate and interviewer, saving code text from the candidate view, refreshing the interviewer view, and verifying the latest workspace content is visible to authorized participants.

**Acceptance Scenarios**:

1. **Given** a scheduled technical interview has started or is open for preparation, **When** the candidate saves code text or run notes, **Then** the workspace records the latest content and timestamp for that interview session.
2. **Given** an assigned interviewer views the same interview session, **When** they refresh the workspace page, **Then** they see the latest saved coding content and can add interviewer notes if permitted.
3. **Given** an observer is assigned as training-only, **When** they open the workspace, **Then** they can view the session content but cannot alter official scoring or candidate-visible code.
4. **Given** an unassigned user attempts to open the workspace, **When** access is checked, **Then** the system denies access and records the denied attempt if audit rules require it.

---

### User Story 4 - HR Approves Technical-Issue Extensions (Priority: P2)

An interviewer can request extra interview time when technical issues occur, and HR can approve or deny the extension with a recorded reason.

**Why this priority**: The extension protocol protects fairness for candidates while keeping HR accountable for schedule changes.

**Independent Test**: Can be fully tested by submitting an extension request from an assigned interviewer, approving it as HR, and confirming the interview end time and audit trail reflect the approved extension.

**Acceptance Scenarios**:

1. **Given** an assigned interviewer reports a technical issue during a scheduled session, **When** they submit an extension request with requested minutes and reason, **Then** HR can review the pending request.
2. **Given** HR approves an extension request, **When** the decision is saved, **Then** the session duration or end time increases by the approved amount and participants can see the updated time.
3. **Given** HR denies an extension request, **When** the decision is saved, **Then** the original session time remains unchanged and the denial reason is visible to authorized participants.
4. **Given** the issue is resolved before HR decides, **When** the requester cancels the pending request, **Then** no time is added and the cancellation is recorded.

---

### User Story 5 - Interview Coordination Changes Are Audited (Priority: P1)

HR and compliance reviewers can trace every scheduling, assignment, extension, and live coding change with who made the change, what changed, when it changed, and why when a reason is required.

**Why this priority**: Auditability is required by the SRS and user request because interview data affects candidate evaluation and fairness.

**Independent Test**: Can be fully tested by scheduling an interview, changing assignments, saving workspace content, approving an extension, and verifying each action appears in the audit trail with actor, timestamp, action type, and changed values.

**Acceptance Scenarios**:

1. **Given** HR creates, updates, cancels, or reschedules an interview, **When** the change is saved, **Then** the audit trail records the scheduling action and before/after values where applicable.
2. **Given** HR accepts, changes, removes, or overrides a panel assignment, **When** the assignment change is saved, **Then** the audit trail records the staff member, panel role, actor, timestamp, and override reason when applicable.
3. **Given** workspace code, run notes, or interviewer notes are saved, **When** the workspace refreshes, **Then** the audit trail records the live coding change without exposing it to unauthorized users.
4. **Given** an extension is requested, approved, denied, or cancelled, **When** the decision is saved, **Then** the audit trail records the extension action, requested minutes, approved minutes if any, reason, actor, and timestamp.

### Edge Cases

- HR tries to schedule an interview for an application that is not in the interview stage.
- The proposed interview time overlaps with another interview for the same candidate or staff member.
- The active staff pool does not include a senior technical interviewer for the requested slot.
- A staff member becomes inactive after being recommended but before HR saves the assignment.
- HR attempts to assign the same person twice to the same panel under different panel roles.
- An observer attempts to submit official feedback or alter candidate-visible coding content.
- A candidate opens a live coding workspace before the allowed preparation or interview window.
- Two authorized participants save workspace updates close together.
- An extension request would push the session into another scheduled interview for a participant.
- HR approves fewer minutes than requested or denies the extension after the original end time has passed.
- An unauthorized user attempts to view candidate interview data, workspace content, or audit details.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow authorized HR Admins to create, update, reschedule, cancel, and view interviews for applications that are eligible for the interview stage.
- **FR-002**: System MUST validate interview scheduling inputs, including application eligibility, date and time, positive duration, interview type, and required participant roles.
- **FR-003**: System MUST recommend interview panel members only from active staff eligible for the requested panel role.
- **FR-004**: System MUST support a balanced panel containing an HR representative, a senior technical interviewer when required, one or more interviewers, and optional observers.
- **FR-005**: System MUST rank recommended panel members using current workload counts and schedule conflict status.
- **FR-006**: System MUST calculate workload counts from upcoming scheduled interviews assigned to each staff member within the configured scheduling window.
- **FR-007**: System MUST detect schedule conflicts for candidates and assigned staff using existing scheduled interviews and the requested session time range.
- **FR-008**: System MUST exclude conflicted staff from default recommendations or clearly mark them as conflicted before HR can override.
- **FR-009**: System MUST prevent double-booked assignments unless HR records an explicit override reason.
- **FR-010**: System MUST save panel assignments with each participant's panel role and whether the participant is training-only or official.
- **FR-011**: System MUST prevent the same user from being assigned more than once to the same interview session.
- **FR-012**: System MUST generate an interview briefing snapshot from available candidate resume/profile data, assessment score summaries, and job requirements, while clearly flagging missing source data.
- **FR-013**: System MUST make the briefing snapshot available to authorized assigned participants before and during the interview.
- **FR-014**: System MUST provide a simulated coding workspace for each technical interview session.
- **FR-015**: System MUST save coding workspace content, candidate run notes, interviewer notes, and timestamps for each authorized form submission.
- **FR-016**: System MUST show the latest saved coding workspace content after participants refresh or resubmit the server-rendered workspace form.
- **FR-017**: System MUST restrict workspace access to the assigned candidate, assigned official interviewers, assigned observers, and authorized HR Admins.
- **FR-018**: System MUST allow assigned interviewers to request a session extension for technical issues with requested minutes and a required reason.
- **FR-019**: System MUST allow authorized HR Admins to approve, deny, or record cancellation of pending extension requests.
- **FR-020**: System MUST update the interview duration or end time only after HR approves an extension and MUST keep the original time when the request is denied or cancelled.
- **FR-021**: System MUST detect when an approved extension creates a new schedule conflict and warn HR before the approval is saved.
- **FR-022**: System MUST notify or visibly inform authorized participants when a session is scheduled, assignments change, workspace content changes, or an extension decision changes their session details.
- **FR-023**: System MUST audit all interview scheduling creates, updates, cancellations, and reschedules.
- **FR-024**: System MUST audit all panel recommendation acceptances, manual assignment changes, removals, conflict overrides, and role changes.
- **FR-025**: System MUST audit all extension requests, approvals, denials, cancellations, requested minutes, approved minutes, and decision reasons.
- **FR-026**: System MUST audit all live coding workspace changes, including actor, timestamp, changed section, and interview session.
- **FR-027**: System MUST preserve audit records so HR can review the interview coordination history for a candidate application.
- **FR-028**: System MUST block candidates, observers, and unassigned interviewers from viewing interview data outside their authorized scope.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST be limited to scheduling, rescheduling, cancellation, assignment approval, panel override, briefing review, extension decisions, live coding oversight, and audit review for recruitment operations.
- **RP-002**: Technical Interviewer access MUST be limited to assigned interview sessions, assigned candidate briefing data, simulated live coding workspace content, extension requests for their sessions, and later feedback entry when applicable.
- **RP-003**: Candidate access MUST be limited to their own scheduled interview details, own simulated coding workspace, allowed timing updates, and participant-visible extension decisions.
- **RP-004**: Junior Staff or observer access MUST remain read-only or training-only for interview content and MUST NOT affect official scoring, panel balance, or final evaluation.
- **RP-005**: Candidate PII, resumes, assessment summaries, interview workspace content, panel assignments, extension details, and audit records MUST be hidden from unauthorized roles.
- **RP-006**: Simulated live coding synchronization MUST be labeled or presented as refresh-based collaboration so participants do not assume real-time external execution or monitoring.

### Key Entities *(include if feature involves data)*

- **Interview Session**: A scheduled interview for an application, including interview type, scheduled time, duration, status, and extension-adjusted time when approved.
- **Panel Assignment**: A participant linked to an interview session with a panel role, official or observer status, conflict status, and assignment reason when needed.
- **Staff Workload Summary**: A recommendation input that counts upcoming scheduled interviews for each eligible staff member during the configured scheduling window.
- **Schedule Conflict**: A detected overlap between the proposed interview time and another interview assigned to the same candidate or staff member.
- **Interview Briefing Snapshot**: A saved preparation bundle containing available candidate profile/resume information, assessment score summaries, and job requirements for assigned participants.
- **Simulated Coding Workspace**: The interview-specific code text, candidate run notes, interviewer notes, latest saved state, and save history refreshed through form submissions.
- **Extension Request**: A technical-issue request for additional interview time, including requester, reason, requested minutes, HR decision, approved minutes, and status.
- **Interview Audit Event**: A trace record for scheduling, assignment, extension, or live coding changes, including actor, timestamp, action type, affected interview, and before/after details where applicable.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can schedule an eligible interview and save a recommended balanced panel in under 5 minutes during user acceptance testing.
- **SC-002**: At least 95% of generated panel recommendations for seeded test data avoid staff and candidate schedule conflicts when conflict-free alternatives exist.
- **SC-003**: At least 90% of recommendations choose the lowest-workload eligible staff member when candidates have equal role eligibility and no conflicts.
- **SC-004**: 100% of saved interview panels either meet the required role mix or are clearly marked incomplete with the missing role visible to HR.
- **SC-005**: Authorized participants can see the latest saved simulated coding workspace content after one page refresh or form submission in 95% of acceptance-test attempts.
- **SC-006**: HR extension approvals update the participant-visible session time within one normal page refresh in 100% of acceptance-test cases.
- **SC-007**: 100% of scheduling, assignment, extension, and live coding changes produce audit records with actor, timestamp, action type, and affected interview session.
- **SC-008**: No unassigned interviewer, observer outside the session, or candidate from another application can access interview workspace, briefing, or audit data during privacy acceptance testing.
- **SC-009**: HR can review a candidate application's complete interview coordination history in under 2 minutes for a session with at least ten audit events.
- **SC-010**: At least 85% of HR and interviewer acceptance-test participants report that the panel recommendation reasons are understandable without additional explanation.

## Assumptions

- The feature uses existing authenticated user accounts and active/inactive status to determine eligible staff.
- Senior technical interviewer eligibility is represented by existing or planned staff profile attributes such as department, seniority, specialization, or panel capability.
- Observer and junior staff participation is represented as a training-only panel assignment even if the baseline user table stores broad account roles.
- Workload balancing uses upcoming scheduled interviews in the current scheduling window; the exact window can be finalized during planning without changing user-facing behavior.
- Schedule conflict detection uses interview sessions and availability already stored in SRIM; external calendar synchronization is out of scope unless separately approved.
- The simulated coding workspace is not a real compiler, sandbox, video feed, or external real-time collaboration tool.
- Server-rendered form refresh is the intended collaboration model for live coding in this feature; participants may need to refresh or submit to see updates.
- Interview briefing snapshots use data already available in SRIM and flag missing resumes, assessment scores, or job requirements rather than blocking scheduling.
- HR remains the authority for approving session extensions; interviewers can request but cannot self-approve added time.
- Audit records are retained according to SRIM compliance and retention policy and are visible only to authorized roles.
