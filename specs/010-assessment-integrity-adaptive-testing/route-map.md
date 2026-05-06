# Route Map: Advanced Assessment Integrity and Adaptive Testing

## HR Routes

| Route Name | Method / Path | Controller Action | View | Purpose |
|------------|---------------|-------------------|------|---------|
| `hr.assessments.index` | `GET /hr/requisitions/{id}/assessments` | `AssessmentController::index` | `views/hr/assessments/index.php` | List assessments for a requisition |
| `hr.assessments.create` | `GET /hr/requisitions/{id}/assessments/create` | `AssessmentController::create` | `views/hr/assessments/form.php` | Show assessment form with rule fields |
| `hr.assessments.store` | `POST /hr/requisitions/{id}/assessments` | `AssessmentController::store` | redirect/form errors | Save assessment, cooldown, and question rules |
| `hr.assessments.show` | `GET /hr/assessments/{id}` | `AssessmentController::show` | `views/hr/assessments/show.php` | Show rules, cooldown, attempts, sufficiency warnings, adaptive suggestion |
| `hr.assessments.edit` | `GET /hr/assessments/{id}/edit` | `AssessmentController::edit` | `views/hr/assessments/form.php` | Edit assessment settings |
| `hr.assessments.update` | `PUT /hr/assessments/{id}` | `AssessmentController::update` | redirect/form errors | Update future assessment settings |
| `hr.assessment-questions.create` | `GET /hr/assessments/{id}/questions/create` | `AssessmentController::createQuestion` | `views/hr/assessment-questions/form.php` | Add question with hidden outputs/common answers |
| `hr.assessment-questions.store` | `POST /hr/assessments/{id}/questions` | `AssessmentController::storeQuestion` | redirect/form errors | Persist question integrity reference records |
| `hr.assessment-questions.edit` | `GET /hr/assessment-questions/{id}/edit` | `AssessmentController::editQuestion` | `views/hr/assessment-questions/form.php` | Edit question and local reference records |
| `hr.assessment-questions.update` | `PUT /hr/assessment-questions/{id}` | `AssessmentController::updateQuestion` | redirect/form errors | Update question for future snapshots |
| `hr.assessment-results.index` | `GET /hr/requisitions/{id}/assessment-results` | `AssessmentController::results` | `views/hr/assessments/results.php` | Review attempts for a requisition |
| `hr.candidate-assessments.show` | `GET /hr/candidate-assessments/{id}` | `AssessmentController::reviewAttempt` | `views/hr/assessments/attempt.php` | Review score and simulated integrity details |

## Candidate Routes

| Route Name | Method / Path | Controller Action | View | Purpose |
|------------|---------------|-------------------|------|---------|
| `candidate.assessments.start` | `POST /candidate/applications/{application}/assessments/{assessment}/start` | `AssessmentController::startCandidate` | redirect/status | Enforce cooldown and bank sufficiency, create randomized attempt |
| `candidate.assessments.show` | `GET /candidate/assessments/{id}` | `AssessmentController::showCandidate` | `views/candidate/assessments/show.php` | Show active attempt and timer |
| `candidate.assessments.answers.update` | `PUT /candidate/assessments/{id}/answers/{question}` | `AssessmentController::saveAnswer` | redirect/status | Save answer and optional stated output |
| `candidate.assessments.heartbeat` | `POST /candidate/assessments/{id}/heartbeat` | `AssessmentController::heartbeat` | narrow in-page response or redirect-safe response | Persist remaining time while server deadline stays authoritative |
| `candidate.assessments.focus-events.store` | `POST /candidate/assessments/{id}/focus-events` | `AssessmentController::focusEvent` | narrow in-page response or redirect-safe response | Save local proctoring/focus event |
| `candidate.assessments.submit` | `POST /candidate/assessments/{id}/submit` | `AssessmentController::submitCandidate` | redirect/status | Submit or expire attempt and score saved answers |
| `candidate.assessments.result` | `GET /candidate/assessments/{id}/result` | `AssessmentController::resultCandidate` | `views/candidate/assessments/result.php` | Show candidate-safe result |

## Authorization Notes

- HR routes require HR Admin role.
- Candidate routes require Candidate role and ownership of the relevant application or attempt.
- Technical Interviewer access remains summary-only through existing interview preparation or evaluation pages; no question-bank maintenance route is added for this feature.
- Junior Staff remains read-only/training-only where assessment summaries appear in later workflows.
