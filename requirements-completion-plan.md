# Requirements Completion Plan

## Purpose

This plan extends the existing `spec workflow.md` roadmap to finish the requirement functions listed in `Diagrams/document.md` that are currently missing or only partially implemented.

The current application already covers the Vanilla PHP MVC foundation, authentication and RBAC, job requisitions, applications, assessments, interviews, feedback, offers, onboarding, notifications, reports, audit logs, and data retention. This roadmap should therefore be treated as a completion roadmap, not a restart.

## Project Rules

- Keep the application as a framework-free Vanilla PHP monolithic MVC system.
- Use server-rendered PHP templates, `routes/web.php`, controllers, repositories, policies, native sessions, CSRF protection, server-side validation, and MySQL through PDO.
- Do not build REST APIs, a SPA, a separated frontend, a runtime framework dependency, queues, or background workers unless the constitution is amended first.
- Keep advanced integrations simulated unless explicitly changed by the team: AI ranking, proctoring, plagiarism detection, code execution, job-board sync, background checks, email, and schedulers.
- Before each phase, read the relevant baseline files in `Diagrams/`: `document.md`, SRS, database schema/ERD, use-case diagram, activity diagrams, class diagram, object diagram, and system architecture diagram.
- Each phase must produce a Spec Kit spec, plan, tasks, implementation, verification evidence, and peer review before the next phase starts.

## Standard Vanilla PHP Plan Prompt

Use this in `/speckit.plan` for every completion phase:

```text
The application uses framework-free Vanilla PHP as a monolithic full-stack MVC application. Backend and frontend are implemented in the same PHP project using routes/web.php, controllers, PHP templates, PDO-backed repositories, SQL schema files, validation, policies/middleware-style guards, native sessions, CSRF protection, and server-rendered pages. Do not build REST APIs or a separated frontend. Use MySQL as the database. AI features, proctoring, plagiarism detection, job-board sync, background checks, code execution, email, calendar, and scheduler behavior are simulated unless this phase explicitly scopes a real integration and the constitution permits it.
```

## Spec Kit Command Flow

Run this sequence for each phase:

```text
/speckit.specify
/speckit.clarify
/speckit.plan
/speckit.tasks
/speckit.analyze
/speckit.implement
```

Do not combine phases during implementation. Only move forward after the current phase is implemented, reviewed, and verified.

## Current Gap Summary

### Missing Functions

- 4. Application Deduplication Logic
- 5. AI-Ranked Shortlisting (Simulated)
- 7. External Job-Board Sync Manager
- 12. Plagiarism Detection Logic (Simulated)
- 13. Dynamic Difficulty Adjustment
- 18. Live Coding Environment Sync
- 20. Session Extension Protocol
- 21. Interviewer Load Balancer
- 23. Score Normalization Algorithm
- 24. Candidate Red-Flag Escalation
- 25. Consensus Meeting Automator
- 26. Post-Interview Sentiment Logger
- 27. Competency Gap Visualizer
- 30. Digital Offer-Letter Generator
- 33. Referral Reward Attribution
- 34. Background Check Integration (Simulated)
- 38. Diversity & Inclusion Audit Reporter
- 40. Template Versioning Manager
- 41. Database Integrity Manager

### Partial Functions To Complete

- 1. Automated Screening Triage
- 2. Dynamic Skill-Weighting Engine
- 3. Job Requisition Approval Workflow
- 6. Pipeline Throughput Analytics
- 9. Randomized Question-Bank Generator
- 10. Timed-Session Heartbeat
- 11. Code-Execution Output Validator (Simulated)
- 14. Assessment Cool-down Manager
- 16. Multi-Representative Panel Builder
- 29. Offer Package Calculator
- 31. Offer Validity Timer
- 32. Counter-Offer Negotiation Tracker
- 35. Pre-Onboarding Welcome Portal
- 42. Automated Notification Escalator

## Phase 7: Screening, Deduplication, Shortlisting (COMPLETED)

Goal: complete the recruitment pipeline and triage functions.

Functions covered: 1, 2, 4, 5.

```text
/speckit.specify Complete the screening and shortlisting workflow for SRIM in server-rendered Vanilla PHP. HR admins can configure skill weights and match thresholds per job requisition. The system recalculates simulated match scores using weighted job skills and candidate profile data, identifies possible duplicate candidate profiles, records candidate merge decisions in an audit log, generates an AI-ranked simulated shortlist for each requisition, and supports automated triage from APPLIED to SCREENING, ASSESSMENT, INTERVIEW, or REJECTED using configurable thresholds. All workflows must remain form-based, auditable, RBAC-protected, and simulated where AI is mentioned.
```

Expected implementation scope:

- Add job-level skill weights and match threshold storage.
- Add candidate duplicate detection by normalized email, phone, name, resume URL, and skill overlap.
- Add `candidate_merge_log` or equivalent merge/audit table aligned with the ERD baseline.
- Add HR pages to review duplicate candidates and approve/skip merges.
- Add shortlist page showing ranked candidates by match score, experience, and keyword coverage.
- Add manual HR-triggered automated triage action with status-history records and notifications.

