SET NAMES utf8mb4;

-- Junction: skills and die level for a character
-- skill_die stores the die face: 4 = d4, 6 = d6, 8 = d8, 10 = d10, 12 = d12
DROP TABLE IF EXISTS skills;
CREATE TABLE IF NOT EXISTS `skills` (
    skill_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    skill_character_id BIGINT UNSIGNED NOT NULL,
    skill_key          VARCHAR(64) NOT NULL,
    skill_core         ENUM('yes', 'no') NOT NULL DEFAULT 'no',
    skill_attribute    VARCHAR(20) NOT NULL,
    skill_die          TINYINT UNSIGNED NOT NULL,
    skill_created      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    skill_updated      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                                     ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (skill_id),
    UNIQUE KEY uq_char_skill (skill_character_id, skill_key),
    CONSTRAINT fk_cs_character FOREIGN KEY (skill_character_id)
        REFERENCES characters(character_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
