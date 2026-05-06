# Web Contracts: Feedback Governance Analytics

This feature exposes server-rendered web routes and form submissions only. There is no REST API contract.

## HR Evaluation Governance Report

- **Route**: `GET /hr/evaluations/{applicationId}/governance`
- **Name**: `hr.evaluations.governance`
- **Actor**: HR Admin
- **Purpose**: View official feedback completeness, raw scores, normalized scores, fallback reasons, serious flags, candidate sentiment, benchmark warnings, competency gaps, debrief state, recommendation state, and governance audit trail.
- **Success State**: Page renders within 2 seconds for demo data and shows decision blockers before approval actions.
- **Denied State**: Non-HR users receive an authorization error.

## HR Recalculate Governed Evaluation

- **Route**: `POST /hr/evaluations/{applicationId}/governance/recalculate`
- **Name**: `hr.evaluations.governance.recalculate`
- **Actor**: HR Admin
- **CSRF**: Required
- **Inputs**: `reason` when recalculation changes an approved or previously reviewed result.
- **Validation**: Application must exist; HR must be authorized; official feedback completeness and open flag blockers must be evaluated.
- **Success State**: New normalized snapshot and audit event are recorded, then HR is redirected to the governance report.

## Interviewer Serious Concern Flag

- **Route**: `GET /interviewer/interviews/{interviewId}/flag`
- **Name**: `interviewer.interviews.flag.create`
- **Actor**: Assigned official interviewer
- **Purpose**: Render concern flag form.
- **Denied State**: Unassigned users, observers, and candidates cannot access the form.

- **Route**: `POST /interviewer/interviews/{interviewId}/flag`
- **Name**: `interviewer.interviews.flag.store`
- **Actor**: Assigned official interviewer
- **CSRF**: Required
- **Inputs**: `category`, `severity`, `explanation`
- **Validation**: Category and severity must be allowed values; explanation is required; interview assignment must be official.
- **Success State**: Flag is open, HR receives in-system notification, final decision actions are blocked, and audit event is recorded.

## HR Serious Concern Review

- **Route**: `GET /hr/evaluations/{applicationId}/flags`
- **Name**: `hr.evaluations.flags`
- **Actor**: HR Admin
- **Purpose**: Review open and resolved serious concern flags for an application.

- **Route**: `POST /hr/evaluations/{applicationId}/flags/{flagId}/resolve`
- **Name**: `hr.evaluations.flags.resolve`
- **Actor**: HR Admin
- **CSRF**: Required
- **Inputs**: `resolution_status`, `resolution_rationale`
- **Validation**: Flag must be open; rationale is required; resolution status must be allowed.
- **Success State**: Flag is resolved, blockers are recalculated, notifications are created when relevant, and audit event is recorded.

## Candidate Sentiment

- **Route**: `GET /candidate/interviews/{interviewId}/sentiment`
- **Name**: `candidate.interviews.sentiment.create`
- **Actor**: Candidate
- **Purpose**: Render post-interview sentiment form for the candidate's own completed interview.
- **Denied State**: Interview not completed, candidate mismatch, or duplicate submission.

- **Route**: `POST /candidate/interviews/{interviewId}/sentiment`
- **Name**: `candidate.interviews.sentiment.store`
- **Actor**: Candidate
- **CSRF**: Required
- **Inputs**: `rating`, `comment`
- **Validation**: Rating is required and in allowed range; comment is length-limited; one submission per candidate/interview.
- **Success State**: Sentiment is saved, excluded from score calculations, acknowledged to candidate, visible to HR, and audit-recorded.

## HR Debrief Record

- **Route**: `GET /hr/evaluations/{applicationId}/debrief`
- **Name**: `hr.evaluations.debrief`
- **Actor**: HR Admin
- **Purpose**: View or complete the in-app debrief record created after all official feedback is submitted.

- **Route**: `POST /hr/evaluations/{applicationId}/debrief`
- **Name**: `hr.evaluations.debrief.store`
- **Actor**: HR Admin
- **CSRF**: Required
- **Inputs**: `participants`, `consensus_level`, `dissent_notes`, `final_recommendation`, `rationale`, `next_action`
- **Validation**: All official feedback must be submitted; no open serious concern flags may exist; required fields must be present; rationale is required.
- **Success State**: Debrief is completed, final recommendation approval may proceed according to policy, and audit event is recorded.

## HR Competency Benchmarks

- **Route**: `GET /hr/jobs/{jobId}/competency-benchmarks`
- **Name**: `hr.jobs.competency-benchmarks.edit`
- **Actor**: HR Admin
- **Purpose**: Maintain ideal competency benchmarks for a job.

- **Route**: `POST /hr/jobs/{jobId}/competency-benchmarks`
- **Name**: `hr.jobs.competency-benchmarks.update`
- **Actor**: HR Admin
- **CSRF**: Required
- **Inputs**: Repeating competency rows with `competency`, `benchmark_score`, optional `weight`
- **Validation**: Competency names are required and unique per job; benchmark scores are 0-10; weights are optional and non-negative.
- **Success State**: Benchmarks are saved and audit-recorded.

## HR Feedback Governance Report

- **Route**: `GET /hr/reports/feedback-governance`
- **Name**: `hr.reports.feedback-governance`
- **Actor**: HR Admin
- **Purpose**: View aggregate governance analytics: pending feedback, open flags, normalization fallback counts, sentiment averages, debrief completion, and benchmark gaps.
- **Filters**: Date range, job, status, interviewer, flag status.
- **Success State**: Report renders as server-side tables/cards within 2 seconds for demo data.
