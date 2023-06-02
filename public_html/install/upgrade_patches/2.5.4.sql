ALTER TABLE `lc_order_statuses`
CHANGE COLUMN `state` ENUM('','created','on_hold','ready','delayed','processing','completed','dispatched','in_transit','delivered','returning','returned','cancelled','fraud','other') NOT NULL DEFAULT '' AFTER `id`;
-- --------------------------------------------------------
UPDATE `lc_order_statuses`
SET `state` = 'completed'
WHERE `id` = 7 and state = '';
