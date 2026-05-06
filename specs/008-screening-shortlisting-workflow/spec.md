# Feature Specification: Screening Shortlisting Workflow

**Feature Branch**: `008-screening-shortlisting-workflow`  
**Created**: 2026-05-05  
**Status**: Draft  
**Input**: User description: "Complete the screening and shortlisting workflow for SRIM in server-rendered Vanilla PHP. HR admins can configure skill weights and match thresholds per job requisition. The system recalculates simulated match scores using weighted job skills and candidate profile data, identifies possible duplicate candidate profiles, records candidate merge decisions in an audit log, generates an AI-ranked simulated shortlist for each requisition, and supports automated triage from APPLIED to SCREENING, ASSESSMENT, INTERVIEW, or REJECTED using configurable thresholds. All workflows must remain form-based, auditable, RBAC-protected, and simulated where AI is mentioned."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/document.md` recruitment lifecycle and functions 1 Automated Screening Triage, 2 Dynamic Skill-Weighting Engine, 4 Application Deduplication Logic, 5 AI-Ranked Shortlisting (Simulated), 36 RBAC, and 39 System Audit Trail; `Diagrams/SRS/SRS-SRIM final ver1.docx` sections covering Dynamic Skill-Weighting Engine, RBAC, System Audit Trail, and simulated AI terminology; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 2.pdf`; `Diagrams/Acrivity Diagram/Activity 3.pdf`; `Diagrams/Acrivity Diagram/Activity 4.pdf`; `Diagrams/Acrivity Diagram/Activity 5.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Acrivity Diagram/Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`.
- **SRS / Use Case IDs**: Baseline functions 1 Automated Screening Triage, 2 Dynamic Skill-Weighting Engine, 4 Application Deduplication Logic, 5 AI-Ranked Shortlisting (Simulated), 36 Role-Based Access Control, and 39 System Audit Trail.
- **Baseline Entities**: Users, roles, candidates, candidate merge log, job requisitions, applications, assessments, candidate assessments, interviews, interview feedback, final evaluations, notifications, and audit/status history records.
- **Baseline Workflow**: Candidate applies, system fetches candidate profile/resume data, screening determines qualification, qualified candidates proceed to assessment or interview, unqualified candidates are rejected, HR reviews recommendations, and all sensitive actions are permission-controlled.
- **Scope Decision**: Matches baseline. The only advanced intelligence in scope is simulated ranking and scoring; no external AI service, automated hiring decision without HR visibility, email delivery, or separated candidate-facing product is introduced.

## Clarifications

### Session 2026-05-05

- Q: Where should candidate skill data come from for the scoring engine, given the existing `candidates` table has no skills column? → A: Candidate skills are derived from existing profile fields (current_title, years_experience, resume metadata). HR maps evidence manually during scoring. No new candidate-facing skill input form is introduced.
- Q: How should per-skill score breakdowns be stored, given `applications.match_score` only holds the total? → A: Add a `match_score_breakdown JSON` column to the `applications` table storing per-skill weighted contributions and missing evidence flags. This follows the existing JSON column pattern (`questions.options`).
- Q: Is duplicate detection on-demand (HR-triggered) or a background batch scan? → A: On-demand per-requisition. HR triggers a duplicate check for a specific requisition's applicant pool. No background batch job is introduced.
- Q: What is the source of the skill list when HR configures screening weights? → A: HR enters skill names as free-text per requisition screening configuration. No global skills taxonomy table is introduced.
- Q: Should ignore/defer decisions and confidence categories extend `candidate_merge_log` or use a new table? → A: Extend `candidate_merge_log` by adding `decision_type ENUM('MERGE','IGNORE','DEFER')` and `confidence_category ENUM('HIGH','MEDIUM','LOW')` columns. No new table is introduced.

## SRIM Delivery Constraints *(mandatory)*

- **Delivery Mode**: Existing SRIM monolithic browser experience with server-rendered pages.
- **Routing**: Web pages and form submissions only; no separate service contract.
- **Data Access**: Existing SRIM recruitment records, status histories, and audit records are the source of truth.
- **Security**: Authenticated sessions, anti-forgery protection, server-side validation, role-based access, and auditable changes are required for every mutating action.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Configure Screening Rules (Priority: P1)

As an HR Admin, I can configure required skills, per-skill weights, and triage thresholds for a job requisition so that applicants are screened consistently against the job's approved criteria.

**Why this priority**: Screening cannot produce trustworthy match scores, shortlists, or triage decisions until each requisition has clear weighted criteria and thresholds.

