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

ALTER TABLE job_requisitions
ADD COLUMN archived_at TIMESTAMP NULL AFTER updated_at,
ADD COLUMN archived_by BIGINT UNSIGNED NULL AFTER archived_at,
ADD CONSTRAINT fk_jobs_archived_by FOREIGN KEY (archived_by) REFERENCES users(user_id) ON DELETE SET NULL;

ALTER TABLE applications
ADD COLUMN archived_at TIMESTAMP NULL AFTER updated_at,
ADD COLUMN archived_by BIGINT UNSIGNED NULL AFTER archived_at,
ADD CONSTRAINT fk_apps_archived_by FOREIGN KEY (archived_by) REFERENCES users(user_id) ON DELETE SET NULL;
