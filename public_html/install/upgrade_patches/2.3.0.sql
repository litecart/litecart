UPDATE `lc_settings` SET `value` = '5' WHERE `key` IN ('box_also_purchased_products_num_items', 'box_campaign_products_num_items', 'box_latest_products_num_items', 'box_popular_products_num_items', 'box_similar_products_num_items') AND `value` = '4';
-- -----
UPDATE `lc_settings` SET `value` = '10' WHERE `key` IN ('box_also_purchased_products_num_items', 'box_campaign_products_num_items', 'box_latest_products_num_items', 'box_popular_products_num_items', 'box_similar_products_num_items') AND `value` = '8';
-- -----
CREATE TABLE `lc_newsletter_recipients` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(128) NOT NULL DEFAULT '',
  `client_ip` VARCHAR(45) NOT NULL DEFAULT '',
  `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email`)
);
-- -----
INSERT INTO `lc_newsletter_recipients`
(email, date_created)
SELECT email, date_created FROM `lc_customers`
WHERE status AND newsletter;
-- -----
ALTER TABLE `lc_customers`
ADD COLUMN `login_attempts` INT NOT NULL DEFAULT '0' AFTER `password_reset_token`,
ADD COLUMN `date_blocked_until` DATETIME NULL DEFAULT NULL AFTER `date_login`,
ADD COLUMN `date_expire_sessions` DATETIME NULL DEFAULT NULL AFTER `date_blocked_until`,
DROP COLUMN `newsletter`;
-- -----
ALTER TABLE `lc_products`
ADD COLUMN `quantity_min` DECIMAL(11,4) UNSIGNED NOT NULL DEFAULT '0.0000' AFTER `quantity`,
ADD COLUMN `quantity_max` DECIMAL(11,4) UNSIGNED NOT NULL DEFAULT '0.0000' AFTER `quantity_min`,
ADD COLUMN `quantity_step` DECIMAL(11,4) UNSIGNED NOT NULL DEFAULT '0.0000' AFTER `quantity_max`;
-- -----
ALTER TABLE `lc_languages`
ADD COLUMN `direction` ENUM('ltr','rtl') NOT NULL DEFAULT 'ltr' AFTER `name`;
-- -----
ALTER TABLE `lc_users`
ADD COLUMN `date_expire_sessions` TIMESTAMP NULL DEFAULT NULL AFTER `total_logins`;
-- -----
ALTER TABLE `lc_zones_to_geo_zones`
ADD COLUMN `city` VARCHAR(32) NOT NULL DEFAULT '' AFTER `zone_code`,
DROP INDEX `region`,
ADD UNIQUE INDEX `region` (`geo_zone_id`, `country_code`, `zone_code`, `city`),
ADD INDEX `city` (`city`);
