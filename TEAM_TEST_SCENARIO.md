# Team Test Scenario

Use this document as the team demo and testing script for SRIM. The goal is to prove that the main recruitment flow works across HR admin, candidate, and interviewer roles.

## Test Setup

1. Confirm MySQL is running.
2. Confirm `.env` has the correct database settings.
3. Import `srim.sql` for the complete seeded demo database.
4. Start the app.

```bash
php -S 127.0.0.1:8000
```

5. Open `http://127.0.0.1:8000`.
6. Keep one browser or private window available for each role if the team wants to test roles side by side.

## Demo Users

All seeded users use password `password`.

| Role | Email | Main Purpose |
| --- | --- | --- |
| HR Admin | `hr.admin@example.com` | Runs the full HR workflow. |
| Department Head / HR Approver | `dana.head@example.com` | Reviews approval-style governance flows if needed. |
| Technical Interviewer | `omar.interviewer@example.com` | Reviews interviews and submits feedback. |
| Shadow Interviewer / Junior Staff | `mona.shadow@example.com` | Tests limited interview access. |
| Candidate | `lina.candidate@example.com` | Tests the candidate journey. |
| Additional Candidate | `karim.candidate@example.com` | Extra seeded candidate data. |
| Additional Candidate | `sara.candidate@example.com` | Extra seeded candidate data. |

## Test Goal

The team should verify this full flow:

1. HR prepares and manages a requisition.
2. Candidate applies or reviews an existing application.
3. HR screens candidates and reviews shortlist/duplicates.
4. HR manages assessments and assessment results.
5. HR schedules or reviews interviews.
6. Interviewer opens the interview workspace and submits feedback.
7. HR reviews feedback governance and final evaluation.
8. HR creates or reviews an offer.
9. Candidate accepts or rejects an offer.
10. HR creates or reviews onboarding.
11. Candidate reviews onboarding tasks.
12. HR verifies reports, compliance, audit logs, notifications, and user access.

## Part 1: HR Admin Flow

Log in as:

```text
hr.admin@example.com
password
```

### 1. Dashboard

1. Open `Dashboard`.
2. Confirm the HR dashboard loads without errors.
3. Review the visible summary cards, recent activity, or workflow shortcuts.

Expected result: HR lands on the HR dashboard and sees HR-level navigation.

### 2. Recruitment And Requisitions

1. Open `Recruitment`.
2. Review the requisition list.
3. Open an existing requisition.
4. Confirm the requisition detail page shows job information, status, and workflow actions.
5. If safe for the demo, create or edit a requisition.
6. Submit, approve, publish, unpublish, open, or close only when the team agrees that changing demo data is acceptable.
7. Open approval queue if available.
8. Open version history.
9. Compare versions if available.
10. Open sync history and governance audit if available.

Expected result: HR can inspect and manage requisition governance from the requisition workflow.

### 3. Applications And Screening

1. From a requisition, open its applications.
2. Review candidate applications and statuses.
3. Update an application status only if the test plan allows data changes.
4. Open screening configuration.
5. Review screening criteria and weights.
6. Recalculate screening scores if safe for the demo.
7. Open triage preview.
8. Execute triage only if the team agrees to change seeded data.
9. Open shortlist.
10. Open duplicate detection.
11. Resolve a duplicate case only if the team agrees to change seeded data.
12. Open screening audit.

Expected result: HR can inspect scoring, triage, shortlist, duplicate detection, and screening audit without broken pages.

### 4. Assessments

1. From a requisition, open assessments.
2. Review the assessment list.
3. Open an assessment detail page.
4. Review assessment questions.
5. Create or edit an assessment/question only if data changes are allowed.
6. Open assessment results.
7. Open a candidate assessment attempt if available.

Expected result: HR can manage assessments and review candidate assessment results.

### 5. Interviews

1. Open `Interviews`.
2. Review the interview list.
3. Open an interview detail page.
4. Confirm candidate information, schedule, interview status, panel members, and briefing data appear.
5. Refresh briefing only if changing demo data is acceptable.
6. Open the interview workspace.
7. Review any extension requests.
8. Approve or deny an extension only if that is part of the test.
9. Mark an interview complete only if the team agrees to change the record.
10. Open interview audit if available.

Expected result: HR can coordinate interviews, inspect the workspace, manage extensions, and view audit records.

### 6. Feedback And Final Evaluation

1. Open `Feedback`.
2. Review feedback governance items.
3. Open a feedback detail page if available.
4. Resolve feedback flags only if changing demo data is acceptable.
5. Open a candidate final evaluation from an application or feedback workflow.
6. Review competency gaps, debrief readiness, sentiment, recommendation, and final decision information.
7. Save a final evaluation only if the team is testing data changes.

Expected result: HR can review feedback quality, governance concerns, candidate sentiment, and final evaluation.

### 7. Offers, Background Checks, Referrals, And Onboarding

1. Open `Offers`.
2. Review existing offers.
3. Open an offer detail page.
4. Generate or view an offer letter.
5. Send an offer only if the test plan allows state changes.
6. Create an offer from an eligible application only if needed.
7. Open background checks from an application.
8. Request or complete a background check only if data changes are allowed.
9. Open referrals.
10. Review or create a referral.
11. Approve, reject, or mark referral rewards paid only if testing those actions.
12. Open onboarding.
13. Review onboarding records.
14. Create onboarding from an accepted offer only if needed.
15. Open an onboarding detail page and review tasks/documents.

Expected result: HR can move from offer review to post-offer checks and onboarding.

### 8. Compliance, Reports, Notifications, And Administration

