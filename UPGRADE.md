# Upgrade #

In this early stage there are no upgrade tools available for upgrading from an older version of LiteCart. A manual upgrade is a drop-on-top set of files and SQL modifications.

## LiteCart 1.0.1 ##
  
  MySQL Changes:
  
    UPDATE `lc_settings` SET value = 0 WHERE value = 'false';
    UPDATE `lc_settings` SET value = 1 WHERE value = 'true';

## LiteCart 1.0.1.1 ##

  No changes

## LiteCart 1.0.1.2 ##

  No changes

## LiteCart 1.0.1.3 ##

  No changes

## LiteCart 1.0.1.4 ##

  No changes

## LiteCart 1.1 ##
  
	MySQL Changes:
  
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
    VALUES ('', 'global', 'Catalog Template Settings', '', 'store_template_catalog_settings', '', '', '', NOW(), NOW()),
    ('listings', 'local', 'Max Age for New Products', 'Display the new sticker for products younger than the give age. Example: 1 month or 14 days', 'new_products_max_age', '1 month', 'input()', 14, NOW(), NOW());
    
    ALTER TABLE `lc_categories` ADD `list_style` VARCHAR( 32 ) NOT NULL AFTER `code`;

  Regular Expressions for the new system model syntax:
  (Can be used with i.e. Notepad++ for updating add-ons)
  
    Search: \$(?:GLOBALS\['system'\]|system|this->system)->([a-z]+)->([a-z|_]+)\(
    Replace: $1::${2}\(

    Search: \$(?:GLOBALS\['system'\]|system|this->system)->([a-z]+)->([a-z|_]+)\[
    Replace: $1::\$${2}\[
  