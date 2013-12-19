# Upgrade

  The following is a list of changes that can be of importance when performing a manual upgrade.

  The standard procedure for upgrading is to replace the old set of files with the new ones and perform any MySQL changes to the database. When replacing the set of files you may keep the following (created by the installer):

    ~/admin/.htaccess
    ~/admin/.htpasswd
    ~/includes/config.inc.php
    ~/.htaccess
    
  You may also want to keep data stored in the following folders:

    ~/data
    ~/images
    
  WinMerge is a powerful free tool to discover differences between two different sets of files and folders.

## LiteCart 1.0.1.6 to 1.1
  
	MySQL Changes:
  
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
    VALUES ('', 'global', 'Catalog Template Settings', '', 'store_template_catalog_settings', '', '', '', NOW(), NOW()),
    ('listings', 'local', 'Max Age for New Products', 'Display the new sticker for products younger than the give age. Example: 1 month or 14 days', 'new_products_max_age', '1 month', 'input()', 14, NOW(), NOW());
    
    ALTER TABLE `lc_categories` ADD `list_style` VARCHAR(32) NOT NULL AFTER `code`;
    
    ALTER TABLE `lc_categories` ADD `dock` VARCHAR(32) NOT NULL AFTER `list_style`;
    
    ALTER TABLE `lc_categories` ADD INDEX (`dock`);
    
    UPDATE `lc_settings` SET `setting_group_key` = 'defaults', `key` = 'default_display_prices_including_tax' WHERE `key` = 'display_prices_including_tax' LIMIT 1;
  
  Regular expressions for the new system model syntax:
  (Can be used with i.e. Notepad++ for updating add-ons)
  
    Search: \$(?:GLOBALS\['system'\]|system|this->system)->([a-z]+)->([a-z|_]+)\(
    Replace: $1::${2}\(

    Search: \$(?:GLOBALS\['system'\]|system|this->system)->([a-z]+)->([a-z|_]+)\[
    Replace: $1::\$${2}\[

    Search: \$(?:GLOBALS\['system'\]|system|this->system)->([a-z]+)->([a-z|_]+)(?:\(|;|\s)
    Replace: $1::\$${2}\[

    Search: \$(?:GLOBALS\['system'\]|system|this->system)->([a-z]+)->([a-z|_]+)(\)|;|\s)
    Replace: $1::\$${2}${3}
  
  New Files:
    
    ~/admin/appearance.app/*
    ~/ext/jquery/jquery-1.10.2.min.map
    ~/images/icons/16x16/settings.png
    ~/includes/column_left.inc.php
    ~/includes/templates/default.catalog/config.inc.php
    ~/includes/boxes/region.inc.php
    
  Moved/Renamed Files:
    
    ~/includes/functions/*.inc.php            =>    ~/includes/functions/func_*.inc.php
    ~/includes/classes/customer.inc.php       =>    ~/includes/modules/mod_customer.inc.php
    ~/includes/classes/jobs.inc.php           =>    ~/includes/modules/mod_jobs.inc.php
    ~/includes/classes/order_action.inc.php   =>    ~/includes/modules/mod_order_action.inc.php
    ~/includes/classes/order_success.inc.php  =>    ~/includes/modules/mod_order_success.inc.php
    ~/includes/classes/order_total.inc.php    =>    ~/includes/modules/mod_order_total.inc.php
    ~/includes/classes/payment.inc.php        =>    ~/includes/modules/mod_payment.inc.php
    ~/includes/classes/shipping.inc.php       =>    ~/includes/modules/mod_shipping.inc.php
    
  (No Deleted Files)
  
## LiteCart 1.0.1.5 to 1.0.1.6

  MySQL changes:
  
    UPDATE `lc_settings` SET `function` = 'zones("default_country_code")' WHERE `key` = 'default_zone_code';
    UPDATE `lc_settings` SET `function` = 'zones("store_country_code")' WHERE `key` = 'store_zone_code';
  
  New Files:
    
    ~/includes/modules/shipping/sm_zone_weight.inc
    ~/includes/templates/default.catalog/styles/loader.css
    
  Deleted Files:
    
    ~/includes/modules/shipping/sm_flat_rate.inc
    ~/includes/modules/shipping/sm_weight_table.inc
    ~/includes/modules/shipping/sm_zone.inc
    ~/includes/templates/default.catalog/styles/loader.css.php
    
## LiteCart 1.0.1.4 to 1.0.1.5

  (No MySQL Changes)
  
  New RewriteRule for products.php in ~/.htacces:
  
    RewriteRule ^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$ product.php?category_id=$1&product_id=$2&%{QUERY_STRING} [L]
    
  New Files:
  
    ~/admin/customers.app/mailchimp.png
    ~/admin/modules.app/run_job.inc.php
    
  Deleted Files:
  
    ~/includes/modules/jobs/job_currency_updater.inc.php
    
## LiteCart 1.0.1.3 to 1.0.1.4

  (No MySQL Changes)
  
  (No New Files)
  
  (No Deleted Files)
  
## LiteCart 1.0.1.2 to 1.0.1.3

  (No MySQL Changes)
  
  New Files:
  
    ~/ext/jquery/jquery-1.10.2.min.js
    ~/ext/jquery/jquery-migrate-1.2.1.min.js
    ~/images/icons/16x16/calendar.png
    
  Deleted Files:
  
    ~/ext/jquery/jquery-1.9.1.min.js
    ~/ext/jquery/jquery-migrate-1.1.1.min.js
    ~/includes/functions/error.inc.php
  
## LiteCart 1.0.1. to 1.0.1.2

  (No MySQL Changes)
  
## LiteCart 1.0 to 1.0.1
  
  MySQL changes:
    
    RENAME TABLE `lc_delivery_status` TO `lc_delivery_statuses`;
    RENAME TABLE `lc_delivery_status_info` TO `lc_delivery_statuses_info`;
    
    RENAME TABLE `lc_sold_out_status` TO `lc_sold_out_statuses`;
    RENAME TABLE `lc_sold_out_status_info` TO `lc_sold_out_statuses_info`;
    
    ALTER TABLE `lc_orders` CHANGE `weight` `weight_total` DECIMAL( 11, 4 ) NOT NULL;
    
    ALTER TABLE `lc_orders_items` DROP `code`, DROP `upc`, DROP `taric`, DROP `tax_class_id`, ADD `weight` DECIMAL(11, 4) NOT NULL AFTER `tax`, ADD `weight_class` VARCHAR(2) NOT NULL AFTER `weight`;
    
    RENAME TABLE `lc_orders_status` TO `lc_order_statuses`;
    RENAME TABLE `lc_orders_status_info` TO `lc_order_statuses_info`;
    
    ALTER TABLE `lc_orders_status` ADD `priority` TINYINT(2) NOT NULL AFTER `notify`;
    
    DROP TABLE `lc_orders_tax`;
    
    ALTER TABLE `lc_orders_totals` DROP `tax_class_id`;
    
    ALTER TABLE `lc_products_options_stock` CHANGE `weight` `weight` DECIMAL(11, 4) NOT NULL;
    
    CREATE TABLE `lc_slides` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `status` tinyint(1) NOT NULL,
      `language_code` varchar(8) NOT NULL,
      `name` varchar(128) NOT NULL,
      `caption` varchar(256) NOT NULL,
      `link` varchar(256) NOT NULL,
      `image` varchar(64) NOT NULL,
      `priority` tinyint(2) NOT NULL,
      `date_valid_from` datetime NOT NULL,
      `date_valid_to` datetime NOT NULL,
      `date_updated` datetime NOT NULL,
      `date_created` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    CREATE TABLE `lc_users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `status` tinyint(1) NOT NULL,
      `username` varchar(32) NOT NULL,
      `password` varchar(128) NOT NULL,
      `last_ip` varchar(15) NOT NULL,
      `last_host` varchar(64) NOT NULL,
      `login_attempts` int(11) NOT NULL,
      `total_logins` int(11) NOT NULL,
      `date_blocked` datetime NOT NULL,
      `date_expires` datetime NOT NULL,
      `date_active` datetime NOT NULL,
      `date_login` datetime NOT NULL,
      `date_created` datetime NOT NULL,
      `date_updated` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
    ('', 'local', 'Installed Customer Modules', '', 'customer_modules', '', '', 0, NOW(), NOW());
    
    DELETE FROM `lc_settings` where `key` in ('checkout_captcha_enabled', 'checkout_ajax_enabled', 'get_address_modules');
    
	  UPDATE `lc_settings` SET `value` = 0 WHERE `value` = 'false';
	  UPDATE `lc_settings` SET `value` = 1 WHERE `value` = 'true';

  New Files:
  
    ~/admin/orders.app/add_custom_item.inc.php
    ~/admin/orders.app/add_product.inc.php
    ~/admin/slides.app/*
    ~/images/slides/*
    ~/includes/modules/customer/*
    
  Deleted Files:
  
    ~/includes/modules/get_address/*
  