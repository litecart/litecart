ALTER TABLE `lc_customers`
DROP COLUMN `date_blocked_until`,
DROP COLUMN `login_attempts`;
-- -----
ALTER TABLE `lc_users` DROP COLUMN `login_attempts`;
