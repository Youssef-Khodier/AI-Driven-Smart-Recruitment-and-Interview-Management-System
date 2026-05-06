# Feature Specification: Feedback Governance Analytics

**Feature Branch**: `012-feedback-governance-analytics`  
**Created**: 2026-05-06  
**Status**: Draft  
**Input**: User description: "Complete feedback governance and evaluation analytics in server-rendered Vanilla PHP. The system calculates normalized feedback scores using interviewer harshness trends, lets interviewers or HR flag serious candidate concerns, records candidate post-interview experience sentiment, triggers a consensus/debrief meeting workflow after all official feedback is submitted, and displays a competency gap visualizer comparing candidate scores against the ideal job profile. All decisions, flags, normalized scores, and meeting outcomes must be auditable and RBAC-protected."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.docx` UC-20 through UC-25 and nonfunctional audit/privacy sections; `Diagrams/document.md` functions 22 through 28 and functions 36 through 39; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 4.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`; active `specs/011-interview-coordination/plan.md`; `.specify/memory/constitution.md`.
- **SRS / Use Case IDs**: UC-20 Multi-Dimensional Feedback Aggregator, UC-21 Score Normalization Algorithm, UC-22 Candidate Red-Flag Escalation, UC-23 Consensus Meeting Automator, UC-24 Competency Gap Visualizer, UC-25 Hiring Recommendation State-Machine, plus System Audit Trail, Role-Based Access Control, and Post-Interview Sentiment Logger.
- **Baseline Entities**: `users`, `candidates`, `job_requisitions`, `applications`, `interviews`, `interviewers_assignment`, `interview_feedback`, `final_evaluations`, `notifications`, and existing or new audit records for interview/evaluation actions.
- **Baseline Workflow**: Extends the post-interview flow where interviewers submit feedback, the system aggregates all feedback, checks for red flags, HR reviews recommendations, HR records agreement or override reasons, and candidate/application status is updated.
- **Scope Decision**: Matches the baseline Feedback & Evaluation scope while making the user-requested governance, auditability, normalized scoring, candidate sentiment, in-app debrief record, and visual competency comparison explicit for the next implementation phase.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates.
- **Routing**: Browser web routes and form submissions only; no REST API contract or separated frontend.
- **Data Access**: MySQL through PDO-backed repositories and plain SQL schema or migration files.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and policies.

## Clarifications

### Session 2026-05-06

- Q: What minimum history is required before applying interviewer harshness normalization? → A: 5 comparable prior submissions in 12 months.
- Q: What thresholds define competency gap severity in the visualizer? → A: Meeting is at least 90% of benchmark, minor gap is 75-89%, and major gap is below 75%.
- Q: What should an unresolved serious concern flag block? → A: Remaining official feedback may continue, but debrief completion, final recommendation approval, and candidate status changes are blocked until HR resolves the flag.
- Q: What is the required debrief meeting workflow scope? → A: In-app debrief record only: HR records participants, consensus, dissent, outcome, rationale, and next action.
- Q: Who owns ideal job competency benchmarks for gap analysis? → A: HR maintains explicit job competency benchmarks, optionally seeded from job requisition skill expectations.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Produce Governed Evaluation Report (Priority: P1)

As an HR Admin, I need a unified post-interview evaluation that aggregates official feedback, shows raw and normalized scores, explains whether normalization was applied, and records the resulting recommendation so that hiring decisions are fair, reviewable, and traceable.

**Why this priority**: The normalized evaluation report is the central business outcome for feedback governance and is required before decisions, debriefs, and competency analytics are useful.

**Independent Test**: Can be tested by completing all required official feedback for one interview panel and confirming HR can view the governed report with raw scores, normalized scores, recommendation state, missing-data status, and audit history.

**Acceptance Scenarios**:

1. **Given** a completed interview with all official panel feedback submitted and an interviewer has at least 5 comparable prior official feedback submissions in the last 12 months, **When** HR opens the evaluation report, **Then** the report displays raw scores, interviewer harshness adjustments, normalized competency and overall scores, the recommendation state, and an audit record of the calculation.
2. **Given** a completed interview with all official panel feedback submitted but an interviewer has fewer than 5 comparable prior official feedback submissions in the last 12 months, **When** HR opens the evaluation report, **Then** the report uses the raw score for that interviewer, labels the fallback reason, includes the interviewer in the completeness summary, and records the fallback in the audit trail.
3. **Given** a candidate has missing official feedback, **When** HR opens the evaluation report, **Then** the report clearly shows that final aggregation is pending and prevents final recommendation approval until required official feedback is complete.

---

### User Story 2 - Flag Serious Candidate Concerns (Priority: P1)