**Independent Test**: Can be fully tested by opening an approved or open requisition, submitting a valid screening configuration form, and confirming the saved weights and thresholds are shown back to HR with an audit entry.

**Acceptance Scenarios**:

1. **Given** an HR Admin views an approved or open requisition with no screening rules, **When** they submit skills whose weights total 100% and thresholds for assessment, interview, and rejection, **Then** the system saves the configuration and records who made the change.
2. **Given** an HR Admin submits invalid weights, missing skills, overlapping thresholds, or out-of-range values, **When** the form is validated, **Then** the system rejects the change and shows field-level errors without changing the existing configuration.
3. **Given** a non-HR user attempts to configure screening rules, **When** they access or submit the form, **Then** the system denies the action and records no configuration change.

---

### User Story 2 - Recalculate Match Scores and Shortlist (Priority: P1)

As an HR Admin, I can recalculate simulated match scores and generate a simulated AI-ranked shortlist for a requisition so that I can review the strongest applicants before moving them forward.

**Why this priority**: The feature's core business value is reducing manual screening effort while keeping the ranking explainable and simulated.

**Independent Test**: Can be fully tested by applying configured rules to a requisition with candidate applications and confirming each applicant receives a score, explanation, rank, and shortlist inclusion or exclusion.

**Acceptance Scenarios**:

1. **Given** a requisition has configured weights and APPLIED candidates with profile data, **When** HR runs recalculation, **Then** each eligible application receives a 0-100 simulated match score based on the configured skills and candidate profile evidence.
2. **Given** recalculation completes, **When** HR views the shortlist, **Then** candidates are ordered by simulated match score with ties resolved by years of relevant experience and application date.
3. **Given** a candidate lacks profile evidence for a weighted skill, **When** scores are recalculated, **Then** the missing evidence reduces only that skill's contribution and the shortlist identifies the missing evidence.
4. **Given** simulated AI-ranked output is shown, **When** HR reviews the shortlist, **Then** the page clearly labels the ranking as simulated and reviewable rather than an external or final hiring decision.

---

### User Story 3 - Automated Triage From Applied (Priority: P2)

As an HR Admin, I can run automated triage for APPLIED candidates so that applications move to SCREENING, ASSESSMENT, INTERVIEW, or REJECTED according to the configured thresholds while remaining auditable.

**Why this priority**: Triage turns scoring into pipeline progress, but it must be controlled and auditable because it changes candidate status.

**Independent Test**: Can be fully tested by running triage on a requisition with APPLIED applications across score bands and verifying the resulting statuses and audit trail.

**Acceptance Scenarios**:

1. **Given** APPLIED applications have current match scores, **When** HR runs triage, **Then** applications are moved to the configured target status for their score band.
2. **Given** an application is already beyond APPLIED, **When** triage is run, **Then** the system does not automatically move it backward or overwrite the current pipeline stage.
3. **Given** triage changes an application status, **When** the status change is saved, **Then** the audit trail captures the previous status, new status, score used, threshold rule used, actor, timestamp, and simulated decision label.
4. **Given** a candidate is rejected by triage, **When** the candidate views their application status, **Then** they see only the status outcome and appropriate candidate-facing messaging, not other candidates' ranking or private HR notes.

---

### User Story 4 - Detect and Resolve Duplicate Candidates (Priority: P2)

As an HR Admin, I can review possible duplicate candidate profiles and record merge decisions so that duplicate applications are handled consistently without silently losing candidate history.

**Why this priority**: Duplicate profiles can distort rankings and audit records; HR must control the merge decision.

**Independent Test**: Can be fully tested by creating candidate profiles with similar email, phone, name, resume link, or profile attributes, reviewing duplicate suggestions, and recording a merge, ignore, or defer decision.

**Acceptance Scenarios**:

1. **Given** two candidate profiles in a requisition's applicant pool share strong duplicate indicators, **When** HR triggers the on-demand duplicate check for that requisition, **Then** the system shows a possible duplicate suggestion with matching evidence and confidence category.
2. **Given** HR chooses to merge candidates, **When** they confirm the primary profile and provide a reason, **Then** the system records the merge decision in the audit log and preserves traceability to both candidate profiles.
3. **Given** HR chooses to ignore or defer a duplicate suggestion, **When** they submit the decision and reason, **Then** the system records the decision and does not merge profiles.
4. **Given** an unauthorized user attempts to view or decide duplicate candidates, **When** they access the workflow, **Then** the system denies the action and exposes no candidate personal data.

---

### User Story 5 - Review Audit Evidence (Priority: P3)

