ALTER TABLE `lc_attribute_values`
ADD COLUMN `priority` INT(11) NOT NULL AFTER `group_id`;
-- --------------------------------------------------------
UPDATE `lc_settings_groups`
SET `name` = 'Customer Details'
WHERE `key` = 'customer_details'
LIMIT 1;
-- --------------------------------------------------------
DELETE FROM `lc_translations`
WHERE `code` = 'settings_group:title_customer_details'
LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('listings', 'global', 'Show Product Count In Category Tree', 'Show the number of products inside each category in the category tree.', 'category_tree_product_count', '0', 'toggle("y/n")', 22, NOW(), NOW());