As an assigned interviewer or HR Admin, I need to flag serious candidate concerns during or after the interview so that urgent ethical, integrity, legal, or safety risks block final decision actions until HR review is complete.

**Why this priority**: Serious concern escalation directly affects hiring risk and must be governed before automated recommendations can be trusted.

**Independent Test**: Can be tested by an assigned interviewer creating a serious concern flag for a candidate and confirming HR receives the flagged evaluation state, review controls, notification, and audit record.

**Acceptance Scenarios**:

1. **Given** an assigned interviewer is viewing their interview feedback flow, **When** they submit a serious candidate concern with category, severity, and explanation, **Then** the candidate evaluation is marked as flagged, HR is alerted, remaining official feedback may continue, debrief completion and final decision actions are blocked, and the flag is audit-recorded with the actor, time, category, and reason.
2. **Given** HR is reviewing an open serious concern flag, **When** HR resolves it with a decision and rationale, **Then** final decision actions either remain blocked, resume, or transition to a no-hire recommendation according to the recorded HR decision, and the outcome is audit-recorded.
3. **Given** an interviewer is not assigned to the candidate interview, **When** they attempt to create or view a serious concern flag, **Then** the action is denied and no candidate data or flag details are exposed.

---

### User Story 3 - Capture Candidate Experience Sentiment (Priority: P2)

As a candidate, I need to submit a short post-interview experience sentiment entry so that HR can monitor interview quality without letting candidate sentiment alter official interviewer scoring.

**Why this priority**: Candidate experience feedback supports employer brand and process improvement while preserving separation from hiring score governance.

**Independent Test**: Can be tested by a candidate completing an interview, submitting an experience rating and comment, and confirming HR can view aggregate sentiment while interviewers cannot use it to change official scores.

**Acceptance Scenarios**:

1. **Given** a candidate has a completed interview, **When** they submit post-interview sentiment with a rating and optional comment, **Then** the sentiment is saved once for that interview, tied to the candidate's own application, and acknowledged to the candidate.
2. **Given** HR views the candidate's evaluation record, **When** sentiment exists, **Then** HR can see the experience rating and comment separately from official feedback scores.
3. **Given** an interviewer views the feedback or evaluation workflow, **When** candidate sentiment exists, **Then** the sentiment is not used to calculate official scores or normalize interviewer ratings.

---

### User Story 4 - Trigger Consensus/Debrief Workflow (Priority: P2)

As HR, I need the system to trigger an in-app consensus or debrief outcome record only after all official feedback is submitted so that panel decisions are coordinated without premature discussion or external meeting scheduling scope.

**Why this priority**: Debriefing is required for final decisions but should not start before the official independent evaluations are complete.

**Independent Test**: Can be tested by submitting feedback for each official panel member and confirming the in-app debrief record appears only after the final required submission, then recording participants, consensus, dissent, outcome, rationale, and next action.

**Acceptance Scenarios**:

1. **Given** one or more official panel members have not submitted feedback, **When** HR views the evaluation, **Then** the in-app debrief record remains pending and identifies the missing official submissions without exposing unauthorized candidate data.
2. **Given** all official panel feedback is submitted, **When** the final submission is received, **Then** an in-app debrief record is created for HR and official panel members with a pending outcome state.
3. **Given** HR completes the debrief, **When** HR records participants, consensus, dissent, final recommendation, rationale, and next action, **Then** the debrief outcome is saved, visible to authorized roles, and audit-recorded.

---

### User Story 5 - Visualize Competency Gaps (Priority: P3)

As HR, I need to compare a candidate's normalized competency scores against the ideal job profile so that strengths and gaps are visible before final hiring decisions.

**Why this priority**: The visualizer supports decision quality after governed scores exist, but it depends on completed feedback and role benchmarks.

**Independent Test**: Can be tested by HR maintaining competency benchmarks for a job, opening an evaluated candidate for that job, and confirming HR sees candidate scores, ideal profile values, gap labels, and missing-benchmark notices.

**Acceptance Scenarios**:

1. **Given** HR has maintained ideal competency benchmarks for a job and the candidate has normalized competency scores, **When** HR opens the gap visualizer, **Then** the page displays each competency's ideal score, candidate score, and gap severity where meeting is at least 90% of benchmark, minor gap is 75-89%, and major gap is below 75%.
2. **Given** one competency benchmark is missing, **When** HR opens the visualizer, **Then** available competencies are still shown and the missing benchmark is clearly flagged for HR completion.
3. **Given** an assigned interviewer views an authorized candidate evaluation, **When** gap data is available, **Then** they may view only the evaluation and gap information for assigned interviews and cannot see unrelated candidate comparisons.

---

### Edge Cases

