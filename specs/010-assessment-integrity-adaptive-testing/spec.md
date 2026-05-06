# Feature Specification: Advanced Assessment Integrity and Adaptive Testing

**Feature Branch**: `010-assessment-integrity-adaptive-testing`  
**Created**: 2026-05-06  
**Status**: Draft  
**Input**: User description: "Complete advanced assessment integrity and adaptive testing in server-rendered Vanilla PHP. HR admins can define question-bank rules by difficulty tier and question count. Candidates receive randomized tests according to those rules, a browser timer heartbeat saves remaining time and expires attempts, simulated code-output validation compares answers against hidden expected output records, simulated plagiarism detection compares submissions against common-answer records, dynamic difficulty suggestions are calculated from previous scores, and cooldown rules prevent retakes for a configured period such as six months. Keep code execution and plagiarism detection simulated and stored locally."

## Baseline Scope Alignment *(mandatory)*

- **Source Materials Reviewed**: `Diagrams/SRS/SRS-SRIM final ver1.docx` section 4 use cases UC-7 through UC-12; `Diagrams/document.md` design functions #8 through #14; `Diagrams/Database/README.md`; `Diagrams/Database/schema.sql`; `Diagrams/Database/schema-erd.svg`; `Diagrams/Use-case Diagram/Usecase.pdf`; `Diagrams/Acrivity Diagram/Activity 1.pdf`; `Diagrams/Acrivity Diagram/Activity 2.pdf`; `Diagrams/Acrivity Diagram/Activity 3.pdf`; `Diagrams/Acrivity Diagram/Activity 4.pdf`; `Diagrams/Acrivity Diagram/Activity 5.pdf`; `Diagrams/Acrivity Diagram/Activity 6.pdf`; `Diagrams/Acrivity Diagram/Activity 7.pdf`; `Diagrams/Class Diagram/Class Diagram.drawio`; `Diagrams/Object Diagram/Object Diagram.pdf`; `Diagrams/System Architecture/system-architecture.drawio`; `.specify/memory/constitution.md`; `specs/009-requisition-governance/plan.md`.
- **SRS / Use Case IDs**: UC-7 Proctored Environment Controller, UC-8 Randomized Question-Bank Generator, UC-9 Code-Execution Output Validator, UC-10 Plagiarism Detection Logic, UC-11 Dynamic Difficulty Adjustment, UC-12 Assessment Cool-down Manager; design functions #8 Proctored Environment Controller, #9 Randomized Question-Bank Generator, #10 Timed-Session Heartbeat, #11 Code-Execution Output Validator (Simulated), #12 Plagiarism Detection Logic (Simulated), #13 Dynamic Difficulty Adjustment, #14 Assessment Cool-down Manager.
- **Baseline Entities**: `users`, `candidates`, `applications`, `assessments`, `questions`, `candidate_assessments`, `submissions`, plus local records needed for assessment rule definitions, hidden expected outputs, common answers, attempt question snapshots, timer state, simulated validation results, simulated plagiarism results, and cooldown decisions.
- **Baseline Workflow**: Candidate applies, reaches the technical assessment stage, receives a generated assessment, completes or expires the timed attempt, receives assessment results, and proceeds or is rejected according to the recruitment lifecycle. HR Admin manages the assessment setup and reviews assessment integrity outcomes. Technical Interviewer may use test scores in interviewer packs or later evaluation workflows but does not administer candidate attempts in this feature.
- **Scope Decision**: Matches the approved assessment and proctoring baseline with one explicit constraint: SRS UC-9 describes code compilation and execution, but this feature keeps code-output validation simulated by comparing submitted answers or outputs against locally stored expected-output records. This follows the design-function label, constitution guidance, and user instruction to keep code execution and plagiarism detection simulated and stored locally.

## Clarifications

### Session 2026-05-06

- Q: What happens when the question bank cannot satisfy configured difficulty counts at candidate start? → A: Block attempt and alert HR.
- Q: How should stale heartbeat or lost connectivity affect timer expiry? → A: Server deadline remains authoritative.
- Q: Should changes to hidden expected outputs or common answers re-score completed attempts? → A: No automatic re-scoring.
- Q: What score bands and sample size drive adaptive difficulty suggestions? → A: Five attempts; <=50 easier, >=80 harder.
- Q: What plagiarism similarity threshold should create a review flag? → A: >=80% flags HR review only.

## Vanilla PHP Delivery Constraints *(mandatory)*

- **Delivery Mode**: Framework-free Vanilla PHP monolithic MVC with server-rendered PHP pages.
- **Routing**: Browser page routes and form submissions only; no REST API contract or separated frontend application.
- **Data Access**: MySQL through PDO and plain SQL schema files.
- **Security**: Native sessions, CSRF protection, server-side validation, middleware-style guards, and authorization policies.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - HR Defines Assessment Rules (Priority: P1)

