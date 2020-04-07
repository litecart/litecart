ALTER TABLE `lc_attribute_values`
ADD COLUMN `priority` INT(11) NOT NULL AFTER `group_id`;
-- --------------------------------------------------------
UPDATE `lc_settings_groups`
SET `name` = 'Customer Details'
WHERE `key` = 'customer_details'
LIMIT 1;
