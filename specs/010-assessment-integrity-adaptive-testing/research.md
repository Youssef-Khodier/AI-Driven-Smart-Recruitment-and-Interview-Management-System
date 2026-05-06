# Research: Advanced Assessment Integrity and Adaptive Testing

## Decision: Keep Assessment Delivery Server-Rendered

**Rationale**: The constitution requires a framework-free Vanilla PHP monolith and the existing assessment workflow already uses `AssessmentController`, `routes/web.php`, native sessions, CSRF, and PHP templates. Keeping the feature in those paths minimizes scope and preserves the demo workflow.

**Alternatives considered**: A REST API and separated frontend would make heartbeat and timer interactions cleaner for a SPA, but violates the constitution and is unnecessary for this academic vertical slice. A framework migration is also rejected because the runtime amendment requires framework-free PHP.

## Decision: Server Deadline Is Authoritative For Timer Expiry

**Rationale**: Browser timers and heartbeat messages can be delayed, lost, or manipulated. Persisting heartbeat remaining time improves continuity, but the server-side `expires_at` deadline must decide expiry to protect assessment integrity.

**Alternatives considered**: Trusting the browser timer would be simpler but insecure. Extending time automatically after stale heartbeats would improve UX for network issues but weakens integrity and creates inconsistent retake rules.

## Decision: Block Candidate Start When Question Rules Cannot Be Satisfied

**Rationale**: HR-defined difficulty counts are fairness rules. Starting a degraded test with missing tiers creates unequal attempts. Blocking the start and alerting HR keeps candidate attempts consistent and makes the issue visible before scoring.

**Alternatives considered**: Falling back to all available questions would reduce candidate disruption but violates the configured rule intent. Allowing repeated questions or tier substitutions would need more policy decisions and could reduce fairness.

## Decision: Snapshot Candidate Attempt Questions

**Rationale**: A candidate attempt must remain stable even if HR edits questions, rules, expected outputs, or common answers later. Snapshotting question text, options, correct answers, points, and order also supports review and audit-style evidence.

**Alternatives considered**: Referencing live question records only would reduce storage but makes historical attempts mutable. Full versioning of every question-bank edit is more complex than needed for this phase.

## Decision: Simulated Output Validation Uses Local Expected-Output Records

**Rationale**: The user request and design function #11 require simulated code-output validation. Comparing candidate-provided output or answer text to local hidden expected-output records avoids real code execution, sandboxing, compiler risk, and external dependencies.

**Alternatives considered**: Running code in a sandbox is out of scope and conflicts with the simulation constraint. Manual-only review would not satisfy the feature's simulated validator requirement.

## Decision: Simulated Plagiarism Uses Local Common-Answer Records

**Rationale**: Local common-answer comparison supports the SRS plagiarism use case without sending candidate content to external services. A similarity score of 80% or higher creates an HR review flag only, preserving human review and avoiding automatic misconduct decisions.

**Alternatives considered**: Third-party plagiarism APIs are rejected because the feature must be local and simulated. Automatic rejection is rejected because simulated similarity is a review signal, not proof.

## Decision: Preserve Completed Integrity Results Without Automatic Re-Scoring

**Rationale**: Completed attempts must remain explainable and stable. If HR edits expected-output or common-answer records later, old simulated results should not silently change. A future explicit re-scoring workflow can be specified separately if needed.

**Alternatives considered**: Automatic re-scoring could keep results aligned with current reference records but risks changing historical decisions without review. Never allowing any re-score is too restrictive for future correction workflows.

## Decision: Adaptive Difficulty Suggestions Use Five Attempts And Score Bands

**Rationale**: The clarification selected a minimum of five completed attempts with average score bands of 50% or lower for easier, 80% or higher for harder, and unchanged otherwise. This is simple, explainable, and demonstrable without opaque AI.

**Alternatives considered**: Per-candidate adaptive follow-up tests would expand workflow scope. More complex statistical models are unnecessary for the academic demo and harder for HR to interpret.

## Decision: Verification Focuses On Manual Flows Plus Targeted PHP Checks

**Rationale**: The project has no mature automated test suite requirement in the current plan context. Manual server-rendered page flows are constitutionally acceptable for demo evidence, and targeted syntax/service checks reduce regression risk.

**Alternatives considered**: Full browser automation would improve confidence but is heavier than necessary for this phase. Skipping checks would make timer/scoring regressions likely.
