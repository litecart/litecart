ALTER TABLE `lc_products`
ADD INDEX `date_created` (`date_created`);
-- -----
ALTER TABLE `lc_products_campaigns`
ADD INDEX `start_date` (`start_date`),
ADD INDEX `end_date` (`end_date`);