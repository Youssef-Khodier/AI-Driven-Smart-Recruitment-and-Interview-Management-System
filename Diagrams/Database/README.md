# SRIM ERD-Based Database Schema

This database schema follows the ERD provided for the **AI-Driven Smart Recruitment & Interview Management System**.

## File

- `schema.sql`: MySQL 8+ schema based on the ERD tables and relationships.
- `schema-erd.svg`: Viewable ERD image generated from the schema.

## Tables

| # | Table | Purpose |
| :--- | :--- | :--- |
| 1 | `users` | Main accounts for HR admins, interviewers, and candidates. |
| 2 | `departments` | Company departments, including optional parent departments. |
| 3 | `job_requisitions` | Job openings created by HR and linked to departments. |
| 4 | `candidates` | Candidate-specific profile data. The primary key is also a foreign key to `users.user_id`. |
| Optional | `candidate_merge_log` | Logs duplicate candidate merges. |
| 5 | `applications` | Candidate applications for job requisitions. |
| 6 | `assessments` | Assessment definitions for jobs. |
| 7 | `questions` | Questions belonging to assessments. |
| 8 | `candidate_assessments` | Assessment attempts by candidates. |
| 9 | `submissions` | Candidate answers/code submissions. |
| 10 | `interviews` | Scheduled interviews for applications. |
| 11 | `interviewers_assignment` | Interview panel assignment and shadowing. |
| 12 | `interview_feedback` | Interviewer feedback and scores. |
| 13 | `final_evaluations` | Final decision for an application. |
| 14 | `offers` | Offer packages for accepted applications. |
| 15 | `onboarding` | Onboarding status after accepted offers. Candidate is reached through `offer -> application -> candidate`. |
| 16 | `notifications` | User notifications. |

## Missed Or Unclear Relations In The ERD

1. `Users` to `Departments` is drawn, but `users` did not show a `department_id` column.
   The schema adds `users.department_id` as a nullable foreign key to `departments.department_id`.

2. `Final_Evaluations` and `Offers` both link to `Applications` through `application_id`, but the ERD visually places them after each other.
   The schema keeps both as direct one-to-one relations with `applications` using `UNIQUE(application_id)`.

3. `Interview_Feedback` links to `Users` through `interviewer_id`, but the ERD does not force that the same user exists in `Interviewers_Assignment`.
   The schema keeps the ERD design. If stricter validation is required, add `assignment_id` to `interview_feedback`.

4. `Candidate_Assessments` has `candidate_id` and `assessment_id`, but no direct `application_id`.
   The schema keeps the ERD design. The related application can be inferred through candidate plus the assessment's job, but adding `application_id` would make reporting easier.

5. The ERD shows `Onboarding.candidate_id`, but this duplicates data because the candidate is already known through `onboarding -> offers -> applications -> candidates`.
   The schema removes `candidate_id` from `onboarding` and keeps only `offer_id` as the foreign key.

## How To Run

From MySQL:

```sql
SOURCE Database/schema.sql;
```

From terminal:

```bash
mysql -u root -p < Database/schema.sql
```

The script creates a database named `srim` and replaces the ERD tables if they already exist.
