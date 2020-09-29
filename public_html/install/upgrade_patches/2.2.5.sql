ALTER TABLE `lc_products`
ADD COLUMN `recommended_price` DECIMAL(11,4) NOT NULL AFTER `purchase_price_currency_code`;