- Official panel members exclude observers and junior staff shadowing roles from required feedback completion and score aggregation.
- If an interviewer has fewer than 5 comparable prior official feedback submissions in the last 12 months, normalization falls back to raw scores for that interviewer and labels the fallback reason.
- If a serious concern flag is opened after a recommendation was calculated, remaining official feedback may continue but debrief completion, final recommendation approval, and candidate status changes are blocked until HR resolves the flag.
- If an HR Admin overrides the recommendation, the override requires a rationale and preserves the original calculated recommendation for audit comparison.
- If candidate sentiment is submitted before the interview is marked completed, the submission is rejected with a clear message.
- If candidate sentiment is submitted more than once for the same interview, the latest allowed behavior is to prevent duplicates and show the existing submitted status.
- If all official feedback is submitted but an in-app debrief record already exists, the system prevents duplicate debrief records.
- If job profile benchmarks are missing or incomplete, the visualizer shows partial data with clear completion warnings instead of hiding the entire evaluation.
- If an authenticated user attempts an action outside their role or assignment, access is denied and sensitive candidate evaluation data remains hidden.
- Invalid, missing, duplicated, or expired form submissions are rejected safely with user-friendly validation messages and no partial decision state changes.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST aggregate official interviewer feedback for a completed interview into competency-level and overall evaluation results only after every required official panel member has submitted feedback.
- **FR-002**: System MUST distinguish official panel members from observers or shadowing staff so that observers can be excluded from required feedback completion, score aggregation, and recommendation thresholds.
- **FR-003**: System MUST calculate normalized feedback scores by comparing each interviewer's submitted scores with their historical scoring trend and applying a documented adjustment only when the interviewer has at least 5 comparable prior official feedback submissions in the last 12 months.
- **FR-004**: System MUST preserve raw submitted scores, normalized scores, normalization status, and fallback reasons so that HR can compare calculated outcomes with original feedback.
- **FR-005**: System MUST generate a final recommendation state from governed evaluation results using the baseline recommendation states: Strong Hire, Hire, No Hire, and Strong No Hire.
- **FR-006**: System MUST keep evaluations pending when required official feedback is missing and identify the missing official submissions to HR without exposing data to unauthorized roles.
- **FR-007**: System MUST allow assigned interviewers and HR Admins to create serious candidate concern flags with category, severity, explanation, candidate/interview reference, and submitted-by information.
- **FR-008**: System MUST allow remaining official feedback to continue when an unresolved serious concern flag exists, but MUST block debrief completion, final recommendation approval, and candidate status changes until HR resolves the flag.
- **FR-009**: System MUST allow HR Admins to resolve serious concern flags with an outcome, rationale, and resulting evaluation action.
- **FR-010**: System MUST notify HR when a serious concern flag is created and notify relevant official panel members when HR records a resolution that affects the debrief or recommendation.
- **FR-011**: System MUST allow candidates to submit one post-interview sentiment record for their own completed interview, including a rating and optional comment.
- **FR-012**: System MUST display candidate sentiment to HR separately from official scoring and MUST NOT include candidate sentiment in interviewer score normalization, score aggregation, or hiring recommendation calculations.
- **FR-013**: System MUST create an in-app consensus/debrief record when all official feedback for an interview is submitted and no duplicate debrief record already exists.
- **FR-014**: System MUST allow HR to record debrief outcome details including participants, outcome status, consensus level, dissent notes when applicable, final recommendation, decision rationale, and next action, without requiring external calendar scheduling.
- **FR-015**: System MUST display a competency gap visualizer that compares candidate competency scores against the ideal competency profile for the job and labels each gap as meeting when the candidate score is at least 90% of benchmark, minor gap when the score is 75-89% of benchmark, or major gap when the score is below 75% of benchmark.
- **FR-016**: System MUST allow HR to maintain explicit job competency benchmarks, optionally seeded from job requisition skill expectations, and MUST show missing or incomplete benchmarks in the visualizer and evaluation report so HR can complete the ideal profile before relying on final gap analysis.
- **FR-017**: System MUST require HR rationale when overriding a calculated recommendation, resolving a serious concern, or recording a debrief outcome that differs from the calculated recommendation.
- **FR-018**: System MUST provide audit history for submitted feedback, normalization calculations, concern flag creation and resolution, candidate sentiment submission, in-app debrief record creation, debrief outcomes, recommendation changes, and HR overrides.
- **FR-019**: System MUST ensure audit records include actor, role, timestamp, affected candidate/application/interview, action type, previous state when applicable, new state, and stated reason when applicable.
- **FR-020**: System MUST validate all feedback, flag, sentiment, debrief, benchmark, and recommendation inputs before saving and reject invalid score ranges, missing required reasons, duplicate restricted submissions, and unauthorized candidate references.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST include reviewing all candidate evaluation reports, normalized scores, concern flags, sentiment records, debrief outcomes, recommendation states, benchmark gaps, and audit history required for governance.
- **RP-002**: Technical Interviewer access MUST be limited to interviews and candidates assigned to them, including submitting official feedback, creating serious concern flags, and viewing authorized debrief or gap information after submission rules allow it.
- **RP-003**: Candidate access MUST be limited to submitting and viewing their own post-interview sentiment status and must not expose interviewer feedback, normalized scores, flags, debrief outcomes, or recommendation internals.
- **RP-004**: Junior Staff or observer access MUST be read-only or training-only where applicable and MUST NOT count toward official feedback completion or score aggregation.
- **RP-005**: Candidate PII, resumes, scores, feedback, concern flags, sentiment comments, recommendations, and debrief outcomes MUST be hidden from unauthorized roles.
- **RP-006**: Any simulated scoring or normalization decision support MUST be labeled as decision support and reviewable by HR before final candidate status changes.