An HR Admin defines how many questions each assessment should include from each difficulty tier so candidates receive balanced, role-appropriate tests.

**Why this priority**: Randomized assessment generation cannot be fair or controlled until HR can define the question distribution rules.

**Independent Test**: Can be fully tested by creating or editing an assessment with easy, medium, and hard question counts, then confirming the saved assessment shows those rules and rejects invalid counts.

**Acceptance Scenarios**:

1. **Given** an HR Admin has permission to manage assessments, **When** they save a rule with valid difficulty tiers and non-negative question counts, **Then** the assessment records the rule and displays the configured tier counts to HR.
2. **Given** an HR Admin enters a negative count, missing tier, or total count of zero, **When** they submit the rule, **Then** the system rejects the change with a clear validation message and keeps the previous valid configuration.
3. **Given** an assessment has more requested questions in a tier than available eligible questions, **When** HR reviews the assessment setup, **Then** the system warns that the bank is insufficient before candidates are assigned the test.
4. **Given** the question bank cannot satisfy the configured difficulty counts, **When** a candidate tries to start the assessment, **Then** the system blocks the attempt and alerts HR to add or adjust questions.

---

### User Story 2 - Candidate Receives Randomized Timed Test (Priority: P1)

A candidate starts an assigned technical assessment and receives a randomized set of questions that follows the HR-defined difficulty rules while a browser timer continuously saves remaining time.

**Why this priority**: This is the primary candidate-facing assessment flow and protects test integrity through randomness and time enforcement.

**Independent Test**: Can be fully tested by assigning an eligible candidate to an assessment with configured rules, starting the attempt, verifying the generated question mix, saving heartbeat updates, and expiring the attempt when time runs out.

**Acceptance Scenarios**:

1. **Given** a candidate is eligible to start an assessment with configured difficulty counts, **When** they begin the attempt, **Then** the test instance contains a randomized question set matching the configured counts where enough eligible questions exist.
2. **Given** a candidate is taking an in-progress attempt, **When** the browser timer reports remaining time, **Then** the system saves the remaining time and keeps the attempt available from the last saved state.
3. **Given** the attempt timer reaches zero or the allowed duration has elapsed, **When** the candidate or HR views the attempt, **Then** the attempt is marked expired and only answers saved before expiry are considered.
4. **Given** a candidate loses connectivity or heartbeat updates stop, **When** the server-side deadline passes, **Then** the attempt expires without extending the timer.

---

### User Story 3 - Simulated Integrity Checks Are Recorded (Priority: P2)

Candidate submissions are checked against locally stored hidden expected outputs and common-answer records so HR can review simulated output correctness and plagiarism similarity without running candidate code or sending content to external services.

**Why this priority**: Integrity scoring adds value after the core randomized timed attempt works and must remain transparent as a simulation.

**Independent Test**: Can be fully tested by preparing a coding question with hidden expected output records and common-answer records, submitting candidate answers, and reviewing the recorded simulated match and similarity results.

**Acceptance Scenarios**:

1. **Given** a coding question has hidden expected-output records, **When** a candidate submits an answer or stated output, **Then** the system compares it with the hidden records and stores a simulated output validation result without executing code.
2. **Given** common-answer records exist for a question, **When** a candidate submission is scored, **Then** the system stores a simulated plagiarism similarity result based only on local records.
3. **Given** HR reviews a submitted attempt, **When** simulated output or plagiarism results are displayed, **Then** the results are clearly labeled as simulated and available for human review rather than treated as automatic misconduct proof.
4. **Given** HR changes hidden expected-output records or common-answer records after an attempt has been scored, **When** the completed attempt is viewed again, **Then** the recorded simulated results remain unchanged unless HR explicitly initiates a future re-scoring workflow outside this feature.
5. **Given** a submission reaches at least 80% similarity to a common-answer record, **When** HR reviews the attempt, **Then** the submission is flagged for HR review only and is not automatically rejected.

---

### User Story 4 - HR Receives Adaptive Difficulty Suggestions (Priority: P3)

HR receives difficulty-mix suggestions based on previous candidate scores so future tests can be adjusted toward easier, harder, or balanced question distributions.

**Why this priority**: Adaptive suggestions improve assessment quality but depend on completed attempts and score history.

**Independent Test**: Can be fully tested by creating prior completed attempts with low, medium, and high score patterns, then confirming HR sees the expected suggestion and supporting score summary.

**Acceptance Scenarios**:

1. **Given** at least five completed attempts exist for an assessment, **When** HR views the assessment, **Then** the system recommends easier questions when the average score is 50% or lower, harder questions when the average score is 80% or higher, and no change otherwise.
2. **Given** too few completed attempts exist to form a reliable pattern, **When** HR views the assessment, **Then** the system states that no reliable suggestion is available yet.
3. **Given** HR changes question-bank rules after seeing a suggestion, **When** future candidates start attempts, **Then** only future attempts use the updated rule snapshot and previous attempts remain unchanged.

