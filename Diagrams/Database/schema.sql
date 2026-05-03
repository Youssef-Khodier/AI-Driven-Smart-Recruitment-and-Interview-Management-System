-- SRIM ERD-Based Database Schema
-- AI-Driven Smart Recruitment & Interview Management System
-- Target DBMS: MySQL 8+

CREATE DATABASE IF NOT EXISTS srim
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE srim;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS onboarding;
DROP TABLE IF EXISTS offers;
DROP TABLE IF EXISTS final_evaluations;
DROP TABLE IF EXISTS interview_feedback;
DROP TABLE IF EXISTS interviewers_assignment;
DROP TABLE IF EXISTS interviews;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS candidate_assessments;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS assessments;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS candidate_merge_log;
DROP TABLE IF EXISTS candidates;
DROP TABLE IF EXISTS job_requisitions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE departments (
  department_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description TEXT,
  parent_department_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_departments_parent
    FOREIGN KEY (parent_department_id) REFERENCES departments(department_id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE users (
  user_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NULL,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('HR_ADMIN', 'INTERVIEWER', 'CANDIDATE') NOT NULL,
  status ENUM('ACTIVE', 'INACTIVE') NOT NULL DEFAULT 'ACTIVE',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_department
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
    ON DELETE SET NULL,
  KEY idx_users_role_status (role, status),
  KEY idx_users_department (department_id)
) ENGINE=InnoDB;

CREATE TABLE candidates (
  candidate_id BIGINT UNSIGNED PRIMARY KEY,
  phone VARCHAR(40),
  current_title VARCHAR(160),
  years_experience DECIMAL(4,1) NOT NULL DEFAULT 0,
  location VARCHAR(160),
  resume_url VARCHAR(700),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_candidates_user
    FOREIGN KEY (candidate_id) REFERENCES users(user_id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE candidate_merge_log (
  merge_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  primary_candidate_id BIGINT UNSIGNED NOT NULL,
  duplicate_candidate_id BIGINT UNSIGNED NOT NULL,
  merged_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  merged_by BIGINT UNSIGNED NOT NULL,
  notes TEXT,
  CONSTRAINT fk_candidate_merge_primary
    FOREIGN KEY (primary_candidate_id) REFERENCES candidates(candidate_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_candidate_merge_duplicate
    FOREIGN KEY (duplicate_candidate_id) REFERENCES candidates(candidate_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_candidate_merge_user
    FOREIGN KEY (merged_by) REFERENCES users(user_id)
    ON DELETE RESTRICT,
  CONSTRAINT chk_candidate_merge_not_same
    CHECK (primary_candidate_id <> duplicate_candidate_id),
  UNIQUE KEY uq_candidate_merge_pair (primary_candidate_id, duplicate_candidate_id)
) ENGINE=InnoDB;

CREATE TABLE job_requisitions (
  job_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT,
  status ENUM('DRAFT', 'PENDING', 'APPROVED', 'OPEN', 'CLOSED') NOT NULL DEFAULT 'DRAFT',
  created_by BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_job_requisitions_department
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_job_requisitions_created_by
    FOREIGN KEY (created_by) REFERENCES users(user_id)
    ON DELETE RESTRICT,
  KEY idx_job_requisitions_department_status (department_id, status),
  KEY idx_job_requisitions_created_by (created_by)
) ENGINE=InnoDB;

CREATE TABLE applications (
  application_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  job_id BIGINT UNSIGNED NOT NULL,
  status ENUM('APPLIED', 'SCREENING', 'ASSESSMENT', 'INTERVIEW', 'OFFER', 'REJECTED', 'HIRED') NOT NULL DEFAULT 'APPLIED',
  match_score DECIMAL(6,3),
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_applications_candidate
    FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_applications_job
    FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id)
    ON DELETE CASCADE,
  CONSTRAINT chk_applications_match_score
    CHECK (match_score IS NULL OR (match_score >= 0 AND match_score <= 100)),
  UNIQUE KEY uq_applications_candidate_job (candidate_id, job_id),
  KEY idx_applications_job_status_score (job_id, status, match_score),
  KEY idx_applications_candidate_status (candidate_id, status)
) ENGINE=InnoDB;

CREATE TABLE assessments (
  assessment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  job_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT,
  type ENUM('TECHNICAL', 'APTITUDE', 'CODING', 'THEORY', 'OTHER') NOT NULL DEFAULT 'TECHNICAL',
  duration_minutes INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_assessments_job
    FOREIGN KEY (job_id) REFERENCES job_requisitions(job_id)
    ON DELETE CASCADE,
  CONSTRAINT chk_assessments_duration
    CHECK (duration_minutes > 0),
  KEY idx_assessments_job_type (job_id, type)
) ENGINE=InnoDB;

CREATE TABLE questions (
  question_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  type ENUM('MCQ', 'CODING', 'THEORY', 'OTHER') NOT NULL,
  difficulty_level ENUM('EASY', 'MEDIUM', 'HARD') NOT NULL DEFAULT 'MEDIUM',
  question_text TEXT NOT NULL,
  options JSON NULL,
  correct_answer TEXT NULL,
  points DECIMAL(6,2) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_questions_assessment
    FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id)
    ON DELETE CASCADE,
  CONSTRAINT chk_questions_points
    CHECK (points > 0),
  KEY idx_questions_assessment_difficulty (assessment_id, difficulty_level)
) ENGINE=InnoDB;

CREATE TABLE candidate_assessments (
  ca_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidate_id BIGINT UNSIGNED NOT NULL,
  assessment_id BIGINT UNSIGNED NOT NULL,
  start_time DATETIME NULL,
  end_time DATETIME NULL,
  status ENUM('IN_PROGRESS', 'SUBMITTED', 'EXPIRED') NOT NULL DEFAULT 'IN_PROGRESS',
  score DECIMAL(6,3),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_candidate_assessments_candidate
    FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_candidate_assessments_assessment
    FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id)
    ON DELETE CASCADE,
  CONSTRAINT chk_candidate_assessments_time
    CHECK (end_time IS NULL OR start_time IS NULL OR end_time >= start_time),
  CONSTRAINT chk_candidate_assessments_score
    CHECK (score IS NULL OR (score >= 0 AND score <= 100)),
  KEY idx_candidate_assessments_candidate_status (candidate_id, status),
  KEY idx_candidate_assessments_assessment (assessment_id)
) ENGINE=InnoDB;

CREATE TABLE submissions (
  submission_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ca_id BIGINT UNSIGNED NOT NULL,
  question_id BIGINT UNSIGNED NULL,
  answer_text LONGTEXT,
  code_output LONGTEXT,
  is_correct BOOLEAN NULL,
  plagiarism_score DECIMAL(6,3) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_submissions_candidate_assessment
    FOREIGN KEY (ca_id) REFERENCES candidate_assessments(ca_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_submissions_question
    FOREIGN KEY (question_id) REFERENCES questions(question_id)
    ON DELETE SET NULL,
  CONSTRAINT chk_submissions_plagiarism_score
    CHECK (plagiarism_score IS NULL OR (plagiarism_score >= 0 AND plagiarism_score <= 100)),
  KEY idx_submissions_ca_question (ca_id, question_id)
) ENGINE=InnoDB;

CREATE TABLE interviews (
  interview_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL,
  interview_type ENUM('TECHNICAL', 'HR', 'PANEL') NOT NULL DEFAULT 'TECHNICAL',
  scheduled_at DATETIME NOT NULL,
  duration_minutes INT UNSIGNED NOT NULL,
  status ENUM('SCHEDULED', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'SCHEDULED',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_interviews_application
    FOREIGN KEY (application_id) REFERENCES applications(application_id)
    ON DELETE CASCADE,
  CONSTRAINT chk_interviews_duration
    CHECK (duration_minutes > 0),
  KEY idx_interviews_application_status (application_id, status),
  KEY idx_interviews_scheduled_at (scheduled_at)
) ENGINE=InnoDB;

CREATE TABLE interviewers_assignment (
  assignment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  interviewer_id BIGINT UNSIGNED NOT NULL,
  role_in_panel ENUM('INTERVIEWER', 'PANEL_LEAD', 'OBSERVER') NOT NULL DEFAULT 'INTERVIEWER',
  is_shadowing BOOLEAN NOT NULL DEFAULT FALSE,
  CONSTRAINT fk_interviewers_assignment_interview
    FOREIGN KEY (interview_id) REFERENCES interviews(interview_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_interviewers_assignment_user
    FOREIGN KEY (interviewer_id) REFERENCES users(user_id)
    ON DELETE RESTRICT,
  UNIQUE KEY uq_interviewers_assignment_interview_user (interview_id, interviewer_id),
  KEY idx_interviewers_assignment_user (interviewer_id)
) ENGINE=InnoDB;

CREATE TABLE interview_feedback (
  feedback_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interview_id BIGINT UNSIGNED NOT NULL,
  interviewer_id BIGINT UNSIGNED NOT NULL,
  technical_score DECIMAL(5,2),
  communication_score DECIMAL(5,2),
  culture_fit_score DECIMAL(5,2),
  overall_score DECIMAL(5,2),
  comments TEXT,
  submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_interview_feedback_interview
    FOREIGN KEY (interview_id) REFERENCES interviews(interview_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_interview_feedback_user
    FOREIGN KEY (interviewer_id) REFERENCES users(user_id)
    ON DELETE RESTRICT,
  CONSTRAINT chk_interview_feedback_technical
    CHECK (technical_score IS NULL OR (technical_score >= 0 AND technical_score <= 10)),
  CONSTRAINT chk_interview_feedback_communication
    CHECK (communication_score IS NULL OR (communication_score >= 0 AND communication_score <= 10)),
  CONSTRAINT chk_interview_feedback_culture
    CHECK (culture_fit_score IS NULL OR (culture_fit_score >= 0 AND culture_fit_score <= 10)),
  CONSTRAINT chk_interview_feedback_overall
    CHECK (overall_score IS NULL OR (overall_score >= 0 AND overall_score <= 10)),
  UNIQUE KEY uq_interview_feedback_interview_user (interview_id, interviewer_id),
  KEY idx_interview_feedback_user (interviewer_id)
) ENGINE=InnoDB;

CREATE TABLE final_evaluations (
  evaluation_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL UNIQUE,
  aggregated_score DECIMAL(6,3),
  recommendation ENUM('STRONG_HIRE', 'HIRE', 'NO_HIRE', 'STRONG_NO_HIRE') NOT NULL,
  status ENUM('EVALUATED', 'COMPLETED') NOT NULL DEFAULT 'EVALUATED',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_final_evaluations_application
    FOREIGN KEY (application_id) REFERENCES applications(application_id)
    ON DELETE CASCADE,
  CONSTRAINT chk_final_evaluations_score
    CHECK (aggregated_score IS NULL OR (aggregated_score >= 0 AND aggregated_score <= 100))
) ENGINE=InnoDB;

CREATE TABLE offers (
  offer_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id BIGINT UNSIGNED NOT NULL UNIQUE,
  offer_type ENUM('FULL_TIME', 'CONTRACT', 'INTERN') NOT NULL,
  ctc DECIMAL(12,2) NOT NULL,
  bonus DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock_options DECIMAL(12,2) NOT NULL DEFAULT 0,
  status ENUM('DRAFT', 'SENT', 'ACCEPTED', 'REJECTED', 'EXPIRED') NOT NULL DEFAULT 'DRAFT',
  expiry_date DATETIME NULL,
  sent_at DATETIME NULL,
  accepted_at DATETIME NULL,
  CONSTRAINT fk_offers_application
    FOREIGN KEY (application_id) REFERENCES applications(application_id)
    ON DELETE CASCADE,
  CONSTRAINT chk_offers_amounts
    CHECK (ctc >= 0 AND bonus >= 0 AND stock_options >= 0),
  KEY idx_offers_status_expiry (status, expiry_date)
) ENGINE=InnoDB;

CREATE TABLE onboarding (
  onboarding_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offer_id BIGINT UNSIGNED NOT NULL UNIQUE,
  status ENUM('PENDING', 'IN_PROGRESS', 'COMPLETED') NOT NULL DEFAULT 'PENDING',
  start_date DATE NULL,
  documents_completed BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_onboarding_offer
    FOREIGN KEY (offer_id) REFERENCES offers(offer_id)
    ON DELETE CASCADE,
  KEY idx_onboarding_status (status)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  notification_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(220) NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(80) NOT NULL,
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  read_at TIMESTAMP NULL,
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE,
  KEY idx_notifications_user_read (user_id, is_read),
  KEY idx_notifications_type_created (type, created_at)
) ENGINE=InnoDB;

INSERT INTO departments (name, description) VALUES
  ('Human Resources', 'Recruitment and HR operations'),
  ('Engineering', 'Technical hiring department')
ON DUPLICATE KEY UPDATE description = VALUES(description);
