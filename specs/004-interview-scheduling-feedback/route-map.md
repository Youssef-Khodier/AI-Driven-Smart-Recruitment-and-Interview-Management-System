# Route Map: Interview Scheduling Feedback

## HR Routes

| Method | Path | Controller Action | Route Name | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/hr/interviews` | `HrInterviewController::index` | `hr.interviews.index` | List interviews and feedback completion state |
| GET | `/hr/applications/{application}/interviews/create` | `HrInterviewController::create` | `hr.interviews.create` | Show schedule form for `INTERVIEW` application |
| POST | `/hr/applications/{application}/interviews` | `HrInterviewController::store` | `hr.interviews.store` | Save schedule and panel assignments |
| GET | `/hr/interviews/{interview}` | `HrInterviewController::show` | `hr.interviews.show` | Review schedule, briefing summary, feedback, audit |
| GET | `/hr/interviews/{interview}/edit` | `HrInterviewController::edit` | `hr.interviews.edit` | Show reschedule form before feedback exists |
| PUT | `/hr/interviews/{interview}` | `HrInterviewController::update` | `hr.interviews.update` | Save reschedule and assignment changes |
| POST | `/hr/interviews/{interview}/cancel` | `HrInterviewController::cancel` | `hr.interviews.cancel` | Mark scheduled interview cancelled |
| POST | `/hr/interviews/{interview}/complete` | `HrInterviewController::complete` | `hr.interviews.complete` | Mark scheduled interview completed |

## Interviewer And Observer Routes

| Method | Path | Controller Action | Route Name | Purpose |
|--------|------|-------------------|------------|---------|
| GET | `/interviewer/interviews` | `InterviewerInterviewController::index` | `interviewer.interviews.index` | Show assigned interviews only |
| GET | `/interviewer/interviews/{interview}` | `InterviewerInterviewController::show` | `interviewer.interviews.show` | Show assigned briefing |
| GET | `/interviewer/interviews/{interview}/feedback` | `InterviewerInterviewController::feedback` | `interviewer.interviews.feedback.create` | Show official feedback form |
| POST | `/interviewer/interviews/{interview}/feedback` | `InterviewerInterviewController::storeFeedback` | `interviewer.interviews.feedback.store` | Submit official feedback |

## Policy Summary

- HR routes require active HR Admin.
- Interviewer routes require active user assigned to the interview.
- Feedback routes require assigned `PANEL_LEAD` or `INTERVIEWER`, not `OBSERVER`.
- Candidate users cannot access HR, interviewer briefing, assignment, or feedback routes.
- Reschedule is denied once any official feedback exists.

## Validation Summary

- Create/update schedule validates CSRF, `INTERVIEW` application status, future date/time, positive duration, at least one official scorer, active users, allowed panel roles, duplicate assignment prevention, and conflict blocking.
- Complete validates current interview status `SCHEDULED`.
- Cancel validates current interview status `SCHEDULED`.
- Feedback validates CSRF, completed interview, official assignment, one feedback per official interviewer, and scores from 0 to 10.
