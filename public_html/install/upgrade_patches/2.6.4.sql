DELETE t1 FROM `lc_products_prices` t1
JOIN `lc_products_prices` t2 ON t1.product_id = t2.product_id AND t1.id > t2.id;
-- -----
ALTER TABLE `lc_products_prices`
DROP KEY product_id,
ADD UNIQUE KEY product_id (product_id);
-- -----
ALTER TABLE `lc_products_images`
ADD UNIQUE INDEX `product_id_filename` (`product_id`, `filename`);
