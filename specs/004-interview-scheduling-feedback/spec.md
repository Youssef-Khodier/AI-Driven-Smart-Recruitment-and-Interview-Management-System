# Feature Specification: Interview Scheduling Feedback

**Feature Branch**: `004-interview-scheduling-feedback`  
**Created**: 2026-05-04  
**Status**: Draft  
**Input**: User description: "Build interview scheduling and feedback workflows in Vanilla PHP. HR admins can schedule interviews for applications, assign interviewers and observers, and avoid obvious scheduling conflicts. Interviewers can view assigned interviews, see candidate and assessment briefing details, and submit feedback scores and comments using server-rendered forms."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.pdf` sections 1.2, 1.3, 3.2, 3.4, 4, and 5.2-5.3; `Diagrams/document.md`; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 2.pdf`; `Diagrams/Acrivity Diagram/Activity 3.pdf`; `Diagrams/Acrivity Diagram/Activity 4.pdf`; `Diagrams/Acrivity Diagram/Activity 5.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Acrivity Diagram/Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`; `.specify/memory/constitution.md`; `specs/003-technical-assessment-management/plan.md`.
- **SRS / Use Case IDs**: UC-13 Interviewer Availability Conflict Resolver, UC-14 Multi-Representative Panel Builder, UC-15 Automated Interview Briefing Generator, UC-17 Interviewer Shadowing Logic, UC-19 Interviewer Load Balancer as workload context only, UC-20 Multi-Dimensional Feedback Aggregator, UC-23 Consensus Meeting Automator as later reminder context only, UC-25 Hiring Recommendation State-Machine as downstream context only.
- **Baseline Entities**: `applications`, `users`, `candidates`, `job_requisitions`, `assessments`, `candidate_assessments`, `submissions`, `interviews`, `interviewers_assignment`, `interview_feedback`, `final_evaluations`, and `notifications` for later reminder context.
- **Baseline Workflow**: Candidate applies, completes assessment, passes to interview, HR schedules the interview, assigned interviewers review candidate and assessment context, interviewers submit feedback, and HR uses submitted feedback for later evaluation decisions.
- **Scope Decision**: Matches baseline with a Vanilla PHP monolith delivery constraint. External calendar booking, email dispatch, live coding synchronization, automatic workload balancing, score normalization, debrief meeting automation, and final hiring recommendation changes are out of scope for this feature unless a later specification adds them.

## Clarifications

### Session 2026-05-04

- Q: Which application status makes an application eligible for interview scheduling? → A: Only applications with status `INTERVIEW` can be scheduled.
- Q: How should the system handle overlapping interview conflicts? → A: Block overlapping interviews; HR must choose another slot.
- Q: When can official interviewers submit official feedback? → A: Official feedback allowed only after interview status is `COMPLETED`.
- Q: How should rescheduling behave after official feedback exists? → A: Block rescheduling after any official feedback exists.
- Q: What audit details are required for schedule and feedback traceability? → A: Actor, action, timestamp, changed fields.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP templates.
- **Routing**: Browser routes and form submissions only; no machine-facing service contract or separated frontend.
- **Data Access**: MySQL through PDO-backed models or repositories and plain SQL schema files.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and authorization policies.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Schedule Interview Panel (Priority: P1)

An HR Admin schedules an interview for an application that has reached the interview stage, selects the interview type, date, time, duration, assigns at least one official interviewer, and may add observers for shadowing.

**Why this priority**: Scheduling is the entry point for the live interview phase and enables all later interviewer briefing and feedback work.

**Independent Test**: Can be fully tested by creating an interview for an eligible application, assigning an interviewer and an observer, and verifying that the schedule is visible to HR and assigned staff.

**Acceptance Scenarios**:

1. **Given** an eligible application and active interviewer accounts, **When** an HR Admin submits a valid schedule with one official interviewer, **Then** the system records the scheduled interview and panel assignments.
2. **Given** an HR Admin selects an observer for an interview, **When** the schedule is saved, **Then** the observer is attached as read-only shadowing staff and is not counted as an official scorer.
3. **Given** a selected interviewer already has a scheduled interview that overlaps the requested time window, **When** HR attempts to save the new schedule, **Then** the system blocks the save and requires HR to choose a non-overlapping slot.

---

### User Story 2 - View Assigned Interview Briefing (Priority: P2)

An assigned interviewer opens their interview dashboard, views upcoming and past assigned interviews, and opens a briefing that includes the candidate, job, application, and available assessment summary needed to prepare for the interview.

