INSERT INTO `lc_currencies` (`status`, `code`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`, `date_created`) VALUES
(1, 'GBP', 'British Pounds', 1, 2, '', ' kr', 0, NOW(), NOW());
-- --------------------------------------------------------
UPDATE `lc_settings`
SET `value` = 'GBP'
WHERE `key` in ('store_currency_code', 'default_currency_code');
-- --------------------------------------------------------
UPDATE `lc_currencies`
SET `value` = 1.629
WHERE `code` = 'USD'
LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_currencies`
SET `value` = 1.183
WHERE `code` = 'EUR'
LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_tax_classes` (`id`, `name`, `description`, `date_updated`, `date_created`) VALUES
(1, 'Standard', '', NOW(), NOW()),
(2, 'Reduced', '', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_tax_rates` (`tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) VALUES
(1, 1, 'percent', 'UK VAT 20%', '', 20, 'both', 'both', NOW(), NOW()),
(2, 1, 'percent', 'UK VAT 5%', '', 5, 'both', 'both', NOW(), NOW());
