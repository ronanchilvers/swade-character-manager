SET NAMES utf8mb4;

ALTER TABLE users
    ADD COLUMN user_superuser TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER user_email,
    ADD COLUMN user_status VARCHAR(16) NOT NULL DEFAULT 'active' AFTER user_superuser;
