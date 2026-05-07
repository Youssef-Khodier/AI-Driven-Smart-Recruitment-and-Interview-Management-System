# Project Folder Guide

This guide explains what each important folder and file does in the SRIM project. Use it when onboarding a teammate, finding where to make a change, or preparing a demo.

## Root Files

### `.env`
Local environment configuration. It stores app mode, debug settings, database connection values, session settings, and first HR admin account defaults. This file should stay local and should not be committed.

### `.env.example`
Template for creating a new `.env` file on another machine. It shows the environment keys required to run the project.

### `.gitignore`
Defines files and folders Git should ignore, such as local environment files, generated logs, dependencies, and editor/runtime artifacts.

### `.htaccess`
Apache routing and protection rules. It routes requests through `index.php` and helps block direct public access to private project folders and sensitive files.

### `composer.json`
Composer metadata for the project. It declares the project name, description, PHP version requirement, and package settings. The app uses a custom autoloader instead of Composer class autoloading.

### `index.php`
Main public entry point. It loads `bootstrap/app.php`, receives the current request, and runs the application.

### `README.md`
Main project overview. It explains the system purpose, requirements, setup steps, seeded demo accounts, navigation, demo flow, documentation files, and troubleshooting.

### `PROJECT_FOLDER_GUIDE.md`
This file. It explains the folder structure and the purpose of the main files.

### `TEAM_TEST_SCENARIO.md`
Team testing and demo checklist. It describes how HR, candidates, and interviewers move through the system and what should be verified.

### `srim.sql`
Full database import file for the demo project. Use it when you want the schema and seeded demo data together.

## Application Code: `app`

The `app` folder contains the main PHP application code: controllers, custom framework classes, enums, models, policies, and services.

### `app/Controllers`
Controllers handle browser requests. They read input, call models/services, apply policies where needed, return views, and redirect after actions.

| File | Purpose |
| --- | --- |
| `AssessmentController.php` | HR assessment management, candidate assessment attempts, answer saving, heartbeat/focus tracking, submission, scoring, and result review. |
| `AuthController.php` | Registration, login, authentication, and logout. |
| `CandidateController.php` | Candidate profile, job browsing, applications, and application detail pages. |
| `CandidateInterviewController.php` | Candidate interview detail and candidate interview workspace actions. |
| `CandidateOfferController.php` | Candidate offer viewing, acceptance, and rejection. |
| `CandidateOnboardingController.php` | Candidate onboarding list, onboarding detail, and task completion. |
| `CandidateSentimentController.php` | Candidate interview sentiment form and submission. |
| `DashboardController.php` | Role-based dashboard routing and dashboard page data. |
| `HrAuditLogController.php` | HR audit log listing. |
| `HrBackgroundCheckController.php` | HR background check requests, status changes, completion, and cancellation. |
| `HrComplianceCheckController.php` | Compliance dashboard, compliance runs, archive actions, and diversity reporting. |
| `HrController.php` | Core HR workflows including requisitions, applications, users, access control, and requisition status changes. |
| `HrDataRetentionController.php` | Data retention dashboard, candidate anonymization, and candidate deletion actions. |
| `HrFeedbackGovernanceController.php` | Feedback governance dashboard, candidate governance detail, and flag resolution. |
| `HrFinalEvaluationController.php` | Final candidate evaluation display and saving. |
| `HrGovernanceController.php` | Requisition approvals, publishing, version history, comparison, sync history, and governance audit. |
| `HrInterviewController.php` | HR interview scheduling, panel recommendations, interview detail, briefing refresh, workspace, extensions, completion, and audit. |
| `HrOfferController.php` | HR offer list, creation, offer detail, sending, offer letter generation, and letter viewing. |
| `HrOnboardingController.php` | HR onboarding creation, listing, detail, and task status updates. |
| `HrReferralController.php` | Referral creation, listing, and referral reward approval/payment actions. |
| `HrReportController.php` | Recruitment reports such as pipeline, time-to-hire, and bottlenecks. |
| `HrScreeningController.php` | Screening configuration, recalculation, shortlist, triage, duplicate resolution, and screening audit. |
| `InterviewerInterviewController.php` | Interviewer interview list, details, workspace, extension requests, and feedback submission. |
| `NotificationController.php` | Notification listing and mark-read actions. |

