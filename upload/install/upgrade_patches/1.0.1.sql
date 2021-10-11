RENAME TABLE `lc_delivery_status` TO `lc_delivery_statuses`;
-- --------------------------------------------------------
RENAME TABLE `lc_delivery_status_info` TO `lc_delivery_statuses_info`;
-- --------------------------------------------------------
RENAME TABLE `lc_sold_out_status` TO `lc_sold_out_statuses`;
-- --------------------------------------------------------
RENAME TABLE `lc_sold_out_status_info` TO `lc_sold_out_statuses_info`;
-- --------------------------------------------------------
ALTER TABLE `lc_orders` CHANGE `weight` `weight_total` DECIMAL( 11, 4 ) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_items` DROP `code`, DROP `upc`, DROP `taric`, DROP `tax_class_id`, ADD `weight` DECIMAL(11, 4) NOT NULL AFTER `tax`, ADD `weight_class` VARCHAR(2) NOT NULL AFTER `weight`;
-- --------------------------------------------------------
RENAME TABLE `lc_orders_status` TO `lc_order_statuses`;
-- --------------------------------------------------------
RENAME TABLE `lc_orders_status_info` TO `lc_order_statuses_info`;
-- --------------------------------------------------------
ALTER TABLE `lc_order_statuses` ADD `priority` TINYINT(2) NOT NULL AFTER `notify`;
-- --------------------------------------------------------
DROP TABLE `lc_orders_tax`;
-- --------------------------------------------------------
ALTER TABLE `lc_orders_totals` DROP `tax_class_id`;
-- --------------------------------------------------------
ALTER TABLE `lc_products_options_stock` CHANGE `weight` `weight` DECIMAL(11, 4) NOT NULL;
-- --------------------------------------------------------
CREATE TABLE `lc_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `language_code` varchar(8) NOT NULL,
  `name` varchar(128) NOT NULL,
  `caption` varchar(256) NOT NULL,
  `link` varchar(256) NOT NULL,
  `image` varchar(64) NOT NULL,
  `priority` tinyint(2) NOT NULL,
  `date_valid_from` datetime NOT NULL,
  `date_valid_to` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
CREATE TABLE `lc_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(128) NOT NULL,
  `last_ip` varchar(15) NOT NULL,
  `last_host` varchar(64) NOT NULL,
  `login_attempts` int(11) NOT NULL,
  `total_logins` int(11) NOT NULL,
  `date_blocked` datetime NOT NULL,
  `date_expires` datetime NOT NULL,
  `date_active` datetime NOT NULL,
  `date_login` datetime NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('', 'local', 'Installed Customer Modules', '', 'customer_modules', '', '', 0, NOW(), NOW());
-- --------------------------------------------------------
DELETE FROM `lc_settings` where `key` in ('checkout_captcha_enabled', 'checkout_ajax_enabled', 'get_address_modules');
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 0 WHERE `value` = 'false';
-- --------------------------------------------------------
UPDATE `lc_settings` SET `value` = 1 WHERE `value` = 'true';