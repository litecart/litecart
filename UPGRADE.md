# Upgrade

In this early stage there are no upgrade tools available for upgrading from an older version of LiteCart. You may perform a manual upgrade by replacing the set of files and performing any SQL modifications listed below.

## LiteCart 1.0.1-dev to 1.0.1
  
  MySQL changes:
  
	  UPDATE `lc_settings` SET `value` = 0 WHERE `value` = 'false';
	  UPDATE `lc_settings` SET `value` = 1 WHERE `value` = 'true';

## LiteCart 1.0.1. to 1.0.1.1

  (No MySQL changes)

## LiteCart 1.0.1. to 1.0.1.2

  (No MySQL changes)

## LiteCart 1.0.1.2 to 1.0.1.3

  (No MySQL changes)

## LiteCart 1.0.1.3 to 1.0.1.4

  (No MySQL changes)

## LiteCart 1.0.1.4 to 1.0.1.5

  (No MySQL changes)
  
  New RewriteRule for products.php in ~/.htacces:
  
    RewriteRule ^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$ product.php?category_id=$1&product_id=$2&%{QUERY_STRING} [L]
  
## LiteCart 1.0.1.6

  MySQL changes:
  
    UPDATE `lc_settings` SET `function` = 'zones("default_country_code")' WHERE `key` = 'default_zone_code';
    UPDATE `lc_settings` SET `function` = 'zones("store_country_code")' WHERE `key` = 'store_zone_code';
  
