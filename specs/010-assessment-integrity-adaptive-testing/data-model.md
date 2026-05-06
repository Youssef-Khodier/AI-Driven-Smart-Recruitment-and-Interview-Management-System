# Data Model: Advanced Assessment Integrity and Adaptive Testing

## Entity Relationship Overview

```text
job_requisitions 1--N assessments
assessments 1--N questions
assessments 1--N assessment_question_rules
questions 1--N question_expected_outputs
assessments 1--N assessment_common_answers
questions 0..1--N assessment_common_answers
applications 1--N candidate_assessments
candidates 1--N candidate_assessments
assessments 1--N candidate_assessments
candidate_assessments 1--N candidate_assessment_questions
candidate_assessments 1--N submissions
candidate_assessment_questions 1--0..1 submissions
candidate_assessments 1--N assessment_integrity_events
```

## Existing Entities Extended

### Assessment

Represents a technical test definition for a job requisition.

**Fields**:

- `assessment_id`: Primary identifier.
- `job_id`: Parent job requisition.
- `title`: HR-visible assessment title, required, max 180 characters.
- `description`: Optional HR/candidate context.
- `type`: Assessment category such as technical, coding, aptitude, theory, or other.
- `duration_minutes`: Positive whole-number duration.
- `cooldown_months`: Non-negative whole-number retake cooldown, default 6.
- `is_active`: Whether candidates can start new attempts.
- `created_at`, `updated_at`: Audit timestamps.

**Validation Rules**:

- Duration must be greater than zero.
- Cooldown must be zero or greater.
- Assessment must belong to an existing job requisition.
- HR may update rules for future attempts only; existing attempt snapshots remain unchanged.

### Question

Represents a reusable question in an assessment's bank.

**Fields**:

- `question_id`: Primary identifier.
- `assessment_id`: Parent assessment.
- `type`: MCQ, coding, theory, or other.
- `difficulty_level`: EASY, MEDIUM, or HARD.
- `question_text`: Candidate-facing prompt.
- `options`: Optional choices for MCQ-style questions.
- `correct_answer`: Local reference answer used by scoring.
- `points`: Positive score value.
- `is_active`: Whether the question may be selected for new snapshots.
- `created_at`, `updated_at`: Audit timestamps.

**Validation Rules**:

- Difficulty must be one of EASY, MEDIUM, HARD.
- Points must be greater than zero.
- Candidate-facing views must never show hidden expected outputs or common answers.

### Candidate Assessment

Represents one candidate's attempt for an assessment and application.

**Fields**:

- `ca_id`: Primary identifier.
- `application_id`: Related application.
- `candidate_id`: Candidate user/profile identifier.
- `assessment_id`: Parent assessment.
- `start_time`: Attempt start timestamp.
- `end_time`: Submission or expiry timestamp.
- `expires_at`: Server-authoritative deadline.
- `remaining_seconds`: Last heartbeat-reported remaining time.
- `last_heartbeat_at`: Last heartbeat timestamp.
- `status`: IN_PROGRESS, SUBMITTED, or EXPIRED.
- `score`: Final percentage score.
- `created_at`, `updated_at`: Audit timestamps.

**Validation Rules**:

- Candidate can only access their own attempts.
- Attempt start is blocked when configured question-bank rules cannot be satisfied.
- Attempt expiry uses `expires_at` even when heartbeat is stale or missing.
- Answers saved after expiry must not affect scoring.
- Retake is blocked while the assessment cooldown remains active for the same candidate and assessment.

### Submission

Represents a candidate's saved answer for an attempt question.

**Fields**:

- `submission_id`: Primary identifier.
- `ca_id`: Parent candidate attempt.
- `attempt_question_id`: Snapshot question answered.
- `question_id`: Source bank question when still available.
- `answer_text`: Candidate answer.
- `code_output`: Candidate-provided output text for simulated code-output validation.
- `saved_at`: Last answer save timestamp.
- `finalized_at`: Timestamp included in submitted/expired scoring.
- `is_correct`: Scoring outcome where applicable.
- `output_matched`: Null when no hidden expected output exists; true/false when simulated comparison runs.
- `awarded_points`: Points awarded for the answer.
- `plagiarism_score`: Simulated local similarity percentage from 0 to 100.
- `created_at`, `updated_at`: Audit timestamps.

