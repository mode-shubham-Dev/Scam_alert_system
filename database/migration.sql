-- ScamShield — Backend Hardening Migration
-- Fresh install: run schema.sql first, then this file.
-- Existing install: run only this file — it is idempotent via the procedure below.

-- Wrap in a procedure so each column addition is idempotent
DROP PROCEDURE IF EXISTS scam_migrate;

DELIMITER $$
CREATE PROCEDURE scam_migrate()
BEGIN
  -- users: brute-force login protection
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'login_attempts'
  ) THEN
    ALTER TABLE users ADD COLUMN login_attempts TINYINT NOT NULL DEFAULT 0;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'locked_until'
  ) THEN
    ALTER TABLE users ADD COLUMN locked_until DATETIME NULL;
  END IF;

  -- reports: validated MIME type + moderator notes
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'evidence_mime'
  ) THEN
    ALTER TABLE reports ADD COLUMN evidence_mime VARCHAR(100) NULL;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'moderator_notes'
  ) THEN
    ALTER TABLE reports ADD COLUMN moderator_notes TEXT NULL;
  END IF;

  -- Performance indexes
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND INDEX_NAME = 'idx_reports_status'
  ) THEN
    ALTER TABLE reports ADD INDEX idx_reports_status (status);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND INDEX_NAME = 'idx_reports_scam_type'
  ) THEN
    ALTER TABLE reports ADD INDEX idx_reports_scam_type (scam_type);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_email'
  ) THEN
    ALTER TABLE users ADD INDEX idx_users_email (email);
  END IF;
END$$
DELIMITER ;

CALL scam_migrate();
DROP PROCEDURE IF EXISTS scam_migrate;
