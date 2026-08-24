DELETE t1 FROM `lc_products_prices` t1
JOIN `lc_products_prices` t2 ON t1.product_id = t2.product_id AND t1.id > t2.id;
-- -----
ALTER TABLE `lc_products_prices`
DROP KEY product_id,
ADD UNIQUE KEY product_id (product_id);
-- -----
ALTER TABLE `lc_products_images`
ADD UNIQUE INDEX `product_id_filename` (`product_id`, `filename`);
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('checkout', 'local', 'Withdrawal Window Days', 'The number of days a customer has to request a withdrawal after placing an order.', 'withdrawal_window_days', '14', 'number()', 14, NOW(), NOW());
-- -----
CREATE TABLE IF NOT EXISTS `lc_products_prices_history` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `campaign_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `price` JSON NOT NULL,
  `valid_from` TIMESTAMP NULL DEFAULT NULL,
  `valid_to` TIMESTAMP NULL DEFAULT NULL,
  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `valid_from` (`valid_from`),
  KEY `valid_to` (`valid_to`),
  KEY `product_valid` (`product_id`, `valid_from`, `valid_to`)
);
-- -----
INSERT INTO `lc_products_prices_history` (`product_id`, `campaign_id`, `price`, `valid_from`, `valid_to`)
SELECT pp.product_id, 0, JSON_OBJECT('USD', pp.USD, 'EUR', pp.EUR), NOW(), NULL FROM `lc_products_prices` pp;
-- -----
INSERT INTO `lc_products_prices_history` (`product_id`, `campaign_id`, `price`, `valid_from`, `valid_to`)
SELECT pc.product_id, pc.id, JSON_OBJECT('USD', pc.USD, 'EUR', pc.EUR), NOW(), NULL FROM `lc_products_campaigns` pc;
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('listings', 'local', 'Display Lowest Price Last 30 Days', 'Display the lowest price of a product in the last 30 days as required by EU Omnibus Directive.', 'display_lowest_price_30_days', '1', 'toggle("e/d")', 50, NOW(), NOW());