**Validation Rules**:

- One submission per attempt question.
- Candidate may save only before submission or expiry.
- Similarity at or above 80% is an HR review flag only, not automatic rejection.
- Completed simulated results are preserved after reference records change.

## New Or Feature-Specific Entities

### Assessment Question Rule

Defines how many questions of each difficulty are required for future candidate attempts.

**Fields**:

- `rule_id`: Primary identifier.
- `assessment_id`: Parent assessment.
- `difficulty_level`: EASY, MEDIUM, or HARD.
- `question_count`: Non-negative whole number.
- `created_at`, `updated_at`: Audit timestamps.

**Relationships**:

- Belongs to one assessment.
- Unique by assessment and difficulty.

**Validation Rules**:

- Total configured count across tiers must be greater than zero.
- Counts must be whole numbers and zero or greater.
- Candidate start requires enough active questions in every configured tier.

### Attempt Question Snapshot

Stores the randomized questions assigned to one candidate attempt.

**Fields**:

- `attempt_question_id`: Primary identifier.
- `ca_id`: Parent candidate attempt.
- `question_id`: Source question, nullable if source is later removed.
- `display_order`: Candidate-facing order.
- `question_type`: Snapshotted question type.
- `question_text`: Snapshotted prompt.
- `options`: Snapshotted choices.
- `correct_answer`: Snapshotted reference answer for scoring.
- `points`: Snapshotted points.
- `created_at`: Snapshot timestamp.

**Validation Rules**:

- Display order is unique per attempt.
- Source question is unique per attempt.
- Existing snapshots do not change when HR edits question rules or source questions later.

### Hidden Expected Output

Stores local expected output for simulated coding validation.

**Fields**:

- `output_id`: Primary identifier.
- `question_id`: Parent coding question.
- `expected_output`: Hidden local expected output text.
- `label`: Optional HR-facing label.
- `is_hidden`: Always treated as hidden from candidates.
- `created_at`: Creation timestamp.

**Validation Rules**:

- Expected output is required when record exists.
- Candidate pages must never render this text.
- Comparison is text normalization only; no code execution.

### Common Answer Record

Stores local answer examples for simulated plagiarism comparison.

**Fields**:

- `common_answer_id`: Primary identifier.
- `assessment_id`: Parent assessment.
- `question_id`: Optional parent question; null means assessment-wide reference.
- `answer_text`: Local common answer text.
- `source_label`: Optional HR-facing source note.
- `created_at`: Creation timestamp.

**Validation Rules**:

- Answer text is required.
- Candidate pages must never render this text.
- Comparison uses local records only.

### Assessment Integrity Event

Stores proctoring-style events such as focus loss for attempt review.

**Fields**:

- `event_id`: Primary identifier.
- `ca_id`: Parent candidate attempt.
- `event_type`: Event type, such as focus loss.
- `occurred_at`: Event timestamp.
- `metadata`: Optional local event details.
- `created_at`: Creation timestamp.

**Validation Rules**:

- Events are review signals only unless an HR decision process uses them later.
- Candidate may only create events for their own active attempt.

## State Transitions

### Candidate Assessment Attempt

```text
Not Started
  -> IN_PROGRESS: candidate starts attempt and question snapshot is created
IN_PROGRESS
  -> SUBMITTED: candidate submits before deadline; saved answers are scored
IN_PROGRESS
  -> EXPIRED: server deadline passes or remaining time reaches zero; saved answers before expiry are scored
SUBMITTED / EXPIRED
  -> Retake Blocked: candidate attempts retake before cooldown ends
SUBMITTED / EXPIRED
  -> Not Started: candidate becomes eligible again after cooldown and receives a new attempt
```

## Data Protection Notes

- Hidden expected outputs and common-answer records are HR-only.
- Candidate pages show only their own prompts, own answers, timer state, and allowed result feedback.
- HR review pages may show simulated output/plagiarism results but must label them simulated.
- Technical Interviewers receive assigned candidate assessment summaries only where needed for interview workflows.
- Completed attempt snapshots and simulated integrity results are retained as review evidence unless a future retention/erasure feature applies candidate privacy operations.
