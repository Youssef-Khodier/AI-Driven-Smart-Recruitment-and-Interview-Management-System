# Route Map: Technical Assessment Management

Routes extend `routes/web.php` inside the existing authenticated and active-user route group. Names are illustrative and should be finalized during implementation while preserving web-form semantics.

## HR Routes

| Method | Path | Name | Controller Action | Purpose |
|--------|------|------|-------------------|---------|
| GET | `/hr/requisitions/{requisition}/assessments` | `hr.assessments.index` | `Hr\AssessmentController@index` | List assessments for a job |
| GET | `/hr/requisitions/{requisition}/assessments/create` | `hr.assessments.create` | `Hr\AssessmentController@create` | Show assessment form |
| POST | `/hr/requisitions/{requisition}/assessments` | `hr.assessments.store` | `Hr\AssessmentController@store` | Create assessment |
| GET | `/hr/assessments/{assessment}` | `hr.assessments.show` | `Hr\AssessmentController@show` | Show assessment and questions |
| GET | `/hr/assessments/{assessment}/edit` | `hr.assessments.edit` | `Hr\AssessmentController@edit` | Show edit form |
| PUT | `/hr/assessments/{assessment}` | `hr.assessments.update` | `Hr\AssessmentController@update` | Update assessment |
| POST | `/hr/assessments/{assessment}/deactivate` | `hr.assessments.deactivate` | `Hr\AssessmentController@deactivate` | Prevent new attempts while preserving history |
| GET | `/hr/assessments/{assessment}/questions/create` | `hr.assessment-questions.create` | `Hr\AssessmentQuestionController@create` | Show question form |
| POST | `/hr/assessments/{assessment}/questions` | `hr.assessment-questions.store` | `Hr\AssessmentQuestionController@store` | Add question |
| GET | `/hr/assessment-questions/{question}/edit` | `hr.assessment-questions.edit` | `Hr\AssessmentQuestionController@edit` | Show question edit form |
| PUT | `/hr/assessment-questions/{question}` | `hr.assessment-questions.update` | `Hr\AssessmentQuestionController@update` | Update question for future attempts |
| POST | `/hr/assessment-questions/{question}/deactivate` | `hr.assessment-questions.deactivate` | `Hr\AssessmentQuestionController@deactivate` | Remove from future snapshots |
| GET | `/hr/requisitions/{requisition}/assessment-results` | `hr.assessment-results.index` | `Hr\AssessmentController@results` | Review attempts for a job |
| GET | `/hr/candidate-assessments/{attempt}` | `hr.candidate-assessments.show` | `Hr\AssessmentController@attempt` | Review attempt details |

**Middleware**: `auth`, `active`, `role:HR_ADMIN` and assessment policies.

## Candidate Routes

| Method | Path | Name | Controller Action | Purpose |
|--------|------|------|-------------------|---------|
| POST | `/candidate/applications/{application}/assessments/{assessment}/start` | `candidate.assessments.start` | `Candidate\AssessmentController@start` | Start or resume eligible attempt |
| GET | `/candidate/assessments/{attempt}` | `candidate.assessments.show` | `Candidate\AssessmentController@show` | Display active attempt |
| PUT | `/candidate/assessments/{attempt}/answers/{attemptQuestion}` | `candidate.assessments.answers.update` | `Candidate\AssessmentController@saveAnswer` | Continuously save latest answer |
| POST | `/candidate/assessments/{attempt}/submit` | `candidate.assessments.submit` | `Candidate\AssessmentController@submit` | Finalize submitted attempt or expire if late |
| POST | `/candidate/assessments/{attempt}/focus-events` | `candidate.assessments.focus-events.store` | `Candidate\AssessmentController@recordFocusEvent` | Record simulated proctoring event |
| GET | `/candidate/assessments/{attempt}/result` | `candidate.assessments.result` | `Candidate\AssessmentController@result` | Show own result summary |

**Middleware**: `auth`, `active`, `role:CANDIDATE` and candidate assessment policies.

## Routing Constraints

- Do not add `routes/api.php` routes for this feature.
- Same-page answer saves and focus event recording may use standard web form submissions or narrowly scoped progressive enhancement against the same web routes; they must not become a REST API contract.
- All final state changes must be validated server-side against attempt ownership, status, and deadline.