**Why this priority**: Interviewers need timely, role-limited context before conducting an interview.

**Independent Test**: Can be tested by logging in as an assigned interviewer and confirming only assigned interviews and relevant briefing details are shown.

**Acceptance Scenarios**:

1. **Given** an interviewer is assigned to one upcoming interview, **When** they open their assigned interview list, **Then** they see that interview with candidate name, job title, schedule, duration, and feedback status.
2. **Given** assessment results exist for the application's job and candidate, **When** the interviewer opens the briefing, **Then** the available assessment title, status, score, and submitted answer summary are visible as preparation context.
3. **Given** another interviewer is not assigned to the interview, **When** they try to access the briefing, **Then** access is denied.

---

### User Story 3 - Submit Interview Feedback (Priority: P3)

An official interviewer submits structured scores and comments after the interview is marked completed, and the system marks the interviewer's feedback as received for HR follow-up.

**Why this priority**: Structured feedback turns interviews into comparable hiring evidence and supports later final evaluation decisions.

**Independent Test**: Can be tested by submitting scores and comments as an assigned official interviewer and verifying HR can see the submitted feedback.

**Acceptance Scenarios**:

1. **Given** an assigned official interviewer has a completed interview awaiting feedback, **When** they submit valid scores and comments, **Then** the feedback is saved and attributed to that interviewer.
2. **Given** a required score is missing or outside the allowed scale, **When** the interviewer submits the form, **Then** the system rejects the submission with field-level errors and preserves entered values for correction.
3. **Given** an official interviewer already submitted feedback for an interview, **When** they submit again, **Then** the system prevents duplicate official feedback unless the feature explicitly allows an HR-controlled revision in a later scope.

---

### User Story 4 - Observe Without Official Scoring (Priority: P4)

A Junior Staff observer or shadowing interviewer can view assigned interview details for learning purposes, but cannot submit official feedback or affect the candidate's score.

**Why this priority**: Observer access supports baseline shadowing while protecting evaluation integrity.

**Independent Test**: Can be tested by assigning an observer, confirming they can view the schedule and briefing, and confirming official feedback submission is unavailable or denied.

**Acceptance Scenarios**:

1. **Given** a user is assigned as an observer, **When** they open assigned interviews, **Then** they can view the schedule and briefing marked as observer access.
2. **Given** an observer attempts to submit official feedback, **When** the submission is attempted, **Then** the system denies it and records no official score.

---

### Edge Cases

- HR schedules an interview for an application whose status is not `INTERVIEW`.
- HR selects no official interviewer, selects only observers, or selects the same staff member twice.
- HR enters a past date, a zero or negative duration, or a malformed date/time.
- HR schedules overlapping interviews for the same application or same assigned staff member.
- HR cancels or reschedules an interview after some panel members have already viewed it or after official feedback exists.
- Interview briefing data is incomplete because the candidate has no resume link or no completed assessment attempt.
- An inactive user account is selected as an interviewer or observer.
- An unauthenticated user, candidate, unassigned interviewer, or observer attempts to access official feedback submission.
- An official interviewer submits invalid, missing, duplicated, or expired form input.
- Candidate personal data, assessment answers, scores, and feedback must remain hidden from unauthorized roles.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow HR Admins to create a scheduled interview only for an existing application with status `INTERVIEW`.
- **FR-002**: System MUST require each scheduled interview to include an application, interview type, scheduled date/time, duration, status, and at least one official interviewer.
- **FR-003**: System MUST allow HR Admins to assign panel members as official interviewers, panel leads, or observers.
- **FR-004**: System MUST prevent duplicate panel assignments for the same interview and same user.
- **FR-005**: System MUST block specified scheduling conflicts before saving a scheduled interview, including overlapping non-cancelled interviews for the same application or any selected official interviewer or observer.
- **FR-006**: System MUST validate scheduling input server-side, including application status `INTERVIEW`, active staff accounts, future schedule time, positive duration, and allowed panel roles.
- **FR-007**: System MUST allow HR Admins to cancel or reschedule a scheduled interview while preserving an auditable change history that records actor, action, timestamp, and changed fields, but MUST block rescheduling after any official feedback exists for that interview.
- **FR-008**: System MUST show HR Admins each interview's schedule, panel assignments, application, candidate, job, current status, and feedback completion state.
- **FR-009**: System MUST allow assigned official interviewers and observers to view only their assigned interview list and briefing pages.
- **FR-010**: System MUST show assigned official interviewers and observers the briefing details needed for preparation, including candidate summary, job requirements, application status, available assessment title, assessment status, assessment score, and submitted answer summary when available.
- **FR-011**: System MUST clearly indicate when briefing details are incomplete and identify which expected data is missing.
- **FR-012**: System MUST allow assigned official interviewers to submit one official feedback record per interview only after the interview status is `COMPLETED`.
- **FR-013**: System MUST collect structured feedback scores for technical, communication, culture fit, and overall dimensions, plus free-text comments.
- **FR-014**: System MUST validate feedback input server-side, including score range, required fields, assignment eligibility, interview status `COMPLETED`, and duplicate submission prevention.
- **FR-015**: System MUST prevent observers from submitting official feedback or influencing official feedback completion.
- **FR-016**: System MUST show HR Admins submitted feedback by interview and interviewer, including score dimensions, comments, and submission time.
- **FR-017**: System MUST mark feedback completion state so HR can distinguish interviews with all official feedback submitted from interviews still missing feedback.
- **FR-018**: System MUST keep existing downstream final evaluation decisions separate; this feature provides interview and feedback evidence but does not automatically create offers or final recommendations.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST be limited to scheduling, rescheduling, cancelling, assigning panels, viewing interview evidence, and viewing feedback for applications within the recruitment workflow.
- **RP-002**: Technical Interviewer access MUST be limited to interviews where they are assigned as official interviewer, panel lead, or observer.
- **RP-003**: Candidate access MUST NOT include interviewer-only briefing packs, panel assignment internals, or submitted interviewer feedback in this feature.
- **RP-004**: Junior Staff or observer access MUST be read-only and training-only for assigned interviews, with no official scoring effect.
- **RP-005**: Candidate PII, resumes, assessment scores, submitted answers, interview notes, and feedback comments MUST be hidden from unauthorized roles.
- **RP-006**: Feedback author, schedule changes, cancellation, and rescheduling actions MUST remain traceable with actor, action, timestamp, and changed fields for HR review.