As an HR Admin, I can review audit evidence for screening configuration, score recalculation, shortlist generation, duplicate decisions, and triage so that the recruitment process is explainable and defensible.

**Why this priority**: Audit review supports governance and troubleshooting after the core workflows exist.

**Independent Test**: Can be fully tested by performing each workflow action and confirming the audit view lists the action, actor, affected records, before/after values, and reason where applicable.

**Acceptance Scenarios**:

1. **Given** screening workflow actions have occurred, **When** HR filters audit evidence by requisition, candidate, action type, or date range, **Then** matching records are listed in reverse chronological order.
2. **Given** an audit record involves sensitive candidate data, **When** HR views the audit entry, **Then** only necessary identifiers and decision evidence are shown, with candidate personal data limited to what HR needs for review.

### Edge Cases

- If a requisition has no configured screening rules, recalculation and triage must be blocked with a clear instruction to configure rules first.
- If skill weights do not total 100%, the configuration must not be saved.
- If thresholds overlap, leave gaps, or assign an unsupported status, the configuration must not be saved.
- If no candidates are APPLIED for a requisition, recalculation and triage must complete with a clear zero-candidate result and no status changes.
- If candidate profile data is incomplete, the match score must still be calculated from available evidence and visibly identify missing evidence.
- If two candidates tie in score and experience, application date must determine display order, with earlier applications ranked first.
- If duplicate suggestions include an already-merged candidate pair, the system must not create duplicate merge decisions.
- If HR submits a merge decision without a reason, the decision must not be recorded.
- If an authenticated user attempts an action outside their role, access must be denied and no candidate data or scoring details must be exposed.
- If stale or duplicate form submissions occur, the system must avoid duplicate triage, duplicate recalculation audit entries for the same run, and duplicate merge decisions.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST allow HR Admins to define and update per-requisition screening skills, skill weights, and triage thresholds through form-based workflows.
- **FR-002**: The system MUST validate that configured skill weights are positive, total 100%, and that each skill name is a non-empty free-text label entered by HR before saving.
- **FR-003**: The system MUST validate that thresholds are ordered, non-overlapping, within a 0-100 scoring range, and map only to SCREENING, ASSESSMENT, INTERVIEW, or REJECTED.
- **FR-004**: The system MUST preserve the most recent active screening configuration for each requisition and record prior values in audit evidence when a configuration changes.
- **FR-005**: The system MUST calculate a simulated match score from 0 to 100 for each eligible application using the active skill weights and candidate profile evidence.
- **FR-006**: The system MUST show HR the match-score explanation by skill, including each weighted contribution and any missing candidate evidence.
- **FR-007**: The system MUST generate a simulated AI-ranked shortlist per requisition using current match scores and label the ranking as simulated.
- **FR-008**: The system MUST rank shortlist entries by score, then relevant experience, then application date, and show enough explanation for HR to review the order.
- **FR-009**: The system MUST allow HR Admins to run recalculation for a requisition without changing application statuses.
- **FR-010**: The system MUST allow HR Admins to run triage for APPLIED applications using the active thresholds after match scores are current.
- **FR-011**: The system MUST move only APPLIED applications during automated triage and MUST NOT automatically overwrite ASSESSMENT, INTERVIEW, OFFER, REJECTED, or HIRED statuses.
- **FR-012**: The system MUST record each triage status change with previous status, new status, score, threshold rule, actor, timestamp, and simulated decision label.
- **FR-013**: The system MUST identify possible duplicate candidate profiles within a requisition's applicant pool when HR triggers an on-demand duplicate check, using candidate identifiers and profile similarities such as email, phone, name, resume link, current title, skills, and experience.
- **FR-014**: The system MUST show duplicate suggestions to HR with matching evidence and a confidence category before any merge decision is recorded.
- **FR-015**: The system MUST require HR Admin confirmation, primary candidate selection, and a reason before recording a merge decision.
- **FR-016**: The system MUST support merge, ignore, and defer decisions for duplicate suggestions and record each decision in the audit log.
- **FR-017**: The system MUST preserve traceability to both candidate profiles involved in a merge decision and avoid silently deleting decision history.
- **FR-018**: The system MUST provide an HR-only audit view covering screening configuration changes, recalculation runs, shortlist generation, duplicate decisions, and triage changes.
- **FR-019**: The system MUST prevent duplicate processing when the same recalculation, triage, or merge form is submitted more than once.
- **FR-020**: The system MUST show user-friendly validation errors and preserve previously entered form values when a screening, threshold, or duplicate-decision form fails validation.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admins MUST be able to configure screening rules, recalculate scores, review simulated shortlists, run triage, review duplicates, record merge decisions, and view related audit evidence.
- **RP-002**: Technical Interviewers MUST NOT be able to configure screening rules, run triage, merge candidates, or view unassigned candidate shortlists; they may only see candidate information already allowed by their assigned interview or assessment workflow.
- **RP-003**: Candidates MUST see only their own application status and candidate-facing messages, not match-score formulas, rankings, duplicate suggestions, other candidates, or HR audit details.
- **RP-004**: Junior Staff or observer access MUST be read-only and limited to training or assigned observation contexts when present.
- **RP-005**: Candidate personal data, resumes, scores, duplicate evidence, merge notes, and audit details MUST be hidden from unauthorized roles.
- **RP-006**: Simulated AI scoring and shortlisting decisions MUST be labeled as simulated, explainable, and reviewable by HR Admins.
- **RP-007**: Automated triage MUST remain an administrative workflow action with audit evidence, not a hidden or irreversible final hiring decision.