Acceptance evidence:

- HR can configure weights and thresholds for a requisition.
- Applying or recalculating changes match scores based on configured weights.
- Duplicate candidates are detected and merge/skip decisions are audited.
- HR can generate and review a top-ranked shortlist.
- Triage updates application statuses and writes audit/status history.

## Phase 8: Advanced Requisition Workflow And Job-Board Sync

Goal: finish requisition approval and external posting simulation.

Functions covered: 3, 7, 40.

```text
/speckit.specify Complete advanced job requisition governance in server-rendered Vanilla PHP. HR admins can submit requisitions through a multi-tier approval workflow, department heads can approve or reject within their department, the system records template versions for job descriptions and rubrics, and HR can simulate publishing approved job posts to external job boards. Job-board sync is simulated with local records showing target platform, payload summary, sync status, and timestamp. All changes must be auditable and must not introduce external API calls.
```

Expected implementation scope:

- Add department-head or approver designation for users/departments.
- Add approval steps and approval history for requisitions.
- Add versioned job description/rubric template records.
- Add simulated job-board sync records for platforms such as LinkedIn, Indeed, and company site.
- Add HR pages for approval status, template versions, and sync history.

Acceptance evidence:

- A requisition cannot open until required approval steps pass.
- Approval/rejection actions are role-checked and audited.
- Template versions can be viewed and linked to requisitions.
- Job-board sync creates local simulated sync records without external calls.

## Phase 9: Assessment Integrity And Adaptive Testing

Goal: complete assessment proctoring, randomization, scoring, plagiarism, and retake controls.

Functions covered: 9, 10, 11, 12, 13, 14.

```text
/speckit.specify Complete advanced assessment integrity and adaptive testing in server-rendered Vanilla PHP. HR admins can define question-bank rules by difficulty tier and question count. Candidates receive randomized tests according to those rules, a browser timer heartbeat saves remaining time and expires attempts, simulated code-output validation compares answers against hidden expected output records, simulated plagiarism detection compares submissions against common-answer records, dynamic difficulty suggestions are calculated from previous scores, and cooldown rules prevent retakes for a configured period such as six months. Keep code execution and plagiarism detection simulated and stored locally.
```

Expected implementation scope:

- Add assessment generation rules by difficulty tier and count.
- Add hidden test-case or expected-output records for coding questions.
- Add common-answer records and similarity/plagiarism score storage.
- Add heartbeat form/endpoint inside the monolith to update attempt activity and auto-expire attempts.
- Add cooldown configuration per assessment or job.
- Add difficulty recommendation logic based on previous completed scores.

Acceptance evidence:

- Candidate attempts contain the configured mix of easy, medium, and hard questions.
- Timer heartbeat updates attempt state and expired attempts cannot accept late answers.
- Simulated code-output validation marks coding answers against stored expected outputs.
- Simulated plagiarism scoring is visible to HR reviewers.
- Retake attempts are blocked during cooldown.
- HR can view difficulty recommendations.

## Phase 10: Interview Logistics, Live Coding Simulation, And Load Balancing

Goal: complete interviewer coordination and logistics.

Functions covered: 16, 18, 20, 21.

```text
/speckit.specify Complete interview coordination workflows in server-rendered Vanilla PHP. HR admins can automatically build a balanced interview panel using active HR, senior technical interviewers, interviewers, and observers. The system recommends assignments using workload counts and schedule conflicts. Interview sessions include a simulated live coding workspace stored in the database and refreshed through server-rendered forms. HR can approve a session extension for technical issues, and all scheduling, assignment, extension, and live coding changes are audited.
```

Expected implementation scope:

- Add panel builder action that proposes a balanced panel.
- Add interviewer workload query based on upcoming and active assignments.
- Add session extension fields and approval history.
- Add simulated live coding workspace records linked to interviews.
- Add candidate/interviewer forms to save code snapshots and comments.

Acceptance evidence:

- HR can generate a proposed panel and accept or edit it.
- Assignment recommendation avoids conflicts and favors lower workload.
- Observer assignments remain non-scoring.
- Live coding workspace changes are stored and visible to assigned participants.
- Session extensions require HR approval and are audited.

## Phase 11: Feedback Governance, Red Flags, Consensus, And Visual Analytics

Goal: complete feedback evaluation and recommendation intelligence.

Functions covered: 23, 24, 25, 26, 27.

```text
/speckit.specify Complete feedback governance and evaluation analytics in server-rendered Vanilla PHP. The system calculates normalized feedback scores using interviewer harshness trends, lets interviewers or HR flag serious candidate concerns, records candidate post-interview experience sentiment, triggers a consensus/debrief meeting workflow after all official feedback is submitted, and displays a competency gap visualizer comparing candidate scores against the ideal job profile. All decisions, flags, normalized scores, and meeting outcomes must be auditable and RBAC-protected.
```

Expected implementation scope:

