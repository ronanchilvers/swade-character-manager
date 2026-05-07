SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `hindrance_catalog` (
    hindrance_catalog_id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    hindrance_catalog_key           VARCHAR(64) NOT NULL,
    hindrance_catalog_source        VARCHAR(64) NOT NULL DEFAULT 'core',
    hindrance_catalog_name          VARCHAR(128) NOT NULL,
    hindrance_catalog_summary       TEXT NOT NULL,
    hindrance_catalog_levels        JSON NOT NULL,
    hindrance_catalog_requirements  JSON NOT NULL,
    hindrance_catalog_effects       JSON NOT NULL,
    hindrance_catalog_notes         JSON NOT NULL,
    hindrance_catalog_source_pages  JSON NOT NULL,
    hindrance_catalog_created       DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    hindrance_catalog_updated       DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                                                 ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (hindrance_catalog_id),
    UNIQUE KEY uq_hindrance_catalog_key (hindrance_catalog_key),
    KEY idx_hindrance_catalog_source (hindrance_catalog_source),
    KEY idx_hindrance_catalog_name (hindrance_catalog_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
