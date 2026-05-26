SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `catalog_sources` (
    catalog_source_id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    catalog_source_key            VARCHAR(64) NOT NULL,
    catalog_source_name           VARCHAR(128) NOT NULL,
    catalog_source_always_enabled TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    catalog_source_position       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    catalog_source_created        DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    catalog_source_updated        DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                                                 ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (catalog_source_id),
    UNIQUE KEY uq_catalog_source_key (catalog_source_key),
    KEY idx_catalog_source_position (catalog_source_position),
    KEY idx_catalog_source_name (catalog_source_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `catalog_sources` (
    catalog_source_key,
    catalog_source_name,
    catalog_source_always_enabled,
    catalog_source_position
) VALUES
    ('core', 'Core Rules (Always Enabled)', 1, 0),
    ('fantasy', 'Savage Worlds Fantasy Companion', 0, 10),
    ('lankhmar', 'Lankhmar: City of Thieves', 0, 20)
ON DUPLICATE KEY UPDATE
    catalog_source_name = VALUES(catalog_source_name),
    catalog_source_always_enabled = VALUES(catalog_source_always_enabled),
    catalog_source_position = VALUES(catalog_source_position),
    catalog_source_updated = CURRENT_TIMESTAMP(6);
