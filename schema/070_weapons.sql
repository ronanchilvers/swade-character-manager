SET NAMES utf8mb4;

-- Weapons carried by a character
-- Ordered by weapon_position; synced as a full list from the sheet view.
DROP TABLE IF EXISTS weapons;
CREATE TABLE IF NOT EXISTS `weapons` (
    weapon_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    weapon_character_id BIGINT UNSIGNED NOT NULL,
    weapon_position     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    weapon_name         VARCHAR(128) NOT NULL DEFAULT '',
    weapon_range        VARCHAR(32)  NOT NULL DEFAULT '',
    weapon_damage       VARCHAR(32)  NOT NULL DEFAULT '',
    weapon_ap           VARCHAR(8)   NOT NULL DEFAULT '',
    weapon_rof          VARCHAR(8)   NOT NULL DEFAULT '',
    weapon_weight       VARCHAR(8)   NOT NULL DEFAULT '',
    weapon_notes        VARCHAR(255) NULL,
    weapon_created      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    weapon_updated      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                                       ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (weapon_id),
    INDEX idx_weapon_character (weapon_character_id),
    CONSTRAINT fk_weapon_character FOREIGN KEY (weapon_character_id)
        REFERENCES characters(character_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