### Key Entities *(include if feature involves data)*

- **Application**: Candidate's application for a job; determines whether interview scheduling is allowed and links candidate, job, assessment evidence, interview records, and downstream evaluation.
- **Interview**: Scheduled live evaluation session for an application; includes type, scheduled date/time, duration, status, and completion state.
- **Interviewer Assignment**: Panel membership for an interview; identifies each assigned staff member and whether they are an official interviewer, panel lead, or observer.
- **Interview Feedback**: Official structured evaluation submitted by an assigned interviewer; includes score dimensions, comments, author, and submitted time.
- **Interview Audit Record**: Traceability record for scheduling, cancellation, rescheduling, and feedback actions; includes actor, action, timestamp, and changed fields.
- **Candidate**: Candidate profile used in briefing views, including only data required for assigned interview preparation.
- **Assessment Evidence**: Existing assessment attempt and submission summary used to brief interviewers before the interview.
- **Job Requisition**: Job context and requirements associated with the application and interview.
- **User**: Authenticated HR Admin, Technical Interviewer, Candidate, or observer-capable staff account governed by role and assignment permissions.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can schedule a valid interview with one interviewer and one observer in under 3 minutes during acceptance testing.
- **SC-002**: 100% of tested overlapping schedule cases for the same application or assigned staff member are blocked before the interview is saved.
- **SC-003**: Assigned interviewers can find and open their next interview briefing in under 2 minutes without HR assistance.
- **SC-004**: Interviewers can submit complete structured feedback in under 3 minutes, and HR can see the submitted feedback immediately after submission.
- **SC-005**: 100% of tested unauthorized access attempts by candidates, unassigned interviewers, inactive users, and observers submitting official feedback are denied.
- **SC-006**: At least 90% of demo reviewers can identify whether an interview is missing feedback without asking the implementation team for explanation.

## Assumptions

- Applications are interview-ready only when their status is `INTERVIEW`.
- Scheduling conflicts are limited to overlapping non-cancelled interviews already stored in SRIM; external calendar availability and time-zone negotiation are out of scope for this feature.
- Email invitations, calendar invites, video links, reminders, automated debrief meetings, and load-balanced auto-assignment are deferred to later features or simulated demo data.
- Assessment briefing uses existing assessment and submission records from the prior assessment management feature; no new assessment scoring behavior is introduced here.
- Observers can view assigned interview context for training, but official feedback is restricted to assigned official interviewers and panel leads.
- Feedback revisions are out of scope for this feature; if a correction is needed, HR handles it through a later controlled revision workflow.
