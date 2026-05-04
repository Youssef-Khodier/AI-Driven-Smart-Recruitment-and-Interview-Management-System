# Web Workflow Contracts: Interview Scheduling Feedback

These are server-rendered page and form contracts for the Vanilla PHP MVC monolith. They are not REST API contracts.

## HR Interview Index

**Route**: `GET /hr/interviews`  
**Actor**: HR Admin  
**Purpose**: View scheduled, completed, and cancelled interviews with feedback completion state.

**Page Data**:

- Interview ID, candidate name, job title, scheduled date/time, duration, status.
- Panel member names and panel roles.
- Feedback completion indicator: complete, partial, or none.

**Authorization**:

- Requires authenticated active HR Admin.

## HR Create Interview Form

**Route**: `GET /hr/applications/{application_id}/interviews/create`  
**Actor**: HR Admin  
**Purpose**: Show scheduling form for an application with status `INTERVIEW`.

**Page Data**:

- Candidate and job summary.
- Application status.
- Active staff options for panel assignment.
- Interview type options and duration field.

**Error States**:

- If application status is not `INTERVIEW`, show an access/validation message and no scheduling form.
- If no active official interviewer candidates exist, show an empty-state message.

## HR Store Interview

**Route**: `POST /hr/applications/{application_id}/interviews`  
**Actor**: HR Admin  
**Purpose**: Save interview schedule and assignments.

**Form Fields**:

- `csrf_token`: Required.
- `interview_type`: Required; allowed `TECHNICAL`, `HR`, `PANEL`.
- `scheduled_at`: Required future date/time.
- `duration_minutes`: Required positive number.
- `panel_members[]`: Required list of user IDs and panel roles.

**Validation**:

- Application status must be `INTERVIEW`.
- At least one official scorer must be assigned.
- No duplicate panel user assignments.
- All panel users must be active and allowed for their panel role.
- Requested slot must not overlap non-cancelled interviews for same application or selected panel users.

**Success**:

- Redirect to HR interview detail page with success message.
- Create audit record for scheduling.

**Failure**:

- Return form with field-level errors and preserved input.

## HR Show Interview

**Route**: `GET /hr/interviews/{interview_id}`  
**Actor**: HR Admin  
**Purpose**: Review schedule, panel, briefing summary, feedback, and audit traceability.

**Page Data**:

- Interview schedule, status, panel assignments.
- Candidate, application, job, and assessment summary.
- Submitted feedback records.
- Feedback completion indicator.
- Audit records for schedule/status/feedback actions.

## HR Reschedule Interview

**Routes**: `GET /hr/interviews/{interview_id}/edit`, `PUT /hr/interviews/{interview_id}`  
**Actor**: HR Admin  
**Purpose**: Change schedule or panel assignments before official feedback exists.

**Validation**:

- Interview must not be cancelled.
- No official feedback may exist.
- New schedule must satisfy the same conflict rules as create.

**Success**:

- Redirect to HR interview detail page.
- Create audit record for rescheduling with changed fields.

## HR Cancel Interview

**Route**: `POST /hr/interviews/{interview_id}/cancel`  
**Actor**: HR Admin  
**Purpose**: Mark a scheduled interview as cancelled.

**Validation**:

- Interview must be `SCHEDULED`.

**Success**:

- Status becomes `CANCELLED`.
- Create audit record for cancellation.

## HR Complete Interview

**Route**: `POST /hr/interviews/{interview_id}/complete`  
**Actor**: HR Admin  
**Purpose**: Mark a scheduled interview as completed so official feedback can be submitted.

**Validation**:

- Interview must be `SCHEDULED`.

**Success**:

- Status becomes `COMPLETED`.
- Create audit record for completion.

## Interviewer Assigned Interviews

**Route**: `GET /interviewer/interviews`  
**Actor**: Technical Interviewer or Junior Staff/observer with assignment  
**Purpose**: View only assigned interviews.

**Page Data**:

- Candidate name, job title, scheduled date/time, duration, interview status, panel role, feedback status.

**Authorization**:

- Requires authenticated active user assigned to at least one interview.

## Interviewer Briefing

**Route**: `GET /interviewer/interviews/{interview_id}`  
**Actor**: Assigned official interviewer, panel lead, or observer  
**Purpose**: View candidate and assessment briefing details.

**Page Data**:

- Candidate summary, job requirements, application status.
- Assessment title, attempt status, score, and submitted answer summary when available.
- Missing-data indicators for incomplete briefing sections.
- If official scorer and interview completed with no feedback yet, show link to feedback form.
- If observer, show read-only observer label and no official feedback action.

## Interviewer Feedback Form

**Routes**: `GET /interviewer/interviews/{interview_id}/feedback`, `POST /interviewer/interviews/{interview_id}/feedback`  
**Actor**: Assigned official interviewer or panel lead  
**Purpose**: Submit one official feedback record for completed interview.

**Form Fields**:

- `csrf_token`: Required.
- `technical_score`: Required number from 0 to 10.
- `communication_score`: Required number from 0 to 10.
- `culture_fit_score`: Required number from 0 to 10.
- `overall_score`: Required number from 0 to 10.
- `comments`: Required free-text comments.

**Validation**:

- Interview must be `COMPLETED`.
- User must be assigned as `PANEL_LEAD` or `INTERVIEWER`.
- User must not already have official feedback for this interview.
- Comments are required.
- Observer submissions are denied.

**Success**:

- Redirect to interviewer briefing page with success message.
- HR feedback completion state updates immediately.
- Create audit record for feedback submission.

**Failure**:

- Return form with field-level errors and preserved input.
