SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `campaign_members` (
    campaign_member_id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    campaign_member_campaign_id BIGINT UNSIGNED NOT NULL,
    campaign_member_user_id     BIGINT UNSIGNED NOT NULL,
    campaign_member_role        VARCHAR(16) NOT NULL DEFAULT 'member',
    campaign_member_created     DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    campaign_member_updated     DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (campaign_member_id),
    UNIQUE KEY uniq_campaign_member_user (campaign_member_campaign_id, campaign_member_user_id),
    INDEX idx_campaign_member_user (campaign_member_user_id),
    INDEX idx_campaign_member_role (campaign_member_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
