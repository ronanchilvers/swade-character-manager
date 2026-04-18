SET NAMES utf8mb4;

-- Gear items carried by a character
-- Ordered by gear_position; synced as a full list from the sheet view.
DROP TABLE IF EXISTS gear;
CREATE TABLE IF NOT EXISTS `gear` (
    gear_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    gear_character_id BIGINT UNSIGNED NOT NULL,
    gear_position     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    gear_name         VARCHAR(128) NOT NULL DEFAULT '',
    gear_notes        VARCHAR(255) NULL,
    gear_created      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    gear_updated      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                                       ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (gear_id),
    INDEX idx_gear_character (gear_character_id),
    CONSTRAINT fk_gear_character FOREIGN KEY (gear_character_id)
        REFERENCES characters(character_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
