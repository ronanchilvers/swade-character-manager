ALTER TABLE `characters`
    ADD COLUMN character_share_token   VARCHAR(32) NULL         AFTER character_notes,
    ADD COLUMN character_share_enabled TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER character_share_token,
    ADD INDEX idx_share_token (character_share_token);
