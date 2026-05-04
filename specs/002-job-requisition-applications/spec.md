# Feature Specification: Job Requisition and Candidate Applications

**Feature Branch**: `002-job-applications`  
**Created**: 2026-05-04  
**Status**: Draft  
**Input**: User description: "Build job requisition and candidate application management in Laravel Blade. HR admins can create, edit, submit, approve, open, and close job requisitions. Candidates can manage their profile, browse open jobs, apply once per job, and track application statuses. The system calculates a simulated match score from job requirements and candidate profile fields."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.pdf` sections 1.3, 3.2, 3.4, 4, and 5.3; `Diagrams/document.md`; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio.pdf`; `Diagrams/Object Diagram/Object Diagram.pdf` pages 2, 4, and 6; `Diagrams/System Architecture/system-architecture.drawio.pdf`.
- **SRS / Use Case IDs**: UC-1 Automated Screening Triage, UC-2 Dynamic Skill-Weighting Engine, UC-3 Job Requisition Approval Workflow, UC-4 Application Deduplication Logic as the baseline for one application per candidate per job, and UC-5 AI-Ranked Shortlisting as later-scope context only.
- **Baseline Entities**: Users, Departments, Candidates, Job Requisitions, Applications, Application Match Scores, and audit-relevant status history for job and application changes.
- **Baseline Workflow**: Candidate applies to an active requisition, the system evaluates profile and job requirements, HR reviews applications and status, and candidates track their own progress. Authentication and role permission checks follow the login/RBAC activity flow.
- **Scope Decision**: Matches the baseline recruitment pipeline scope for requisitions, applications, and simulated match scoring. External job-board synchronization, real resume parsing, assessments, interviews, offers, onboarding, email notifications, and real AI/NLP integrations are excluded from this feature phase.

## Laravel Delivery Constraints *(mandatory)*

- **Delivery Mode**: Laravel monolithic MVC with Blade server-rendered pages.
- **Routing**: Web routes and form submissions only; no REST API contract.
- **Data Access**: MySQL through Eloquent models and migrations.
- **Security**: Sessions, CSRF protection, server-side validation, middleware, and policies.

## Clarifications

### Session 2026-05-04

- Q: What format should candidates use to provide skills for simulated match scoring? -> A: Required comma-separated skills or keywords list.
- Q: Who may approve a submitted job requisition? -> A: A different active HR Admin must approve it.
- Q: Which application statuses should candidates see? -> A: Exact pipeline statuses: Applied, Screening, Assessment, Interview, Offer, Rejected, Hired.
- Q: How should the simulated match score be weighted? -> A: Skills overlap 70%, title match 15%, experience match 15%.
- Q: How should concurrent requisition edits be handled? -> A: Block save and ask HR Admin to reload if the requisition changed.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Manage Job Requisition Lifecycle (Priority: P1)

An HR Admin creates a job requisition, completes required job details, submits it for approval, approves it, opens it for candidates, and closes it when hiring is no longer accepting applications.

**Why this priority**: Open jobs are the starting point for candidate applications and match scoring. Without this lifecycle, candidates have no approved roles to browse or apply to.

**Independent Test**: Can be fully tested by signing in as an HR Admin, creating one requisition, moving it through every allowed status, and confirming candidate visibility changes only when the requisition is open.

**Acceptance Scenarios**:

1. **Given** an active HR Admin and valid department details, **When** the HR Admin creates a draft requisition with title, description, requirements, and department, **Then** the requisition is saved as Draft and is not visible to candidates.
2. **Given** a complete Draft requisition, **When** the HR Admin submits it, **Then** its status changes to Pending Approval and the change is recorded for review.
3. **Given** a Pending Approval requisition, **When** a different active HR Admin approves and opens it, **Then** candidates can find it in the open jobs list and view its details.
4. **Given** an Open requisition, **When** an HR Admin closes it, **Then** it is removed from candidate browsing and no new applications are accepted while existing applications remain visible to authorized users.

---

### User Story 2 - Candidate Profile, Job Browsing, and Application (Priority: P2)

A candidate maintains a professional profile, browses open jobs, reviews requirements, and applies once to a suitable job. The candidate receives an application record with an initial simulated match score.

**Why this priority**: Candidate application submission is the core recruitment action and provides the data HR uses for screening.

**Independent Test**: Can be fully tested by signing in as a candidate, completing profile fields, browsing an open job, applying once, and confirming duplicate applications are blocked.

**Acceptance Scenarios**:

1. **Given** a candidate with an incomplete profile, **When** they save valid profile information including current title, years of experience, location, resume reference, and a required comma-separated skills or keywords list, **Then** the profile is updated and available for future applications.
2. **Given** at least one Open requisition, **When** a candidate browses jobs, **Then** only Open requisitions are shown and each job displays enough requirements for the candidate to decide whether to apply.
3. **Given** a candidate with a complete profile and an Open requisition, **When** the candidate applies, **Then** one application is created with Applied status, application time, and a simulated match score from 0 to 100.
4. **Given** a candidate has already applied to a job, **When** they attempt to apply to the same job again, **Then** the system blocks the duplicate attempt and shows the existing application status instead.

