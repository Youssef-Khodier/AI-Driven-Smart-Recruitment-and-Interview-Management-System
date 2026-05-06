# Phase 0 Research: Feedback Governance Analytics

## Decision: Keep Delivery As Server-Rendered Vanilla PHP MVC

**Rationale**: The constitution requires framework-free Vanilla PHP MVC, `routes/web.php`, native sessions, CSRF, server-side validation, policies, PDO repositories, and server-rendered PHP templates. The feature is a governance workflow with forms, tables, summaries, and audit views that fit existing HR, interviewer, and candidate portal patterns.

**Alternatives considered**: REST endpoints with a separate analytics UI were rejected because they violate the constitution and add unnecessary integration scope. Client-side chart libraries are deferred; a server-rendered competency comparison can use accessible tables and lightweight HTML/CSS bars.

## Decision: Normalize Only With 5 Comparable Prior Official Submissions In 12 Months

**Rationale**: The clarified spec sets a measurable threshold that avoids overfitting interviewer harshness trends from too little data. Comparable history keeps adjustments defensible and auditable while preserving fallback to raw scores for newer interviewers.

**Alternatives considered**: Three submissions was too noisy. Ten same-job-family submissions was too restrictive for academic/demo data. Manual HR enablement would create inconsistent outcomes and harder acceptance tests.

## Decision: Use Raw Feedback Scale As 0-10 And Evaluation Outputs As 0-100

**Rationale**: Existing feedback fields and baseline schema use 0-10 interview scores, while `final_evaluations.aggregate_score` is constrained to 0-100. Planning will preserve raw 0-10 feedback, compute normalized competency scores on 0-10, and persist/report aggregate evaluation scores on 0-100 with explicit labels.

**Alternatives considered**: Changing all feedback to 0-100 would break existing views and validation patterns. Keeping final evaluations on 0-10 would conflict with current schema constraints.

## Decision: Serious Concern Flags Block Final Decision Actions, Not Remaining Feedback

**Rationale**: Remaining official feedback should continue so HR has complete evidence, but debrief completion, final recommendation approval, and candidate status changes must wait for HR flag resolution. This matches the clarified spec and reduces decision risk.

**Alternatives considered**: Blocking all feedback would lose useful evidence. Warning-only behavior would allow risky decisions. Automatic Strong No Hire would be too punitive without HR review.

## Decision: Debrief Scope Is An In-App Outcome Record

**Rationale**: The clarified spec requires participants, consensus, dissent, outcome, rationale, and next action, but not external scheduling. This preserves the SRS consensus workflow while avoiding calendar integration and background scheduling.

**Alternatives considered**: Full scheduling, rescheduling, attendance tracking, and external calendar integration were rejected as out of scope for this phase.

## Decision: HR Owns Competency Benchmarks

**Rationale**: HR-maintained explicit benchmarks are auditable, testable, and align with governance. They may be seeded from job requisition skill expectations, but HR must review and maintain them before final gap analysis is relied upon.

**Alternatives considered**: Fully automatic benchmarks from job weights were too opaque. A fixed global benchmark would ignore role differences. Interviewer-submitted ideal scores would create inconsistent candidate comparisons.

## Decision: Use Governance-Specific Audit Records And Integrate Them Into Existing Audit Reporting

**Rationale**: Existing audit patterns use JSON change payloads and consolidated HR audit views. Feedback governance needs long-lived audit records for score calculations, flag decisions, sentiment submissions, debrief outcomes, benchmark changes, and overrides. Governance audit records should reference applications/interviews/evaluations and be included in the consolidated audit log.

**Alternatives considered**: Reusing only `interview_audit_records` risks losing governance history if interviews are deleted and lacks entity coverage for benchmarks/sentiment/debriefs. Spreading audit data across unrelated tables would make compliance review harder.

## Decision: In-System Notifications Only

**Rationale**: Existing `notifications` supports referenced, deduplicated user notifications. Serious flag alerts and feedback/debrief notices can use this local mechanism without SMTP or external services.

**Alternatives considered**: Email delivery and external escalation services were rejected as integration scope not required by the clarified feature.

## Decision: Implement Normalization As A Small Service

**Rationale**: Normalization will be needed in governed evaluation reports, final recommendation calculations, audit explanations, and possible reports. A focused service keeps the math testable while repositories own persistence.

**Alternatives considered**: Embedding the calculation in controllers would duplicate logic. Embedding all calculation in SQL would be harder to test and explain in audit output.