### `app/Core`
Small custom framework layer used by the application.

| File | Purpose |
| --- | --- |
| `App.php` | Coordinates request handling through the router. |
| `Auth.php` | Authentication helper logic for logged-in users and role checks. |
| `Config.php` | Loads and reads environment/configuration values. |
| `Controller.php` | Base controller helpers shared by application controllers. |
| `Csrf.php` | CSRF token generation and validation. |
| `Database.php` | PDO database connection setup. |
| `helpers.php` | Global helper functions for URLs, escaping, auth, flash messages, and views. |
| `HttpException.php` | Exception type for HTTP errors. |
| `RedirectException.php` | Exception type used to interrupt flow with a redirect response. |
| `Request.php` | Represents the current HTTP request and input access. |
| `Response.php` | Creates view, redirect, and HTTP responses. |
| `Router.php` | Registers routes, matches requests, and dispatches controller actions. |
| `Session.php` | Session access, flash messages, and session helpers. |
| `ValidationException.php` | Exception used when validation fails. |
| `Validator.php` | Server-side validation rules. |
| `View.php` | Renders PHP templates from the `views` folder. |

### `app/Enums`
Enums define fixed statuses, roles, decision values, action names, and workflow types. They keep the app from using random strings across controllers, models, views, and services.

Examples include:

- User and account values: `UserRole.php`, `AccountStatus.php`.
- Recruitment values: `JobRequisitionStatus.php`, `ApplicationStatus.php`, `ApprovalDecision.php`.
- Assessment values: `AssessmentType.php`, `AssessmentQuestionType.php`, `AssessmentAttemptStatus.php`.
- Screening and duplicate values: `DuplicateConfidence.php`, `DuplicateDecisionType.php`, `ScreeningAuditAction.php`.
- Interview values: `InterviewStatus.php`, `InterviewAssignmentRole.php`, `InterviewAssignmentSource.php`, `InterviewExtensionStatus.php`, `InterviewAuditAction.php`.
- Feedback/evaluation values: `FeedbackConcernStatus.php`, `CompetencyGapSeverity.php`, `FinalEvaluationStatus.php`, `FinalEvaluationRecommendation.php`, `EvaluationDebriefStatus.php`.
- Offer/onboarding values: `OfferStatus.php`, `OfferType.php`, `OfferLetterStatus.php`, `OfferNegotiationStatus.php`, `OnboardingStatus.php`, `OnboardingDocumentStatus.php`.
- Compliance/audit values: `AuditAction.php`, `ComplianceAuditAction.php`, `GovernanceAuditAction.php`, `RetentionAuditAction.php`, `PostOfferAuditAction.php`.
- Support workflow values: `BackgroundCheckStatus.php`, `BackgroundCheckOutcome.php`, `ReferralRewardStatus.php`, `NotificationType.php`, `SyncStatus.php`.

### `app/Models`
Models perform database reads and writes. They are the direct data access layer for the custom MVC structure.

| File | Purpose |
| --- | --- |
| `AuditLogModel.php` | Reads and writes general audit log records. |
| `DataRetentionModel.php` | Supports retention, anonymization, and deletion workflows. |
| `DuplicateModel.php` | Stores and resolves duplicate candidate/application findings. |
| `FeedbackGovernanceModel.php` | Reads feedback flags, governance details, competency gaps, and debrief data. |
| `FinalEvaluationModel.php` | Stores and retrieves final hiring evaluations. |
| `GovernanceModel.php` | Requisition approval, versioning, publishing, sync, and governance audit data. |
| `InterviewAuditModel.php` | Interview-specific audit records. |
| `InterviewFeedbackModel.php` | Interview feedback forms and related feedback data. |
| `InterviewModel.php` | Interview scheduling, assignments, briefing, workspace, extensions, and interview status data. |
| `NotificationModel.php` | User notifications and unread counts. |
| `OfferModel.php` | Offers, offer letters, negotiations, and related offer actions. |
| `OnboardingModel.php` | Onboarding records, documents, and task progress. |
| `PostOfferAuditModel.php` | Audit records for post-offer workflows. |
| `ReferralModel.php` | Candidate referrals and referral reward status. |
| `ReportModel.php` | Recruitment reporting queries. |
| `ScreeningAuditModel.php` | Screening-specific audit records. |
| `ScreeningConfigModel.php` | Screening rules, score configuration, shortlist, triage, and scoring support. |