---

### User Story 3 - Manage Applications and Track Status (Priority: P3)

An HR Admin reviews candidates who applied to each requisition, compares simulated match scores, and updates application statuses. Candidates view the current status of their own applications without seeing other candidates' data.

**Why this priority**: Application management turns submitted applications into an actionable hiring pipeline and keeps candidates informed.

**Independent Test**: Can be fully tested by creating applications for one open job, signing in as HR Admin to view and update application statuses, and signing in as each candidate to confirm only their own status is visible.

**Acceptance Scenarios**:

1. **Given** applications exist for a requisition, **When** an HR Admin opens the applicant list, **Then** each application shows candidate summary information, current status, applied date, and simulated match score.
2. **Given** an application in Applied status, **When** an HR Admin changes it to Screening, Assessment, Interview, Offer, Rejected, or Hired, **Then** the new status is saved and the prior status is retained for review.
3. **Given** a candidate has one or more applications, **When** they open their application tracking page, **Then** they see only their own jobs, exact pipeline statuses, applied dates, and score labels.
4. **Given** a user without HR Admin permission attempts to view another candidate's application, **When** they request that page or submit that action, **Then** access is denied and no candidate data is disclosed.

---

### Edge Cases

- If a requisition is missing required title, department, description, or requirements, it cannot be submitted, approved, or opened.
- If the HR Admin who created a requisition attempts to approve the same requisition, approval is denied and another active HR Admin must review it.
- If a Pending Approval or Approved requisition needs material changes, it must return to Draft or be replaced before opening so candidates do not apply to unstable requirements.
- If an HR Admin tries to save requisition edits after another change was saved, the save is blocked and the HR Admin is asked to reload before editing again.
- If a candidate opens a job page that was closed after they loaded the listing, the application attempt is rejected with a clear message.
- If a candidate profile lacks required scoring fields, the candidate is prompted to complete the profile before applying.
- If match score inputs are incomplete or do not overlap, the application is still saved only when minimum profile requirements are met, and the score can be zero with a simulated-score explanation.
- If an inactive user or wrong-role user attempts HR or candidate actions, the action is denied without changing data.
- If an HR Admin closes a requisition with existing applications, application history and candidate tracking remain available.
- If two application submissions are attempted for the same candidate and job, only one application is accepted.
- Candidate personal details, resume references, match scores, and application statuses are hidden from unauthorized users.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow active HR Admins to view job requisitions grouped or filtered by Draft, Pending Approval, Approved, Open, and Closed status.
- **FR-002**: System MUST allow active HR Admins to create Draft job requisitions with title, department, description, requirements, and optional notes that support later screening.
- **FR-003**: System MUST validate requisition title, department, description, and requirements before a requisition can move beyond Draft.
- **FR-004**: System MUST allow active HR Admins to edit Draft requisitions and must prevent candidate visibility while a requisition remains Draft.
- **FR-005**: System MUST allow an active HR Admin to submit a complete Draft requisition for approval, changing its status to Pending Approval.
- **FR-006**: System MUST allow a different active HR Admin to approve a Pending Approval requisition, changing its status to Approved, and MUST prevent the creating HR Admin from approving their own requisition.
- **FR-007**: System MUST allow an active HR Admin to open an Approved requisition, making it visible and available for candidate applications.
- **FR-008**: System MUST allow an active HR Admin to close an Approved or Open requisition, preventing new candidate applications while preserving existing application records.
- **FR-009**: System MUST keep a review trail for requisition status changes, including actor, timestamp, previous status, and new status.
- **FR-010**: System MUST allow candidates to view and update their own professional profile fields needed for applications and scoring, including current title, years of experience, location, resume reference, and a required comma-separated skills or keywords list.
- **FR-011**: System MUST validate candidate profile fields before saving and before allowing an application that depends on those fields, including requiring at least one skill or keyword in the comma-separated list.
- **FR-012**: System MUST show candidates only Open requisitions in browse and search results.
- **FR-013**: System MUST allow candidates to view details for Open requisitions, including title, department, description, requirements, and current availability.
- **FR-014**: System MUST allow a candidate with a complete profile to apply to an Open requisition.
- **FR-015**: System MUST prevent more than one active application by the same candidate for the same requisition.
- **FR-016**: System MUST create each accepted application with Applied status, applied date, linked job, linked candidate, and a simulated match score.
- **FR-017**: System MUST calculate the simulated match score as a 0 to 100 advisory value based on job requirements compared with candidate profile fields, weighted as skills or keyword overlap 70%, current-title match 15%, and years-of-experience match 15%.
- **FR-018**: System MUST label match scores as simulated and advisory, and MUST NOT automatically reject, advance, or hire candidates based only on the score in this feature.
- **FR-019**: System MUST store the match score calculated at the time of application so later profile edits do not silently change historical application results.
- **FR-020**: System MUST allow HR Admins to view applications for each requisition with candidate summary, application status, applied date, and simulated match score.
- **FR-021**: System MUST allow HR Admins to update application status to Applied, Screening, Assessment, Interview, Offer, Rejected, or Hired, subject to role permission and valid application ownership.
- **FR-022**: System MUST keep a review trail for application status changes, including actor, timestamp, previous status, new status, and optional reason.
- **FR-023**: System MUST allow candidates to view only their own applications, including job title, current exact pipeline status, applied date, and simulated score label. Candidate-visible statuses MUST use Applied, Screening, Assessment, Interview, Offer, Rejected, and Hired.
- **FR-024**: System MUST prevent candidates from viewing other candidates' profiles, applications, match scores, or application statuses.
- **FR-025**: System MUST show clear validation and conflict messages for duplicate applications, closed jobs, incomplete profiles, invalid status changes, and unauthorized actions.
- **FR-026**: System MUST prevent stale requisition edit overwrites by blocking an HR Admin's save when the requisition changed after they loaded it, and MUST instruct the HR Admin to reload before editing again.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST be limited to managing requisitions, reviewing applications, viewing candidate summaries tied to applications, viewing simulated match scores, and updating application statuses for recruitment purposes.
- **RP-002**: Technical Interviewer access is outside this feature and MUST NOT include unassigned candidate applications or requisition administration through this feature.
- **RP-003**: Candidate access MUST be limited to their own profile, open job browsing, their own applications, their own exact pipeline status history, and their own simulated score labels.
- **RP-004**: Junior Staff or observer access is outside this feature and MUST NOT expose candidate application data unless a later reviewed feature grants training-only visibility.
- **RP-005**: Candidate PII, resume references, match scores, and application statuses MUST be hidden from unauthorized roles and from other candidates.
- **RP-006**: Simulated match scores MUST be labeled as simulated, reviewable by HR Admins, and not represented as final hiring decisions.
- **RP-007**: Status changes and score-bearing application records MUST remain traceable for audit and fairness review.

