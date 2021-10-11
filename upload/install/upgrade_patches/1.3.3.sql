ALTER TABLE `lc_order_statuses` CHANGE `color` `color` VARCHAR(7) NOT NULL;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET color = '#c0c0c0', icon = 'fa-hourglass-o' WHERE icon = 'fa-chain-broken';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET color = '#d7d96f' WHERE icon = 'fa-clock-o';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET color = '#ffa851' WHERE icon = 'fa-cog';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET color = '#99cc66' WHERE icon = 'fa-truck';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET color = '#ff6666' WHERE icon = 'fa-times';
-- --------------------------------------------------------
DELETE FROM `lc_countries` WHERE `iso_code_2` = 'FX' LIMIT 1;