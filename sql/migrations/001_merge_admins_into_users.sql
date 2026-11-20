-- =====================================================================
-- Migration 001: merge the `admins` table into `users`
-- =====================================================================
-- Run ONCE on a database created from the previous sql/schema.sql
-- (with both `admins` and `users` tables). Fresh installs don't need it:
-- they get the new layout from sql/schema.sql directly.
--
-- BEFORE RUNNING: export a full backup in phpMyAdmin (Export -> Quick -> SQL).
-- MySQL can't roll back table changes, so the backup is the undo button.
--
-- What it does:
--   1. Drops the foreign keys that point at `admins`
--   2. Renames users.invitation_token -> token_hash and hashes pending tokens
--      (pending invitation links keep working)
--   3. Copies every admin into `users` with role 'admin'
--      (an admin whose email already exists in `users` is merged into that row)
--   4. Rewrites created_by / invited_by / forum_replies.admin_id to the new user ids
--   5. Re-creates the foreign keys against `users`
--   6. Renames `admins` to `admins_pre_merge_backup` (drop it once all is verified)
--
-- After the migration, former admins log in with their EMAIL (not username).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Drop foreign keys that reference `admins` (names looked up, not assumed)
-- ---------------------------------------------------------------------
SET @fk = (SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'publications' AND REFERENCED_TABLE_NAME = 'admins' LIMIT 1);
SET @sql = IF(@fk IS NULL, 'DO 0', CONCAT('ALTER TABLE `publications` DROP FOREIGN KEY `', @fk, '`'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = (SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'forum_replies' AND REFERENCED_TABLE_NAME = 'admins' LIMIT 1);
SET @sql = IF(@fk IS NULL, 'DO 0', CONCAT('ALTER TABLE `forum_replies` DROP FOREIGN KEY `', @fk, '`'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = (SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND REFERENCED_TABLE_NAME = 'admins' LIMIT 1);
SET @sql = IF(@fk IS NULL, 'DO 0', CONCAT('ALTER TABLE `users` DROP FOREIGN KEY `', @fk, '`'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = (SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'page_sections' AND REFERENCED_TABLE_NAME = 'admins' LIMIT 1);
SET @sql = IF(@fk IS NULL, 'DO 0', CONCAT('ALTER TABLE `page_sections` DROP FOREIGN KEY `', @fk, '`'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = (SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'use_cases' AND REFERENCED_TABLE_NAME = 'admins' LIMIT 1);
SET @sql = IF(@fk IS NULL, 'DO 0', CONCAT('ALTER TABLE `use_cases` DROP FOREIGN KEY `', @fk, '`'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = (SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS
           WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'news_posts' AND REFERENCED_TABLE_NAME = 'admins' LIMIT 1);
SET @sql = IF(@fk IS NULL, 'DO 0', CONCAT('ALTER TABLE `news_posts` DROP FOREIGN KEY `', @fk, '`'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------
-- 2. Token column: store only SHA-256 hashes from now on
-- ---------------------------------------------------------------------
ALTER TABLE users
    CHANGE invitation_token token_hash CHAR(64) NULL,
    ADD INDEX idx_users_token_hash (token_hash),
    ADD COLUMN legacy_admin_id INT NULL;

UPDATE users SET token_hash = SHA2(token_hash, 256) WHERE token_hash IS NOT NULL;

-- ---------------------------------------------------------------------
-- 3. Copy admins into users
-- ---------------------------------------------------------------------
-- 3a. Admin whose email already has a users row: merge into that row.
UPDATE users u JOIN admins a ON a.email = u.email
SET u.legacy_admin_id = a.id, u.role = 'admin';

-- If that users row was never activated, take over the admin's password.
UPDATE users u JOIN admins a ON a.id = u.legacy_admin_id
SET u.password_hash = a.password_hash
WHERE u.status <> 'active';

UPDATE users u JOIN admins a ON a.id = u.legacy_admin_id
SET u.status = IF(a.is_active, 'active', 'inactive'), u.token_hash = NULL, u.token_expires_at = NULL
WHERE u.status <> 'active';

-- 3b. All other admins become new users.
INSERT INTO users (name, email, role, password_hash, status, created_at, last_login, legacy_admin_id)
SELECT a.username, a.email, 'admin', a.password_hash,
       IF(a.is_active, 'active', 'inactive'), a.created_at, a.last_login, a.id
FROM admins a
WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.legacy_admin_id = a.id);

-- ---------------------------------------------------------------------
-- 4. Point references at the new user ids
-- ---------------------------------------------------------------------
UPDATE publications t JOIN users u ON u.legacy_admin_id = t.created_by SET t.created_by = u.id;
UPDATE page_sections t JOIN users u ON u.legacy_admin_id = t.created_by SET t.created_by = u.id;
UPDATE use_cases t JOIN users u ON u.legacy_admin_id = t.created_by SET t.created_by = u.id;
UPDATE news_posts t JOIN users u ON u.legacy_admin_id = t.created_by SET t.created_by = u.id;
UPDATE forum_replies t JOIN users u ON u.legacy_admin_id = t.admin_id SET t.admin_id = u.id;
UPDATE users t JOIN users u ON u.legacy_admin_id = t.invited_by SET t.invited_by = u.id;

-- Anything that still points nowhere (shouldn't happen) is cleared.
UPDATE publications SET created_by = NULL WHERE created_by IS NOT NULL AND created_by NOT IN (SELECT id FROM users);
UPDATE page_sections SET created_by = NULL WHERE created_by IS NOT NULL AND created_by NOT IN (SELECT id FROM users);
UPDATE use_cases SET created_by = NULL WHERE created_by IS NOT NULL AND created_by NOT IN (SELECT id FROM users);
UPDATE news_posts SET created_by = NULL WHERE created_by IS NOT NULL AND created_by NOT IN (SELECT id FROM users);
UPDATE forum_replies SET admin_id = NULL WHERE admin_id IS NOT NULL AND admin_id NOT IN (SELECT id FROM users);

-- ---------------------------------------------------------------------
-- 5. New foreign keys against users
-- ---------------------------------------------------------------------
ALTER TABLE users DROP COLUMN legacy_admin_id;

ALTER TABLE users ADD CONSTRAINT fk_users_invited_by
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE publications ADD CONSTRAINT fk_publications_created_by
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE page_sections ADD CONSTRAINT fk_page_sections_created_by
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE use_cases ADD CONSTRAINT fk_use_cases_created_by
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE news_posts ADD CONSTRAINT fk_news_posts_created_by
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE forum_replies ADD CONSTRAINT fk_forum_replies_admin_id
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------------
-- 6. Keep the old table as a backup until you've verified everything
-- ---------------------------------------------------------------------
RENAME TABLE admins TO admins_pre_merge_backup;

-- Once logins and content look right:
-- DROP TABLE admins_pre_merge_backup;
