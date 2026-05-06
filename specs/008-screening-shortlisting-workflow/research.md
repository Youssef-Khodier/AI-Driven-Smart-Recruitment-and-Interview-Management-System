# Research: Screening & Shortlisting Workflow

**Feature**: `008-screening-shortlisting-workflow`
**Date**: 2026-05-05

## R1: Weighted Scoring Algorithm Design

**Decision**: Deterministic weighted scoring using HR-configured skill weights and candidate profile evidence fields.

**Rationale**: The spec requires "simulated AI" that is explainable and deterministic. A weighted sum formula (`score = Σ(skill_weight × evidence_match)`) is transparent, reproducible, and requires no external dependencies. The existing `SimulatedMatchScorer` uses keyword intersection which we will extend to support per-skill weights.

**Algorithm**:
1. For each configured skill in the requisition's screening config:
   - Search candidate evidence fields (`current_title`, `years_experience`, `skill_keywords`, `resume_url` filename) for keyword matches
   - Calculate `skill_score`: 1.0 if evidence found, 0.0 if missing
   - Apply weight: `weighted_contribution = skill_weight × skill_score`
2. Sum all weighted contributions → raw score (0–100)
3. Apply experience bonus: `min(10, years_experience × 2)` added to raw score
4. Clamp to [0, 100]
5. Store total in `applications.match_score`, breakdown JSON in `applications.match_score_breakdown`

**Alternatives considered**:
- TF-IDF scoring: Over-engineered for simulated scope; not deterministic without corpus
- Fuzzy matching (Levenshtein): Too slow for up to 100 applicants; keyword matching sufficient for demo
- External AI API: Explicitly out of scope per constitution

## R2: Duplicate Detection Strategy

**Decision**: On-demand string-similarity comparison within a requisition's applicant pool using deterministic matching rules.

**Rationale**: The spec clarifies duplicate detection is on-demand per-requisition. Comparing only within one requisition's applicants keeps the candidate set small (≤100) and avoids system-wide scans. Deterministic rules allow confidence categorization.

**Matching Rules** (each produces a match signal):
| Field | Match Method | Confidence Weight |
|-------|-------------|-------------------|
| `email` | Exact match (case-insensitive) | HIGH |
| `phone` | Normalized digit comparison | HIGH |
| `name` | Case-insensitive, trimmed comparison | MEDIUM |
| `current_title` + `years_experience` | Combined exact match | LOW |
| `resume_url` | Exact URL match | MEDIUM |

**Confidence Category**:
- HIGH: ≥1 HIGH signal
- MEDIUM: ≥2 MEDIUM signals or 1 MEDIUM + ≥1 LOW
- LOW: 1 MEDIUM signal or ≥2 LOW signals

**Alternatives considered**:
- Phonetic matching (Soundex/Metaphone): Unnecessary complexity for demo; exact/case-insensitive sufficient
- Background scheduled scan: Violates monolithic server-rendered architecture constraint
- Cross-requisition global scan: Scope creep; spec clarified per-requisition only

## R3: Triage Threshold Model

**Decision**: Ordered, non-overlapping score bands mapping to target application statuses.

**Rationale**: HR configures threshold boundaries (e.g., 0–39 → REJECTED, 40–59 → SCREENING, 60–79 → ASSESSMENT, 80–100 → INTERVIEW). The system validates no gaps or overlaps exist and that each band maps to an allowed status.

**Data Model**:
```
screening_thresholds:
  - min_score: 0,   max_score: 39,  target_status: REJECTED
  - min_score: 40,  max_score: 59,  target_status: SCREENING
  - min_score: 60,  max_score: 79,  target_status: ASSESSMENT
  - min_score: 80,  max_score: 100, target_status: INTERVIEW
```

**Validation Rules**:
- Bands must be contiguous (no gaps from 0 to 100)
- Bands must not overlap
- Each band must map to one of: SCREENING, ASSESSMENT, INTERVIEW, REJECTED
- `min_score` ≤ `max_score` for each band
- All scores from 0 to 100 must be covered

**Alternatives considered**:
- Single cutoff with pass/fail: Too simplistic; spec requires 4 target statuses
- Percentile-based thresholds: Non-deterministic; changes with applicant pool composition

## R4: Schema Extension Strategy

**Decision**: Add new tables for screening configuration and audit; extend existing tables with new columns where appropriate.

**Rationale**: The existing `applications` table already has `match_score`. Adding `match_score_breakdown` as JSON follows the established pattern (`questions.options` JSON). New tables for screening configs, skills, thresholds, and audit are needed since no existing table covers this domain. The `candidate_merge_log` table is extended with `decision_type` and `confidence_category` columns per clarification.

**New Tables**:
1. `screening_configs` — per-requisition screening configuration header
2. `screening_skills` — weighted skills per config
3. `screening_thresholds` — score band thresholds per config
4. `screening_audit_records` — audit trail for all screening actions

**Altered Tables**:
1. `applications` — add `match_score_breakdown JSON NULL`
2. `candidate_merge_log` — add `decision_type VARCHAR(20) NOT NULL DEFAULT 'MERGE'`, `confidence_category VARCHAR(20) NULL`, `job_id BIGINT UNSIGNED NULL`

**Alternatives considered**:
- JSON-only config storage: Harder to validate, query, and audit; normalized tables preferred for relational integrity
- Reuse existing audit tables: Existing audit tables are domain-specific (account, interview, post-offer); a screening-specific audit table follows the same pattern

## R5: Anti-Duplicate Submission Strategy

**Decision**: Use CSRF tokens (already in place) plus server-side idempotency checks via unique constraints and status guards.

**Rationale**: FR-019 requires preventing duplicate triage/recalculation/merge submissions. The existing CSRF mechanism prevents replay attacks. Additionally:
- Triage: Only APPLIED applications are moved; re-running triage on already-triaged applications is a no-op (guard: `WHERE status = 'APPLIED'`)
- Recalculation: Scores are overwritten; re-running is idempotent
- Merge: `candidate_merge_log` has `UNIQUE(primary_candidate_id, duplicate_candidate_id)` preventing duplicate merge records

**Alternatives considered**:
- Token-based form submission IDs: Adds complexity; not needed given status guards and unique constraints
- JavaScript form disabling: Insufficient as sole protection; server-side checks are primary

## R6: Existing SimulatedMatchScorer Reuse

**Decision**: Enhance the existing `SimulatedMatchScorer` service rather than replace it.

**Rationale**: The existing service already handles keyword extraction and basic scoring. The enhancement will:
1. Add a new method `scoreWeighted(array $config, array $candidate): array` that accepts a screening config with weighted skills
2. Preserve the existing `score()` method for backward compatibility (used during candidate application)
3. The new method returns both the total score and per-skill breakdown

**Alternatives considered**:
- Separate `WeightedMatchScorer` class: Unnecessary duplication of keyword logic
- Replace `score()` entirely: Would break existing application flow that uses it
