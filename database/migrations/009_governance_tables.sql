ALTER TABLE users ADD COLUMN is_department_head BOOLEAN NOT NULL DEFAULT FALSE AFTER status;

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
