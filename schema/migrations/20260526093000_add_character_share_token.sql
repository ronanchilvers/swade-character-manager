SET NAMES utf8mb4;

ALTER TABLE characters
    ADD COLUMN character_share_token VARCHAR(64) NULL AFTER character_sharing,
    ADD UNIQUE KEY uq_character_share_token (character_share_token);