### Key Entities *(include if feature involves data)*

- **Screening Configuration**: Per-requisition criteria defining weighted skills, active thresholds, status mappings, and configuration history.
- **Weighted Skill**: A free-text skill name entered by HR for a requisition, with a percentage weight contributing to the match score. No global skills taxonomy is required.
- **Triage Threshold**: A score band that maps APPLIED candidates to SCREENING, ASSESSMENT, INTERVIEW, or REJECTED.
- **Application Match Score**: The current simulated score (stored in `applications.match_score`) and per-skill breakdown (stored in `applications.match_score_breakdown` as JSON containing each skill's weighted contribution and missing-evidence flags) for a candidate's application against a requisition.
- **Simulated Shortlist Entry**: A ranked candidate application with score, rank, tie-break evidence, and inclusion reason for a requisition shortlist.
- **Duplicate Candidate Suggestion**: A possible duplicate pair identified within a requisition's applicant pool, stored in `candidate_merge_log` with matching evidence, a `confidence_category` (HIGH/MEDIUM/LOW), and a pending or recorded decision.
- **Candidate Merge Decision**: HR's recorded merge, ignore, or defer decision stored in `candidate_merge_log.decision_type` (MERGE/IGNORE/DEFER), including primary candidate selection where applicable, reason in `notes`, and `confidence_category`.
- **Screening Audit Entry**: Evidence of configuration changes, recalculation runs, shortlist generation, duplicate decisions, and triage status changes.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can configure screening weights and thresholds for a requisition in under 5 minutes during a demo with no assistance.
- **SC-002**: 100% of invalid screening configurations tested with bad weights, missing skills, or invalid thresholds are rejected with actionable error messages.
- **SC-003**: For a requisition with up to 100 applicants, HR can recalculate scores and view the refreshed shortlist in under 10 seconds during normal demo conditions.
- **SC-004**: 100% of triage status changes include audit evidence showing actor, timestamp, previous status, new status, score, and threshold rule.
- **SC-005**: 100% of simulated shortlist pages clearly label scoring and ranking as simulated and HR-reviewable.
- **SC-006**: HR can resolve a duplicate candidate suggestion as merge, ignore, or defer in under 2 minutes with a recorded reason.
- **SC-007**: Unauthorized roles are blocked from all HR-only screening, shortlist, duplicate, and audit actions in 100% of access-control test attempts.
- **SC-008**: At least 90% of HR reviewers in a project demo can explain why a candidate was shortlisted or triaged by reading the displayed score breakdown and audit evidence.

## Assumptions

- Candidate profile evidence is derived from existing candidate fields (`current_title`, `years_experience`, `resume_url` metadata, `location`, `phone`) and existing assessment or application data already captured in SRIM. No new candidate-facing skill input form or `candidate_skills` table is introduced; HR maps available profile evidence to requisition skills manually during scoring configuration.
- Screening thresholds default to HR-defined per-requisition values; no global default automatically changes application statuses without HR running triage.
- Recalculation and triage are HR-initiated form actions, not scheduled automation.
- Duplicate detection produces suggestions for HR review; it does not automatically merge candidates.
- Merge decisions preserve audit history and traceability even if future retention workflows anonymize or remove candidate personal data.
- Shortlist size is derived from score thresholds and ranking; if no explicit limit is configured later, all candidates meeting the shortlist criteria are displayed in rank order.
- Simulated AI means deterministic, explainable scoring and ranking within SRIM, with no external model, no external applicant data sharing, and no final hiring decision made solely by the system.
