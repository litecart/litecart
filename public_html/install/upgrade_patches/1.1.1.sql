ALTER TABLE `lc_currencies` ADD `number` VARCHAR(3) NOT NULL AFTER `code`;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `number` = '978' WHERE `code` = 'EUR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `number` = '840' WHERE `code` = 'USD' LIMIT 1;
-- --------------------------------------------------------
ALTER TABLE `lc_slides` CHANGE `caption` `caption` VARCHAR(512);
-- --------------------------------------------------------
ALTER TABLE `lc_tax_classes` ADD `code` VARCHAR(32) NOT NULL AFTER `id`;
-- --------------------------------------------------------
ALTER TABLE `lc_tax_rates` ADD `code` VARCHAR(32) NOT NULL AFTER `geo_zone_id`;
