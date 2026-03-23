SET NAMES utf8mb4;

DROP TABLE IF EXISTS characters;
CREATE TABLE IF NOT EXISTS `characters` (
    character_id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    character_hash      VARCHAR(32),
    character_user      INT(11),
    character_name      VARCHAR(128),
    -- rank and core attributes stored as die face values (4/6/8/10/12)
    character_rank      ENUM('Novice','Seasoned','Veteran','Heroic','Legendary')
                           NOT NULL DEFAULT 'Novice',
    character_agility   TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_smarts    TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_spirit    TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_strength  TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_vigor     TINYINT UNSIGNED NOT NULL DEFAULT 4,
    character_pace      TINYINT UNSIGNED NOT NULL DEFAULT 6,
    character_parry     TINYINT UNSIGNED NOT NULL DEFAULT 2,
    character_toughness TINYINT UNSIGNED NOT NULL DEFAULT 0,
    character_created   DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    character_updated   DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (character_id),
    INDEX idx_hash (character_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
    user_id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_firstname VARCHAR(64) NOT NULL,
    user_lastname  VARCHAR(128) NOT NULL,
    user_email     VARCHAR(128) NOT NULL,
    user_created   DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    user_updated   DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Junction: skills and die level for a character
-- skill_die stores the die face: 4 = d4, 6 = d6, 8 = d8, 10 = d10, 12 = d12
DROP TABLE IF EXISTS skills;
CREATE TABLE IF NOT EXISTS `skills` (
    skill_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    skill_character_id BIGINT UNSIGNED NOT NULL,
    skill_key          VARCHAR(64) NOT NULL,
    skill_die          TINYINT UNSIGNED NOT NULL,
    skill_created      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    skill_updated      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                                     ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (skill_id),
    UNIQUE KEY uq_char_skill (skill_character_id, skill_key),
    CONSTRAINT fk_cs_character FOREIGN KEY (skill_character_id)
        REFERENCES characters(character_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Junction: edges selected for a character
DROP TABLE IF EXISTS edges;
CREATE TABLE IF NOT EXISTS `edges` (
    edge_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    edge_character_id BIGINT UNSIGNED NOT NULL,
    edge_key          VARCHAR(64) NOT NULL,
    edge_created      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (edge_id),
    UNIQUE KEY uq_char_edge (edge_character_id, edge_key),
    CONSTRAINT fk_ce_character FOREIGN KEY (edge_character_id)
        REFERENCES characters(character_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
