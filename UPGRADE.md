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
    
    UPDATE `lc_countries` SET `currency_code` = 'AFN' WHERE `iso_code_2` ='AF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ALL' WHERE `iso_code_2` ='AL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DZD' WHERE `iso_code_2` ='DZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='AS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='AD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AOA' WHERE `iso_code_2` ='AO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='AI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='AQ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='AG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ARS' WHERE `iso_code_2` ='AR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AMD' WHERE `iso_code_2` ='AM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AWG' WHERE `iso_code_2` ='AW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` ='AU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='AT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AZN' WHERE `iso_code_2` ='AZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BSD' WHERE `iso_code_2` ='BS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BHD' WHERE `iso_code_2` ='BH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BDT' WHERE `iso_code_2` ='BD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BBD' WHERE `iso_code_2` ='BB' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BYR' WHERE `iso_code_2` ='BY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='BE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BZD' WHERE `iso_code_2` ='BZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` ='BJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BMD' WHERE `iso_code_2` ='BM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BTN' WHERE `iso_code_2` ='BT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BOB' WHERE `iso_code_2` ='BO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BAM' WHERE `iso_code_2` ='BA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BWP' WHERE `iso_code_2` ='BW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` ='BV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BRL' WHERE `iso_code_2` ='BR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='IO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BND' WHERE `iso_code_2` ='BN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BGN' WHERE `iso_code_2` ='BG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` ='BF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BIF' WHERE `iso_code_2` ='BI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KHR' WHERE `iso_code_2` ='KH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` ='CM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CAD' WHERE `iso_code_2` ='CA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CVE' WHERE `iso_code_2` ='CV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KYD' WHERE `iso_code_2` ='KY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` ='CF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` ='TD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CLP' WHERE `iso_code_2` ='CL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CNY' WHERE `iso_code_2` ='CN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` ='CX' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` ='CC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'COP' WHERE `iso_code_2` ='CO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KMF' WHERE `iso_code_2` ='KM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` ='CG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` ='CK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CRC' WHERE `iso_code_2` ='CR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` ='CI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HRK' WHERE `iso_code_2` ='HR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CUP' WHERE `iso_code_2` ='CU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='CY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CZK' WHERE `iso_code_2` ='CZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` ='DK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DJF' WHERE `iso_code_2` ='DJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='DM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DOP' WHERE `iso_code_2` ='DO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='TP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ECS' WHERE `iso_code_2` ='EC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EGP' WHERE `iso_code_2` ='EG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SVC' WHERE `iso_code_2` ='SV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` ='GQ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ERN' WHERE `iso_code_2` ='ER' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='EE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ETB' WHERE `iso_code_2` ='ET' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'FKP' WHERE `iso_code_2` ='FK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` ='FO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'FJD' WHERE `iso_code_2` ='FJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='FI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='FR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='FX' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='GF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` ='PF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='TF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` ='GA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GMD' WHERE `iso_code_2` ='GM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GEL' WHERE `iso_code_2` ='GE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='DE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GHS' WHERE `iso_code_2` ='GH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GIP' WHERE `iso_code_2` ='GI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='GR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` ='GL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='GD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='GP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='GU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'QTQ' WHERE `iso_code_2` ='GT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GNF' WHERE `iso_code_2` ='GN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GWP' WHERE `iso_code_2` ='GW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GYD' WHERE `iso_code_2` ='GY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HTG' WHERE `iso_code_2` ='HT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` ='HM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HNL' WHERE `iso_code_2` ='HN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HKD' WHERE `iso_code_2` ='HK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HUF' WHERE `iso_code_2` ='HU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ISK' WHERE `iso_code_2` ='IS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'INR' WHERE `iso_code_2` ='IN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'IDR' WHERE `iso_code_2` ='ID' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'IRR' WHERE `iso_code_2` ='IR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'IQD' WHERE `iso_code_2` ='IQ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='IE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ILS' WHERE `iso_code_2` ='IL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='IT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'JMD' WHERE `iso_code_2` ='JM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'JPY' WHERE `iso_code_2` ='JP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'JOD' WHERE `iso_code_2` ='JO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KZT' WHERE `iso_code_2` ='KZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KES' WHERE `iso_code_2` ='KE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` ='KI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KPW' WHERE `iso_code_2` ='KP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KRW' WHERE `iso_code_2` ='KR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KWD' WHERE `iso_code_2` ='KW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KGS' WHERE `iso_code_2` ='KG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LAK' WHERE `iso_code_2` ='LA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LVL' WHERE `iso_code_2` ='LV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LBP' WHERE `iso_code_2` ='LB' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LSL' WHERE `iso_code_2` ='LS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LRD' WHERE `iso_code_2` ='LR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LYD' WHERE `iso_code_2` ='LY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CHF' WHERE `iso_code_2` ='LI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LTL' WHERE `iso_code_2` ='LT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='LU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MOP' WHERE `iso_code_2` ='MO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MKD' WHERE `iso_code_2` ='MK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MGF' WHERE `iso_code_2` ='MG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MWK' WHERE `iso_code_2` ='MW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MYR' WHERE `iso_code_2` ='MY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MVR' WHERE `iso_code_2` ='MV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` ='ML' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='MT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='MH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='MQ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MRO' WHERE `iso_code_2` ='MR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MUR' WHERE `iso_code_2` ='MU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='YT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MXN' WHERE `iso_code_2` ='MX' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='FM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MDL' WHERE `iso_code_2` ='MD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='MC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MNT' WHERE `iso_code_2` ='MN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='MS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MAD' WHERE `iso_code_2` ='MA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MZN' WHERE `iso_code_2` ='MZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MMK' WHERE `iso_code_2` ='MM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NAD' WHERE `iso_code_2` ='NA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` ='NR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NPR' WHERE `iso_code_2` ='NP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='NL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ANG' WHERE `iso_code_2` ='AN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` ='NC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` ='NZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NIO' WHERE `iso_code_2` ='NI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` ='NE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NGN' WHERE `iso_code_2` ='NG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` ='NU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` ='NF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='MP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` ='NO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'OMR' WHERE `iso_code_2` ='OM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PKR' WHERE `iso_code_2` ='PK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='PW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PAB' WHERE `iso_code_2` ='PA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PGK' WHERE `iso_code_2` ='PG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PYG' WHERE `iso_code_2` ='PY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PEN' WHERE `iso_code_2` ='PE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PHP' WHERE `iso_code_2` ='PH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` ='PN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PLN' WHERE `iso_code_2` ='PL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='PT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='PR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'QAR' WHERE `iso_code_2` ='QA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='RE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'RON' WHERE `iso_code_2` ='RO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'RUB' WHERE `iso_code_2` ='RU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'RWF' WHERE `iso_code_2` ='RW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='KN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='LC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` ='VC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'WST' WHERE `iso_code_2` ='WS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='SM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'STD' WHERE `iso_code_2` ='ST' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SAR' WHERE `iso_code_2` ='SA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` ='SN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SCR' WHERE `iso_code_2` ='SC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SLL' WHERE `iso_code_2` ='SL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SGD' WHERE `iso_code_2` ='SG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='SK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='SI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SBD' WHERE `iso_code_2` ='SB' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SOS' WHERE `iso_code_2` ='SO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ZAR' WHERE `iso_code_2` ='ZA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GBP' WHERE `iso_code_2` ='GS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='ES' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LKR' WHERE `iso_code_2` ='LK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SHP' WHERE `iso_code_2` ='SH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='PM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SDG' WHERE `iso_code_2` ='SD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SRD' WHERE `iso_code_2` ='SR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` ='SJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SZL' WHERE `iso_code_2` ='SZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SEK' WHERE `iso_code_2` ='SE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CHF' WHERE `iso_code_2` ='CH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SYP' WHERE `iso_code_2` ='SY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TWD' WHERE `iso_code_2` ='TW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TJS' WHERE `iso_code_2` ='TJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TZS' WHERE `iso_code_2` ='TZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'THB' WHERE `iso_code_2` ='TH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` ='TG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` ='TK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TOP' WHERE `iso_code_2` ='TO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TTD' WHERE `iso_code_2` ='TT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TND' WHERE `iso_code_2` ='TN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TRY' WHERE `iso_code_2` ='TR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TMT' WHERE `iso_code_2` ='TM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='TC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` ='TV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'UGX' WHERE `iso_code_2` ='UG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'UAH' WHERE `iso_code_2` ='UA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AED' WHERE `iso_code_2` ='AE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GBP' WHERE `iso_code_2` ='GB' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='US' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='UM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'UYU' WHERE `iso_code_2` ='UY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'UZS' WHERE `iso_code_2` ='UZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'VUV' WHERE `iso_code_2` ='VU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` ='VA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'VEF' WHERE `iso_code_2` ='VE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'VND' WHERE `iso_code_2` ='VN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='VG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` ='VI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` ='WF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MAD' WHERE `iso_code_2` ='EH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'YER' WHERE `iso_code_2` ='YE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'YUM' WHERE `iso_code_2` ='YU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` ='CD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ZMW' WHERE `iso_code_2` ='ZM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ZWD' WHERE `iso_code_2` ='ZW' LIMIT 1;
  
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
  