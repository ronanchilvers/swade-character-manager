SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `edge_catalog` (
    edge_catalog_id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    edge_catalog_key           VARCHAR(64) NOT NULL,
    edge_catalog_source        VARCHAR(64) NOT NULL DEFAULT 'core',
    edge_catalog_name          VARCHAR(128) NOT NULL,
    edge_catalog_category      VARCHAR(64) NOT NULL,
    edge_catalog_repeatable    TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    edge_catalog_summary       TEXT NOT NULL,
    edge_catalog_requirements  JSON NOT NULL,
    edge_catalog_effects       JSON NOT NULL,
    edge_catalog_notes         JSON NOT NULL,
    edge_catalog_source_pages  JSON NOT NULL,
    edge_catalog_created       DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    edge_catalog_updated       DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                                                ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (edge_catalog_id),
    UNIQUE KEY uq_edge_catalog_key (edge_catalog_key),
    KEY idx_edge_catalog_source (edge_catalog_source),
    KEY idx_edge_catalog_name (edge_catalog_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
