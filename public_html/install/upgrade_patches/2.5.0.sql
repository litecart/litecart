ALTER TABLE `lc_order_statuses`
ADD COLUMN `is_trackable` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `is_archived`,
ADD COLUMN `stock_action` ENUM('none','reserve','commit') NOT NULL DEFAULT 'none' AFTER `is_trackable`,
ADD COLUMN `state` ENUM('','created','on_hold','ready','delayed','processing','dispatched','in_transit','delivered','returning','returned','cancelled','fraud') NOT NULL DEFAULT '' AFTER `id`,
DROP COLUMN `keywords`;
 -- --------------------------------------------------------
UPDATE `lc_order_statuses`
SET stock_action = 'commit'
WHERE is_sale = 1;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` os
LEFT JOIN `lc_order_statuses_info` osi on (os.id = osi.order_status_id and osi.language_code = 'en')
SET os.`state` = 'on_hold', os.icon = 'fa-money', color = '#c0c0c0', is_sale = 0, is_archived = 0, is_trackable = 0, stock_action = 'none'
WHERE osi.name = 'Awaiting payment';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` os
LEFT JOIN `lc_order_statuses_info` osi on (os.id = osi.order_status_id and osi.language_code = 'en')
SET osi.name = 'Ready', os.`state` = 'ready', os.icon = 'fa-clock-o', color = '#bec11d', is_sale = 1, is_archived = 0, is_trackable = 0, stock_action = 'reserve'
WHERE osi.name = 'Pending';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` os
LEFT JOIN `lc_order_statuses_info` osi on (os.id = osi.order_status_id and osi.language_code = 'en')
SET os.`state` = 'processing', os.icon = 'fa-cog', color = '#e3ab44', is_sale = 1, is_archived = 0, is_trackable = 0, stock_action = 'reserve'
WHERE osi.name = 'Processing';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` os
LEFT JOIN `lc_order_statuses_info` osi on (os.id = osi.order_status_id and osi.language_code = 'en')
SET os.`state` = 'dispatched', os.icon = 'fa-truck', color = '#99cc66', is_sale = 1, is_archived = 0, is_trackable = 1, stock_action = 'commit'
WHERE osi.name = 'Dispatched';
-- --------------------------------------------------------
UPDATE `lc_order_statuses` os
LEFT JOIN `lc_order_statuses_info` osi on (os.id = osi.order_status_id and osi.language_code = 'en')
SET os.`state` = 'cancelled', os.icon = 'fa-times', color = '#ff6666', is_sale = 0, is_archived = 1, is_trackable = 0, stock_action = 'none'
WHERE osi.name = 'Cancelled';
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses` (`state`, `icon`, `color`, `is_sale`, `is_archived`, `is_trackable`, `stock_action`, `date_updated`, `date_created`) VALUES
('created', 'fa-plus', '#c0c0c0', 0, 0, 0, 'none', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses_info` (`order_status_id`, `language_code`, `name`, `description`) VALUES
(LAST_INSERT_ID(), 'en', 'Created', '');
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses` (`state`, `icon`, `color`, `is_sale`, `is_archived`, `is_trackable`, `stock_action`, `date_updated`, `date_created`) VALUES
('on_hold', 'fa-pause', '#c0c0c0', 1, 0, 0, 'none', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses_info` (`order_status_id`, `language_code`, `name`, `description`) VALUES
(LAST_INSERT_ID(), 'en', 'On hold', '');
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses` (`state`, `icon`, `color`, `is_sale`, `is_archived`, `is_trackable`, `stock_action`, `date_updated`, `date_created`) VALUES
('delayed', 'fa-hourglass-half', '#e3ab44', 1, 0, 0, 'reserve', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses_info` (`order_status_id`, `language_code`, `name`, `description`) VALUES
(LAST_INSERT_ID(), 'en', 'Delayed', '');
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses` (`state`, `icon`, `color`, `is_sale`, `is_archived`, `is_trackable`, `stock_action`, `date_updated`, `date_created`) VALUES
('in_transit', 'fa-truck', '#e3ab44', 1, 0, 1, 'commit', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses_info` (`order_status_id`, `language_code`, `name`, `description`) VALUES
(LAST_INSERT_ID(), 'en', 'In Transit', '');
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses` (`state`, `icon`, `color`, `is_sale`, `is_archived`, `is_trackable`, `stock_action`, `date_updated`, `date_created`) VALUES
('returning', 'fa-undo', '#e3ab44', 1, 0, 1, 'reserved', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses_info` (`order_status_id`, `language_code`, `name`, `description`) VALUES
(LAST_INSERT_ID(), 'en', 'Returning', '');
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses` (`state`, `icon`, `color`, `is_sale`, `is_archived`, `is_trackable`, `stock_action`, `date_updated`, `date_created`) VALUES
('returned', 'fa-building', '#99cc66', 1, 1, 0, 'commit', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses_info` (`order_status_id`, `language_code`, `name`, `description`) VALUES
(LAST_INSERT_ID(), 'en', 'Returned', '');
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses` (`state`, `icon`, `color`, `is_sale`, `is_archived`, `is_trackable`, `stock_action`, `date_updated`, `date_created`) VALUES
('cancelled', 'fa-exclamation', '#ff6666', 0, 1, 0, 'none', NOW(), NOW());
-- --------------------------------------------------------
INSERT INTO `lc_order_statuses_info` (`order_status_id`, `language_code`, `name`, `description`) VALUES
(LAST_INSERT_ID(), 'en', 'Fraud', '');
-- --------------------------------------------------------
ALTER TABLE `lc_newsletter_recipients`
ADD COLUMN `firstname` VARCHAR(32) NOT NULL DEFAULT '' AFTER `email`,
ADD COLUMN `lastname` VARCHAR(32) NOT NULL DEFAULT '' AFTER `firstname`;
-- --------------------------------------------------------
UPDATE `lc_newsletter_recipients` nr
LEFT JOIN `lc_customers` c on (c.email = nr.email)
LEFT JOIN (
  SELECT customer_email as email, customer_firstname as firstname, customer_lastname as lastname
  FROM `lc_orders` o
  GROUP BY customer_email
) o on (o.email = nr.email)
SET nr.firstname = COALESCE(c.firstname, o.firstname, ''),
nr.lastname = COALESCE(c.lastname, o.lastname, '');
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
('listings', 'global', 'Important Notice', 'An important notice to be displayed above your website.', 'important_notice', '', 'regional_text()', 0, NOW(), NOW()),
('listings', 'global', 'Development Mode', 'Development mode restricts frontend access to backend users only.', 'development_mode', '0', 'toggle()', 2, NOW(), NOW());