ALTER TABLE `lc_products_attributes`
DROP INDEX `id`,
ADD UNIQUE INDEX `product_attribute` (`product_id`, `group_id`, `value_id`, `custom_value`) USING BTREE,
ADD INDEX `custom_value` (`custom_value`);
-- -----
ALTER TABLE `lc_emails`
ADD COLUMN `error` VARCHAR(256) NULL DEFAULT NULL AFTER `multiparts`;
