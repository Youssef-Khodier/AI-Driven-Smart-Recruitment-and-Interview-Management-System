# Feature Specification: Technical Assessment Management

**Feature Branch**: `002-job-applications`  
**Created**: 2026-05-04  
**Status**: Draft  
**Input**: User description: "Build technical assessment management as server-rendered Laravel pages. HR admins can create assessments and questions for jobs. Candidates can start timed assessments, receive randomized questions, submit answers, and receive simulated scores. The system tracks focus-loss events as simulated proctoring data and expires assessments when time runs out."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.pdf` sections 1.3, 3.2, 3.4, 4, and 5.2-5.3; `Diagrams/document.md`; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 2.pdf`; `Diagrams/Acrivity Diagram/Activity 3.pdf`; `Diagrams/Acrivity Diagram/Activity 4.pdf`; `Diagrams/Acrivity Diagram/Activity 5.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Acrivity Diagram/Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio.pdf`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio.pdf`.
- **SRS / Use Case IDs**: UC-7 Proctored Environment Controller, UC-8 Randomized Question-Bank Generator, UC-9 Code-Execution Output Validator as simulated scoring context only, UC-10 Plagiarism Detection Logic as later-scope integrity context only, UC-12 Assessment Cool-down Manager as later-scope context only, and SRS 5.2 Timed-Session Heartbeat.
- **Baseline Entities**: `job_requisitions`, `applications`, `assessments`, `questions`, `candidate_assessments`, `submissions`, `users`, and `candidates`; an assessment integrity event record is required to preserve focus-loss timestamps and review notes.
- **Baseline Workflow**: Candidate application moves from screening to technical test, the candidate completes a timed assessment, system records answers and a simulated score, and HR can review assessment outcomes before interview progression.
- **Scope Decision**: Matches baseline assessment and proctored simulation scope. Real code execution, plagiarism detection, webcam/video proctoring, dynamic difficulty adjustment, cool-down reuse, email links, and external integrations remain out of scope unless approved in a later feature.

## Laravel Delivery Constraints *(mandatory)*

- **Delivery Mode**: Laravel monolithic MVC with Blade server-rendered pages.
- **Routing**: Web routes and form submissions only; no REST API contract.
- **Data Access**: MySQL through Eloquent models and migrations.
- **Security**: Sessions, CSRF protection, server-side validation, middleware, and policies.

## Clarifications

### Session 2026-05-04

- Q: How should expired assessment attempts be scored? → A: Expired attempts receive a simulated score based only on answers saved before the deadline.
- Q: Which question types are included in V1? → A: MCQ, theory/free-text, and coding-as-text without execution.
- Q: How should assessment attempt evidence handle later HR edits? → A: Snapshot question text, choices, points, and randomized order at attempt start.
- Q: How should answers be saved before timeout? → A: Save answers continuously during the attempt before final submit.
- Q: Which applications are eligible to start an assessment? → A: Only applications in `ASSESSMENT` status are eligible.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - HR Defines Job Assessment (Priority: P1)

An HR Admin creates an assessment for a job requisition, sets the assessment duration and instructions, and adds MCQ, theory/free-text, or coding-as-text questions so candidates can be evaluated consistently for that job without real code execution.

**Why this priority**: Candidate testing cannot occur until HR has an assessment and question set tied to the relevant job.

**Independent Test**: Can be tested by signing in as an HR Admin, creating an assessment for an existing job, adding questions, and confirming the assessment is available for candidate attempts without exposing it to unauthorized users.

**Acceptance Scenarios**:

1. **Given** an HR Admin and an existing job requisition, **When** the HR Admin enters valid assessment details and questions, **Then** the assessment is saved with its questions and linked to that job.
2. **Given** an HR Admin creates a question with missing required text, invalid points, or incomplete answer choices, **When** the form is submitted, **Then** the system rejects the request and shows clear correction messages without saving invalid data.
3. **Given** a Candidate or unauthorized staff member attempts to manage assessments, **When** they access assessment management, **Then** the system denies access.

---

### User Story 2 - Candidate Completes Timed Assessment (Priority: P2)

A Candidate whose application is in `ASSESSMENT` status starts the assigned test, receives a randomized question order, works within the visible time limit, has answers saved continuously during the attempt, submits final answers, and receives a clearly labeled simulated score.

**Why this priority**: This is the core candidate-facing value of the feature and provides technical screening evidence for the recruitment pipeline.

**Independent Test**: Can be tested by preparing a job assessment, signing in as an eligible Candidate, starting the assessment, answering randomized questions, submitting before time expires, and seeing a simulated score and completion status.

**Acceptance Scenarios**:

1. **Given** a Candidate with an application in `ASSESSMENT` status and an available assessment, **When** the Candidate starts the assessment, **Then** the system creates one active attempt, records the start time, snapshots question text, choices, points, and randomized order, displays the remaining time, and presents the assessment questions in randomized order.
2. **Given** a Candidate answers questions and submits before the deadline, **When** the submission is accepted, **Then** the attempt is marked submitted, continuously saved answers are finalized, and a simulated score from 0 to 100 is shown with a label that it is simulated.
3. **Given** a Candidate already has an active or completed attempt for the same assessment, **When** they try to start another attempt, **Then** the system blocks duplicate active attempts and explains the current attempt status.

---

### User Story 3 - Assessment Expires on Timeout (Priority: P3)

A Candidate who runs out of time cannot continue editing answers. The system expires the assessment, preserves answers submitted so far, and records the outcome consistently for HR review.

**Why this priority**: Timed assessment fairness requires consistent enforcement even when the candidate delays, reloads, or leaves the page open.

**Independent Test**: Can be tested by starting a short-duration assessment, waiting until the deadline passes, attempting to submit or change answers, and confirming the attempt is expired with no further edits accepted.

**Acceptance Scenarios**:

1. **Given** an assessment attempt whose time limit has passed, **When** the Candidate reloads, navigates, or submits the page, **Then** the system marks the attempt expired and prevents further answer changes.
2. **Given** an expired assessment has saved answers, **When** HR reviews the attempt, **Then** the system shows the expired status, saved answers, elapsed timing, and a simulated score based only on answers saved before the deadline.

---

### User Story 4 - HR Reviews Simulated Proctoring Signals (Priority: P4)

An HR Admin reviews candidate assessment attempts, including score, submitted answers, timing, and focus-loss events captured as simulated proctoring data, so integrity concerns can be considered before moving candidates forward.

**Why this priority**: Proctoring data supports HR review but should not automatically make hiring decisions without human interpretation.

**Independent Test**: Can be tested by recording focus-loss events during a candidate attempt, submitting the assessment, and confirming HR can see the timestamped events while the Candidate cannot see other candidates' data.

**Acceptance Scenarios**:

1. **Given** a Candidate switches away from the assessment window during an active attempt, **When** focus is lost and then restored, **Then** the system records timestamped focus-loss events for that attempt.
2. **Given** an HR Admin views an assessment attempt, **When** focus-loss events exist, **Then** the events are displayed as simulated proctoring data and do not automatically reject the Candidate.

### Edge Cases

- Candidate opens the assessment after the job, application, or assessment has become unavailable; the system must block access with a clear message.
- Candidate opens an assessment while their application is not in `ASSESSMENT` status; the system must block access with a clear message.
- Candidate refreshes or returns to an active attempt; the system must preserve the existing attempt, continuously saved answers, and remaining time rather than creating a new attempt.
- Candidate submits after the time limit due to delay, reload, or stale page; the system must expire the attempt, reject late answer changes, and score only answers saved before the deadline.
- Assessment has too few questions to randomize meaningfully; the system must still present all available active questions and flag the limited pool to HR.
- HR removes or edits questions while candidates have attempts; existing attempts must remain reviewable using the question text, choices, points, and randomized order captured at attempt start.
- Invalid, missing, duplicated, or malformed question inputs must be rejected with field-level guidance.
- Unauthorized users must not create assessments, start attempts for another candidate, view another candidate's answers, or view another candidate's proctoring events.
- Simulated score and proctoring labels must remain visible anywhere results or integrity events are displayed.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow HR Admins to create, edit, view, and deactivate assessments linked to job requisitions.
- **FR-002**: System MUST allow HR Admins to add, edit, view, and deactivate MCQ, theory/free-text, and coding-as-text questions for an assessment, including question text, type, difficulty, point value, answer choices when applicable, and expected answer or scoring reference when applicable.
- **FR-003**: System MUST validate assessment duration, title, job association, question text, question type, point values, answer choices, and scoring references before saving changes.
- **FR-004**: System MUST prevent unauthorized roles from creating or changing assessments and questions.
- **FR-005**: System MUST allow only the authenticated Candidate who owns an application in `ASSESSMENT` status to start or resume that candidate's assigned assessment.
- **FR-006**: System MUST create one assessment attempt per Candidate and assessment unless a future approved policy explicitly permits retakes.
- **FR-007**: System MUST record attempt start time, submission time, expiry time when applicable, status, score, and relationship to the Candidate and assessment.
- **FR-008**: System MUST present assessment questions in randomized order for each attempt while preserving the exact order used for later review.
- **FR-009**: System MUST display the assessment duration and remaining time to the Candidate during an active attempt.
- **FR-010**: System MUST continuously save Candidate answers during an active attempt and associate each saved answer with the relevant attempt and question.
- **FR-011**: System MUST calculate and display a simulated assessment score from 0 to 100 for submitted attempts using the saved answers and configured question values.
- **FR-012**: System MUST clearly label assessment scores as simulated anywhere they are shown to Candidates, HR Admins, or other authorized reviewers.
- **FR-013**: System MUST expire attempts whose time limit has passed and prevent answer changes after expiry.
- **FR-014**: System MUST preserve answers saved before expiry, calculate the simulated score using only answers saved before the deadline, and show expired status distinctly from submitted status.
- **FR-015**: System MUST record focus-loss events for active assessment attempts with event time, attempt reference, and event type.
- **FR-016**: System MUST show focus-loss events to HR Admins as simulated proctoring data for review.
- **FR-017**: System MUST NOT automatically reject, advance, or hire candidates solely because of simulated score or simulated proctoring data.
- **FR-018**: System MUST provide HR Admins with a job-level assessment results view showing candidates, attempt status, simulated score, submission timing, expiry status, and focus-loss event count.
- **FR-019**: System MUST maintain assessment attempt evidence by preserving the question text, answer choices, point values, and randomized order captured at attempt start even if HR later edits or deactivates the assessment or questions.
- **FR-020**: System MUST provide clear success, error, denial, expiry, and validation messages for HR Admin and Candidate assessment workflows.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST be limited to managing assessments and questions, viewing assessment attempts, viewing simulated scores, and reviewing simulated proctoring data for recruitment purposes.
- **RP-002**: Technical Interviewer access MUST be limited to assigned candidate assessment summaries only when the interview preparation workflow later grants that access; no assessment authoring access is included in this feature.
- **RP-003**: Candidate access MUST be limited to their own assessment availability, active attempt, submitted answers, own simulated score, and own assessment status.
- **RP-004**: Junior Staff or observer access MUST remain read-only/training-only when applicable and MUST NOT influence official assessment scores.
- **RP-005**: Candidate PII, answers, scores, and simulated proctoring events MUST be hidden from unauthorized roles and from other candidates.
- **RP-006**: Simulated scoring and simulated proctoring decisions MUST be labeled as simulated and reviewable by an authorized role.

### Key Entities *(include if feature involves data)*

- **Assessment**: A job-linked test definition containing title, description or instructions, type, duration, status, and the job it evaluates.
- **Question**: A prompt within an assessment, limited in this version to MCQ, theory/free-text, or coding-as-text without execution, including difficulty, point value, answer choices when applicable, and expected answer or scoring reference when applicable.
- **Candidate Assessment Attempt**: A candidate's instance of an assessment, including start time, end or expiry time, status, simulated score, and a snapshot of question text, answer choices, point values, and randomized question order shown at start.
- **Submission Answer**: A candidate's saved answer for a specific question within an attempt.
- **Assessment Integrity Event**: A timestamped simulated proctoring event such as focus loss or focus return tied to a candidate assessment attempt.
- **Job Requisition**: The job opening that owns assessment definitions and connects assessment results to applications.
- **Application**: The candidate's application that determines assessment eligibility through `ASSESSMENT` status and links assessment evidence to the recruitment pipeline.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can create a job assessment with at least 10 questions in under 5 minutes during a guided demo.
- **SC-002**: Candidates can start, complete, submit, and view a simulated score for a 10-question assessment in one continuous session with a 95% completion rate during acceptance testing.
- **SC-003**: 100% of attempts submitted after the configured time limit are marked expired, block further answer changes, and score only answers saved before the deadline in validation testing.
- **SC-004**: 100% of simulated scores shown to users include a visible simulated label in candidate and HR review screens.
- **SC-005**: Focus-loss events triggered during active attempts appear in HR review with timestamps in at least 95% of acceptance test runs.
- **SC-006**: HR Admins can identify assessment status, simulated score, and focus-loss count for 50 candidate attempts on one job in under 30 seconds during review testing.
- **SC-007**: Unauthorized role access to assessment authoring, other candidates' attempts, answers, scores, and proctoring events is blocked in 100% of role-boundary tests.

## Assumptions

- Candidate assessment eligibility is based on an existing application reaching `ASSESSMENT` status in the recruitment pipeline.
- HR Admins are responsible for preparing sufficient questions before candidates begin attempts.
- Simulated scoring is deterministic and advisory for the academic demo; it is not a real AI grading model and coding-as-text answers are not compiled, run, or checked against hidden test cases.
- Focus-loss tracking is limited to browser focus or visibility events available during the assessment page session; webcam, microphone, screen recording, and lockdown-browser behavior are out of scope.
- The first version supports one attempt per candidate per assessment; retakes and cool-down reuse are deferred to a later feature.
- Existing authentication, role detection, and job/application workflows from earlier SRIM phases are reused.
