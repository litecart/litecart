ALTER TABLE `lc_products`
ADD COLUMN `recommended_price` DECIMAL(11,4) NOT NULL AFTER `purchase_price_currency_code`,
CHANGE COLUMN `weight` `weight` DECIMAL(11,4) NOT NULL AFTER `quantity_unit_id`,
CHANGE COLUMN `dim_x` `dim_x` DECIMAL(11,4) NOT NULL AFTER `weight_class`,
CHANGE COLUMN `dim_y` `dim_y` DECIMAL(11,4) NOT NULL AFTER `dim_x`,
CHANGE COLUMN `dim_z` `dim_z` DECIMAL(11,4) NOT NULL AFTER `dim_y`,
CHANGE COLUMN `purchase_price` `purchase_price` DECIMAL(11,4) NOT NULL AFTER `dim_class`;
-- -----
ALTER TABLE `lc_order_statuses_info`
CHANGE COLUMN `email_message` `email_message` TEXT NOT NULL;
-- -----
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('store_info', 'global', 'Store Postcode', 'The postcode of your store.', 'store_postcode', '', 'text()', 18, NOW(), NOW());
