CREATE DATABASE IF NOT EXISTS srim CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE srim;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS feedback_governance_audit_records;
DROP TABLE IF EXISTS competency_gap_snapshots;
DROP TABLE IF EXISTS job_competency_benchmarks;
DROP TABLE IF EXISTS evaluation_debrief_records;
DROP TABLE IF EXISTS candidate_interview_sentiment;
DROP TABLE IF EXISTS feedback_concern_flags;
DROP TABLE IF EXISTS compliance_audit_events;
DROP TABLE IF EXISTS archive_actions;
DROP TABLE IF EXISTS compliance_run_check_findings;
DROP TABLE IF EXISTS compliance_run_check_batches;
DROP TABLE IF EXISTS candidate_demographics;
DROP TABLE IF EXISTS normalized_evaluation_snapshots;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS post_offer_audit_records;
DROP TABLE IF EXISTS onboarding;
DROP TABLE IF EXISTS offers;
DROP TABLE IF EXISTS final_evaluations;
DROP TABLE IF EXISTS workspace_history_records;
DROP TABLE IF EXISTS simulated_coding_workspaces;
DROP TABLE IF EXISTS interview_extension_requests;
DROP TABLE IF EXISTS interview_briefing_snapshots;
DROP TABLE IF EXISTS panel_recommendation_snapshots;
DROP TABLE IF EXISTS staff_panel_capabilities;
DROP TABLE IF EXISTS interview_audit_records;
DROP TABLE IF EXISTS interview_feedback;
DROP TABLE IF EXISTS interviewers_assignment;
DROP TABLE IF EXISTS interviews;
DROP TABLE IF EXISTS assessment_integrity_events;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS candidate_assessment_questions;
DROP TABLE IF EXISTS candidate_assessments;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS assessments;
DROP TABLE IF EXISTS application_status_histories;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS requisition_governance_audit;
DROP TABLE IF EXISTS job_board_sync_records;
DROP TABLE IF EXISTS job_board_platforms;
DROP TABLE IF EXISTS requisition_template_versions;
DROP TABLE IF EXISTS requisition_approval_steps;
DROP TABLE IF EXISTS job_requisition_status_histories;
DROP TABLE IF EXISTS job_requisitions;
DROP TABLE IF EXISTS account_audit_records;
DROP TABLE IF EXISTS candidates;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE departments (
  department_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description TEXT NULL,
  parent_department_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_departments_parent FOREIGN KEY (parent_department_id) REFERENCES departments(department_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE users (
  user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NULL,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(32) NOT NULL DEFAULT 'CANDIDATE',
  status VARCHAR(32) NOT NULL DEFAULT 'ACTIVE',
  is_department_head BOOLEAN NOT NULL DEFAULT FALSE,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL,
  KEY idx_users_role_status (role, status)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  notification_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(80) NOT NULL,
  reference_id BIGINT UNSIGNED NULL,
  reference_type VARCHAR(40) NULL,
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  read_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  KEY idx_notifications_user_read (user_id, is_read),
  KEY idx_notifications_reference (reference_type, reference_id),
  KEY idx_notifications_type_created (type, created_at)
) ENGINE=InnoDB;

CREATE TABLE candidates (
  candidate_id BIGINT UNSIGNED PRIMARY KEY,
  phone VARCHAR(40) NOT NULL,
  current_title VARCHAR(160) NULL,
  years_experience TINYINT UNSIGNED NOT NULL DEFAULT 0,
  location VARCHAR(160) NULL,
  resume_url VARCHAR(2048) NULL,
  skill_keywords TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_candidates_user FOREIGN KEY (candidate_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE account_audit_records (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  target_user_id BIGINT UNSIGNED NULL,
  action VARCHAR(48) NOT NULL,
  old_values JSON NULL,
  new_values JSON NOT NULL,
  -- For retention audits (CANDIDATE_DELETED / CANDIDATE_ANONYMIZED):
  -- new_values contains the snapshot or redaction details.
  -- target_user_id becomes NULL upon candidate hard deletion to preserve audit evidence.
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_account_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_account_audit_target FOREIGN KEY (target_user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE job_requisitions (
  job_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'DRAFT',
  created_by BIGINT UNSIGNED NOT NULL,
  approved_by BIGINT UNSIGNED NULL,
  approved_at TIMESTAMP NULL,
  opened_at TIMESTAMP NULL,
  closed_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  archived_at TIMESTAMP NULL,
  archived_by BIGINT UNSIGNED NULL,
  CONSTRAINT fk_jobs_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE RESTRICT,
  CONSTRAINT fk_jobs_created_by FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_jobs_approved_by FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_jobs_archived_by FOREIGN KEY (archived_by) REFERENCES users(user_id) ON DELETE SET NULL,
  KEY idx_jobs_status (status)
) ENGINE=InnoDB;

CREATE TABLE job_requisition_status_histories (
  history_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  old_status VARCHAR(40) NULL,
  new_status VARCHAR(40) NOT NULL,
  reason TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_job_history_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_job_history_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE requisition_approval_steps (
  step_id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id        BIGINT UNSIGNED NOT NULL,
  approver_id   BIGINT UNSIGNED NOT NULL,
  decision      VARCHAR(20) NOT NULL,          -- 'APPROVED' or 'REJECTED'
  comments      TEXT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_approval_steps_job FOREIGN KEY (job_id)
    REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_approval_steps_approver FOREIGN KEY (approver_id)
    REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_approval_steps_job (job_id),
  KEY idx_approval_steps_approver (approver_id)
) ENGINE=InnoDB;

CREATE TABLE requisition_template_versions (
  version_id     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id         BIGINT UNSIGNED NOT NULL,
  version_number INT UNSIGNED NOT NULL,
  description_body TEXT NOT NULL,
  requirements_body TEXT NOT NULL,
  created_by     BIGINT UNSIGNED NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_template_versions_job FOREIGN KEY (job_id)
    REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_template_versions_user FOREIGN KEY (created_by)
    REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_template_version_job_num (job_id, version_number),
  KEY idx_template_versions_job (job_id)
) ENGINE=InnoDB;

CREATE TABLE job_board_platforms (
  platform_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL UNIQUE,
  is_active   BOOLEAN NOT NULL DEFAULT TRUE,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO job_board_platforms (name) VALUES
  ('LinkedIn Jobs'),
  ('Indeed'),
  ('Glassdoor'),
  ('Internal Careers Page')
ON DUPLICATE KEY UPDATE name = VALUES(name);

CREATE TABLE job_board_sync_records (
  sync_id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id         BIGINT UNSIGNED NOT NULL,
  platform_id    BIGINT UNSIGNED NOT NULL,
  payload_summary TEXT NOT NULL,                 -- JSON: {title, department, description_excerpt, requirements}
  status         VARCHAR(40) NOT NULL,           -- 'QUEUED', 'PUBLISHED', 'UNPUBLISHED', 'FAILED'
  queued_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at   TIMESTAMP NULL,
  created_by     BIGINT UNSIGNED NOT NULL,
  CONSTRAINT fk_sync_records_job FOREIGN KEY (job_id)
    REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_sync_records_platform FOREIGN KEY (platform_id)
    REFERENCES job_board_platforms(platform_id) ON DELETE RESTRICT,
  CONSTRAINT fk_sync_records_user FOREIGN KEY (created_by)
    REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_sync_records_job (job_id),
  KEY idx_sync_records_platform_status (platform_id, status),
  KEY idx_sync_records_job_platform (job_id, platform_id)
) ENGINE=InnoDB;

CREATE TABLE requisition_governance_audit (
  audit_id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id         BIGINT UNSIGNED NOT NULL,
  actor_user_id  BIGINT UNSIGNED NOT NULL,
  action         VARCHAR(60) NOT NULL,
  old_values     JSON NULL,
  new_values     JSON NULL,
  comments       TEXT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gov_audit_job FOREIGN KEY (job_id)
    REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_gov_audit_actor FOREIGN KEY (actor_user_id)
    REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_gov_audit_job (job_id),
  KEY idx_gov_audit_action (action),
  KEY idx_gov_audit_actor (actor_user_id),
  KEY idx_gov_audit_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE applications (
  application_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  job_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'APPLIED',
  match_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  archived_at TIMESTAMP NULL,
  archived_by BIGINT UNSIGNED NULL,
  CONSTRAINT fk_applications_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE RESTRICT,
  CONSTRAINT fk_apps_archived_by FOREIGN KEY (archived_by) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_applications_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_applications_candidate_job (candidate_id, job_id),
  KEY idx_applications_job_status (job_id, status)
) ENGINE=InnoDB;

CREATE TABLE application_status_histories (
  history_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  old_status VARCHAR(40) NULL,
  new_status VARCHAR(40) NOT NULL,
  reason TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_application_history_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE RESTRICT,
  CONSTRAINT fk_application_history_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE assessments (
  assessment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  type VARCHAR(40) NOT NULL DEFAULT 'TECHNICAL',
  duration_minutes INT UNSIGNED NOT NULL,
  cooldown_months INT UNSIGNED NOT NULL DEFAULT 6,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_assessments_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  KEY idx_assessments_job_type (job_id, type)
) ENGINE=InnoDB;

CREATE TABLE assessment_question_rules (
  rule_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  difficulty_level VARCHAR(20) NOT NULL,
  question_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_assessment_rules_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id) ON DELETE CASCADE,
  UNIQUE KEY uq_assessment_rule_difficulty (assessment_id, difficulty_level)
) ENGINE=InnoDB;

CREATE TABLE questions (
  question_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  type VARCHAR(40) NOT NULL DEFAULT 'MCQ',
  difficulty_level VARCHAR(20) NOT NULL DEFAULT 'MEDIUM',
  question_text TEXT NOT NULL,
  options JSON NULL,
  correct_answer TEXT NULL,
  points DECIMAL(6,2) NOT NULL DEFAULT 1,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_questions_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id) ON DELETE CASCADE,
  KEY idx_questions_assessment_difficulty (assessment_id, difficulty_level)
) ENGINE=InnoDB;

CREATE TABLE question_expected_outputs (
  output_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id BIGINT UNSIGNED NOT NULL,
  expected_output LONGTEXT NOT NULL,
  label VARCHAR(120) NULL,
  is_hidden BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_expected_outputs_question FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
  KEY idx_expected_outputs_question (question_id)
) ENGINE=InnoDB;

CREATE TABLE assessment_common_answers (
  common_answer_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NULL,
  answer_text LONGTEXT NOT NULL,
  source_label VARCHAR(120) NULL,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_common_answers_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id) ON DELETE CASCADE,
  CONSTRAINT fk_common_answers_question FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
  KEY idx_common_answers_assessment (assessment_id),
  KEY idx_common_answers_question (question_id)
) ENGINE=InnoDB;

CREATE TABLE candidate_assessments (
  ca_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  candidate_id BIGINT UNSIGNED NOT NULL,
  assessment_id BIGINT UNSIGNED NOT NULL,
  start_time TIMESTAMP NULL,
  end_time TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  remaining_seconds INT UNSIGNED NULL,
  last_heartbeat_at TIMESTAMP NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'IN_PROGRESS',
  score DECIMAL(6,3) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_attempts_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_attempts_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  CONSTRAINT fk_attempts_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id) ON DELETE CASCADE,
  KEY idx_attempt_candidate_assessment (candidate_id, assessment_id),
  KEY idx_attempt_application_status (application_id, status)
) ENGINE=InnoDB;

CREATE TABLE candidate_assessment_questions (
  attempt_question_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ca_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NULL,
  display_order INT UNSIGNED NOT NULL,
  question_type VARCHAR(40) NOT NULL,
  question_text TEXT NOT NULL,
  options JSON NULL,
  correct_answer TEXT NULL,
  points DECIMAL(6,2) NOT NULL,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_attempt_questions_attempt FOREIGN KEY (ca_id) REFERENCES candidate_assessments(ca_id) ON DELETE CASCADE,
  CONSTRAINT fk_attempt_questions_question FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE SET NULL,
  UNIQUE KEY uq_attempt_question_order (ca_id, display_order),
  UNIQUE KEY uq_attempt_question_source (ca_id, question_id)
) ENGINE=InnoDB;

CREATE TABLE submissions (
  submission_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ca_id BIGINT UNSIGNED NOT NULL,
  attempt_question_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NULL,
  answer_text LONGTEXT NULL,
  code_output LONGTEXT NULL,
  saved_at TIMESTAMP NULL,
  finalized_at TIMESTAMP NULL,
  is_correct BOOLEAN NULL,
  output_matched BOOLEAN NULL,
  awarded_points DECIMAL(6,2) NULL,
  plagiarism_score DECIMAL(6,3) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_submissions_attempt FOREIGN KEY (ca_id) REFERENCES candidate_assessments(ca_id) ON DELETE CASCADE,
  CONSTRAINT fk_submissions_attempt_question FOREIGN KEY (attempt_question_id) REFERENCES candidate_assessment_questions(attempt_question_id) ON DELETE CASCADE,
  CONSTRAINT fk_submissions_question FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE SET NULL,
  UNIQUE KEY uq_submission_attempt_question (ca_id, attempt_question_id)
) ENGINE=InnoDB;

CREATE TABLE assessment_integrity_events (
  event_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ca_id BIGINT UNSIGNED NOT NULL,
  event_type VARCHAR(40) NOT NULL,
  occurred_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  metadata JSON NULL,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_integrity_attempt FOREIGN KEY (ca_id) REFERENCES candidate_assessments(ca_id) ON DELETE CASCADE,
  KEY idx_integrity_attempt_type (ca_id, event_type)
) ENGINE=InnoDB;

-- Schema Verification:
-- interviews requires indexes on application_id, scheduled_at, and status for conflict detection.
-- interviewers_assignment requires unique key on interview_id + interviewer_id.
CREATE TABLE interviews (
  interview_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  interview_type VARCHAR(40) NOT NULL,
  scheduled_at TIMESTAMP NOT NULL,
  duration_minutes INT UNSIGNED NOT NULL,
  extended_duration_minutes INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(40) NOT NULL DEFAULT 'SCHEDULED',
  created_by BIGINT UNSIGNED NOT NULL,
  last_extension_decision_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_interviews_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE RESTRICT,
  CONSTRAINT fk_interviews_created_by FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_interviews_application (application_id),
  KEY idx_interviews_scheduled_at (scheduled_at),
  KEY idx_interviews_status (status)
) ENGINE=InnoDB;

CREATE TABLE interviewers_assignment (
  assignment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  interviewer_id BIGINT UNSIGNED NOT NULL,
  role_in_panel VARCHAR(40) NOT NULL,
  is_shadowing BOOLEAN NOT NULL DEFAULT FALSE,
  assignment_source VARCHAR(40) NOT NULL DEFAULT 'MANUAL',
  override_reason TEXT NULL,
  conflict_overridden BOOLEAN NOT NULL DEFAULT FALSE,
  assigned_by BIGINT UNSIGNED NULL,
  assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_assignment_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  CONSTRAINT fk_assignment_interviewer FOREIGN KEY (interviewer_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_assignment_interview_user (interview_id, interviewer_id),
  KEY idx_assignment_interviewer (interviewer_id)
) ENGINE=InnoDB;

CREATE TABLE interview_feedback (
  feedback_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  interviewer_id BIGINT UNSIGNED NOT NULL,
  technical_score DECIMAL(4,2) NOT NULL,
  communication_score DECIMAL(4,2) NOT NULL,
  culture_fit_score DECIMAL(4,2) NOT NULL,
  overall_score DECIMAL(4,2) NOT NULL,
  comments TEXT NOT NULL,
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_feedback_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  CONSTRAINT fk_feedback_interviewer FOREIGN KEY (interviewer_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_feedback_interview_user (interview_id, interviewer_id)
) ENGINE=InnoDB;

CREATE TABLE interview_audit_records (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(48) NOT NULL,
  changed_fields JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_interview_audit_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  CONSTRAINT fk_interview_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_interview_audit_interview (interview_id),
  KEY idx_interview_audit_action (action)
) ENGINE=InnoDB;

CREATE TABLE staff_panel_capabilities (
  capability_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  can_represent_hr BOOLEAN NOT NULL DEFAULT FALSE,
  can_lead_technical BOOLEAN NOT NULL DEFAULT FALSE,
  can_interview BOOLEAN NOT NULL DEFAULT FALSE,
  can_observe BOOLEAN NOT NULL DEFAULT FALSE,
  specialization VARCHAR(180) NULL,
  seniority_level VARCHAR(80) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_staff_capability_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY uq_staff_capability_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE panel_recommendation_snapshots (
  recommendation_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  requested_start_at TIMESTAMP NOT NULL,
  requested_duration_minutes INT UNSIGNED NOT NULL,
  required_panel_mix JSON NOT NULL,
  recommendation_payload JSON NOT NULL,
  accepted_interview_id BIGINT UNSIGNED NULL,
  generated_by BIGINT UNSIGNED NOT NULL,
  generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_panel_recommendation_app FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_panel_recommendation_user FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_panel_recommendation_interview FOREIGN KEY (accepted_interview_id) REFERENCES interviews(interview_id) ON DELETE SET NULL,
  KEY idx_recommendation_app (application_id)
) ENGINE=InnoDB;

CREATE TABLE interview_briefing_snapshots (
  briefing_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  candidate_summary TEXT NULL,
  assessment_summary TEXT NULL,
  job_requirements_summary TEXT NULL,
  missing_data_flags JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_briefing_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  UNIQUE KEY uq_briefing_interview (interview_id)
) ENGINE=InnoDB;

CREATE TABLE simulated_coding_workspaces (
  workspace_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  prompt_text LONGTEXT NULL,
  code_text LONGTEXT NULL,
  candidate_run_notes LONGTEXT NULL,
  interviewer_notes LONGTEXT NULL,
  version_number INT UNSIGNED NOT NULL DEFAULT 1,
  last_saved_by BIGINT UNSIGNED NOT NULL,
  last_saved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_workspace_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  CONSTRAINT fk_workspace_saved_by FOREIGN KEY (last_saved_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_workspace_interview (interview_id)
) ENGINE=InnoDB;

CREATE TABLE workspace_history_records (
  history_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workspace_id BIGINT UNSIGNED NOT NULL,
  interview_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  changed_section VARCHAR(40) NOT NULL,
  previous_version_number INT UNSIGNED NOT NULL,
  new_version_number INT UNSIGNED NOT NULL,
  change_summary TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_workspace_history_workspace FOREIGN KEY (workspace_id) REFERENCES simulated_coding_workspaces(workspace_id) ON DELETE CASCADE,
  CONSTRAINT fk_workspace_history_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  CONSTRAINT fk_workspace_history_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_workspace_history_workspace (workspace_id)
) ENGINE=InnoDB;

CREATE TABLE interview_extension_requests (
  extension_request_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  requested_by BIGINT UNSIGNED NOT NULL,
  requested_minutes INT UNSIGNED NOT NULL,
  request_reason TEXT NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'PENDING',
  approved_minutes INT UNSIGNED NULL,
  decided_by BIGINT UNSIGNED NULL,
  decision_reason TEXT NULL,
  requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  decided_at TIMESTAMP NULL,
  cancelled_at TIMESTAMP NULL,
  CONSTRAINT fk_extension_req_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  CONSTRAINT fk_extension_req_requester FOREIGN KEY (requested_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_extension_req_decider FOREIGN KEY (decided_by) REFERENCES users(user_id) ON DELETE SET NULL,
  KEY idx_extension_req_interview (interview_id),
  KEY idx_extension_req_status (status)
) ENGINE=InnoDB;

CREATE TABLE final_evaluations (
  evaluation_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  aggregate_score DECIMAL(5,2) NULL,
  recommendation VARCHAR(40) NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'EVALUATED',
  decision_notes TEXT NOT NULL,
  partial_evidence_acknowledged BOOLEAN NOT NULL DEFAULT FALSE,
  evaluated_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_evaluations_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE RESTRICT,
  CONSTRAINT fk_evaluations_evaluated_by FOREIGN KEY (evaluated_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_evaluations_application (application_id),
  CONSTRAINT chk_eval_score CHECK (aggregate_score >= 0 AND aggregate_score <= 100)
) ENGINE=InnoDB;

CREATE TABLE offers (
  offer_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  offer_sequence INT UNSIGNED NOT NULL,
  replaces_offer_id BIGINT UNSIGNED NULL,
  offer_type VARCHAR(40) NOT NULL,
  ctc DECIMAL(12,2) NOT NULL,
  bonus DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock_options DECIMAL(12,2) NOT NULL DEFAULT 0,
  status VARCHAR(40) NOT NULL DEFAULT 'DRAFT',
  expiry_date TIMESTAMP NOT NULL,
  sent_at TIMESTAMP NULL,
  accepted_at TIMESTAMP NULL,
  rejected_at TIMESTAMP NULL,
  expired_at TIMESTAMP NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_offers_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE RESTRICT,
  CONSTRAINT fk_offers_replaces FOREIGN KEY (replaces_offer_id) REFERENCES offers(offer_id) ON DELETE SET NULL,
  CONSTRAINT fk_offers_created_by FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_offers_app_seq (application_id, offer_sequence),
  CONSTRAINT chk_offer_ctc CHECK (ctc >= 0),
  CONSTRAINT chk_offer_bonus CHECK (bonus >= 0),
  CONSTRAINT chk_offer_stock CHECK (stock_options >= 0)
) ENGINE=InnoDB;

CREATE TABLE onboarding (
  onboarding_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offer_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'PENDING',
  start_date DATE NULL,
  documents_completed BOOLEAN NOT NULL DEFAULT FALSE,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_onboarding_offer FOREIGN KEY (offer_id) REFERENCES offers(offer_id) ON DELETE RESTRICT,
  CONSTRAINT fk_onboarding_created_by FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_onboarding_offer (offer_id)
) ENGINE=InnoDB;

CREATE TABLE post_offer_audit_records (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  offer_id BIGINT UNSIGNED NULL,
  onboarding_id BIGINT UNSIGNED NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(60) NOT NULL,
  changed_fields JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_po_audit_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_po_audit_offer FOREIGN KEY (offer_id) REFERENCES offers(offer_id) ON DELETE CASCADE,
  CONSTRAINT fk_po_audit_onboarding FOREIGN KEY (onboarding_id) REFERENCES onboarding(onboarding_id) ON DELETE CASCADE,
  CONSTRAINT fk_po_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_po_audit_application (application_id),
  KEY idx_po_audit_action (action)
) ENGINE=InnoDB;

INSERT INTO departments (name, description) VALUES
('Human Resources', 'Recruitment and HR operations'),
('Engineering', 'Technical hiring department')
ON DUPLICATE KEY UPDATE description = VALUES(description);

CREATE TABLE screening_configs (
  config_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_screening_configs_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_screening_configs_user FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_screening_configs_job (job_id, is_active)
) ENGINE=InnoDB;

CREATE TABLE screening_skills (
  skill_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  config_id BIGINT UNSIGNED NOT NULL,
  skill_name VARCHAR(160) NOT NULL,
  weight DECIMAL(5,2) NOT NULL,
  evidence_field VARCHAR(80) NOT NULL DEFAULT 'skill_keywords',
  CONSTRAINT fk_screening_skills_config FOREIGN KEY (config_id) REFERENCES screening_configs(config_id) ON DELETE CASCADE,
  CONSTRAINT chk_screening_skill_weight CHECK (weight > 0 AND weight <= 100),
  UNIQUE KEY uq_screening_skill_name (config_id, skill_name)
) ENGINE=InnoDB;

CREATE TABLE screening_thresholds (
  threshold_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  config_id BIGINT UNSIGNED NOT NULL,
  min_score TINYINT UNSIGNED NOT NULL,
  max_score TINYINT UNSIGNED NOT NULL,
  target_status VARCHAR(40) NOT NULL,
  CONSTRAINT fk_screening_thresholds_config FOREIGN KEY (config_id) REFERENCES screening_configs(config_id) ON DELETE CASCADE,
  CONSTRAINT chk_screening_threshold_range CHECK (min_score <= max_score AND min_score >= 0 AND max_score <= 100)
) ENGINE=InnoDB;

CREATE TABLE screening_audit_records (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(60) NOT NULL,
  entity_type VARCHAR(40) NULL,
  entity_id BIGINT UNSIGNED NULL,
  old_values JSON NULL,
  new_values JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_screening_audit_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  CONSTRAINT fk_screening_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_screening_audit_job (job_id),
  KEY idx_screening_audit_action (action),
  KEY idx_screening_audit_actor (actor_user_id),
  KEY idx_screening_audit_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE candidate_merge_log (
  merge_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  primary_candidate_id BIGINT UNSIGNED NOT NULL,
  duplicate_candidate_id BIGINT UNSIGNED NOT NULL,
  merged_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  merged_by BIGINT UNSIGNED NOT NULL,
  notes TEXT,
  decision_type VARCHAR(20) NOT NULL DEFAULT 'MERGE',
  confidence_category VARCHAR(20) NULL,
  job_id BIGINT UNSIGNED NULL,
  matching_evidence JSON NULL,
  CONSTRAINT fk_candidate_merge_primary FOREIGN KEY (primary_candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  CONSTRAINT fk_candidate_merge_duplicate FOREIGN KEY (duplicate_candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  CONSTRAINT fk_candidate_merge_user FOREIGN KEY (merged_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_merge_log_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE SET NULL,
  CONSTRAINT chk_candidate_merge_not_same CHECK (primary_candidate_id <> duplicate_candidate_id),
  UNIQUE KEY uq_candidate_merge_pair (primary_candidate_id, duplicate_candidate_id)
) ENGINE=InnoDB;

CREATE TABLE normalized_evaluation_snapshots (
  snapshot_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  interview_id BIGINT UNSIGNED NULL,
  calculated_by BIGINT UNSIGNED NULL,
  raw_score_summary JSON NOT NULL,
  normalized_score_summary JSON NOT NULL,
  aggregate_score DECIMAL(5,2) NOT NULL,
  recommendation VARCHAR(40) NOT NULL,
  normalization_status VARCHAR(40) NOT NULL,
  fallback_reasons JSON NULL,
  included_feedback_count INT UNSIGNED NOT NULL DEFAULT 0,
  missing_feedback_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_snapshots_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_snapshots_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE SET NULL,
  CONSTRAINT fk_snapshots_user FOREIGN KEY (calculated_by) REFERENCES users(user_id) ON DELETE SET NULL,
  KEY idx_snapshots_application (application_id)
) ENGINE=InnoDB;

CREATE TABLE feedback_concern_flags (
  flag_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  interview_id BIGINT UNSIGNED NULL,
  candidate_id BIGINT UNSIGNED NOT NULL,
  category VARCHAR(60) NOT NULL,
  severity VARCHAR(40) NOT NULL,
  explanation TEXT NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'OPEN',
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_by BIGINT UNSIGNED NULL,
  resolved_at TIMESTAMP NULL,
  resolution_rationale TEXT NULL,
  CONSTRAINT fk_concern_flags_app FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_concern_flags_int FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE SET NULL,
  CONSTRAINT fk_concern_flags_cand FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  CONSTRAINT fk_concern_flags_creator FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_concern_flags_resolver FOREIGN KEY (resolved_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  KEY idx_concern_flags_app_status (application_id, status)
) ENGINE=InnoDB;

CREATE TABLE candidate_interview_sentiment (
  sentiment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  application_id BIGINT UNSIGNED NOT NULL,
  interview_id BIGINT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NULL,
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sentiment_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  CONSTRAINT fk_sentiment_app FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_sentiment_interview FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  UNIQUE KEY uq_sentiment_candidate_interview (candidate_id, interview_id)
) ENGINE=InnoDB;

CREATE TABLE evaluation_debrief_records (
  debrief_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  interview_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'PENDING',
  participants JSON NULL,
  consensus_level VARCHAR(40) NULL,
  dissent_notes TEXT NULL,
  final_recommendation VARCHAR(40) NULL,
  rationale TEXT NULL,
  next_action VARCHAR(60) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_by BIGINT UNSIGNED NULL,
  completed_at TIMESTAMP NULL,
  CONSTRAINT fk_debriefs_app FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_debriefs_int FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  CONSTRAINT fk_debriefs_completer FOREIGN KEY (completed_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  UNIQUE KEY uq_debrief_interview (interview_id),
  KEY idx_debriefs_app_status (application_id, status)
) ENGINE=InnoDB;

CREATE TABLE job_competency_benchmarks (
  benchmark_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  competency VARCHAR(120) NOT NULL,
  benchmark_score DECIMAL(5,2) NOT NULL,
  weight DECIMAL(5,2) NULL,
  source VARCHAR(80) NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_benchmarks_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE CASCADE,
  CONSTRAINT fk_benchmarks_updater FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL,
  UNIQUE KEY uq_benchmark_job_competency (job_id, competency),
  CONSTRAINT chk_benchmark_score CHECK (benchmark_score >= 0 AND benchmark_score <= 10)
) ENGINE=InnoDB;

CREATE TABLE competency_gap_snapshots (
  gap_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  snapshot_id BIGINT UNSIGNED NOT NULL,
  benchmark_id BIGINT UNSIGNED NOT NULL,
  competency VARCHAR(120) NOT NULL,
  candidate_score DECIMAL(5,2) NOT NULL,
  benchmark_score DECIMAL(5,2) NOT NULL,
  gap_ratio DECIMAL(5,2) NOT NULL,
  severity VARCHAR(40) NOT NULL,
  CONSTRAINT fk_gap_snapshots_snapshot FOREIGN KEY (snapshot_id) REFERENCES normalized_evaluation_snapshots(snapshot_id) ON DELETE CASCADE,
  CONSTRAINT fk_gap_snapshots_benchmark FOREIGN KEY (benchmark_id) REFERENCES job_competency_benchmarks(benchmark_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE feedback_governance_audit_records (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED NULL,
  actor_role VARCHAR(60) NULL,
  application_id BIGINT UNSIGNED NULL,
  interview_id BIGINT UNSIGNED NULL,
  entity_type VARCHAR(60) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(80) NOT NULL,
  old_values JSON NULL,
  new_values JSON NULL,
  reason TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fg_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_fg_audit_app FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_fg_audit_int FOREIGN KEY (interview_id) REFERENCES interviews(interview_id) ON DELETE CASCADE,
  KEY idx_fg_audit_action (action),
  KEY idx_fg_audit_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE candidate_demographics (
  demographic_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  gender_category VARCHAR(80) NULL,
  ethnicity_category VARCHAR(80) NULL,
  disability_category VARCHAR(80) NULL,
  veteran_status_category VARCHAR(80) NULL,
  consent_flag BOOLEAN NOT NULL DEFAULT FALSE,
  withdrawn_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cand_demographics_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  UNIQUE KEY uq_cand_demographics_candidate (candidate_id)
) ENGINE=InnoDB;

CREATE TABLE compliance_run_check_batches (
  batch_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  check_type VARCHAR(60) NOT NULL,
  selected_scope JSON NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'STARTED',
  total_findings INT UNSIGNED NOT NULL DEFAULT 0,
  new_notifications INT UNSIGNED NOT NULL DEFAULT 0,
  duplicate_notifications_skipped INT UNSIGNED NOT NULL DEFAULT 0,
  archive_recommendations INT UNSIGNED NOT NULL DEFAULT 0,
  blocked_actions INT UNSIGNED NOT NULL DEFAULT 0,
  summary_message TEXT NULL,
  started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  CONSTRAINT fk_crc_batches_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE compliance_run_check_findings (
  finding_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  batch_id BIGINT UNSIGNED NOT NULL,
  finding_type VARCHAR(60) NOT NULL,
  severity VARCHAR(40) NOT NULL,
  entity_type VARCHAR(60) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  candidate_id BIGINT UNSIGNED NULL,
  responsible_user_id BIGINT UNSIGNED NULL,
  due_date TIMESTAMP NULL,
  recommended_action VARCHAR(80) NULL,
  existing_notification_id BIGINT UNSIGNED NULL,
  created_notification_id BIGINT UNSIGNED NULL,
  archive_eligibility_status VARCHAR(40) NULL,
  reason TEXT NULL,
  resolved_marker BOOLEAN NOT NULL DEFAULT FALSE,
  detected_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_crc_findings_batch FOREIGN KEY (batch_id) REFERENCES compliance_run_check_batches(batch_id) ON DELETE CASCADE,
  CONSTRAINT fk_crc_findings_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE SET NULL,
  CONSTRAINT fk_crc_findings_responsible FOREIGN KEY (responsible_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  CONSTRAINT fk_crc_findings_exist_notif FOREIGN KEY (existing_notification_id) REFERENCES notifications(notification_id) ON DELETE SET NULL,
  CONSTRAINT fk_crc_findings_new_notif FOREIGN KEY (created_notification_id) REFERENCES notifications(notification_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE archive_actions (
  archive_action_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entity_type VARCHAR(60) NOT NULL,
  entity_id BIGINT UNSIGNED NOT NULL,
  action_status VARCHAR(40) NOT NULL,
  reason TEXT NOT NULL,
  eligibility_snapshot JSON NULL,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  previous_active_status VARCHAR(40) NULL,
  new_archive_status VARCHAR(40) NULL,
  affected_record_summary JSON NULL,
  action_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_archive_actions_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE compliance_audit_events (
  audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  actor_role VARCHAR(60) NULL,
  entity_type VARCHAR(60) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  old_values JSON NULL,
  new_values JSON NULL,
  reason TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_comp_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE background_checks (
  background_check_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  candidate_id BIGINT UNSIGNED NOT NULL,
  check_type VARCHAR(60) NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'REQUESTED',
  result_notes TEXT NULL,
  requested_by BIGINT UNSIGNED NOT NULL,
  requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  completed_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_bgcheck_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_bgcheck_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  CONSTRAINT fk_bgcheck_requested_by FOREIGN KEY (requested_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_bgcheck_completed_by FOREIGN KEY (completed_by) REFERENCES users(user_id) ON DELETE SET NULL,
  KEY idx_bgcheck_app_type (application_id, check_type),
  KEY idx_bgcheck_status (status)
) ENGINE=InnoDB;

CREATE TABLE referrals (
  referral_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  candidate_id BIGINT UNSIGNED NOT NULL,
  referrer_user_id BIGINT UNSIGNED NULL,
  referrer_name VARCHAR(255) NULL,
  referrer_email VARCHAR(255) NULL,
  referral_source VARCHAR(120) NOT NULL DEFAULT 'INTERNAL',
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_referral_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_referral_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  CONSTRAINT fk_referral_referrer FOREIGN KEY (referrer_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
  UNIQUE KEY uq_referral_application (application_id)
) ENGINE=InnoDB;

CREATE TABLE referral_rewards (
  reward_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  referral_id BIGINT UNSIGNED NOT NULL,
  reward_status VARCHAR(60) NOT NULL DEFAULT 'PENDING',
  reward_amount DECIMAL(10,2) NULL,
  reward_type VARCHAR(60) NULL DEFAULT 'MONETARY',
  approved_by BIGINT UNSIGNED NULL,
  approved_at TIMESTAMP NULL,
  paid_at TIMESTAMP NULL,
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_reward_referral FOREIGN KEY (referral_id) REFERENCES referrals(referral_id) ON DELETE CASCADE,
  CONSTRAINT fk_reward_approved_by FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL,
  UNIQUE KEY uq_reward_referral (referral_id)
) ENGINE=InnoDB;
