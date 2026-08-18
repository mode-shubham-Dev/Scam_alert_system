-- ScamShield — Test Account Seed
-- Purpose : Wipe all data and insert the 4 test accounts.
-- Password : ScamShield@123  (bcrypt cost 12, verified)
-- Run AFTER schema.sql + migration.sql on a fresh or reset database.
--
-- TablePlus: open Query tab → paste this entire file → Run

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE alerts;
TRUNCATE TABLE reports;
TRUNCATE TABLE subscribers;
TRUNCATE TABLE users;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO users (name, email, password_hash, role, login_attempts, locked_until) VALUES
(
  'Admin User',
  'karnashubham72@gmail.com',
  '$2y$12$ScDfyflUezVK8GeN5VH.V.XlIED0qiVrkOa1ONFeIL6tXjMvuAbAy',
  'admin',
  0,
  NULL
),
(
  'Test Reporter',
  'reporter@test.com',
  '$2y$12$KVv48q5pMJuoW9kde2UQR.t7.vI6c6EHYc1ZLi.5Z8Num6EXlxS1W',
  'reporter',
  0,
  NULL
),
(
  'Test Moderator',
  'moderator@test.com',
  '$2y$12$s0f7F5RJDjciX31XjYIBn.l7JimMHnYQNHZMn/g4dSnrwOyZ6/cdu',
  'moderator',
  0,
  NULL
),
(
  'Test Manager',
  'manager@test.com',
  '$2y$12$aZW9U5uBmhnZcLklmaPdNu1vXBdAJV.yYDfBsYJguRyCi21lxOisy',
  'awareness_manager',
  0,
  NULL
);

-- Verify: run this SELECT after the INSERT to confirm all 4 rows
-- SELECT id, name, email, role FROM users;
