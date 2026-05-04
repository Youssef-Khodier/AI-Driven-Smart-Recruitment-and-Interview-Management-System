# Research: Technical Assessment Management

## Decision: Use Existing Laravel 12 MVC Conventions

**Rationale**: The repository already uses PHP 8.2+, Laravel 12.x, Blade, `routes/web.php`, Eloquent, policies, middleware, sessions, CSRF, and PHPUnit. Reusing these conventions satisfies the constitution and avoids introducing a second delivery style.

**Alternatives considered**: REST API endpoints, SPA pages, or external assessment service integration were rejected because they violate current SRIM delivery constraints and add unnecessary scope for an academic vertical slice.

## Decision: Model Assessments with Baseline Tables plus Attempt Evidence Extensions

**Rationale**: The baseline schema already includes `assessments`, `questions`, `candidate_assessments`, and `submissions`. Adding an `application_id` link, attempt question snapshots, and integrity events preserves baseline alignment while satisfying clarified requirements for application-stage eligibility, immutable attempt evidence, and focus-loss review.

**Alternatives considered**: Inferring applications only through candidate plus assessment job was rejected because it complicates privacy checks and HR reporting. Locking assessment editing after any attempt was rejected because snapshots provide reliable evidence without blocking HR from maintaining future test versions.

## Decision: Use Deterministic Simulated Scoring

**Rationale**: Scores must be simulated, explainable, and testable. MCQ answers can be scored by exact expected-answer match. Theory/free-text and coding-as-text answers can be scored by deterministic keyword/reference overlap against HR-provided scoring references. This supports acceptance tests without real AI, compilers, or hidden test cases.

**Alternatives considered**: Real code execution, NLP/AI grading, plagiarism detection, and manual-only HR scoring were rejected for this phase. Real grading features are baseline future context but exceed the requested simulated scope.

## Decision: Enforce Timeouts Server-Side on Every Attempt Action

**Rationale**: The visible timer improves candidate experience, but fairness requires server-side deadline checks whenever the candidate opens, saves, or submits an attempt. Expired attempts are scored only from answers saved before the deadline and then locked.

**Alternatives considered**: Browser-only countdown enforcement was rejected because it can be bypassed by reloads or stale pages. Background-only expiry was rejected because the first request after expiry can deterministically enforce the same rule for the academic scope.

## Decision: Continuously Save Answers During Active Attempts

**Rationale**: Continuous saving supports timeout scoring, refresh recovery, and lower candidate data-loss risk. It also makes expired-attempt behavior testable because the system has a clear set of answers saved before the deadline.

**Alternatives considered**: Final-submit-only saving was rejected because it makes expiry scoring unfair when a candidate finishes work but misses final submit. Manual save buttons alone were rejected because they shift too much risk to the candidate.

## Decision: Capture Simulated Proctoring as Focus Events Only

**Rationale**: The SRS and user request specifically identify focus-loss tracking. Capturing focus-loss and focus-return events with timestamps is sufficient for simulated integrity review while avoiding invasive monitoring.

**Alternatives considered**: Webcam/video proctoring, microphone monitoring, screen recording, and lockdown-browser behavior were rejected because the spec explicitly keeps them out of scope and the constitution requires simulated advanced features to be labeled clearly.

## Decision: One Attempt per Candidate per Assessment

**Rationale**: The clarified spec requires one attempt unless a future approved policy permits retakes. This keeps V1 simple and prevents ambiguous score replacement behavior.

**Alternatives considered**: Unlimited retakes, HR-triggered retakes, and cool-down score reuse were rejected because they introduce policy complexity reserved for a later feature.
