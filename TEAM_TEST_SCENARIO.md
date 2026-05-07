# Team Test Scenario

## Test Users

Use these seeded accounts:

| Role | Email | Password |
| --- | --- | --- |
| HR Admin | `hr.admin@example.com` | `password` |
| Candidate | `lina.candidate@example.com` | `password` |
| Technical Interviewer | `omar.interviewer@example.com` | `password` |

## Before Testing

1. Make sure `.env` has the correct database settings.
2. Import the database schema.
3. Seed the demo data.
4. Start the local PHP server.
5. Open the app in the browser.

## Scenario Goal

Test the main recruitment flow from three sides:

- HR Admin manages recruitment, screening, interviews, offers, onboarding, compliance, reports, and users.
- Candidate browses jobs, applies, completes assessments, checks interviews, reviews offers, and completes onboarding.
- Interviewer reviews assigned interviews, opens interview workspaces, requests extensions, and submits feedback.

## Part 1: HR Admin Test Flow

1. Log in as `hr.admin@example.com`.
2. Open the HR dashboard.
3. Go to `Recruitment`.
4. Open an existing requisition.
5. Review requisition details.
6. Check approval or publishing actions if available.
7. Review version history if available.
8. Open applications for the requisition.
9. Review candidate applications.
10. Open screening tools.
11. Review screening configuration.
12. Run or review triage results if available.
13. Review shortlist results.
14. Open duplicate candidate detection.
15. Resolve a duplicate candidate case if available.
16. Open assessments.
17. Review assessment list and assessment details.
18. Review assessment results.
19. Go to `Interviews`.
20. Open an interview record.
21. Review interview details, panel members, briefing information, and status.
22. Schedule or update an interview if the action is available.
23. Review any interview extension requests.
24. Open `Feedback`.
25. Review feedback governance items.
26. Open final evaluation for a candidate if available.
27. Review competency gaps, sentiment, recommendations, and final decision information.
28. Open `Offers`.
29. Create or review an offer.
30. Review offer letter details.
31. Review offer revision or negotiation history if available.
32. Open background checks.
33. Run or review background check results if available.
34. Open referrals.
35. Create or review referral information.
36. Open onboarding.
37. Create or review onboarding tasks for an accepted candidate.
38. Open `Compliance`.
39. Run or review compliance checks.
40. Review diversity reporting.
41. Review data retention tools.
42. Open audit logs.
43. Confirm important actions are recorded.
44. Open notifications.
45. Confirm system notifications appear for workflow activity.
46. Open user management.
47. Review users, roles, and access controls.
48. Log out.

## Part 2: Candidate Test Flow

1. Log in as `lina.candidate@example.com`.
2. Open the candidate dashboard.
3. Open profile.
4. Review candidate profile information.
5. Go to jobs.
6. Open an available job.
7. Apply to a job if the option is available.
8. Go to applications.
9. Open an application.
10. Confirm the application status and details.
11. Go to assessments.
12. Open an assigned assessment if available.
13. Start or review the assessment.
14. Submit answers if the assessment is available for testing.
15. Open assessment result after submission.
16. Go to interviews.
17. Open an interview.
18. Review schedule, status, and interview details.
19. Review candidate sentiment page if available.
20. Go to offers.
21. Open an offer if available.
22. Review offer details and letter.
23. Accept or reject the offer only if the test plan allows changing seeded data.
24. Go to onboarding.
25. Open onboarding welcome or task list.
26. Review onboarding tasks and document status.
27. Open notifications.
28. Confirm candidate notifications appear.
29. Log out.

## Part 3: Interviewer Test Flow

1. Log in as `omar.interviewer@example.com`.
2. Open the interviewer dashboard.
3. Go to `My Interviews`.
4. Review assigned interviews.
5. Open an interview.
6. Review candidate information.
7. Review interview schedule and status.
8. Review briefing information.
9. Open the interview workspace if available.
10. Check coding workspace or interview workspace content.
11. Request an extension if the action is available and appropriate for testing.
12. Return to the interview details page.
13. Open feedback form.
14. Enter feedback scores and notes.
15. Submit feedback.
16. Confirm the interview status or feedback status updated.
17. Open notifications.
18. Confirm interviewer notifications appear.
19. Log out.

## End-to-End Checks

1. HR Admin should be able to see candidate progress after candidate actions.
2. HR Admin should be able to see interviewer feedback after interviewer submission.
3. Candidate should be able to see relevant application, interview, offer, and onboarding updates.
4. Interviewer should only see assigned interview work.
5. Unauthorized pages should show an access error or redirect.
6. Audit logs should record important HR actions.
7. Notifications should appear for major workflow events.
8. Reports should show recruitment pipeline and time-to-hire data.
9. Compliance pages should load without errors.
10. No page should show raw PHP errors, database errors, or broken layout.

## Suggested Testing Order

1. HR Admin reviews existing seeded data.
2. Candidate applies or reviews an existing application.
3. HR Admin screens and shortlists the candidate.
4. HR Admin schedules or reviews an interview.
5. Interviewer submits interview feedback.
6. HR Admin reviews feedback and final evaluation.
7. HR Admin creates or reviews an offer.
8. Candidate reviews and responds to the offer.
9. HR Admin creates or reviews onboarding.
10. Candidate reviews onboarding tasks.
11. HR Admin checks compliance, reports, audit logs, notifications, and user access.
