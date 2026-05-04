CREATE DATABASE IF NOT EXISTS srim CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE srim;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS post_offer_audit_records;
DROP TABLE IF EXISTS onboarding;
DROP TABLE IF EXISTS offers;
DROP TABLE IF EXISTS final_evaluations;
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
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL,
  KEY idx_users_role_status (role, status)
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
  target_user_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(48) NOT NULL,
  old_values JSON NULL,
  new_values JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_account_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_account_audit_target FOREIGN KEY (target_user_id) REFERENCES users(user_id) ON DELETE CASCADE
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
  CONSTRAINT fk_jobs_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE RESTRICT,
  CONSTRAINT fk_jobs_created_by FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT,
  CONSTRAINT fk_jobs_approved_by FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL,
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

CREATE TABLE applications (
  application_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  job_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'APPLIED',
  match_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_applications_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE RESTRICT,
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
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_assessments_job FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id) ON DELETE RESTRICT,
  KEY idx_assessments_job_type (job_id, type)
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

CREATE TABLE candidate_assessments (
  ca_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  candidate_id BIGINT UNSIGNED NOT NULL,
  assessment_id BIGINT UNSIGNED NOT NULL,
  start_time TIMESTAMP NULL,
  end_time TIMESTAMP NULL,
  expires_at TIMESTAMP NULL,
  status VARCHAR(40) NOT NULL DEFAULT 'IN_PROGRESS',
  score DECIMAL(6,3) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_attempts_application FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE,
  CONSTRAINT fk_attempts_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE,
  CONSTRAINT fk_attempts_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id) ON DELETE CASCADE,
  UNIQUE KEY uq_attempt_candidate_assessment (candidate_id, assessment_id),
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
  saved_at TIMESTAMP NULL,
  finalized_at TIMESTAMP NULL,
  is_correct BOOLEAN NULL,
  awarded_points DECIMAL(6,2) NULL,
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
  status VARCHAR(40) NOT NULL DEFAULT 'SCHEDULED',
  created_by BIGINT UNSIGNED NOT NULL,
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
