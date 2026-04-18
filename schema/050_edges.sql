SET NAMES utf8mb4;

-- Junction: edges selected for a character
-- edge_count stores how many times the same edge has been taken
DROP TABLE IF EXISTS edges;
CREATE TABLE IF NOT EXISTS `edges` (
    edge_id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    edge_character_id BIGINT UNSIGNED NOT NULL,
    edge_key          VARCHAR(64) NOT NULL,
    edge_count        TINYINT UNSIGNED NOT NULL DEFAULT 1,
    edge_created      DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (edge_id),
    UNIQUE KEY uq_char_edge (edge_character_id, edge_key),
    CONSTRAINT fk_ce_character FOREIGN KEY (edge_character_id)
        REFERENCES characters(character_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