---

### User Story 5 - Candidate Retakes Respect Cooldown (Priority: P3)

Candidates are prevented from retaking the same configured assessment during the HR-defined cooldown period, such as six months, to reduce repeated guessing and preserve test fairness.

**Why this priority**: Cooldown strengthens integrity after the main attempt lifecycle exists and must be clear to candidates.

**Independent Test**: Can be fully tested by completing or expiring an attempt, configuring a cooldown period, and confirming the candidate is blocked until the next eligible date.

**Acceptance Scenarios**:

1. **Given** a candidate has completed or expired an assessment attempt inside the configured cooldown period, **When** they try to start the same assessment again, **Then** the system blocks the retake and shows the next eligible date.
2. **Given** the configured cooldown period has elapsed, **When** the candidate is assigned the assessment again, **Then** the candidate can start a new randomized attempt.
3. **Given** HR changes the cooldown period, **When** eligibility is checked, **Then** the current configured cooldown is used consistently for future retake decisions.

### Edge Cases

- Candidate loses network connectivity during an attempt and the latest heartbeat is older than expected.
- Candidate heartbeat stops, but the server deadline still determines expiry and no extra time is granted automatically.
- Candidate submits answers at the same time the timer expires.
- HR attempts to reduce question rules below the number needed for an already active attempt.
- The question bank lacks enough eligible questions in one or more configured difficulty tiers.
- The question bank is insufficient at attempt start, causing the candidate start attempt to be blocked while HR is alerted.
- Hidden expected-output records or common-answer records are missing for a coding question.
- A candidate attempts to access another candidate's attempt or result.
- HR or another role attempts to view hidden expected outputs from a candidate-facing page.
- A completed attempt is viewed after common-answer or expected-output records change; recorded simulated results remain unchanged.
- A retake is attempted for a different job application that uses the same assessment during the cooldown period.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow authorized HR Admins to define assessment question-bank rules by difficulty tier and question count.
- **FR-002**: System MUST validate rule inputs so difficulty tiers are recognized, counts are whole numbers, total requested questions are greater than zero, and invalid configurations are not saved.
- **FR-003**: System MUST warn HR when an assessment's question bank does not contain enough eligible questions to satisfy the configured difficulty counts.
- **FR-004**: System MUST create a candidate-specific question snapshot when an eligible candidate starts an assessment.
- **FR-005**: System MUST randomize each candidate's question snapshot according to the configured difficulty counts while preserving the same snapshot throughout that attempt.
- **FR-006**: System MUST save the active attempt's remaining time from browser timer heartbeat updates.
- **FR-007**: System MUST expire an attempt when remaining time reaches zero or the authoritative assessment duration has elapsed.
- **FR-008**: System MUST score only answers saved before an attempt is submitted or expired.
- **FR-008a**: System MUST block candidate attempt start and alert HR when configured question-bank rules cannot be satisfied.
- **FR-008b**: System MUST use the assessment deadline as authoritative when heartbeat updates are stale, missing, or interrupted.
- **FR-009**: System MUST let HR maintain hidden expected-output records for coding questions.
- **FR-010**: System MUST compare candidate answers or stated outputs against hidden expected-output records without executing candidate code.
- **FR-011**: System MUST let HR maintain local common-answer records used for simulated plagiarism comparison.
- **FR-012**: System MUST compare candidate submissions against local common-answer records and record a similarity percentage or no-match outcome.
- **FR-013**: System MUST label output validation and plagiarism detection results as simulated wherever they are shown.
- **FR-013a**: System MUST flag submissions with simulated plagiarism similarity of 80% or higher for HR review only and MUST NOT automatically reject candidates based on that flag.
- **FR-013b**: System MUST preserve recorded simulated output and plagiarism results for completed attempts when hidden expected-output records or common-answer records later change.
- **FR-014**: System MUST calculate adaptive difficulty suggestions from at least five previous completed assessment scores using these bands: average score of 50% or lower suggests easier distribution, average score of 80% or higher suggests harder distribution, and other averages suggest no change.
- **FR-015**: System MUST show HR the adaptive suggestion together with enough score context to support human review.
- **FR-016**: System MUST allow HR to configure an assessment retake cooldown period, including a six-month period.
- **FR-017**: System MUST block candidate retakes for the same assessment while the configured cooldown remains active.
- **FR-018**: System MUST show blocked candidates a clear next eligible date and must not reveal hidden expected outputs, common answers, or other candidates' submissions.
- **FR-019**: System MUST preserve historical attempt snapshots, timer state, scores, and simulated integrity results for review after assessment completion or expiry.
- **FR-020**: System MUST keep all simulated validation and plagiarism records local to SRIM and must not require any external code execution, plagiarism, or AI service.