- Add score normalization service using historical interviewer averages.
- Add red-flag fields and escalation records.
- Add candidate sentiment/experience score form after interview completion.
- Add consensus meeting records and outcome workflow.
- Add competency definitions per job and a server-rendered visual comparison.

Acceptance evidence:

- Raw and normalized feedback scores are visible to HR.
- Red flags appear in final evaluation and require acknowledgement.
- Candidate sentiment is captured separately from interviewer feedback.
- Consensus meeting is available once all official feedback is complete.
- Competency gap view compares candidate evidence against ideal profile.

## Phase 12: Offer Letters, Negotiation, Referral, Background Checks, And Welcome Portal

Goal: complete offers and onboarding beyond the existing basic flow.

Functions covered: 29, 30, 31, 32, 33, 34, 35.

```text
/speckit.specify Complete offer and onboarding workflows in server-rendered Vanilla PHP. HR admins can calculate offer packages using role level, base salary, bonus, and stock rules; generate a versioned digital offer letter from an approved template; track offer expiry and negotiation revisions; attribute referral rewards; run simulated background checks; and provide candidates with a pre-onboarding welcome portal for day-one documents. Offer expiry and background checks remain manually triggered through HR Run Checks unless a constitution-approved scheduler is added.
```

Expected implementation scope:

- Add compensation rule/configuration records for offer calculations.
- Add offer-letter templates and generated letter snapshots.
- Add negotiation revision history beyond the current replacement-offer sequence.
- Add referral source/referrer fields and reward status records.
- Add simulated background check requests and statuses.
- Add candidate pre-onboarding portal with required document checklist.
- Expand manual Run Checks to include expiry and background-check reminders.

Acceptance evidence:

- HR can calculate and then override an offer with reason.
- Generated offer letters preserve the exact template version used.
- Counter-offer revisions and approvals are visible in history.
- Referral rewards can be attributed and tracked.
- Simulated background checks move through requested, in-progress, passed, or failed.
- Candidate can view welcome tasks and submit document completion status.

## Phase 13: Compliance Reporting, Diversity Audit, Archiving, And Automation Checks

Goal: complete administration, compliance, and automated escalation scope.

Functions covered: 6, 38, 41, 42.

```text
/speckit.specify Complete compliance reporting and operational maintenance in server-rendered Vanilla PHP. HR admins can view enhanced pipeline throughput analytics with bottleneck detection, diversity and inclusion audit reports from optional candidate demographic fields, database integrity archive actions for closed requisitions and rejected candidates, and notification escalations for missing feedback, offer expiry, background checks, and onboarding tasks. Automated behavior must be implemented as HR-triggered Run Checks unless scheduler scope is explicitly approved.
```

Expected implementation scope:

- Extend pipeline analytics with stage duration, conversion rates, and bottleneck markers.
- Add optional candidate demographic fields with privacy notes and HR-only reporting.
- Add D&I aggregate reports without exposing individual sensitive attributes unnecessarily.
- Add archive markers/tables for closed requisitions and old rejected candidates.
- Expand notification escalator rules and deduplication references.
- Add Run Checks summary page with audit trail of each check execution.

Acceptance evidence:

- HR can identify slow stages per requisition and department.
- D&I reports show aggregate applicant metrics only to HR.
- Closed/rejected records can be archived without deleting required audit evidence.
- Run Checks generates deduplicated notifications and records execution details.
- Existing audit and data-retention behavior continues to work.

## Cross-Phase Verification

Every phase must include:

- PHP syntax checks using `composer test` or the project check script.
- Manual web-flow evidence for each actor involved.
- Schema review for added tables, indexes, constraints, and foreign keys.
- RBAC checks for HR admin, technical interviewer, junior staff/observer, and candidate where relevant.
- CSRF and server-side validation checks for all forms.
- Audit log evidence for status, score, approval, offer, retention, and escalation changes.
- Peer review before marking the phase complete.

## Team Split For Completion Work

- Person 1: SQL schema, repositories, services, controllers, policies, and audit integration.
- Person 2: Server-rendered PHP templates, dashboards, forms, visual reports, and mobile/desktop UI checks.
- Person 3: Spec Kit artifacts, validation scripts, manual test evidence, diagram traceability, and peer review.

Rotate ownership by phase. The reviewer must be different from the primary implementer for each phase.

## Recommended Implementation Order

1. Phase 7: Screening, Deduplication, Shortlisting
2. Phase 8: Advanced Requisition Workflow And Job-Board Sync
3. Phase 9: Assessment Integrity And Adaptive Testing
4. Phase 10: Interview Logistics, Live Coding Simulation, And Load Balancing
5. Phase 11: Feedback Governance, Red Flags, Consensus, And Visual Analytics
6. Phase 12: Offer Letters, Negotiation, Referral, Background Checks, And Welcome Portal
7. Phase 13: Compliance Reporting, Diversity Audit, Archiving, And Automation Checks

This order follows the recruitment lifecycle from application intake to compliance reporting, while keeping each phase small enough to specify, implement, verify, and review independently.
