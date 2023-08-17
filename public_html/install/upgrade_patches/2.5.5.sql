ALTER TABLE `lc_orders_items`
ADD COLUMN `priority` INT(11) NOT NULL DEFAULT 0 AFTER `dim_class`;