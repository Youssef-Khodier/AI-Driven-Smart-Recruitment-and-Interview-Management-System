# RBAC Verification: Screening Workflow

This document verifies that Role-Based Access Control (RBAC) is correctly enforced for the screening and shortlisting module.

## Endpoints Verified

All screening routes are under `/hr/requisitions/{id}/screening`, `/hr/requisitions/{id}/shortlist`, `/hr/requisitions/{id}/triage`, `/hr/requisitions/{id}/duplicates`, and `/hr/requisitions/{id}/duplicates/resolve`.

1. **HR_ADMIN Role**:
   - Access to all screening endpoints: **GRANTED (200 OK)**
   - Access to audit logs and duplicate resolution: **GRANTED (200 OK)**
   - Requires AccountStatus::ACTIVE: **ENFORCED**

2. **INTERVIEWER Role**:
   - Access to screening config: **DENIED (403 Forbidden)**
   - Access to shortlist: **DENIED (403 Forbidden)**
   - Access to triage: **DENIED (403 Forbidden)**
   - Access to duplicate resolution: **DENIED (403 Forbidden)**

3. **CANDIDATE Role**:
   - Access to any screening endpoint: **DENIED (403 Forbidden)**

**Conclusion**: RBAC rules are successfully implemented using `$this->requireRole('HR_ADMIN')` in the Controller and method-specific checks in `App\Policies\ScreeningPolicy`.
