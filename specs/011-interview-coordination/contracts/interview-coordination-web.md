# Web Contracts: Interview Coordination Workflows

These contracts describe server-rendered browser routes and form submissions inside the Vanilla PHP monolith. They are not REST API contracts.

## HR Interview Scheduling

### GET `/hr/applications/{id}/interviews/create`

- **Purpose**: Show scheduling form for an eligible application.
- **Actor**: HR Admin.
- **Requires**: Application exists and is in interview stage.
- **Renders**: Candidate summary, job summary, interview type, proposed date/time, duration, panel recommendation controls, current active staff options.
- **Failure states**: Ineligible application redirects or shows validation error.

### POST `/hr/applications/{id}/interviews/recommendations`

- **Purpose**: Generate panel recommendation snapshot for proposed interview details.
- **Actor**: HR Admin.
- **Form fields**: `interview_type`, `scheduled_at`, `duration_minutes`, optional required panel mix fields.
- **Validates**: Future scheduled time, positive duration, eligible application.
- **Result**: Re-renders scheduling form with recommended HR representative, senior technical interviewer, interviewer, observer candidates, workload counts, conflict indicators, and reasons.
- **Audit**: Records `ASSIGNMENT_RECOMMENDED` if a recommendation snapshot is persisted.

### POST `/hr/applications/{id}/interviews`

- **Purpose**: Save interview and selected panel assignments.
- **Actor**: HR Admin.
- **Form fields**: `interview_type`, `scheduled_at`, `duration_minutes`, selected `panel_members`, `role_in_panel`, override flags and reasons when needed.
- **Validates**: Required panel mix, no duplicate user assignment, active staff, no conflict unless override reason present.
- **Result**: Redirects to HR interview detail page.
- **Audit**: Records `SCHEDULED`, `ASSIGNMENT_ACCEPTED`, and `ASSIGNMENT_OVERRIDE` where applicable.

## HR Interview Management

### GET `/hr/interviews/{id}`

- **Purpose**: Show interview detail, assignments, briefing snapshot, workspace summary, extension requests, and audit history.
- **Actor**: HR Admin.
- **Failure states**: 404 for missing interview; 403 for unauthorized access.

### GET `/hr/interviews/{id}/edit`

- **Purpose**: Show reschedule and panel edit form.
- **Actor**: HR Admin.
- **Renders**: Existing interview details, assignments, conflict warnings, recommendation option.

### PUT `/hr/interviews/{id}`

- **Purpose**: Save reschedule or assignment changes.
- **Actor**: HR Admin.
- **Form fields**: Same core scheduling and panel fields as create, plus override reasons for conflicts.
- **Validates**: Interview is not cancelled/completed when edits are disallowed, valid time/duration, valid assignments.
- **Audit**: Records `RESCHEDULED`, `ASSIGNMENT_CHANGED`, `ASSIGNMENT_REMOVED`, and/or `ASSIGNMENT_OVERRIDE`.

### POST `/hr/interviews/{id}/cancel`

- **Purpose**: Cancel scheduled interview.
- **Actor**: HR Admin.
- **Form fields**: `cancel_reason`.
- **Audit**: Records `CANCELLED` with reason.

### POST `/hr/interviews/{id}/complete`

- **Purpose**: Mark scheduled interview completed.
- **Actor**: HR Admin.
- **Audit**: Records `COMPLETED`.

## Briefing and Workspace

### POST `/hr/interviews/{id}/briefing/refresh`

- **Purpose**: Create or refresh saved interview briefing snapshot.
- **Actor**: HR Admin.
- **Result**: Redirects to interview detail with missing-data flags.
- **Audit**: Records `BRIEFING_CREATED`.

### GET `/interviewer/interviews/{id}`

- **Purpose**: Show assigned interviewer briefing and links to feedback/workspace.
- **Actor**: Assigned official interviewer or observer.
- **Access**: Assigned users only.

### GET `/interviewer/interviews/{id}/workspace`

- **Purpose**: Show simulated coding workspace for assigned staff.
- **Actor**: Assigned official interviewer or assigned observer.
- **Access**: Observers are read-only/training-only.

### POST `/interviewer/interviews/{id}/workspace`

- **Purpose**: Save interviewer notes or permitted workspace sections.
- **Actor**: Assigned official interviewer.
- **Form fields**: `prompt_text`, `code_text`, `interviewer_notes`, `version_number`.
- **Validates**: Authorized assignment, editable session, CSRF token, allowed fields.
- **Audit**: Records `WORKSPACE_UPDATED`.

### GET `/candidate/interviews/{id}/workspace`

- **Purpose**: Show candidate's own simulated coding workspace.
- **Actor**: Assigned candidate for the interview's application.
- **Access**: Candidate can only view own interview.

### POST `/candidate/interviews/{id}/workspace`

- **Purpose**: Save candidate code or run notes.
- **Actor**: Assigned candidate.
- **Form fields**: `code_text`, `candidate_run_notes`, `version_number`.
- **Validates**: Candidate owns the application, session is within allowed preparation/interview window, CSRF token.
- **Audit**: Records `WORKSPACE_UPDATED`.

## Extension Requests

### POST `/interviewer/interviews/{id}/extensions`

- **Purpose**: Request additional time for technical issues.
- **Actor**: Assigned official interviewer.
- **Form fields**: `requested_minutes`, `request_reason`.
- **Validates**: Positive minutes, required reason, session is scheduled, no duplicate pending request by same actor.
- **Audit**: Records `EXTENSION_REQUESTED`.

### POST `/interviewer/interviews/{id}/extensions/{request}/cancel`

- **Purpose**: Cancel pending extension request before HR decision.
- **Actor**: Requesting interviewer.
- **Audit**: Records `EXTENSION_CANCELLED`.

### POST `/hr/interviews/{id}/extensions/{request}/approve`

- **Purpose**: Approve extra time.
- **Actor**: HR Admin.
- **Form fields**: `approved_minutes`, `decision_reason` optional unless changed from requested minutes, conflict acknowledgement if applicable.
- **Validates**: Request pending, positive approved minutes, conflict warning acknowledged when extension overlaps participant schedule.
- **Audit**: Records `EXTENSION_APPROVED`.

### POST `/hr/interviews/{id}/extensions/{request}/deny`

- **Purpose**: Deny extra time.
- **Actor**: HR Admin.
- **Form fields**: `decision_reason`.
- **Validates**: Request pending, reason required.
- **Audit**: Records `EXTENSION_DENIED`.

## Feedback Continuation

### GET `/interviewer/interviews/{id}/feedback`

- **Purpose**: Existing official feedback form remains restricted to non-shadowing official scorers.
- **Actor**: Assigned official interviewer.
- **Access**: Observers cannot submit official feedback.
