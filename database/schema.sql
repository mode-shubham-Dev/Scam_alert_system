-- ScamShield — Base Schema
-- Run ONCE on a fresh database, then run migration.sql for subsequent updates.
-- mysql -u root -p scam_alert_db < database/schema.sql

CREATE DATABASE IF NOT EXISTS scam_alert_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scam_alert_db;

CREATE TABLE IF NOT EXISTS users (
    id             INT          NOT NULL AUTO_INCREMENT,
    name           VARCHAR(100) NOT NULL,
    email          VARCHAR(255) NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    role           ENUM('reporter','moderator','admin','awareness_manager')
                               NOT NULL DEFAULT 'reporter',
    login_attempts TINYINT      NOT NULL DEFAULT 0,
    locked_until   DATETIME     NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reports (
    id              INT          NOT NULL AUTO_INCREMENT,
    reporter_id     INT          NOT NULL,
    scam_type       VARCHAR(100) NOT NULL,
    description     TEXT         NOT NULL,
    evidence_path   VARCHAR(500) NULL,
    evidence_mime   VARCHAR(100) NULL,
    status          ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    submitted_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_by     INT          NULL,
    reviewed_at     DATETIME     NULL,
    moderator_notes TEXT         NULL,
    PRIMARY KEY (id),
    INDEX idx_reports_status    (status),
    INDEX idx_reports_scam_type (scam_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS alerts (
    id         INT          NOT NULL AUTO_INCREMENT,
    title      VARCHAR(255) NOT NULL,
    body       TEXT         NOT NULL,
    created_by INT          NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscribers (
    id         INT          NOT NULL AUTO_INCREMENT,
    email      VARCHAR(255) NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_subscribers_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
