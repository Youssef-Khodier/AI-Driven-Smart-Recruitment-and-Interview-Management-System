-- Migration: 012 Feedback Governance Analytics
-- Feature: Feedback Governance Analytics

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
