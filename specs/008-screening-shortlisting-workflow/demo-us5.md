# Demo US5: Review Audit Evidence

1. Login as an HR Admin.
2. Navigate to HR Dashboard -> Requisitions.
3. Open a requisition that has had screening configuration, recalculations, or triage actions performed.
4. Click "Screening Audit" in the management actions.
5. Verify the audit log displays all actions associated with the requisition.
6. Verify the JSON details contain old and new configuration values, or summary statistics for batch actions.
7. Use the filters (Action Type, Dates) to narrow down the records.
8. Navigate to the global `HR Dashboard -> Audit Log` and verify that screening records (with entity_type SCREENING) appear in the system-wide log.
