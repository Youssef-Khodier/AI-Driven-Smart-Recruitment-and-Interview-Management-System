# Candidate Privacy Verification: Screening Workflow

This document verifies that candidate privacy is maintained throughout the screening and shortlisting module.

## Verification Points

1. **Match Score Breakdowns**:
   - Simulated AI ranking breakdowns (skills, weights, match flags) are visible **only** to HR Admins in the Shortlist view.
   - Candidates viewing their own `/candidate/applications/{id}` page do **not** see these internal metrics.

2. **Automated Triage Rules**:
   - Threshold configurations mapping scores to statuses (e.g. 0-40 -> REJECTED) are visible **only** to HR Admins.

3. **Duplicate Merge Logs**:
   - The existence of candidate duplicate matching or merge decisions is completely hidden from the candidate portal.
   - Candidate merge logs and matching evidence are visible **only** in the HR Duplicate Resolution view and Audit Log.

4. **Audit Trails**:
   - The Screening Audit Log is secured behind HR_ADMIN RBAC and is not exposed to candidates.

**Conclusion**: Candidates only see their final application status (e.g., REJECTED, SCREENING, ASSESSMENT), without exposure to the internal scoring logic, AI annotations, or duplicate deduplication processes. Privacy requirements (RP-005) are upheld.
