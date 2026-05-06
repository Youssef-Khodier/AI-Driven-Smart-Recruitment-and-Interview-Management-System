ALTER TABLE assessments
  ADD COLUMN cooldown_months INT UNSIGNED NOT NULL DEFAULT 6 AFTER duration_minutes;

CREATE TABLE IF NOT EXISTS assessment_question_rules (
  rule_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  assessment_id BIGINT UNSIGNED NOT NULL,
  difficulty_level VARCHAR(20) NOT NULL,
  question_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_assessment_rules_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(assessment_id) ON DELETE CASCADE,
  UNIQUE KEY uq_assessment_rule_difficulty (assessment_id, difficulty_level)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS question_expected_outputs (
  output_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id BIGINT UNSIGNED NOT NULL,
  expected_output LONGTEXT NOT NULL,
  label VARCHAR(120) NULL,
  is_hidden BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  CONSTRAINT fk_expected_outputs_question FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
  KEY idx_expected_outputs_question (question_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assessment_common_answers (
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

ALTER TABLE candidate_assessments
  ADD COLUMN remaining_seconds INT UNSIGNED NULL AFTER expires_at,
  ADD COLUMN last_heartbeat_at TIMESTAMP NULL AFTER remaining_seconds,
  DROP INDEX uq_attempt_candidate_assessment,
  ADD KEY idx_attempt_candidate_assessment (candidate_id, assessment_id);

ALTER TABLE submissions
  ADD COLUMN code_output LONGTEXT NULL AFTER answer_text,
  ADD COLUMN output_matched BOOLEAN NULL AFTER is_correct,
  ADD COLUMN plagiarism_score DECIMAL(6,3) NULL AFTER awarded_points;
