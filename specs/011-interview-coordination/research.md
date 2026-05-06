# Phase 0 Research: Interview Coordination Workflows

## Decision: Keep Assignment Recommendations Deterministic and Local

**Rationale**: The feature must recommend panel members using active staff, workload counts, and schedule conflicts. A deterministic local rule set is easy to demo, test, audit, and explain to HR. It avoids hidden randomness except as a final tie-breaker and keeps recommendations reproducible.

**Alternatives considered**: Random assignment was rejected because it weakens workload fairness and explainability. External calendar optimization was rejected because the spec keeps conflict detection local and the constitution avoids external integration unless explicitly approved.

## Decision: Use Upcoming Scheduled Assignments for Workload Counts

**Rationale**: Workload balancing is most useful for immediate scheduling decisions, so count each staff member's non-cancelled upcoming assignments within the scheduling window. This aligns with the spec and avoids including old completed sessions that no longer affect availability.

**Alternatives considered**: Lifetime interview counts were rejected because they can over-penalize experienced interviewers. Daily-only counts were rejected because they miss near-term load across the week.

## Decision: Detect Conflicts with Local Interview Time Ranges

**Rationale**: Local conflict checks can compare requested start/end time against non-cancelled SRIM interviews for the candidate and each proposed staff member. This satisfies acceptance tests without Google Calendar dependency.

**Alternatives considered**: Google Calendar booking was rejected as out of scope for this feature. Ignoring conflicts and relying on HR review was rejected because it contradicts the core recommendation requirement.

## Decision: Model Balanced Panel Eligibility Through Role and Capability Fields

**Rationale**: Existing `users.role` identifies HR Admin, Interviewer, Candidate, and Junior Staff. The balanced panel also needs senior technical interviewer eligibility, official interviewer status, and observer/training-only behavior. A small staff capability/profile layer can express this without changing candidate accounts.

**Alternatives considered**: Hard-coding seniority from user names or departments was rejected because it is not testable. Creating many new account roles was rejected because it would make RBAC more brittle.

## Decision: Save Interview Briefing Snapshots

**Rationale**: Briefing packs should remain stable for the scheduled interview and should flag missing source data. Saving a snapshot keeps the interview context auditable even if a resume, assessment score, or job requirement changes later.

**Alternatives considered**: Live-querying briefing data only was rejected because it creates inconsistent interview context. Blocking scheduling when data is missing was rejected because the spec says missing data should be flagged, not necessarily block the interview.

## Decision: Use Refresh-Based Simulated Coding Workspace

**Rationale**: The spec requires a simulated live coding workspace stored in the database and refreshed through server-rendered forms. Saving current workspace state plus history records supports candidate/interviewer collaboration, observer visibility, and auditability without real-time infrastructure.

**Alternatives considered**: Websockets or a third-party collaborative editor were rejected because they introduce runtime dependencies and SPA-like behavior. Real code execution was rejected because the feature is about simulated interview coordination, not compiler integration.

## Decision: Use Last-Write-Wins With Visible Metadata for Workspace Saves

**Rationale**: Concurrent workspace saves are possible in a form-refresh model. Last-write-wins is simple, but each save should record actor, timestamp, prior content hash/version, and changed section so HR can audit overwritten changes.

**Alternatives considered**: Full merge/conflict resolution was rejected as too complex for the academic slice. Blocking all other users while one user edits was rejected because it harms interview usability.

## Decision: HR Approves All Extension Time

**Rationale**: Extensions affect fairness and schedules. Interviewers may request or cancel requests, but HR must approve, deny, or record cancellation and see conflicts before approval is saved.

**Alternatives considered**: Interviewer self-approval was rejected because it weakens HR control. Automatic extension on technical issue report was rejected because it is easy to abuse and difficult to audit fairly.

## Decision: Observers Are Training-Only

**Rationale**: UC-17 requires junior staff or observers to shadow interviews without affecting official scoring. Observers can view authorized session content but cannot change candidate-visible code or submit official feedback.

**Alternatives considered**: Allowing observer notes into final scoring was rejected because it contradicts the use case. Blocking observers from workspace visibility was rejected because shadowing is part of the requested panel mix.

## Decision: Append Audit Events for Every Coordination Change

**Rationale**: The user explicitly requires scheduling, assignment, extension, and live coding changes to be audited. Append-only audit records with actor, action, affected interview, timestamp, changed fields, and reason where required support compliance review and demo validation.

**Alternatives considered**: Updating only the current interview record was rejected because it loses history. A separate external audit service was rejected because it violates the monolithic/local delivery constraints.
