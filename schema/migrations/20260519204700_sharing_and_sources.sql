SET NAMES utf8mb4;

ALTER TABLE characters
    ADD COLUMN character_sharing INT(1) UNSIGNED DEFAULT 0 AFTER character_rank,
    ADD COLUMN character_sources VARCHAR(1024) NOT NULL AFTER character_sharing;
