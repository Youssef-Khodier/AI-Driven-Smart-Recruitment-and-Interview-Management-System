# Data Model: Technical Assessment Management

## Entity: Assessment

Represents a job-linked assessment definition created by HR.

**Fields**:

- `assessment_id`: Primary identifier.
- `job_id`: Required link to `JobRequisition`.
- `title`: Required display name, 3-180 characters.
- `description`: Optional candidate-facing instructions.
- `type`: Required assessment category, default `TECHNICAL`.
- `duration_minutes`: Required positive whole number; minimum 1.
- `is_active`: Indicates whether candidates may start new attempts.
- `created_at`, `updated_at`: Audit timestamps.

**Relationships**:

- Belongs to one `JobRequisition`.
- Has many `Question` records.
- Has many `CandidateAssessment` attempts.

**Validation Rules**:

- HR Admin must choose an existing job requisition.
- Duration must be positive.
- Inactive assessments remain reviewable but cannot be newly started.

## Entity: Question

Represents a prompt within an assessment.

**Fields**:

- `question_id`: Primary identifier.
- `assessment_id`: Required link to `Assessment`.
- `type`: Required; allowed values are `MCQ`, `THEORY`, and `CODING_TEXT`.
- `difficulty_level`: Required; `EASY`, `MEDIUM`, or `HARD`.
- `question_text`: Required prompt text.
- `options`: Required structured answer choices for MCQ; empty for theory/free-text and coding-as-text.
- `correct_answer`: Required for MCQ; optional scoring reference for text questions when HR wants keyword scoring.
- `points`: Required positive number.
- `is_active`: Indicates whether the question is available for new attempt snapshots.
- `created_at`, `updated_at`: Audit timestamps.

**Relationships**:

- Belongs to one `Assessment`.
- May appear in many `CandidateAssessmentQuestion` snapshots.

**Validation Rules**:

- MCQ questions require at least two answer choices and one correct answer matching a choice.
- Theory/free-text and coding-as-text questions do not require executable code, hidden tests, or compiler settings.
- Points must be greater than zero.

## Entity: CandidateAssessment

Represents one candidate's attempt for one assessment and application.

**Fields**:

- `ca_id`: Primary identifier.
- `application_id`: Required link to the candidate application in `ASSESSMENT` status.
- `candidate_id`: Required link to `Candidate`.
- `assessment_id`: Required link to `Assessment`.
- `start_time`: Set when the attempt begins.
- `end_time`: Set when submitted or expired.
- `expires_at`: Calculated from `start_time` plus assessment duration.
- `status`: `IN_PROGRESS`, `SUBMITTED`, or `EXPIRED`.
- `score`: Simulated score from 0 to 100 when submitted or expired.
- `created_at`, `updated_at`: Audit timestamps.

**Relationships**:

- Belongs to one `Application`.
- Belongs to one `Candidate`.
- Belongs to one `Assessment`.
- Has many `CandidateAssessmentQuestion` snapshots.
- Has many `Submission` answers.
- Has many `AssessmentIntegrityEvent` records.

**Uniqueness Rules**:

- One attempt per `candidate_id` and `assessment_id` for this phase.
- Candidate must own the linked application.
- Linked application must be in `ASSESSMENT` status to start.

**State Transitions**:

```text
none -> IN_PROGRESS -> SUBMITTED
none -> IN_PROGRESS -> EXPIRED
```

- `SUBMITTED` and `EXPIRED` are terminal for this phase.
- Expired attempts calculate score only from answers saved before `expires_at`.

## Entity: CandidateAssessmentQuestion

Represents immutable question evidence captured at attempt start.

**Fields**:

- `attempt_question_id`: Primary identifier.
- `ca_id`: Required link to `CandidateAssessment`.
- `question_id`: Optional link to source `Question`; retained for traceability even if the question changes later.
- `display_order`: Required randomized order position.
- `question_type`: Snapshot of question type.
- `question_text`: Snapshot of prompt text.
- `options`: Snapshot of answer choices.
- `correct_answer`: Snapshot of expected answer or scoring reference for simulated scoring.
- `points`: Snapshot of point value.
- `created_at`: Snapshot timestamp.

**Relationships**:

- Belongs to one `CandidateAssessment`.
- Optionally references one source `Question`.
- Has one or many `Submission` records depending on save history strategy; latest saved answer is authoritative for scoring.

**Validation Rules**:

- Display order must be unique within an attempt.
- Snapshot fields must not change after attempt creation.

## Entity: Submission

Represents a continuously saved or finalized candidate answer.

**Fields**:

- `submission_id`: Primary identifier.
- `ca_id`: Required link to `CandidateAssessment`.
- `attempt_question_id`: Required link to the attempt snapshot.
- `question_id`: Optional source-question link for baseline compatibility.
- `answer_text`: Candidate answer text or selected MCQ choice.
- `saved_at`: Time the answer was saved.
- `finalized_at`: Set when final submission occurs; null for draft saved answers until submit or expiry.
- `is_correct`: Simulated correctness flag when score is calculated.
- `awarded_points`: Points awarded by the simulated scorer.

**Relationships**:

- Belongs to one `CandidateAssessment`.
- Belongs to one `CandidateAssessmentQuestion`.

**Validation Rules**:

- Answers can be saved only while the attempt is `IN_PROGRESS` and before `expires_at`.
- Late answer changes are rejected after expiry.
- The latest saved answer before submit or expiry is used for scoring.

## Entity: AssessmentIntegrityEvent

Represents simulated proctoring evidence for an attempt.

**Fields**:

- `event_id`: Primary identifier.
- `ca_id`: Required link to `CandidateAssessment`.
- `event_type`: `FOCUS_LOST` or `FOCUS_RETURNED` for this phase.
- `occurred_at`: Timestamp captured during the active attempt.
- `metadata`: Optional small structured context such as visible/hidden state.
- `created_at`: Storage timestamp.

**Relationships**:

- Belongs to one `CandidateAssessment`.

**Validation Rules**:

- Events can be recorded only for the authenticated candidate's active attempt.
- Events are review data only and never automatically reject or advance candidates.

## Existing Entity Extensions

### Application

- Existing `ApplicationStatus::ASSESSMENT` determines candidate eligibility to start an assessment.
- Has many `CandidateAssessment` records through `application_id`.

### JobRequisition

- Has many `Assessment` records.
- HR results pages aggregate candidate attempts by job.

## Retention and Privacy Notes

- Candidate attempts, answers, scores, and integrity events are candidate evaluation data and must be hidden from other candidates and unauthorized staff.
- Simulated score and simulated proctoring labels must be displayed wherever results or integrity events appear.
- Deactivating an assessment or question must not delete historical attempt evidence.