### `app/Policies`
Policies centralize authorization rules. Controllers use them to decide whether the current user can view or perform an action.

| File | Purpose |
| --- | --- |
| `ApplicationPolicy.php` | Application access and update permissions. |
| `AuditLogPolicy.php` | Audit log visibility permissions. |
| `DataRetentionPolicy.php` | Data retention and deletion permissions. |
| `FinalEvaluationPolicy.php` | Final evaluation access and update permissions. |
| `GovernancePolicy.php` | Governance, approval, publishing, and version permissions. |
| `InterviewFeedbackPolicy.php` | Feedback view/submit permissions. |
| `InterviewPolicy.php` | Interview access, scheduling, workspace, extension, and completion permissions. |
| `JobRequisitionPolicy.php` | Requisition create, view, edit, submit, approve, publish, and close permissions. |
| `NotificationPolicy.php` | Notification access permissions. |
| `OfferPolicy.php` | Offer creation, viewing, sending, and response permissions. |
| `OnboardingPolicy.php` | Onboarding view/update/task permissions. |
| `ReportPolicy.php` | Report access permissions. |
| `ScreeningPolicy.php` | Screening, triage, shortlist, duplicate, and audit permissions. |

### `app/Services`
Services hold business logic that is more complex than simple database access.

| File | Purpose |
| --- | --- |
| `DuplicateDetectionService.php` | Calculates and labels possible duplicate candidate/application records. |
| `FeedbackNormalizationService.php` | Normalizes feedback data for governance and evaluation workflows. |
| `OfferLetterTemplateService.php` | Builds offer letters from templates and offer data. |
| `OfferPackageCalculator.php` | Calculates compensation package values. |
| `ScreeningScoreService.php` | Calculates screening scores from candidate/application data and screening configuration. |
| `SimulatedAssessmentScorer.php` | Scores assessment attempts for the demo assessment workflow. |
| `SimulatedBackgroundCheckService.php` | Produces demo background check outcomes. |
| `SimulatedMatchScorer.php` | Produces demo candidate/job match scores. |
| `TemplateVersionDiffService.php` | Compares template or version text for governance/version history screens. |

## Startup Code: `bootstrap`

### `bootstrap/app.php`
Loads the autoloader, configuration, sessions, database, and routes. It returns the configured app instance used by `index.php`.

### `bootstrap/autoload.php`
Custom PSR-style class loader for project classes under the `App` namespace.

## Database Files: `database`

### `database/.gitkeep`
Keeps the database folder committed even if other generated files are absent.

### `database/schema.sql`
Main database schema file used to create project tables.

### `database/migrations`
Incremental SQL files for later feature groups. Apply them in filename order when starting from an older schema.

| File | Purpose |
| --- | --- |
| `009_governance_tables.sql` | Governance, approval, versioning, and related requisition workflow tables. |
| `010_assessment_integrity_adaptive_testing.sql` | Assessment integrity, adaptive testing, attempt, question, and scoring support tables. |
| `011_interview_coordination_workflows.sql` | Interview scheduling, panel, workspace, briefing, extension, and audit workflow tables. |
| `012_feedback_governance_analytics.sql` | Feedback governance, analytics, competency, sentiment, and final evaluation support. |
| `013_offer_onboarding_workflows.sql` | Offer, offer letter, referral, background check, and onboarding workflow tables. |
| `014_compliance_reporting_maintenance.sql` | Compliance, reporting, retention, archive, and maintenance support tables. |

## Diagrams And Documentation: `Diagrams`

Contains supporting documentation and design artifacts for the academic/project submission.

| Path | Purpose |
| --- | --- |
| `Diagrams/document.md` | Notes or exported documentation related to the diagrams. |
| `Diagrams/Acrivity Diagram` | Activity diagram PDFs. The folder name is currently spelled `Acrivity` in the project. |
| `Diagrams/Class Diagram` | Class diagram Draw.io source and exported PDF. |
| `Diagrams/Database` | Database diagram README, ERD SVG, and database schema copy. |
| `Diagrams/Object Diagram` | Object diagram PDF. |
| `Diagrams/SRS` | Software Requirements Specification document. |
| `Diagrams/System Architecture` | System architecture Draw.io source and exported PDF. |
| `Diagrams/Use-case Diagram` | Use-case diagram PDF. |

