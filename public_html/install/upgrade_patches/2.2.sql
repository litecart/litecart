ALTER TABLE `lc_customers`
ADD COLUMN `last_ip` VARCHAR(39) NOT NULL AFTER `password_reset_token`,
ADD COLUMN `last_agent` VARCHAR(256) NOT NULL AFTER `last_ip`
ADD COLUMN `date_login` DATETIME NOT NULL AFTER `last_ip`;
