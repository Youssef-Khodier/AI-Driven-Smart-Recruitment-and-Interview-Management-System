# Data Model: Feedback Governance Analytics

## Entity: Official Feedback Submission

**Backed by**: Existing `interview_feedback`, extended as needed.

**Fields**:

- `feedback_id`: Unique feedback identifier.
- `interview_id`: Interview being evaluated.
- `interviewer_id`: User who submitted the feedback.
- `technical_score`: Raw technical score, 0-10.
- `communication_score`: Raw communication score, 0-10.
- `culture_fit_score`: Raw culture-fit score, 0-10.
- `overall_score`: Raw overall score, 0-10.
- `comments`: Interviewer comments.
- `submitted_at`: Submission timestamp.
- `is_official`: Derived from assignment role; observers and shadowing staff are non-official.

**Relationships**:

- Belongs to `interviews`.
- Belongs to interviewer `users`.
- Participates in `normalized_evaluation_snapshots` when official.

**Validation Rules**:

- Scores must be numeric 0-10.
- Only assigned official interviewers can submit official feedback.
- One feedback row per interview and interviewer.
- Observers and junior staff shadowing roles do not count toward required completion.

## Entity: Normalized Evaluation Snapshot

**Purpose**: Stores an auditable calculation result for raw, normalized, and aggregate scores.

**Fields**:

- `snapshot_id`: Unique snapshot identifier.
- `application_id`: Candidate application being evaluated.
- `interview_id`: Interview source when applicable.
- `calculated_by`: User or system actor that triggered calculation.
- `raw_score_summary`: JSON summary of raw competency and overall scores.
- `normalized_score_summary`: JSON summary of normalized scores.
- `aggregate_score`: Final evaluation score on 0-100 scale.
- `recommendation`: Strong Hire, Hire, No Hire, or Strong No Hire.
- `normalization_status`: Applied, partial, or raw fallback.
- `fallback_reasons`: JSON list of interviewers or competencies using raw scores.
- `included_feedback_count`: Count of included official feedback submissions.
- `missing_feedback_count`: Count of required official submissions still missing.
- `created_at`: Calculation timestamp.

**Relationships**:

- Belongs to `applications`.
- May update or support `final_evaluations`.
- Has many governance audit events.

**Validation Rules**:

- Aggregate score must be 0-100.
- Normalized values must preserve raw values separately.
- Final recommendation approval is blocked when missing official feedback or unresolved serious flags exist.

## Entity: Interviewer Harshness Trend

**Purpose**: Derived summary used to decide and explain normalization.

**Fields**:

- `interviewer_id`: Interviewer being analyzed.
- `window_start`: Start date of the 12-month history window.
- `window_end`: End date of the history window.
- `comparable_submission_count`: Count of comparable prior official submissions.
- `average_delta`: Difference between interviewer average and comparable panel average.
- `status`: Eligible or insufficient_history.

**Relationships**:

- Derived from prior official feedback submissions.
- Referenced by normalized evaluation snapshots and audit explanations.

**Validation Rules**:

- Eligible only when comparable prior official submission count is at least 5 in the last 12 months.
- If ineligible, raw scores are used and fallback is recorded.

## Entity: Serious Concern Flag

**Proposed table**: `feedback_concern_flags`

**Fields**:

- `flag_id`: Unique flag identifier.
- `application_id`: Candidate application affected.
- `interview_id`: Interview affected, nullable if HR flags at application level.
- `candidate_id`: Candidate reference for efficient authorization checks.
- `category`: Ethical, integrity, legal, safety, conduct, or other.
- `severity`: High or critical.
- `explanation`: Required submitted explanation.
- `status`: Open, resolved_resume, resolved_blocked, resolved_no_hire.
- `created_by`: HR Admin or assigned interviewer who created the flag.
- `created_at`: Creation timestamp.
- `resolved_by`: HR Admin who resolved the flag.
- `resolved_at`: Resolution timestamp.
- `resolution_rationale`: Required HR rationale.

**Relationships**:

- Belongs to application, candidate, optional interview, creator user, and resolver user.
- Blocks debrief completion, final recommendation approval, and candidate status changes while open.
- Creates notifications for HR and relevant official panel members.

**Validation Rules**:

- Category, severity, and explanation are required.
- Assigned interviewers can create flags only for their assigned interviews.
- Only HR Admin can resolve flags.
- Resolution rationale is required.

**State Transitions**:

- `open` -> `resolved_resume`
- `open` -> `resolved_blocked`
- `open` -> `resolved_no_hire`

## Entity: Candidate Sentiment Entry

**Proposed table**: `candidate_interview_sentiment`

**Fields**:

- `sentiment_id`: Unique sentiment identifier.
- `candidate_id`: Candidate submitting feedback.
- `application_id`: Candidate application.
- `interview_id`: Completed interview.
- `rating`: Candidate experience rating.
- `comment`: Optional candidate comment.
- `submitted_at`: Submission timestamp.

**Relationships**:

- Belongs to candidate, application, and interview.
- Visible to HR separately from official scoring.
- Has one governance audit event for submission.

**Validation Rules**:

- Candidate can submit only for their own completed interview.
- One sentiment entry per candidate and interview.
- Rating is required; comment is optional and length-limited.
- Sentiment is excluded from score aggregation, normalization, and recommendation calculations.

## Entity: Consensus/Debrief Record

**Proposed table**: `evaluation_debrief_records`

**Fields**:

- `debrief_id`: Unique debrief identifier.
- `application_id`: Application being discussed.
- `interview_id`: Interview whose feedback completed the trigger.
- `status`: Pending, completed, blocked_by_flag.
- `participants`: JSON list of HR and official panel participants.
- `consensus_level`: Consensus, mixed, or no_consensus.
- `dissent_notes`: Optional notes when dissent exists.
- `final_recommendation`: Final recorded recommendation.
- `rationale`: Required HR rationale.
- `next_action`: Continue pipeline, reject, request follow-up, or hold.
- `created_at`: Creation timestamp.
- `completed_by`: HR Admin who completed the record.
- `completed_at`: Completion timestamp.

**Relationships**:

- Belongs to application and interview.
- References final evaluation when approved.
- Has many governance audit events.

**Validation Rules**:

- Created exactly once after 100% official feedback submission.
- Cannot be completed while any serious concern flag remains open.
- Completion requires participants, consensus level, final recommendation, rationale, and next action.

**State Transitions**:

- `pending` -> `blocked_by_flag`
- `blocked_by_flag` -> `pending`
- `pending` -> `completed`

## Entity: Competency Benchmark Profile

**Proposed table**: `job_competency_benchmarks`

**Fields**:

- `benchmark_id`: Unique benchmark identifier.
- `job_id`: Job requisition.
- `competency`: Competency name.
- `benchmark_score`: Ideal score, 0-10.
- `weight`: Optional competency weight for display and future scoring.
- `source`: HR maintained or seeded from requisition.
- `updated_by`: HR Admin who last changed it.
- `updated_at`: Last update timestamp.

**Relationships**:

- Belongs to job requisition.
- Used by competency gap snapshots.
- Changes are audit-recorded.

**Validation Rules**:

- HR Admin maintains benchmarks.
- Competency names are unique per job.
- Benchmark score must be 0-10.
- Missing benchmarks are shown as warnings in evaluation and visualizer views.

## Entity: Competency Gap Snapshot

**Proposed table**: `competency_gap_snapshots`

**Fields**:

- `gap_id`: Unique gap identifier.
- `snapshot_id`: Normalized evaluation snapshot.
- `benchmark_id`: Competency benchmark used.
- `competency`: Competency label.
- `candidate_score`: Candidate normalized score, 0-10.
- `benchmark_score`: Ideal score, 0-10.
- `gap_ratio`: Candidate score divided by benchmark score.
- `severity`: Meeting, minor_gap, or major_gap.

**Relationships**:

- Belongs to normalized evaluation snapshot and benchmark profile.

**Validation Rules**:

- Meeting when candidate score is at least 90% of benchmark.
- Minor gap when candidate score is 75-89% of benchmark.
- Major gap when candidate score is below 75% of benchmark.

## Entity: Feedback Governance Audit Event

**Proposed table**: `feedback_governance_audit_records`

**Fields**:

- `audit_id`: Unique audit identifier.
- `actor_user_id`: User who performed the action, nullable only for system-triggered actions.
- `actor_role`: Role at time of action.
- `application_id`: Affected application when applicable.
- `interview_id`: Affected interview when applicable.
- `entity_type`: Feedback, normalized_snapshot, concern_flag, sentiment, debrief, benchmark, recommendation.
- `entity_id`: Affected entity identifier.
- `action`: Action enum.
- `old_values`: JSON previous state when applicable.
- `new_values`: JSON new state when applicable.
- `reason`: Required for overrides, flag resolution, and debrief decisions.
- `created_at`: Event timestamp.

**Relationships**:

- References users, applications, and interviews where available.
- Included in consolidated HR audit reporting.

**Validation Rules**:

- Audit records are append-only.
- Reasons are required for HR overrides, flag resolutions, and debrief outcomes that differ from calculated recommendation.
- Unauthorized roles cannot view audit events containing candidate evaluation details.