### Key Entities *(include if feature involves data)*

- **Job Requisition**: A hiring request with title, department, description, requirements, lifecycle status, creator, and dates. It becomes candidate-visible only when Open.
- **Candidate Profile**: A candidate-owned professional record containing contact-related profile details and scoring fields such as current title, years of experience, location, resume reference, and a required comma-separated skills or keywords list.
- **Application**: A candidate's single submission for a specific job requisition, with status, applied date, match score, and links to the candidate and requisition.
- **Simulated Match Score**: An advisory score from 0 to 100 comparing job requirements with candidate profile information, weighted as skills or keyword overlap 70%, current-title match 15%, and years-of-experience match 15%; it supports HR review but does not make automatic hiring decisions.
- **Department**: The organization unit associated with a job requisition and optionally with HR users.
- **Status History / Audit Record**: A review record for requisition and application status changes, including actor, timestamp, old value, new value, and optional reason.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can create, submit, approve, open, and close a complete job requisition in under 3 minutes during a guided acceptance test.
- **SC-002**: Candidates can update their profile, find an open job, and submit one application in under 4 minutes during a guided acceptance test.
- **SC-003**: 100% of duplicate application attempts for the same candidate and job are blocked in acceptance testing.
- **SC-004**: 100% of closed, draft, pending, or approved-but-not-open requisitions are unavailable for new candidate applications in acceptance testing.
- **SC-005**: At least 95% of applications with complete candidate profile fields receive a visible simulated match score within 3 seconds of successful submission during demo testing.
- **SC-006**: HR Admins can identify the highest-scoring applicants for a requisition containing 100 applications in under 10 seconds using the applicant list.
- **SC-007**: 100% of tested unauthorized attempts to access another candidate's profile, application, status, or score are denied without data disclosure.
- **SC-008**: At least 90% of demo users can correctly explain that the match score is simulated and advisory after seeing the application or HR applicant list screens.

## Assumptions

- Existing authentication, active/inactive account handling, and HR Admin/Candidate role access from the RBAC foundation are available.
- HR Admins are the approving authority for this academic phase; a different active HR Admin must approve a submitted requisition, while separate department-head or finance approval chains are deferred.
- Candidate skills or keywords are entered manually as a comma-separated profile list for this phase; real resume parsing and external AI/NLP extraction are not included.
- Match score calculation is simulated, deterministic enough for testing, uses skills overlap 70%, title match 15%, and experience match 15%, and is advisory only.
- Candidate applications are not withdrawn or deleted in this feature; retention and erasure workflows are handled by a later privacy feature unless already provided by the foundation.
- Profile edits after an application do not silently recalculate that application's historical score.
- External job-board publishing, email notifications, assessments, interviews, offers, onboarding, and analytics are out of scope except where application statuses reference later pipeline stages.
