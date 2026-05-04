# Web Workflow Contracts: Technical Assessment Management

These contracts describe server-rendered page and form behavior. They are not REST API contracts and must be implemented through authenticated web routes, Blade views, redirects, sessions, CSRF protection, authorization policies, and server-side validation.

## HR Assessment Management

**Actor**: HR Admin  
**Purpose**: Create and maintain job-linked assessments.

### List Assessments for a Job

- **Entry**: HR opens a job requisition and chooses assessment management.
- **Inputs**: Existing job requisition identifier.
- **Authorization**: HR Admin only.
- **Success**: Page lists assessments, active status, duration, question count, and link to results.
- **Failure**: Unauthorized users are denied; missing jobs return the standard not-found handling.

### Create or Update Assessment

- **Inputs**: Job, title, instructions, type, duration, active/inactive status.
- **Validation**: Required title, existing job, positive duration, valid type.
- **Success**: Assessment is saved and HR is redirected to the assessment detail page with a success message.
- **Failure**: Form re-renders with field-level validation messages and no invalid data is saved.

### Create or Update Question

- **Inputs**: Question type, prompt, difficulty, points, MCQ options when applicable, expected answer or scoring reference when applicable.
- **Validation**: Required prompt, valid type, valid difficulty, positive points, at least two MCQ options and matching correct answer for MCQ.
- **Success**: Question is saved under the assessment and shown in the assessment detail page.
- **Failure**: Form re-renders with field-level validation messages.

## Candidate Assessment Attempt

**Actor**: Candidate  
**Purpose**: Start, complete, and submit an assigned assessment.

### Start or Resume Assessment

- **Entry**: Candidate opens an assessment link or an assessment action from their application page.
- **Inputs**: Candidate's application and assessment identifiers.
- **Authorization**: Authenticated Candidate who owns an application in `ASSESSMENT` status for the assessment's job.
- **Success**: System creates or resumes one attempt, snapshots active questions, randomizes display order, records start and expiry times, and shows the assessment page.
- **Failure**: Non-owner, wrong application status, inactive assessment, unavailable job, or duplicate terminal attempt shows a clear denial/status message.

### Save Answer Continuously

- **Inputs**: Attempt, snapshot question, answer text or selected choice.
- **Authorization**: Authenticated owner of an `IN_PROGRESS` attempt.
- **Success**: Latest answer is saved before the deadline and the candidate remains on the assessment page.
- **Failure**: Expired attempts are marked expired and reject changes; unauthorized users are denied; invalid question references are rejected.

### Submit Assessment

- **Inputs**: Attempt confirmation and latest saved answers.
- **Authorization**: Authenticated owner of an `IN_PROGRESS` attempt.
- **Success**: Attempt is marked `SUBMITTED`, answers are finalized, simulated score is calculated, and the result page labels the score as simulated.
- **Failure**: If the deadline has passed, attempt is marked `EXPIRED`, score is calculated from answers saved before the deadline, and further edits are blocked.

### Record Focus Event

- **Inputs**: Attempt and focus event type (`FOCUS_LOST` or `FOCUS_RETURNED`).
- **Authorization**: Authenticated owner of an `IN_PROGRESS` attempt.
- **Success**: Event is stored with a timestamp as simulated proctoring data.
- **Failure**: Expired or submitted attempts reject new events; unauthorized users are denied.

## HR Results Review

**Actor**: HR Admin  
**Purpose**: Review assessment outcome evidence for one job.

### Review Job Assessment Results

- **Inputs**: Job requisition and optional assessment filter.
- **Authorization**: HR Admin only.
- **Success**: Page shows candidates, application status, attempt status, simulated score, start/end/expiry timing, saved/submitted answer evidence, and focus-loss count.
- **Failure**: Unauthorized users are denied; missing job or assessment returns standard not-found handling.

### Review Attempt Detail

- **Inputs**: Candidate assessment attempt identifier.
- **Authorization**: HR Admin only for recruitment review; Candidate can view only their own result summary.
- **Success**: HR sees immutable question snapshots, candidate answers, scoring outcome, and simulated focus events.
- **Failure**: Candidate or unauthorized staff cannot view another candidate's attempt.

## Cross-Cutting Contract Rules

- All forms use CSRF protection and server-side validation.
- All authorization decisions use role middleware and policies.
- Simulated score and simulated proctoring labels must be visible on candidate and HR review pages.
- Final and expired attempts are read-only.
- Real code execution, webcam/video proctoring, plagiarism detection, email links, and external integrations are out of scope.
