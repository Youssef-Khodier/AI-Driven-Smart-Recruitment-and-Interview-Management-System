# Data Model: Interview Scheduling Feedback

## Application

Represents a candidate's application for a job.

**Relevant Fields**:

- `application_id`: Unique identifier.
- `candidate_id`: Candidate linked to the application.
- `job_id`: Job requisition linked to the application.
- `status`: Must be `INTERVIEW` before an interview can be scheduled.
- `match_score`, `applied_at`, timestamps: Existing screening context.

**Relationships**:

- Belongs to Candidate.
- Belongs to Job Requisition.
- Has many Interviews.
- Has assessment evidence through Candidate Assessment records.

**Validation Rules**:

- Interview creation requires `status = INTERVIEW`.
- Candidate and job relationships must exist.

## Interview

Represents a scheduled live interview session for an application.

**Fields**:

- `interview_id`: Unique identifier.
- `application_id`: Required link to Application.
- `interview_type`: `TECHNICAL`, `HR`, or `PANEL`.
- `scheduled_at`: Required future date/time for create and reschedule.
- `duration_minutes`: Required positive duration.
- `status`: `SCHEDULED`, `COMPLETED`, or `CANCELLED`.
- `created_by`: HR Admin who created the interview.
- `created_at`, `updated_at`: Record timestamps.

**Relationships**:

- Belongs to Application.
- Has many Interviewer Assignments.
- Has many Interview Feedback records.
- Has many Interview Audit Records.

**Validation Rules**:

- Must have at least one assigned official scorer: `PANEL_LEAD` or `INTERVIEWER`.
- Cannot be scheduled in the past.
- Cannot overlap any non-cancelled interview for the same application.
- Cannot overlap any non-cancelled interview for any selected panel user.
- Rescheduling is blocked after any official feedback exists.

**State Transitions**:

- `SCHEDULED` → `COMPLETED`: HR marks interview complete.
- `SCHEDULED` → `CANCELLED`: HR cancels interview.
- `SCHEDULED` → `SCHEDULED`: HR reschedules while no feedback exists.
- `COMPLETED` and `CANCELLED` are terminal for this feature.

## Interviewer Assignment

Represents one user assigned to an interview panel.

**Fields**:

- `assignment_id`: Unique identifier.
- `interview_id`: Required link to Interview.
- `interviewer_id`: Required link to User.
- `role_in_panel`: `PANEL_LEAD`, `INTERVIEWER`, or `OBSERVER`.
- `is_shadowing`: True for observer or training-only assignment.

**Relationships**:

- Belongs to Interview.
- Belongs to User.

**Validation Rules**:

- Unique pair: `interview_id` + `interviewer_id`.
- Assigned user must be active.
- At least one assignment per interview must be official: `PANEL_LEAD` or `INTERVIEWER`.
- `OBSERVER` assignments are read-only and do not count toward official feedback completion.

## Interview Feedback

Represents official structured feedback submitted by an assigned official interviewer.

**Fields**:

- `feedback_id`: Unique identifier.
- `interview_id`: Required link to Interview.
- `interviewer_id`: Required link to User.
- `technical_score`: Score from 0 to 10.
- `communication_score`: Score from 0 to 10.
- `culture_fit_score`: Score from 0 to 10.
- `overall_score`: Score from 0 to 10.
- `comments`: Required free-text comments; must be safely displayed.
- `submitted_at`: Submission timestamp.

**Relationships**:

- Belongs to Interview.
- Belongs to User.

**Validation Rules**:

- Interview status must be `COMPLETED`.
- User must be assigned to the interview as `PANEL_LEAD` or `INTERVIEWER`.
- Observers and Junior Staff observer assignments cannot submit official feedback.
- Unique pair: `interview_id` + `interviewer_id`.
- Scores must be numeric and within 0 to 10.
- Comments are required.

## Interview Audit Record

Represents traceability for schedule and feedback actions.

**Fields**:

- `audit_id`: Unique identifier.
- `interview_id`: Required link to Interview.
- `actor_user_id`: User who performed the action.
- `action`: `SCHEDULED`, `RESCHEDULED`, `CANCELLED`, `COMPLETED`, or `FEEDBACK_SUBMITTED`.
- `changed_fields`: Structured list of fields changed by the action.
- `created_at`: Action timestamp.

**Relationships**:

- Belongs to Interview.
- Belongs to User as actor.

**Validation Rules**:

- Every create, reschedule, cancel, complete, and feedback submit action creates one audit record.
- Changed fields must include only fields relevant to the action.

## Briefing View Model

Represents read-only data assembled for assigned interview preparation. This is not a stored table.

**Fields**:

- Candidate summary: name, current title, experience, location, resume availability.
- Job summary: title, department, requirements.
- Application summary: status, match score, applied date.
- Assessment summary: assessment title, attempt status, score, submitted answer summary when available.
- Missing-data indicators: resume missing, no completed assessment, no submitted answers.

**Validation Rules**:

- Visible only to HR Admins and users assigned to the interview.
- Candidate-facing users cannot access interviewer briefing pages.
- Missing data is displayed as a clear partial briefing notice rather than causing page failure.

## User

Represents an authenticated account.

**Relevant Fields**:

- `user_id`: Unique identifier.
- `name`, `email`: Display and identity fields.
- `role`: `HR_ADMIN`, `INTERVIEWER`, `CANDIDATE`, or `JUNIOR_STAFF`.
- `status`: Must be `ACTIVE` for assignment and access.

**Relationships**:

- Can create interviews as HR Admin.
- Can be assigned to interviews as official interviewer, panel lead, or observer.
- Can author official feedback only when assigned as official scorer.
- Junior Staff can only access interviews through observer assignments.
