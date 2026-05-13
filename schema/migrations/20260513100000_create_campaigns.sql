SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `campaigns` (
    campaign_id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    campaign_hash        VARCHAR(32) NOT NULL,
    campaign_user        BIGINT UNSIGNED NOT NULL,
    campaign_name        VARCHAR(128) NOT NULL,
    campaign_description TEXT NULL,
    campaign_created     DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    campaign_updated     DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (campaign_id),
    UNIQUE KEY uniq_campaign_hash (campaign_hash),
    INDEX idx_campaign_user (campaign_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
