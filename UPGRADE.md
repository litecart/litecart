# Upgrade

The following is a list of changes that can be of importance when performing a manual upgrade.

The standard procedure for upgrading is to replace the old set of files with the new ones and perform any MySQL changes to the database. When replacing the set of files you may keep the following (created by the installer):

  ~/admin/.htaccess
  ~/admin/.htpasswd
  ~/includes/config.inc.php
  ~/.htaccess
  
WinMerge is a powerful free tool to discover differences between two different sets of files and folders.

## LiteCart 1.1
  
	MySQL Changes:
  
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
    VALUES ('', 'global', 'Catalog Template Settings', '', 'store_template_catalog_settings', '', '', '', NOW(), NOW()),
    ('listings', 'local', 'Max Age for New Products', 'Display the new sticker for products younger than the give age. Example: 1 month or 14 days', 'new_products_max_age', '1 month', 'input()', 14, NOW(), NOW());
    
    ALTER TABLE `lc_categories` ADD `list_style` VARCHAR(32) NOT NULL AFTER `code`;

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
  
  Deleted Files:
    
    includes/functions/*
    
## LiteCart 1.0.1.6

  MySQL changes:
  
    UPDATE `lc_settings` SET `function` = 'zones("default_country_code")' WHERE `key` = 'default_zone_code';
    UPDATE `lc_settings` SET `function` = 'zones("store_country_code")' WHERE `key` = 'store_zone_code';
    
  Deleted Files:
    
    includes/templates/default.catalog/styles/loader.css.php
    
## LiteCart 1.0.1.4 to 1.0.1.5

  (No MySQL changes)
  
  New RewriteRule for products.php in ~/.htacces:
  
    RewriteRule ^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$ product.php?category_id=$1&product_id=$2&%{QUERY_STRING} [L]
    
  Deleted Files:
  
    includes/modules/jobs/job_currency_updater.inc.php
    
## LiteCart 1.0.1.3 to 1.0.1.4

  (No MySQL changes)
    
## LiteCart 1.0.1.2 to 1.0.1.3

  (No MySQL changes)
    
## LiteCart 1.0.1. to 1.0.1.2

  (No MySQL changes)
    
## LiteCart 1.0.1-dev to 1.0.1
  
  MySQL changes:
  
	  UPDATE `lc_settings` SET `value` = 0 WHERE `value` = 'false';
	  UPDATE `lc_settings` SET `value` = 1 WHERE `value` = 'true';
