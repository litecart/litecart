UPDATE `lc_settings` SET
	`function` = 'toggle("y/n")',
	description = 'Send order confirmations via email.'
WHERE `key` = 'send_order_confirmation'
LIMIT 1;
-- -----
DELETE FROM `lc_translations` WHERE code = 'email_customer_feedback' LIMIT 1;