### Key Entities *(include if feature involves data)*

- **Official Feedback Submission**: Interviewer-provided competency scores and comments for a specific assigned interview, including submitted status and official/non-official participation context.
- **Interviewer Harshness Trend**: Historical scoring summary for an interviewer based on at least 5 comparable prior official feedback submissions in the last 12 months, used to determine whether submitted scores are consistently stricter, neutral, or more generous than comparable panel scores.
- **Normalized Evaluation Result**: Candidate evaluation output containing raw scores, normalized scores, fallback indicators, aggregate competency scores, overall score, and recommendation state.
- **Serious Concern Flag**: Escalation record tied to a candidate interview or application, with category, severity, explanation, submitted-by actor, HR review state, resolution, and rationale.
- **Candidate Sentiment Entry**: Candidate's post-interview experience rating and optional comment tied to their own completed interview.
- **Consensus/Debrief Record**: In-app workflow record created after all official feedback is submitted, with participants, status, outcome, consensus level, dissent notes, final recommendation, rationale, and next action.
- **Competency Benchmark Profile**: HR-maintained ideal competency expectations for a job profile, optionally seeded from job requisition skill expectations, used to compare candidate scores against role requirements.
- **Competency Gap Snapshot**: Candidate-vs-ideal comparison for each competency, including candidate score, benchmark score, gap amount, and severity label where meeting is at least 90% of benchmark, minor gap is 75-89%, and major gap is below 75%.
- **Audit Event**: Immutable governance event describing who performed an evaluation-related action, what changed, when it changed, and why it changed when a reason is required.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR can review a completed candidate evaluation report with raw scores, normalized scores, recommendation state, and audit history within 2 minutes after the final official feedback submission.
- **SC-002**: 100% of serious concern flags created by assigned interviewers or HR are visible to HR with category, severity, explanation, status, and audit trail before any final recommendation can be approved.
- **SC-003**: The in-app debrief record is created exactly once for each interview evaluation and only after 100% of official panel feedback has been submitted.
- **SC-004**: At least 90% of candidate post-interview sentiment submissions can be completed in under 1 minute by candidates who have completed an interview.
- **SC-005**: HR can identify the top competency gaps for an evaluated candidate in under 30 seconds when ideal profile benchmarks are present.
- **SC-006**: 100% of HR recommendation overrides, flag resolutions, normalized score calculations, and debrief outcomes include an audit event with actor, timestamp, affected record, action, and rationale when required.
- **SC-007**: Unauthorized users are prevented from viewing or changing candidate evaluation data in all role-based access checks for HR, interviewer, candidate, and observer contexts.
- **SC-008**: In user acceptance testing, at least 85% of HR reviewers agree that the governed evaluation report makes the basis for final decisions clear and reviewable.

## Assumptions

- Existing authentication, role assignment, interview scheduling, interview assignment, feedback, notification, and audit foundations from earlier phases are reused and extended.
- Official feedback means feedback from assigned non-observer panel members; junior staff and observers may be visible in debrief context but do not affect required completion or scoring.
- Historical harshness trends use prior completed official feedback available inside SRIM; if an interviewer has fewer than 5 comparable prior official feedback submissions in the last 12 months, the system uses raw scores and labels the fallback.
- Candidate sentiment is intended for process quality monitoring and employer-brand analytics, not for hiring-score calculation.
- The ideal job competency profile is HR-maintained, may be seeded from job requisition skill expectations, and must be available before final gap analysis is relied upon.
- Notifications are in-system notifications unless a later implementation plan explicitly includes external delivery.
- Audit records are retained according to SRIM's existing compliance and retention policy and are not editable by normal users.
