-- Migration: 011_interview_coordination_workflows.sql

ALTER TABLE interviews ADD COLUMN extended_duration_minutes INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE interviews ADD COLUMN last_extension_decision_at TIMESTAMP NULL;

ALTER TABLE interviewers_assignment ADD COLUMN assignment_source VARCHAR(40) NOT NULL DEFAULT 'MANUAL';
ALTER TABLE interviewers_assignment ADD COLUMN override_reason TEXT NULL;
ALTER TABLE interviewers_assignment ADD COLUMN conflict_overridden BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE interviewers_assignment ADD COLUMN assigned_by BIGINT UNSIGNED NULL;
ALTER TABLE interviewers_assignment ADD COLUMN assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

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
