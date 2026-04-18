SET NAMES utf8mb4;

DROP TABLE IF EXISTS characters;
CREATE TABLE IF NOT EXISTS `characters` (
    character_id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    character_hash       VARCHAR(32),
    character_user       INT(11),
    character_name       VARCHAR(128),
    -- rank and core attributes stored as die face values (4/6/8/10/12)
    character_rank       ENUM('Novice','Seasoned','Veteran','Heroic','Legendary')
                             NOT NULL DEFAULT 'Novice',
    character_agility    TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_smarts     TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_spirit     TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_strength   TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_vigor      TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_pace       TINYINT UNSIGNED NOT NULL DEFAULT 6,
    character_parry      TINYINT UNSIGNED NOT NULL DEFAULT 2,
    character_toughness  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    -- session-state columns (live-editable from the sheet view)
    character_wounds        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    character_fatigue       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    character_incapacitated TINYINT UNSIGNED NOT NULL DEFAULT 0,
    character_bennies       TINYINT UNSIGNED NOT NULL DEFAULT 3,
    character_conviction    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    character_notes         TEXT NULL,
    character_created    DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    character_updated    DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (character_id),
    INDEX idx_hash (character_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
