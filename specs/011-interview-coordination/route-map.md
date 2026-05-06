# Route Map: Interview Coordination Workflows

## Existing Routes To Extend

| Route | Controller Action | Change |
|-------|-------------------|--------|
| `GET /hr/interviews` | `HrInterviewController@index` | Include recommendation/workspace/extension status summaries where useful. |
| `GET /hr/applications/{id}/interviews/create` | `HrInterviewController@create` | Load recommendation form state and panel capability data. |
| `POST /hr/applications/{id}/interviews` | `HrInterviewController@store` | Save recommendation-aware assignments, briefing snapshot, override reasons, and audit events. |
| `GET /hr/interviews/{id}` | `HrInterviewController@show` | Show assignments, briefing, workspace summary, extension requests, and audit history. |
| `GET /hr/interviews/{id}/edit` | `HrInterviewController@edit` | Replace placeholder with reschedule/assignment form. |
| `PUT /hr/interviews/{id}` | `HrInterviewController@update` | Replace placeholder with reschedule/assignment update flow. |
| `POST /hr/interviews/{id}/cancel` | `HrInterviewController@cancel` | Replace placeholder with cancellation reason and audit. |
| `POST /hr/interviews/{id}/complete` | `HrInterviewController@complete` | Keep existing completion flow, include extension-adjusted timing context. |
| `GET /interviewer/interviews` | `InterviewerInterviewController@index` | Include workspace and extension status indicators. |
| `GET /interviewer/interviews/{id}` | `InterviewerInterviewController@show` | Show briefing snapshot and links to workspace/extension request. |
| `GET /interviewer/interviews/{id}/feedback` | `InterviewerInterviewController@feedback` | Preserve observer exclusion from official feedback. |
| `POST /interviewer/interviews/{id}/feedback` | `InterviewerInterviewController@storeFeedback` | Preserve observer exclusion from official feedback. |

## New HR Routes

| Route | Controller Action | Purpose |
|-------|-------------------|---------|
| `POST /hr/applications/{id}/interviews/recommendations` | `HrInterviewController@recommendPanel` | Generate workload/conflict-ranked panel recommendation snapshot. |
| `POST /hr/interviews/{id}/briefing/refresh` | `HrInterviewController@refreshBriefing` | Create or refresh interview briefing snapshot. |
| `GET /hr/interviews/{id}/workspace` | `HrInterviewController@workspace` | HR review of simulated coding workspace. |
| `POST /hr/interviews/{id}/workspace` | `HrInterviewController@saveWorkspace` | HR note/prompt updates where authorized. |
| `GET /hr/interviews/{id}/extensions/{request}` | `HrInterviewController@showExtension` | HR review page for pending/decided extension request. |
| `POST /hr/interviews/{id}/extensions/{request}/approve` | `HrInterviewController@approveExtension` | Approve extension and update effective session time. |
| `POST /hr/interviews/{id}/extensions/{request}/deny` | `HrInterviewController@denyExtension` | Deny extension with reason. |
| `GET /hr/interviews/{id}/audit` | `HrInterviewController@audit` | Dedicated audit history view if detail page becomes too dense. |

## New Interviewer Routes

| Route | Controller Action | Purpose |
|-------|-------------------|---------|
| `GET /interviewer/interviews/{id}/workspace` | `InterviewerInterviewController@workspace` | View and edit permitted workspace content. |
| `POST /interviewer/interviews/{id}/workspace` | `InterviewerInterviewController@saveWorkspace` | Save interviewer workspace updates. |
| `POST /interviewer/interviews/{id}/extensions` | `InterviewerInterviewController@requestExtension` | Request technical-issue extension. |
| `POST /interviewer/interviews/{id}/extensions/{request}/cancel` | `InterviewerInterviewController@cancelExtension` | Cancel own pending extension request. |

## New Candidate Routes

| Route | Controller Action | Purpose |
|-------|-------------------|---------|
| `GET /candidate/interviews/{id}` | `CandidateInterviewController@show` | Candidate view of own interview details. |
| `GET /candidate/interviews/{id}/workspace` | `CandidateInterviewController@workspace` | Candidate simulated live coding workspace. |
| `POST /candidate/interviews/{id}/workspace` | `CandidateInterviewController@saveWorkspace` | Save candidate code and run notes. |

## Policy Requirements

- HR Admin can manage interview scheduling, assignments, briefing snapshots, workspace oversight, extension decisions, and audit history.
- Assigned official interviewers can view assigned briefing/workspace, save permitted workspace sections, request/cancel extension requests, and submit official feedback.
- Assigned observers can view assigned briefing/workspace but cannot edit candidate-visible content or submit official feedback.
- Candidate can view and edit only their own interview workspace.
- Unassigned users receive 403 and may trigger `UNAUTHORIZED_ACCESS_DENIED` audit where the attempted interview is known.
