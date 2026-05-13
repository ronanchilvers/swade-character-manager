SET NAMES utf8mb4;

ALTER TABLE characters
    ADD COLUMN character_campaign BIGINT UNSIGNED NULL AFTER character_user,
    ADD INDEX idx_character_campaign (character_campaign);