1. Open `Compliance`.
2. Review compliance checks.
3. Run checks only if the team agrees to change demo data.
4. Open diversity reporting.
5. Open data retention.
6. Do not anonymize or delete candidates unless this is an intentional destructive test.
7. Open audit logs.
8. Confirm important HR actions are visible.
9. Open reports: pipeline, time-to-hire, and bottlenecks.
10. Open notifications.
11. Mark notifications read only if state changes are allowed.
12. Open `Administration`.
13. Review users.
14. Create users or update access only if the test plan requires it.
15. Log out.

Expected result: HR can access compliance, reporting, audit, notifications, and user administration.

## Part 2: Candidate Flow

Log in as:

```text
lina.candidate@example.com
password
```

### 1. Dashboard And Profile

1. Open `Dashboard`.
2. Confirm candidate dashboard loads.
3. Open `My Profile`.
4. Review candidate profile data.
5. Update the profile only if the team is testing profile changes.

Expected result: Candidate can view personal dashboard and profile.

### 2. Jobs And Applications

1. Open `Open Jobs`.
2. Review available jobs.
3. Open a job detail page.
4. Apply only if the test plan allows creating a new application.
5. Open `My Applications`.
6. Open an application detail page.
7. Confirm application status, requisition details, and available next steps.

Expected result: Candidate can browse jobs and track applications.

### 3. Assessments

1. From an application, open an assigned assessment if available.
2. Start the assessment only if testing an active attempt.
3. Save answers if the assessment is active.
4. Submit only if the team agrees to change the attempt status.
5. Open the assessment result page after submission or for an existing completed attempt.

Expected result: Candidate can access assigned assessments and view results when available.

### 4. Interviews And Sentiment

1. Open an interview from the application or dashboard if available.
2. Review interview details, schedule, status, and instructions.
3. Open the interview workspace if available.
4. Save workspace content only if testing that workflow.
5. Open candidate sentiment page.
6. Submit sentiment only if the test plan allows data changes.

Expected result: Candidate can review interviews and submit sentiment when allowed.

### 5. Offers And Onboarding

1. Open an offer if available.
2. Review offer details and letter.
3. Accept or reject only if the team intends to change the offer status.
4. Open `Onboarding`.
5. Review onboarding welcome/details and task list.
6. Complete a task only if the team is testing onboarding progress.
7. Open notifications.
8. Confirm candidate notifications appear.
9. Log out.

Expected result: Candidate can review offers, respond when allowed, and access onboarding.

## Part 3: Interviewer Flow

Log in as:

```text
omar.interviewer@example.com
password
```

### 1. Dashboard And Assigned Interviews

1. Open `Dashboard`.
2. Confirm interviewer dashboard loads.
3. Open `My Interviews`.
4. Review assigned interviews.
5. Open an interview detail page.

Expected result: Interviewer sees only assigned interview work.

### 2. Workspace, Extension, And Feedback

1. Review candidate information, schedule, briefing, and interview status.
2. Open the interview workspace.
3. Save workspace content only if the test plan allows it.
4. Request an extension only if testing extension workflow.
5. Open feedback form.
6. Enter feedback scores and notes.
7. Submit feedback only if the team wants to complete the feedback action.
8. Open notifications.
9. Confirm interviewer notifications appear.
10. Log out.

Expected result: Interviewer can review assigned interviews, use the workspace, request extensions, and submit feedback.

## Optional: Junior Staff / Shadow Interviewer Check

Log in as:

```text
mona.shadow@example.com
password
```

1. Open `My Interviews`.
2. Confirm the user can only access permitted interview records.
3. Open an assigned interview.
4. Confirm feedback or workspace actions match the limited role.
5. Try opening an HR-only URL manually.

Expected result: limited users should not access HR-only workflows.

## End-To-End Acceptance Checklist

Use this checklist before saying the demo is ready:

| Area | Pass Criteria |
| --- | --- |
| Authentication | Login and logout work for HR, candidate, and interviewer accounts. |
| Role Navigation | Each role sees the correct top navigation. |
| Recruitment | HR can open requisitions, applications, governance, screening, and assessments. |
| Candidate Flow | Candidate can view jobs, applications, assessments, interviews, offers, onboarding, and notifications. |
| Interview Flow | Interviewer can open assigned interviews, workspace, extensions, and feedback. |
| Feedback | HR can review feedback governance and final evaluation. |
| Offers | HR can review/create offers and candidate can review/respond to offers. |
| Onboarding | HR and candidate onboarding pages load and show task progress. |
| Compliance | Compliance, diversity, retention, audit logs, and reports load. |
| Notifications | Notifications appear and can be marked read where allowed. |
| Authorization | Unauthorized pages redirect or show an access error. |
| UI | No broken layout, raw PHP warning, SQL error, or missing view appears. |

## Suggested Live Demo Order

1. HR Admin: dashboard and recruitment overview.
2. HR Admin: requisition detail, applications, screening, shortlist, duplicates.
3. HR Admin: assessments and assessment results.
4. Candidate: jobs, application detail, assessment/interview/offer/onboarding views.
5. Interviewer: assigned interview, workspace, extension request, feedback.
6. HR Admin: interviews, feedback governance, final evaluation.
7. HR Admin: offers, background checks, referrals, onboarding.
8. HR Admin: compliance, reports, audit logs, notifications, user access.

## Data Safety Notes

- Reviewing pages is safe.
- Actions such as submit, approve, reject, publish, archive, anonymize, delete, accept offer, reject offer, complete task, or mark paid change seeded data.
- For a clean demo reset, re-import `srim.sql`.
- Do not run destructive retention actions unless the team intentionally tests them.
