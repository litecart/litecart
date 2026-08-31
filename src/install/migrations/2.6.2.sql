ALTER TABLE `lc_products_attributes`
CHANGE `custom_value` `custom_value` VARCHAR(215) NOT NULL,
DROP INDEX `id`,
ADD UNIQUE INDEX `product_attribute` (`product_id`, `group_id`, `value_id`, `custom_value`) USING BTREE,
ADD INDEX `custom_value` (`custom_value`);
-- -----
ALTER TABLE `lc_emails`
ADD COLUMN `error` VARCHAR(256) NULL DEFAULT NULL AFTER `multiparts`;
-- -----
DELETE FROM `lc_products_attributes`
WHERE product_id NOT IN (SELECT id from `lc_products`)
OR group_id NOT IN (SELECT id from `lc_attribute_groups`)
OR (
	value_id IS NOT NULL
	AND value_id != 0
	AND value_id NOT IN (SELECT id from `lc_attribute_values`)
);