### Role & Privacy Requirements *(mandatory when candidate or evaluation data is touched)*

- **RP-001**: HR Admin access MUST be limited to assessment configuration, question-bank rules, hidden expected outputs, common-answer records, candidate attempt review, adaptive suggestions, and cooldown settings.
- **RP-002**: Technical Interviewer access MUST be limited to assigned candidate assessment summaries needed for interview preparation and evaluation; hidden expected outputs and common-answer maintenance remain HR-only unless explicitly authorized later.
- **RP-003**: Candidate access MUST be limited to their own assigned assessments, active attempts, saved answers, timer status, eligibility messages, and allowed result feedback.
- **RP-004**: Junior Staff or observer access MUST remain read-only/training-only when assessment outcomes are included in interview or evaluation views.
- **RP-005**: Candidate PII, attempts, answers, scores, simulated integrity flags, hidden expected outputs, common-answer records, and cooldown decisions MUST be hidden from unauthorized roles.
- **RP-006**: Simulated code-output validation and simulated plagiarism decisions MUST be labeled as simulated and reviewable by HR before they affect candidate status.

### Key Entities *(include if feature involves data)*

- **Assessment**: A technical test definition tied to a recruitment workflow, including duration, question rules, cooldown settings, and adaptive suggestion context.
- **Question**: A bank item associated with an assessment and difficulty tier, optionally with coding-answer expectations and local common-answer references.
- **Question-Bank Rule**: HR-defined counts for how many easy, medium, and hard questions should be included in future candidate attempts.
- **Candidate Attempt**: A candidate's instance of an assessment, including start/end status, remaining time, submitted answers, score, and expiry state.
- **Attempt Question Snapshot**: The randomized set of questions assigned to a candidate attempt, preserved so later rule changes do not alter an active or completed attempt.
- **Hidden Expected Output**: A local, HR-managed expected result for simulated coding-output comparison that is not visible to candidates.
- **Common Answer Record**: A local reference answer used to simulate plagiarism similarity comparison.
- **Submission Integrity Result**: The recorded simulated output match and simulated plagiarism similarity for a candidate submission, preserved as the scored result even if HR later changes expected-output or common-answer records.
- **Cooldown Decision**: The eligibility result that determines whether a candidate can retake an assessment and, if blocked, the next eligible date.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: HR Admins can configure difficulty-tier question counts and a retake cooldown for an assessment in under 3 minutes during user acceptance testing.
- **SC-002**: In 95% of eligible attempt starts, the generated question snapshot matches the configured difficulty counts when sufficient eligible questions exist.
- **SC-003**: Timer heartbeat updates preserve the candidate's remaining time within one heartbeat interval during normal browser use.
- **SC-004**: 100% of attempts that reach zero remaining time are marked submitted or expired before additional answers can affect the score.
- **SC-005**: 100% of simulated output validation and plagiarism results shown to HR are labeled as simulated.
- **SC-006**: Candidates blocked by cooldown can see the reason and next eligible date without contacting support in at least 90% of acceptance-test cases.
- **SC-007**: HR can review prior score trends and understand the adaptive difficulty suggestion outcome in under 2 minutes for an assessment with at least five completed attempts.
- **SC-008**: No candidate-facing assessment page exposes hidden expected outputs, common-answer records, or another candidate's submissions during privacy acceptance testing.
- **SC-009**: 100% of candidate start attempts are blocked when configured question-bank rules cannot be satisfied, with HR receiving a corrective alert.
- **SC-010**: 100% of submissions at or above 80% simulated plagiarism similarity are flagged for HR review without automatic candidate rejection.

## Assumptions

- Candidates use modern browsers that can run a basic in-page timer and send periodic heartbeat updates while the assessment page is open.
- HR Admins are responsible for maintaining enough questions per difficulty tier before assigning assessments to candidates.
- Difficulty tiers are Easy, Medium, and Hard unless a later approved baseline change expands the taxonomy.
- The default cooldown used in examples is six months, but HR may configure another whole-month duration if allowed by assessment policy.
- Adaptive difficulty suggestions inform HR configuration decisions; they do not automatically change an active assessment without HR review.
- Adaptive difficulty suggestions require at least five completed attempts and use average-score bands of 50% or lower for easier, 80% or higher for harder, and otherwise unchanged.
- Simulated output validation compares submitted text or stated output, not compiled or executed candidate code.
- Simulated plagiarism detection compares against local common-answer records, not internet sources or third-party plagiarism services.
- A stale or missing browser heartbeat never extends the assessment beyond the server-side deadline.
- Completed simulated integrity results are not automatically re-scored when HR edits expected-output or common-answer records later.
