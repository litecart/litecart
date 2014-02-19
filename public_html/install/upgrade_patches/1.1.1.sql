ALTER TABLE `lc_currencies` ADD `number` VARCHAR(3) NOT NULL AFTER `code`;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `number` = '978' WHERE `code` = 'EUR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_currencies` SET `number` = '840' WHERE `code` = 'USD' LIMIT 1;
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('general', 'global', 'Catalog Only Mode', 'Disables the cart and checkout features leaving only a browsable catalog.', 'catalog_only_mode', '0', 'toggle("t/f")', 17, NOW(), NOW());
-- --------------------------------------------------------    
ALTER TABLE `lc_slides` CHANGE `caption` `caption` VARCHAR(512);
-- --------------------------------------------------------
ALTER TABLE `lc_tax_classes` ADD `code` VARCHAR(32) NOT NULL AFTER `id`;
-- --------------------------------------------------------
ALTER TABLE `lc_tax_rates` ADD `code` VARCHAR(32) NOT NULL AFTER `geo_zone_id`;
