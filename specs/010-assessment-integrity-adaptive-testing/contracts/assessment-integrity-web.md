# Web Interaction Contracts: Advanced Assessment Integrity and Adaptive Testing

This feature exposes server-rendered browser routes and form submissions only. These contracts document user-facing web workflows, not REST APIs.

## HR Assessment Configuration

### Create Assessment

- **Route name**: `hr.assessments.create`, `hr.assessments.store`
- **Actor**: HR Admin
- **Input fields**: title, description, type, duration minutes, cooldown months, easy count, medium count, hard count, active flag
- **Validation**: title required; type recognized; duration > 0; cooldown >= 0; counts are whole numbers; total count > 0
- **Success result**: assessment is saved, rules are saved, HR is redirected to assessment details
- **Failure result**: form re-renders with validation errors and no invalid configuration is persisted

### Edit Assessment

- **Route name**: `hr.assessments.edit`, `hr.assessments.update`
- **Actor**: HR Admin
- **Input fields**: same as create
- **Success result**: future attempts use updated configuration; existing attempts keep snapshots
- **Failure result**: form re-renders with validation errors

## HR Question Integrity Configuration

### Create Or Edit Question

- **Route names**: `hr.assessment-questions.create`, `hr.assessment-questions.store`, `hr.assessment-questions.edit`, `hr.assessment-questions.update`
- **Actor**: HR Admin
- **Input fields**: question type, difficulty, prompt, options, correct answer, points, active flag, hidden expected outputs, common answers
- **Validation**: difficulty recognized; points > 0; hidden expected output text required when a record is submitted; common answer text required when a record is submitted
- **Success result**: question and local simulated-validation reference records are saved
- **Privacy contract**: hidden expected outputs and common answers are never rendered on candidate pages

## Candidate Attempt Start

### Start Assessment

- **Route name**: `candidate.assessments.start`
- **Actor**: Candidate
- **Preconditions**: candidate owns the application; assessment is active; cooldown is not active; question-bank rules can be satisfied
- **Success result**: candidate assessment attempt is created, randomized question snapshot is stored, server deadline is set, candidate is redirected to attempt page
- **Failure results**: if cooldown is active, show next eligible date; if bank is insufficient, block start and alert HR; if unauthorized, deny access

## Candidate Attempt Taking

### Show Attempt

- **Route name**: `candidate.assessments.show`
- **Actor**: Candidate
- **Output**: own question snapshot, saved answers, remaining time, candidate-safe instructions
- **Privacy contract**: no hidden expected outputs, common answers, other candidate submissions, or HR-only flags

### Save Answer

- **Route name**: `candidate.assessments.answers.update`
- **Actor**: Candidate
- **Input fields**: answer text and optional stated code output
- **Preconditions**: candidate owns active attempt; server deadline has not passed
- **Success result**: answer is saved with timestamp
- **Failure result**: expired or submitted attempts reject further scoring-affecting saves

### Heartbeat

- **Route name**: `candidate.assessments.heartbeat`
- **Actor**: Candidate browser for the candidate's own active attempt
- **Input fields**: remaining seconds
- **Success result**: remaining seconds and heartbeat timestamp are saved
- **Authority contract**: server deadline remains authoritative when heartbeat is stale, missing, delayed, or inconsistent

### Submit Attempt

- **Route name**: `candidate.assessments.submit`
- **Actor**: Candidate
- **Preconditions**: candidate owns active attempt
- **Success result**: attempt becomes submitted or expired based on deadline; saved answers before cutoff are scored; simulated validation and plagiarism results are recorded
- **Failure result**: unauthorized candidates are denied access

## Candidate Results

### View Result

- **Route name**: `candidate.assessments.result`
- **Actor**: Candidate
- **Output**: own score/status and candidate-safe feedback
- **Privacy contract**: no hidden expected outputs, common answers, other candidate submissions, or HR-only integrity-review material

## HR Attempt Review

### Review Attempt

- **Route name**: `hr.candidate-assessments.show`
- **Actor**: HR Admin
- **Output**: candidate attempt, saved answers, score, focus/integrity events, simulated output match, simulated plagiarism similarity, HR review flags
- **Decision contract**: simulated plagiarism similarity >= 80% is a review flag only and does not automatically reject the candidate
- **Snapshot contract**: completed simulated results remain unchanged when HR later edits expected-output or common-answer records

## HR Assessment Results

### Review Requisition Assessment Results

- **Route name**: `hr.assessment-results.index`
- **Actor**: HR Admin
- **Output**: attempts for a requisition, statuses, scores, and integrity-event counts

## HR Assessment Details

### View Assessment

- **Route name**: `hr.assessments.show`
- **Actor**: HR Admin
- **Output**: assessment configuration, question counts, rule sufficiency warnings, cooldown, attempt list, adaptive difficulty suggestion
- **Adaptive contract**: at least five completed attempts are required; average <= 50% suggests easier, average >= 80% suggests harder, otherwise unchanged
