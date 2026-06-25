SET NAMES utf8mb4;

ALTER TABLE characters
    ADD COLUMN character_shaken TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER character_incapacitated;
