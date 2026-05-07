SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `skill_catalog` (
    skill_catalog_id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    skill_catalog_key                VARCHAR(64) NOT NULL,
    skill_catalog_source             VARCHAR(64) NOT NULL DEFAULT 'core',
    skill_catalog_name               VARCHAR(128) NOT NULL,
    skill_catalog_linked_attribute   VARCHAR(20) NOT NULL,
    skill_catalog_core_skill         TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    skill_catalog_arcane_background  VARCHAR(64) NULL,
    skill_catalog_summary            TEXT NOT NULL,
    skill_catalog_requirements       JSON NOT NULL,
    skill_catalog_effects            JSON NOT NULL,
    skill_catalog_notes              JSON NOT NULL,
    skill_catalog_source_pages       JSON NOT NULL,
    skill_catalog_created            DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    skill_catalog_updated            DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
                                                     ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (skill_catalog_id),
    UNIQUE KEY uq_skill_catalog_key (skill_catalog_key),
    KEY idx_skill_catalog_source (skill_catalog_source),
    KEY idx_skill_catalog_name (skill_catalog_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
