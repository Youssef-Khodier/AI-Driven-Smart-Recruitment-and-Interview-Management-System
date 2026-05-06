# Demo US1: Configure Screening Rules

1. Login as an HR Admin.
2. Navigate to HR Dashboard -> Requisitions.
3. Click on a requisition that is in APPROVED or OPEN status.
4. Click the "Configure screening" button in the management actions.
5. Fill in the "Required Skills & Weights":
   - Add Skill: `PHP`, Weight: `50`, Evidence Field: `Anywhere`
   - Add Skill: `MySQL`, Weight: `50`, Evidence Field: `Anywhere`
6. Fill in the "Automated Triage Thresholds":
   - 0 to 49 -> REJECTED
   - 50 to 79 -> SCREENING
   - 80 to 100 -> INTERVIEW
7. Click "Save Configuration".
8. Verify success message appears and form reflects saved data.
9. Try to save with invalid data (e.g. weights sum to 90) and verify error messages.
