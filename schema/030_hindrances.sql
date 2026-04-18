SET NAMES utf8mb4;

-- Junction: hindrances selected for a character
-- hindrance_key references the `id` field in data/hindrances.json
DROP TABLE IF EXISTS hindrances;
CREATE TABLE IF NOT EXISTS `hindrances` (
    hindrance_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    hindrance_character_id BIGINT UNSIGNED NOT NULL,
    hindrance_key          VARCHAR(64) NOT NULL,
    hindrance_level        ENUM('minor','major') NOT NULL,
    hindrance_created      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (hindrance_id),
    UNIQUE KEY uq_char_hindrance (hindrance_character_id, hindrance_key),
    CONSTRAINT fk_ch_character FOREIGN KEY (hindrance_character_id)
        REFERENCES characters(character_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