## Routes: `routes`

### `routes/web.php`
Defines all web routes, route names, controller mappings, and role workflow URLs. This is the main place to check when you need to know which controller method handles a page or form action.

## Runtime Storage: `storage`

Files here are for runtime/generated data and should not be public.

### `storage/app`
Reserved for generated application files or uploaded files if the app needs them.

### `storage/app/.gitignore`
Keeps runtime files inside `storage/app` out of Git while preserving the folder.

### `storage/logs`
Reserved for runtime log files.

### `storage/logs/.gitignore`
Keeps logs out of Git while preserving the folder.

## Views: `views`

Server-rendered PHP templates. Controllers pass data into these templates, and `views/layouts/app.php` wraps most pages with the shared layout and navigation.

| Path | Purpose |
| --- | --- |
| `views/welcome.php` | Public welcome page. |
| `views/layouts/app.php` | Main shared layout, header, navigation, flash messages, error display, and page content wrapper. |
| `views/auth` | Login and registration pages. |
| `views/errors` | Error pages for 403, 404, 419, and 500 responses. |
| `views/notifications` | Notification list page. |

### Candidate Views

| Path | Purpose |
| --- | --- |
| `views/candidate/dashboard.php` | Candidate dashboard. |
| `views/candidate/profile.php` | Candidate profile page. |
| `views/candidate/jobs` | Open job list and job detail pages. |
| `views/candidate/applications` | Candidate application list and application detail pages. |
| `views/candidate/assessments` | Candidate assessment attempt and result pages. |
| `views/candidate/interviews` | Candidate interview detail and sentiment pages. |
| `views/candidate/offers` | Candidate offer detail and response page. |
| `views/candidate/onboarding` | Candidate onboarding list, welcome, and task pages. |

### HR Views

| Path | Purpose |
| --- | --- |
| `views/hr/dashboard.php` | HR dashboard. |
| `views/hr/requisitions` | Requisition list, create/edit form, and detail page. |
| `views/hr/applications` | HR application list for requisitions. |
| `views/hr/screening` | Screening configuration, triage, shortlist, duplicates, duplicate resolution, and screening audit pages. |
| `views/hr/assessments` | Assessment list, form, detail, candidate attempt review, and result pages. |
| `views/hr/assessment-questions` | Assessment question create/edit form. |
| `views/hr/interviews` | Interview list, scheduling form, detail, workspace, extension, and audit pages. |
| `views/hr/governance` | Approval queue, approval form, publish form, version history, version comparison, sync history, and governance audit pages. |
| `views/hr/governance/feedback-governance.php` | Feedback governance dashboard. |
| `views/hr/governance/feedback-detail.php` | Feedback governance detail for a candidate/application. |
| `views/hr/evaluations` | Final evaluation page. |
| `views/hr/offers` | Offer list, offer form, offer detail, and offer letter page. |
| `views/hr/onboarding` | HR onboarding list, creation form, and onboarding detail page. |
| `views/hr/referrals` | Referral list and referral form. |
| `views/hr/background-checks` | Background check list and status actions for an application. |
| `views/hr/compliance` | Compliance dashboard and diversity report. |
| `views/hr/data-retention` | Data retention, anonymization, and deletion page. |
| `views/hr/audit-log` | Audit log page. |
| `views/hr/reports` | Pipeline, time-to-hire, and bottleneck reports. |
| `views/hr/users` | User list, user creation, and access-control pages. |
| `views/hr/run-checks` | Reserved support folder for HR check/run-check views if used by future compliance workflows. |

### Interviewer Views

| Path | Purpose |
| --- | --- |
| `views/interviewer/dashboard.php` | Interviewer dashboard. |
| `views/interviewer/interviews` | Assigned interview list, interview detail, and feedback form pages. |
| `views/interviews/workspace.php` | Shared interview workspace used by HR, candidate, or interviewer routes depending on role. |
