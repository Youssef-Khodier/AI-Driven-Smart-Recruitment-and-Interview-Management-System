# Route Map: Feedback Governance Analytics

## Existing Routes To Extend

- `routes/web.php` interview feedback routes for assigned interviewer feedback submission.
- `routes/web.php` HR final evaluation routes for application-level decision review.
- `routes/web.php` HR report routes for analytics pages.
- `routes/web.php` candidate interview routes for candidate-owned interview details.
- `routes/web.php` audit log routes for consolidated audit review.

## New Route Groups

### HR Evaluation Governance

| Name | Method | Path | Controller Action | View |
|------|--------|------|-------------------|------|
| `hr.evaluations.governance` | GET | `/hr/evaluations/{applicationId}/governance` | `HrFinalEvaluationController::governance` | `views/hr/evaluations/governance.php` |
| `hr.evaluations.governance.recalculate` | POST | `/hr/evaluations/{applicationId}/governance/recalculate` | `HrFinalEvaluationController::recalculateGovernance` | Redirect |
| `hr.evaluations.flags` | GET | `/hr/evaluations/{applicationId}/flags` | `HrFinalEvaluationController::flags` | `views/hr/evaluations/flags.php` |
| `hr.evaluations.flags.resolve` | POST | `/hr/evaluations/{applicationId}/flags/{flagId}/resolve` | `HrFinalEvaluationController::resolveFlag` | Redirect |
| `hr.evaluations.debrief` | GET | `/hr/evaluations/{applicationId}/debrief` | `HrFinalEvaluationController::debrief` | `views/hr/evaluations/debrief.php` |
| `hr.evaluations.debrief.store` | POST | `/hr/evaluations/{applicationId}/debrief` | `HrFinalEvaluationController::storeDebrief` | Redirect |

### Interviewer Concern Flags

| Name | Method | Path | Controller Action | View |
|------|--------|------|-------------------|------|
| `interviewer.interviews.flag.create` | GET | `/interviewer/interviews/{interviewId}/flag` | `InterviewerInterviewController::createFlag` | `views/interviewer/interviews/flag.php` |
| `interviewer.interviews.flag.store` | POST | `/interviewer/interviews/{interviewId}/flag` | `InterviewerInterviewController::storeFlag` | Redirect |

### Candidate Sentiment

| Name | Method | Path | Controller Action | View |
|------|--------|------|-------------------|------|
| `candidate.interviews.sentiment.create` | GET | `/candidate/interviews/{interviewId}/sentiment` | `CandidateInterviewController::createSentiment` | `views/candidate/interviews/sentiment.php` |
| `candidate.interviews.sentiment.store` | POST | `/candidate/interviews/{interviewId}/sentiment` | `CandidateInterviewController::storeSentiment` | Redirect |

### HR Benchmarks And Reports

| Name | Method | Path | Controller Action | View |
|------|--------|------|-------------------|------|
| `hr.jobs.competency-benchmarks.edit` | GET | `/hr/jobs/{jobId}/competency-benchmarks` | `HrFinalEvaluationController::editBenchmarks` | `views/hr/evaluations/benchmarks.php` |
| `hr.jobs.competency-benchmarks.update` | POST | `/hr/jobs/{jobId}/competency-benchmarks` | `HrFinalEvaluationController::updateBenchmarks` | Redirect |
| `hr.reports.feedback-governance` | GET | `/hr/reports/feedback-governance` | `HrReportController::feedbackGovernance` | `views/hr/reports/feedback-governance.php` |

## Authorization Summary

- HR Admin can view and manage governance reports, flags, debrief records, benchmarks, recommendation approval, audit history, and governance analytics.
- Assigned official interviewers can submit feedback and create concern flags only for assigned interviews.
- Candidates can submit sentiment only for their own completed interviews.
- Observers and junior staff can view only explicitly authorized training/read-only pages and never count as official scorers.

## Validation Summary

- All POST routes require CSRF.
- All candidate/application/interview IDs must be server-validated against the authenticated user's role and assignment.
- Duplicate restricted submissions are rejected: one feedback per interviewer/interview, one sentiment per candidate/interview, one debrief record per evaluation.
- Open serious concern flags block final decision actions.
