# Upgrade

## Instructions

  Note: Add-ons are version specific and might cause your upgraded platform to malfunction. Make sure all your add-ons are up to date.
  
  1. Backup your files and database!!
  
  2. Note your current platform version.
  
  3. Upload the contents of the folder public_html/* to the corresponding path of your installation replacing the old files. Any modified files will be overwritten!
  
  4. Point your browser to http://www.yoursite.com/path/to/install/upgrade.php and follow the instructions on the page.
  
  5. Make sure everything went fine and delete the install/ folder.
  
  If you need help, turn to our forums at http://forums.litecart.net.
  
## Performing a Manual Upgrade
  
  This chapter contains is a list of changes that can be of importance when performing a manual upgrade.
  
  The standard procedure for manual upgrading is to replace the old set of files with the new ones and perform any MySQL changes necessary to the database. When replacing the set of files you may keep the following (created by the installer):
  
    ~/admin/.htaccess
    ~/admin/.htpasswd
    ~/includes/config.inc.php
    ~/.htaccess
  
  You may also want to keep data stored in the following folders:
  
    ~/data
    ~/images
  
  WinMerge is a powerful free tool to discover differences between two different sets of files and folders. Especially if they contain modifications by third party add-ons.

### LiteCart 1.2.2.1 to 1.3
  
  MySQL Changes:
  
    CREATE TABLE IF NOT EXISTS `lc_quantity_units` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `decimals` tinyint(1) NOT NULL,
      `separate` tinyint(1) NOT NULL
      `priority` tinyint(2) NOT NULL,
      `date_updated` datetime NOT NULL,
      `date_created` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    CREATE TABLE IF NOT EXISTS `lc_quantity_units_info` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `quantity_unit_id` int(11) NOT NULL,
      `language_code` varchar(2) NOT NULL,
      `name` varchar(32) NOT NULL,
      `description` VARCHAR(512),
      PRIMARY KEY (`id`),
      KEY `quantity_unit_id` (`quantity_unit_id`),
      KEY `language_code` (`language_code`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    INSERT INTO `lc_quantity_units` (`id`, `decimals`, `separate`, `priority`, `date_updated`, `date_created`) VALUES
    (1, 0, 0, 0, NOW(), NOW());
    
    INSERT INTO `lc_quantity_units_info` (`id`, `quantity_unit_id`, `language_code`, `name`) VALUES
    (1, 1, 'en', 'pcs');
    
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
    ('defaults', 'global', 'Default Quantity Unit', 'Default quantity unit that will be preset when creating new products.', 'default_quantity_unit_id', '1', 'quantity_units()', 15, NOW(), NOW());
    
    ALTER TABLE `lc_products` ADD `quantity_unit_id` TINYINT(1) NOT NULL AFTER  `quantity`;
    
    UPDATE `lc_products` set quantity_unit_id = 1 WHERE quantity_unit_id = 0;
    
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
    VALUES ('default', 'global', 'Default Sold Out Status', 'Default delivery status that will be preset when creating new products.', 'default_sold_out_status_id', '1', 'sold_out_statuses()', 17, NOW(), NOW()),
    ('default', 'global', 'Default Delivery Status', 'Default sold out status that will be preset when creating new products.', 'default_delivery_status_id', '1', 'delivery_statuses()', 18, NOW(), NOW());
    
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
    VALUES ('advanced', 'global', 'Clear System Cache', 'Remove all cached system information.', 'cache_clear', '1', 'toggle()', 11, NOW(), NOW());
    
    ALTER TABLE `lc_languages` ADD `code2` VARCHAR(3) NOT NULL AFTER `code`;
    
    UPDATE `lc_languages` set code2 = 'eng' WHERE code = 'en' LIMIT 1;
    
    ALTER TABLE `lc_order_statuses` ADD `icon` VARCHAR(16) NOT NULL AFTER `id`, ADD `color` VARCHAR(16) NOT NULL AFTER `icon`;
  
  Deleted Files:
    
    admin/appearance.app/icon.png
    admin/catalog.app/icon.png
    admin/countries.app/icon.png
    admin/currencies.app/icon.png
    admin/customers.app/icon.png
    admin/geo_zones.app/icon.png
    admin/languages.app/icon.png
    admin/modules.app/icon.png
    admin/orders.app/icon.png
    admin/pages.app/icon.png
    admin/reports.app/icon.png
    admin/settings.app/icon.png
    admin/slides.app/icon.png
    admin/tax.app/icon.png
    admin/translations.app/icon.png
    admin/translations.app/pages.inc.php
    admin/translations.app/untranslated.inc.php
    admin/users.app/icon.png
    admin/vqmods.app/icon.png
    ext/fancybox/jquery.fancybox-1.3.4.js
    ext/nivo-slider/themes/default/arrows.png
    ext/nivo-slider/themes/default/bullets.png
    ext/nivo-slider/themes/default/default.css
    ext/nivo-slider/themes/default/loading.gif
    ext/nivo-slider/jquery.nivo.slider.pack.js
    ext/nivo-slider/license.txt
    ext/nivo-slider/nivo-slider.css
    images/icons/16x16/add.png
    images/icons/16x16/box.png
    images/icons/16x16/calendar.png
    images/icons/16x16/cancel.png
    images/icons/16x16/collapse.png
    images/icons/16x16/delete.png
    images/icons/16x16/down.png
    images/icons/16x16/download.png
    images/icons/16x16/edit.png
    images/icons/16x16/expand.png
    images/icons/16x16/folder_closed.png
    images/icons/16x16/folder_opened.png
    images/icons/16x16/home.png
    images/icons/16x16/index.html
    images/icons/16x16/label.png
    images/icons/16x16/loading.gif
    images/icons/16x16/off.png
    images/icons/16x16/on.png
    images/icons/16x16/preview.png
    images/icons/16x16/print.png
    images/icons/16x16/remove.png
    images/icons/16x16/save.png
    images/icons/16x16/settings.png
    images/icons/16x16/up.png
    images/icons/24x24/catalog.png
    images/icons/24x24/database.png
    images/icons/24x24/exit.png
    images/icons/24x24/help.png
    images/icons/24x24/home.png
    images/icons/24x24/index.html
    images/icons/24x24/mail.png
    includes/templates/default.catalog/images/home.png
    includes/templates/default.catalog/images/scroll_up.png
    includes/templates/default.catalog/images/search.png
  
  New Files:
  
    admin/catalog.app/edit_quantity_unit.inc.php
    admin/catalog.app/quantity_units.inc.php
    ext/fontawesome/css/font-awesome.min.css
    ext/fontawesome/fonts/fontawesome-webfont.eot
    ext/fontawesome/fonts/fontawesome-webfont.svg
    ext/fontawesome/fonts/fontawesome-webfont.ttf
    ext/fontawesome/fonts/fontawesome-webfont.woff
    ext/fontawesome/fonts/fontawesome-webfont.woff2
    ext/fontawesome/fonts/FontAwesome.otf
    ext/responsiveslider/README.md
    ext/responsiveslider/responsiveslides.css
    ext/responsiveslider/responsiveslides.min.js
    includes/controllers/ctrl_quantity_unit.inc.php
    includes/library/lib_catalog.inc.php
    includes/templates/default.admin/images/loader.png

### LiteCart 1.2.2 to 1.2.2.1

  (No MySQL Changes)
  
  (No New or Deleted Files)

### LiteCart 1.2.1 to 1.2.2
  
  MySQL Changes:
    
    DROP TABLE `lc_seo_links_cache`;
    
    DELETE FROM `lc_settings` where `key` = 'cache_clear_seo_links';
    
    UPDATE `lc_settings_groups` SET `priority` = '60' WHERE `key` = 'advanced';
    
    INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES ('security', 'Security', 'Site security and protection against threats', 70);
    
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
    VALUES ('security', 'global', 'Session Hijacking Protection', 'Destroy sessions that were signed for a different IP address and user agent.', 'security_session_hijacking', '1', 'toggle("e/d")', '2', NOW(), NOW()),
    ('security', 'global', 'Blacklist', 'Deny blacklisted clients access to the site.', 'security_blacklist', '1', 'toggle("e/d")', '1', NOW(), NOW()),
    ('security', 'global', 'HTTP POST Protection', 'Prevent incoming HTTP POST data from external sites by checking for valid form tickets.', 'security_http_post', '1', 'toggle("e/d")', '3', NOW(), NOW()),
    ('security', 'global', 'Bad Bot Trap', 'Catch bad behaving bots from crawling your website.', 'security_bot_trap', '0', 'toggle("e/d")', '4', NOW(), NOW()),
    ('security', 'global', 'Cross-site Scripting (XSS) Detection', 'Detect common XSS attacks and prevent access to the site.', 'security_xss', '1', 'toggle("e/d")', '5', NOW(), NOW()),
    ('checkout', 'global', 'Round Amounts', 'Round currency amounts to prevent hidden decimals.', 'round_amounts', '0', 'toggle()', '13', NOW(), NOW());
    
    ALTER TABLE `lc_delivery_statuses_info` CHANGE `name` `name` VARCHAR(64) NOT NULL;
    ALTER TABLE `lc_sold_out_statuses_info` CHANGE `name` `name` VARCHAR(64) NOT NULL;
    
    ALTER TABLE `lc_countries` ADD `iso_code_1` VARCHAR(3) NOT NULL AFTER `domestic_name`;
    UPDATE `lc_countries` SET iso_code_1 = '004' WHERE iso_code_2 = 'AF';
    UPDATE `lc_countries` SET iso_code_1 = '248' WHERE iso_code_2 = 'AX';
    UPDATE `lc_countries` SET iso_code_1 = '008' WHERE iso_code_2 = 'AL';
    UPDATE `lc_countries` SET iso_code_1 = '012' WHERE iso_code_2 = 'DZ';
    UPDATE `lc_countries` SET iso_code_1 = '016' WHERE iso_code_2 = 'AS';
    UPDATE `lc_countries` SET iso_code_1 = '020' WHERE iso_code_2 = 'AD';
    UPDATE `lc_countries` SET iso_code_1 = '024' WHERE iso_code_2 = 'AO';
    UPDATE `lc_countries` SET iso_code_1 = '660' WHERE iso_code_2 = 'AI';
    UPDATE `lc_countries` SET iso_code_1 = '010' WHERE iso_code_2 = 'AQ';
    UPDATE `lc_countries` SET iso_code_1 = '028' WHERE iso_code_2 = 'AG';
    UPDATE `lc_countries` SET iso_code_1 = '032' WHERE iso_code_2 = 'AR';
    UPDATE `lc_countries` SET iso_code_1 = '051' WHERE iso_code_2 = 'AM';
    UPDATE `lc_countries` SET iso_code_1 = '533' WHERE iso_code_2 = 'AW';
    UPDATE `lc_countries` SET iso_code_1 = '036' WHERE iso_code_2 = 'AU';
    UPDATE `lc_countries` SET iso_code_1 = '040' WHERE iso_code_2 = 'AT';
    UPDATE `lc_countries` SET iso_code_1 = '031' WHERE iso_code_2 = 'AZ';
    UPDATE `lc_countries` SET iso_code_1 = '044' WHERE iso_code_2 = 'BS';
    UPDATE `lc_countries` SET iso_code_1 = '048' WHERE iso_code_2 = 'BH';
    UPDATE `lc_countries` SET iso_code_1 = '050' WHERE iso_code_2 = 'BD';
    UPDATE `lc_countries` SET iso_code_1 = '052' WHERE iso_code_2 = 'BB';
    UPDATE `lc_countries` SET iso_code_1 = '112' WHERE iso_code_2 = 'BY';
    UPDATE `lc_countries` SET iso_code_1 = '056' WHERE iso_code_2 = 'BE';
    UPDATE `lc_countries` SET iso_code_1 = '084' WHERE iso_code_2 = 'BZ';
    UPDATE `lc_countries` SET iso_code_1 = '204' WHERE iso_code_2 = 'BJ';
    UPDATE `lc_countries` SET iso_code_1 = '060' WHERE iso_code_2 = 'BM';
    UPDATE `lc_countries` SET iso_code_1 = '064' WHERE iso_code_2 = 'BT';
    UPDATE `lc_countries` SET iso_code_1 = '068' WHERE iso_code_2 = 'BO';
    UPDATE `lc_countries` SET iso_code_1 = '535' WHERE iso_code_2 = 'BQ';
    UPDATE `lc_countries` SET iso_code_1 = '070' WHERE iso_code_2 = 'BA';
    UPDATE `lc_countries` SET iso_code_1 = '072' WHERE iso_code_2 = 'BW';
    UPDATE `lc_countries` SET iso_code_1 = '074' WHERE iso_code_2 = 'BV';
    UPDATE `lc_countries` SET iso_code_1 = '076' WHERE iso_code_2 = 'BR';
    UPDATE `lc_countries` SET iso_code_1 = '086' WHERE iso_code_2 = 'IO';
    UPDATE `lc_countries` SET iso_code_1 = '096' WHERE iso_code_2 = 'BN';
    UPDATE `lc_countries` SET iso_code_1 = '100' WHERE iso_code_2 = 'BG';
    UPDATE `lc_countries` SET iso_code_1 = '854' WHERE iso_code_2 = 'BF';
    UPDATE `lc_countries` SET iso_code_1 = '108' WHERE iso_code_2 = 'BI';
    UPDATE `lc_countries` SET iso_code_1 = '116' WHERE iso_code_2 = 'KH';
    UPDATE `lc_countries` SET iso_code_1 = '120' WHERE iso_code_2 = 'CM';
    UPDATE `lc_countries` SET iso_code_1 = '124' WHERE iso_code_2 = 'CA';
    UPDATE `lc_countries` SET iso_code_1 = '132' WHERE iso_code_2 = 'CV';
    UPDATE `lc_countries` SET iso_code_1 = '136' WHERE iso_code_2 = 'KY';
    UPDATE `lc_countries` SET iso_code_1 = '140' WHERE iso_code_2 = 'CF';
    UPDATE `lc_countries` SET iso_code_1 = '148' WHERE iso_code_2 = 'TD';
    UPDATE `lc_countries` SET iso_code_1 = '152' WHERE iso_code_2 = 'CL';
    UPDATE `lc_countries` SET iso_code_1 = '156' WHERE iso_code_2 = 'CN';
    UPDATE `lc_countries` SET iso_code_1 = '162' WHERE iso_code_2 = 'CX';
    UPDATE `lc_countries` SET iso_code_1 = '166' WHERE iso_code_2 = 'CC';
    UPDATE `lc_countries` SET iso_code_1 = '170' WHERE iso_code_2 = 'CO';
    UPDATE `lc_countries` SET iso_code_1 = '174' WHERE iso_code_2 = 'KM';
    UPDATE `lc_countries` SET iso_code_1 = '178' WHERE iso_code_2 = 'CG';
    UPDATE `lc_countries` SET iso_code_1 = '180' WHERE iso_code_2 = 'CD';
    UPDATE `lc_countries` SET iso_code_1 = '184' WHERE iso_code_2 = 'CK';
    UPDATE `lc_countries` SET iso_code_1 = '188' WHERE iso_code_2 = 'CR';
    UPDATE `lc_countries` SET iso_code_1 = '384' WHERE iso_code_2 = 'CI';
    UPDATE `lc_countries` SET iso_code_1 = '191' WHERE iso_code_2 = 'HR';
    UPDATE `lc_countries` SET iso_code_1 = '192' WHERE iso_code_2 = 'CU';
    UPDATE `lc_countries` SET iso_code_1 = '531' WHERE iso_code_2 = 'CW';
    UPDATE `lc_countries` SET iso_code_1 = '196' WHERE iso_code_2 = 'CY';
    UPDATE `lc_countries` SET iso_code_1 = '203' WHERE iso_code_2 = 'CZ';
    UPDATE `lc_countries` SET iso_code_1 = '208' WHERE iso_code_2 = 'DK';
    UPDATE `lc_countries` SET iso_code_1 = '262' WHERE iso_code_2 = 'DJ';
    UPDATE `lc_countries` SET iso_code_1 = '212' WHERE iso_code_2 = 'DM';
    UPDATE `lc_countries` SET iso_code_1 = '214' WHERE iso_code_2 = 'DO';
    UPDATE `lc_countries` SET iso_code_1 = '218' WHERE iso_code_2 = 'EC';
    UPDATE `lc_countries` SET iso_code_1 = '818' WHERE iso_code_2 = 'EG';
    UPDATE `lc_countries` SET iso_code_1 = '222' WHERE iso_code_2 = 'SV';
    UPDATE `lc_countries` SET iso_code_1 = '226' WHERE iso_code_2 = 'GQ';
    UPDATE `lc_countries` SET iso_code_1 = '232' WHERE iso_code_2 = 'ER';
    UPDATE `lc_countries` SET iso_code_1 = '233' WHERE iso_code_2 = 'EE';
    UPDATE `lc_countries` SET iso_code_1 = '231' WHERE iso_code_2 = 'ET';
    UPDATE `lc_countries` SET iso_code_1 = '238' WHERE iso_code_2 = 'FK';
    UPDATE `lc_countries` SET iso_code_1 = '234' WHERE iso_code_2 = 'FO';
    UPDATE `lc_countries` SET iso_code_1 = '242' WHERE iso_code_2 = 'FJ';
    UPDATE `lc_countries` SET iso_code_1 = '246' WHERE iso_code_2 = 'FI';
    UPDATE `lc_countries` SET iso_code_1 = '250' WHERE iso_code_2 = 'FR';
    UPDATE `lc_countries` SET iso_code_1 = '254' WHERE iso_code_2 = 'GF';
    UPDATE `lc_countries` SET iso_code_1 = '258' WHERE iso_code_2 = 'PF';
    UPDATE `lc_countries` SET iso_code_1 = '260' WHERE iso_code_2 = 'TF';
    UPDATE `lc_countries` SET iso_code_1 = '266' WHERE iso_code_2 = 'GA';
    UPDATE `lc_countries` SET iso_code_1 = '270' WHERE iso_code_2 = 'GM';
    UPDATE `lc_countries` SET iso_code_1 = '268' WHERE iso_code_2 = 'GE';
    UPDATE `lc_countries` SET iso_code_1 = '276' WHERE iso_code_2 = 'DE';
    UPDATE `lc_countries` SET iso_code_1 = '288' WHERE iso_code_2 = 'GH';
    UPDATE `lc_countries` SET iso_code_1 = '292' WHERE iso_code_2 = 'GI';
    UPDATE `lc_countries` SET iso_code_1 = '300' WHERE iso_code_2 = 'GR';
    UPDATE `lc_countries` SET iso_code_1 = '304' WHERE iso_code_2 = 'GL';
    UPDATE `lc_countries` SET iso_code_1 = '308' WHERE iso_code_2 = 'GD';
    UPDATE `lc_countries` SET iso_code_1 = '312' WHERE iso_code_2 = 'GP';
    UPDATE `lc_countries` SET iso_code_1 = '316' WHERE iso_code_2 = 'GU';
    UPDATE `lc_countries` SET iso_code_1 = '320' WHERE iso_code_2 = 'GT';
    UPDATE `lc_countries` SET iso_code_1 = '831' WHERE iso_code_2 = 'GG';
    UPDATE `lc_countries` SET iso_code_1 = '324' WHERE iso_code_2 = 'GN';
    UPDATE `lc_countries` SET iso_code_1 = '624' WHERE iso_code_2 = 'GW';
    UPDATE `lc_countries` SET iso_code_1 = '328' WHERE iso_code_2 = 'GY';
    UPDATE `lc_countries` SET iso_code_1 = '332' WHERE iso_code_2 = 'HT';
    UPDATE `lc_countries` SET iso_code_1 = '334' WHERE iso_code_2 = 'HM';
    UPDATE `lc_countries` SET iso_code_1 = '336' WHERE iso_code_2 = 'VA';
    UPDATE `lc_countries` SET iso_code_1 = '340' WHERE iso_code_2 = 'HN';
    UPDATE `lc_countries` SET iso_code_1 = '344' WHERE iso_code_2 = 'HK';
    UPDATE `lc_countries` SET iso_code_1 = '348' WHERE iso_code_2 = 'HU';
    UPDATE `lc_countries` SET iso_code_1 = '352' WHERE iso_code_2 = 'IS';
    UPDATE `lc_countries` SET iso_code_1 = '356' WHERE iso_code_2 = 'IN';
    UPDATE `lc_countries` SET iso_code_1 = '360' WHERE iso_code_2 = 'ID';
    UPDATE `lc_countries` SET iso_code_1 = '364' WHERE iso_code_2 = 'IR';
    UPDATE `lc_countries` SET iso_code_1 = '368' WHERE iso_code_2 = 'IQ';
    UPDATE `lc_countries` SET iso_code_1 = '372' WHERE iso_code_2 = 'IE';
    UPDATE `lc_countries` SET iso_code_1 = '833' WHERE iso_code_2 = 'IM';
    UPDATE `lc_countries` SET iso_code_1 = '376' WHERE iso_code_2 = 'IL';
    UPDATE `lc_countries` SET iso_code_1 = '380' WHERE iso_code_2 = 'IT';
    UPDATE `lc_countries` SET iso_code_1 = '388' WHERE iso_code_2 = 'JM';
    UPDATE `lc_countries` SET iso_code_1 = '392' WHERE iso_code_2 = 'JP';
    UPDATE `lc_countries` SET iso_code_1 = '832' WHERE iso_code_2 = 'JE';
    UPDATE `lc_countries` SET iso_code_1 = '400' WHERE iso_code_2 = 'JO';
    UPDATE `lc_countries` SET iso_code_1 = '398' WHERE iso_code_2 = 'KZ';
    UPDATE `lc_countries` SET iso_code_1 = '404' WHERE iso_code_2 = 'KE';
    UPDATE `lc_countries` SET iso_code_1 = '296' WHERE iso_code_2 = 'KI';
    UPDATE `lc_countries` SET iso_code_1 = '408' WHERE iso_code_2 = 'KP';
    UPDATE `lc_countries` SET iso_code_1 = '410' WHERE iso_code_2 = 'KR';
    UPDATE `lc_countries` SET iso_code_1 = '414' WHERE iso_code_2 = 'KW';
    UPDATE `lc_countries` SET iso_code_1 = '417' WHERE iso_code_2 = 'KG';
    UPDATE `lc_countries` SET iso_code_1 = '418' WHERE iso_code_2 = 'LA';
    UPDATE `lc_countries` SET iso_code_1 = '428' WHERE iso_code_2 = 'LV';
    UPDATE `lc_countries` SET iso_code_1 = '422' WHERE iso_code_2 = 'LB';
    UPDATE `lc_countries` SET iso_code_1 = '426' WHERE iso_code_2 = 'LS';
    UPDATE `lc_countries` SET iso_code_1 = '430' WHERE iso_code_2 = 'LR';
    UPDATE `lc_countries` SET iso_code_1 = '434' WHERE iso_code_2 = 'LY';
    UPDATE `lc_countries` SET iso_code_1 = '438' WHERE iso_code_2 = 'LI';
    UPDATE `lc_countries` SET iso_code_1 = '440' WHERE iso_code_2 = 'LT';
    UPDATE `lc_countries` SET iso_code_1 = '442' WHERE iso_code_2 = 'LU';
    UPDATE `lc_countries` SET iso_code_1 = '446' WHERE iso_code_2 = 'MO';
    UPDATE `lc_countries` SET iso_code_1 = '807' WHERE iso_code_2 = 'MK';
    UPDATE `lc_countries` SET iso_code_1 = '450' WHERE iso_code_2 = 'MG';
    UPDATE `lc_countries` SET iso_code_1 = '454' WHERE iso_code_2 = 'MW';
    UPDATE `lc_countries` SET iso_code_1 = '458' WHERE iso_code_2 = 'MY';
    UPDATE `lc_countries` SET iso_code_1 = '462' WHERE iso_code_2 = 'MV';
    UPDATE `lc_countries` SET iso_code_1 = '466' WHERE iso_code_2 = 'ML';
    UPDATE `lc_countries` SET iso_code_1 = '470' WHERE iso_code_2 = 'MT';
    UPDATE `lc_countries` SET iso_code_1 = '584' WHERE iso_code_2 = 'MH';
    UPDATE `lc_countries` SET iso_code_1 = '474' WHERE iso_code_2 = 'MQ';
    UPDATE `lc_countries` SET iso_code_1 = '478' WHERE iso_code_2 = 'MR';
    UPDATE `lc_countries` SET iso_code_1 = '480' WHERE iso_code_2 = 'MU';
    UPDATE `lc_countries` SET iso_code_1 = '175' WHERE iso_code_2 = 'YT';
    UPDATE `lc_countries` SET iso_code_1 = '484' WHERE iso_code_2 = 'MX';
    UPDATE `lc_countries` SET iso_code_1 = '583' WHERE iso_code_2 = 'FM';
    UPDATE `lc_countries` SET iso_code_1 = '498' WHERE iso_code_2 = 'MD';
    UPDATE `lc_countries` SET iso_code_1 = '492' WHERE iso_code_2 = 'MC';
    UPDATE `lc_countries` SET iso_code_1 = '496' WHERE iso_code_2 = 'MN';
    UPDATE `lc_countries` SET iso_code_1 = '499' WHERE iso_code_2 = 'ME';
    UPDATE `lc_countries` SET iso_code_1 = '500' WHERE iso_code_2 = 'MS';
    UPDATE `lc_countries` SET iso_code_1 = '504' WHERE iso_code_2 = 'MA';
    UPDATE `lc_countries` SET iso_code_1 = '508' WHERE iso_code_2 = 'MZ';
    UPDATE `lc_countries` SET iso_code_1 = '104' WHERE iso_code_2 = 'MM';
    UPDATE `lc_countries` SET iso_code_1 = '516' WHERE iso_code_2 = 'NA';
    UPDATE `lc_countries` SET iso_code_1 = '520' WHERE iso_code_2 = 'NR';
    UPDATE `lc_countries` SET iso_code_1 = '524' WHERE iso_code_2 = 'NP';
    UPDATE `lc_countries` SET iso_code_1 = '528' WHERE iso_code_2 = 'NL';
    UPDATE `lc_countries` SET iso_code_1 = '540' WHERE iso_code_2 = 'NC';
    UPDATE `lc_countries` SET iso_code_1 = '554' WHERE iso_code_2 = 'NZ';
    UPDATE `lc_countries` SET iso_code_1 = '558' WHERE iso_code_2 = 'NI';
    UPDATE `lc_countries` SET iso_code_1 = '562' WHERE iso_code_2 = 'NE';
    UPDATE `lc_countries` SET iso_code_1 = '566' WHERE iso_code_2 = 'NG';
    UPDATE `lc_countries` SET iso_code_1 = '570' WHERE iso_code_2 = 'NU';
    UPDATE `lc_countries` SET iso_code_1 = '574' WHERE iso_code_2 = 'NF';
    UPDATE `lc_countries` SET iso_code_1 = '580' WHERE iso_code_2 = 'MP';
    UPDATE `lc_countries` SET iso_code_1 = '578' WHERE iso_code_2 = 'NO';
    UPDATE `lc_countries` SET iso_code_1 = '512' WHERE iso_code_2 = 'OM';
    UPDATE `lc_countries` SET iso_code_1 = '586' WHERE iso_code_2 = 'PK';
    UPDATE `lc_countries` SET iso_code_1 = '585' WHERE iso_code_2 = 'PW';
    UPDATE `lc_countries` SET iso_code_1 = '275' WHERE iso_code_2 = 'PS';
    UPDATE `lc_countries` SET iso_code_1 = '591' WHERE iso_code_2 = 'PA';
    UPDATE `lc_countries` SET iso_code_1 = '598' WHERE iso_code_2 = 'PG';
    UPDATE `lc_countries` SET iso_code_1 = '600' WHERE iso_code_2 = 'PY';
    UPDATE `lc_countries` SET iso_code_1 = '604' WHERE iso_code_2 = 'PE';
    UPDATE `lc_countries` SET iso_code_1 = '608' WHERE iso_code_2 = 'PH';
    UPDATE `lc_countries` SET iso_code_1 = '612' WHERE iso_code_2 = 'PN';
    UPDATE `lc_countries` SET iso_code_1 = '616' WHERE iso_code_2 = 'PL';
    UPDATE `lc_countries` SET iso_code_1 = '620' WHERE iso_code_2 = 'PT';
    UPDATE `lc_countries` SET iso_code_1 = '630' WHERE iso_code_2 = 'PR';
    UPDATE `lc_countries` SET iso_code_1 = '634' WHERE iso_code_2 = 'QA';
    UPDATE `lc_countries` SET iso_code_1 = '638' WHERE iso_code_2 = 'RE';
    UPDATE `lc_countries` SET iso_code_1 = '642' WHERE iso_code_2 = 'RO';
    UPDATE `lc_countries` SET iso_code_1 = '643' WHERE iso_code_2 = 'RU';
    UPDATE `lc_countries` SET iso_code_1 = '646' WHERE iso_code_2 = 'RW';
    UPDATE `lc_countries` SET iso_code_1 = '652' WHERE iso_code_2 = 'BL';
    UPDATE `lc_countries` SET iso_code_1 = '654' WHERE iso_code_2 = 'SH';
    UPDATE `lc_countries` SET iso_code_1 = '659' WHERE iso_code_2 = 'KN';
    UPDATE `lc_countries` SET iso_code_1 = '662' WHERE iso_code_2 = 'LC';
    UPDATE `lc_countries` SET iso_code_1 = '663' WHERE iso_code_2 = 'MF';
    UPDATE `lc_countries` SET iso_code_1 = '666' WHERE iso_code_2 = 'PM';
    UPDATE `lc_countries` SET iso_code_1 = '670' WHERE iso_code_2 = 'VC';
    UPDATE `lc_countries` SET iso_code_1 = '882' WHERE iso_code_2 = 'WS';
    UPDATE `lc_countries` SET iso_code_1 = '674' WHERE iso_code_2 = 'SM';
    UPDATE `lc_countries` SET iso_code_1 = '678' WHERE iso_code_2 = 'ST';
    UPDATE `lc_countries` SET iso_code_1 = '682' WHERE iso_code_2 = 'SA';
    UPDATE `lc_countries` SET iso_code_1 = '686' WHERE iso_code_2 = 'SN';
    UPDATE `lc_countries` SET iso_code_1 = '688' WHERE iso_code_2 = 'RS';
    UPDATE `lc_countries` SET iso_code_1 = '690' WHERE iso_code_2 = 'SC';
    UPDATE `lc_countries` SET iso_code_1 = '694' WHERE iso_code_2 = 'SL';
    UPDATE `lc_countries` SET iso_code_1 = '702' WHERE iso_code_2 = 'SG';
    UPDATE `lc_countries` SET iso_code_1 = '534' WHERE iso_code_2 = 'SX';
    UPDATE `lc_countries` SET iso_code_1 = '703' WHERE iso_code_2 = 'SK';
    UPDATE `lc_countries` SET iso_code_1 = '705' WHERE iso_code_2 = 'SI';
    UPDATE `lc_countries` SET iso_code_1 = '090' WHERE iso_code_2 = 'SB';
    UPDATE `lc_countries` SET iso_code_1 = '706' WHERE iso_code_2 = 'SO';
    UPDATE `lc_countries` SET iso_code_1 = '710' WHERE iso_code_2 = 'ZA';
    UPDATE `lc_countries` SET iso_code_1 = '239' WHERE iso_code_2 = 'GS';
    UPDATE `lc_countries` SET iso_code_1 = '728' WHERE iso_code_2 = 'SS';
    UPDATE `lc_countries` SET iso_code_1 = '724' WHERE iso_code_2 = 'ES';
    UPDATE `lc_countries` SET iso_code_1 = '144' WHERE iso_code_2 = 'LK';
    UPDATE `lc_countries` SET iso_code_1 = '729' WHERE iso_code_2 = 'SD';
    UPDATE `lc_countries` SET iso_code_1 = '740' WHERE iso_code_2 = 'SR';
    UPDATE `lc_countries` SET iso_code_1 = '744' WHERE iso_code_2 = 'SJ';
    UPDATE `lc_countries` SET iso_code_1 = '748' WHERE iso_code_2 = 'SZ';
    UPDATE `lc_countries` SET iso_code_1 = '752' WHERE iso_code_2 = 'SE';
    UPDATE `lc_countries` SET iso_code_1 = '756' WHERE iso_code_2 = 'CH';
    UPDATE `lc_countries` SET iso_code_1 = '760' WHERE iso_code_2 = 'SY';
    UPDATE `lc_countries` SET iso_code_1 = '158' WHERE iso_code_2 = 'TW';
    UPDATE `lc_countries` SET iso_code_1 = '762' WHERE iso_code_2 = 'TJ';
    UPDATE `lc_countries` SET iso_code_1 = '834' WHERE iso_code_2 = 'TZ';
    UPDATE `lc_countries` SET iso_code_1 = '764' WHERE iso_code_2 = 'TH';
    UPDATE `lc_countries` SET iso_code_1 = '626' WHERE iso_code_2 = 'TL';
    UPDATE `lc_countries` SET iso_code_1 = '768' WHERE iso_code_2 = 'TG';
    UPDATE `lc_countries` SET iso_code_1 = '772' WHERE iso_code_2 = 'TK';
    UPDATE `lc_countries` SET iso_code_1 = '776' WHERE iso_code_2 = 'TO';
    UPDATE `lc_countries` SET iso_code_1 = '780' WHERE iso_code_2 = 'TT';
    UPDATE `lc_countries` SET iso_code_1 = '788' WHERE iso_code_2 = 'TN';
    UPDATE `lc_countries` SET iso_code_1 = '792' WHERE iso_code_2 = 'TR';
    UPDATE `lc_countries` SET iso_code_1 = '795' WHERE iso_code_2 = 'TM';
    UPDATE `lc_countries` SET iso_code_1 = '796' WHERE iso_code_2 = 'TC';
    UPDATE `lc_countries` SET iso_code_1 = '798' WHERE iso_code_2 = 'TV';
    UPDATE `lc_countries` SET iso_code_1 = '800' WHERE iso_code_2 = 'UG';
    UPDATE `lc_countries` SET iso_code_1 = '804' WHERE iso_code_2 = 'UA';
    UPDATE `lc_countries` SET iso_code_1 = '784' WHERE iso_code_2 = 'AE';
    UPDATE `lc_countries` SET iso_code_1 = '826' WHERE iso_code_2 = 'GB';
    UPDATE `lc_countries` SET iso_code_1 = '840' WHERE iso_code_2 = 'US';
    UPDATE `lc_countries` SET iso_code_1 = '581' WHERE iso_code_2 = 'UM';
    UPDATE `lc_countries` SET iso_code_1 = '858' WHERE iso_code_2 = 'UY';
    UPDATE `lc_countries` SET iso_code_1 = '860' WHERE iso_code_2 = 'UZ';
    UPDATE `lc_countries` SET iso_code_1 = '548' WHERE iso_code_2 = 'VU';
    UPDATE `lc_countries` SET iso_code_1 = '862' WHERE iso_code_2 = 'VE';
    UPDATE `lc_countries` SET iso_code_1 = '704' WHERE iso_code_2 = 'VN';
    UPDATE `lc_countries` SET iso_code_1 = '092' WHERE iso_code_2 = 'VG';
    UPDATE `lc_countries` SET iso_code_1 = '850' WHERE iso_code_2 = 'VI';
    UPDATE `lc_countries` SET iso_code_1 = '876' WHERE iso_code_2 = 'WF';
    UPDATE `lc_countries` SET iso_code_1 = '732' WHERE iso_code_2 = 'EH';
    UPDATE `lc_countries` SET iso_code_1 = '887' WHERE iso_code_2 = 'YE';
    UPDATE `lc_countries` SET iso_code_1 = '894' WHERE iso_code_2 = 'ZM';
    UPDATE `lc_countries` SET iso_code_1 = '716' WHERE iso_code_2 = 'ZW';
    UPDATE `lc_countries` SET iso_code_1 = '890' WHERE iso_code_2 = 'YU';
    UPDATE `lc_countries` SET iso_code_1 = '530' WHERE iso_code_2 = 'AN';
    UPDATE `lc_countries` SET iso_code_1 = '249' WHERE iso_code_2 = 'FX';
    UPDATE `lc_countries` SET iso_code_1 = '626' WHERE iso_code_2 = 'TP';
  
### LiteCart 1.2 to 1.2.1
  
  (No MySQL Changes)
  
New Files:
  
    admin/discussions.widget/config.inc.php
    admin/discussions.widget/discussions.cache
    admin/discussions.widget/discussions.inc.php
    admin/discussions.widget/index.html

### LiteCart 1.1.2.1 to 1.2
  
  (No MySQL Changes)
  
  (No New Or Deleted Files)

### LiteCart 1.1.2.1 to 1.2
  
  MySQL Changes:
  
    ALTER TABLE `lc_languages` DROP mysql_collation;
    
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) VALUES
    ('listings', 'local', 'Similar Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_similar_products_num_items', '10', 'int()', 15, NOW(), NOW()),
    ('listings', 'local', 'Recently Viewed Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_recently_viewed_products_num_items', '4', 'int()', 16, NOW(), NOW()),
    ('listings', 'local', 'Latest Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_latest_products_num_items', '10', 'int()', 17, NOW(), NOW()),
    ('listings', 'local', 'Most Popular Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_most_popular_products_num_items', '10', 'int()', 18, NOW(), NOW()),
    ('listings', 'local', 'Campaign Products Box: Number of Items', 'The maximum amount of items to be display in the box.', 'box_campaign_products_num_items', '5', 'int()', 19, NOW(), NOW());
  
  New Files:
  
    admin/languages.app/storage_encoding.inc.php
    admin/vqmods.app/config.inc.php
    admin/vqmods.app/download.inc.php
    admin/vqmods.app/icon.png
    admin/vqmods.app/index.html
    admin/vqmods.app/log.inc.php
    admin/vqmods.app/vqmods.inc.php
    ext/sceditor/languages/.jshintrc
    ext/sceditor/languages/it.js
    ext/sceditor/languages/ja.js
    ext/sceditor/languages/pt-PT.js
    ext/sceditor/languages/tw.js
    ext/sceditor/languages/uk.js
    includes/boxes/box_account.inc.php
    includes/boxes/box_also_purchased_products.inc.php
    includes/boxes/box_campaign_products.inc.php
    includes/boxes/box_cart.inc.php
    includes/boxes/box_categories.inc.php
    includes/boxes/box_category_tree.inc.php
    includes/boxes/box_customer_service_links.inc.php
    includes/boxes/box_filter.inc.php
    includes/boxes/box_information_links.inc.php
    includes/boxes/box_latest_products.inc.php
    includes/boxes/box_manufacturer_logotypes.inc.php
    includes/boxes/box_manufacturers_list.inc.php
    includes/boxes/box_most_popular_products.inc.php
    includes/boxes/box_recently_viewed_products.inc.php
    includes/boxes/box_region.inc.php
    includes/boxes/box_search.inc.php
    includes/boxes/box_similar_products.inc.php
    includes/boxes/box_site_footer.inc.php
    includes/boxes/box_site_menu.inc.php
    includes/boxes/box_slider.inc.php
    includes/classes/view.inc.php
    includes/classes/vmod.inc.php
    includes/library/lib_route.inc.php
    includes/routes/index.html
    includes/routes/url_category.inc.php
    includes/routes/url_customer_service.inc.php
    includes/routes/url_index.inc.php
    includes/routes/url_information.inc.php
    includes/routes/url_manufacturer.inc.php
    includes/routes/url_product.inc.php
    includes/templates/default.catalog/fonts/index.html
    includes/templates/default.catalog/images/cart.png
    includes/templates/default.catalog/images/cart_filled.png
    includes/templates/default.catalog/views/box_account.inc.php
    includes/templates/default.catalog/views/box_account_login.inc.php
    includes/templates/default.catalog/views/box_also_purchased_products.inc.php
    includes/templates/default.catalog/views/box_campaign_products.inc.php
    includes/templates/default.catalog/views/box_cart.inc.php
    includes/templates/default.catalog/views/box_categories.inc.php
    includes/templates/default.catalog/views/box_category.inc.php
    includes/templates/default.catalog/views/box_category_tree.inc.php
    includes/templates/default.catalog/views/box_checkout_cart.inc.php
    includes/templates/default.catalog/views/box_checkout_customer.inc.php
    includes/templates/default.catalog/views/box_checkout_payment.inc.php
    includes/templates/default.catalog/views/box_checkout_shipping.inc.php
    includes/templates/default.catalog/views/box_checkout_summary.inc.php
    includes/templates/default.catalog/views/box_contact_us.inc.php
    includes/templates/default.catalog/views/box_create_account.inc.php
    includes/templates/default.catalog/views/box_customer_service_links.inc.php
    includes/templates/default.catalog/views/box_edit_account.inc.php
    includes/templates/default.catalog/views/box_filter.inc.php
    includes/templates/default.catalog/views/box_information.inc.php
    includes/templates/default.catalog/views/box_information_links.inc.php
    includes/templates/default.catalog/views/box_latest_products.inc.php
    includes/templates/default.catalog/views/box_login.inc.php
    includes/templates/default.catalog/views/box_manufacturer.inc.php
    includes/templates/default.catalog/views/box_manufacturer_logotypes.inc.php
    includes/templates/default.catalog/views/box_manufacturers.inc.php
    includes/templates/default.catalog/views/box_manufacturers_list.inc.php
    includes/templates/default.catalog/views/box_most_popular_products.inc.php
    includes/templates/default.catalog/views/box_order_history.inc.php
    includes/templates/default.catalog/views/box_order_success.inc.php
    includes/templates/default.catalog/views/box_product.inc.php
    includes/templates/default.catalog/views/box_recently_viewed_products.inc.php
    includes/templates/default.catalog/views/box_region.inc.php
    includes/templates/default.catalog/views/box_regional_settings.inc.php
    includes/templates/default.catalog/views/box_search.inc.php
    includes/templates/default.catalog/views/box_similar_products.inc.php
    includes/templates/default.catalog/views/box_site_footer.inc.php
    includes/templates/default.catalog/views/box_site_menu.inc.php
    includes/templates/default.catalog/views/box_slider.inc.php
    includes/templates/default.catalog/views/box_store_map.inc.php
    includes/templates/default.catalog/views/breadcrumbs.inc.php
    includes/templates/default.catalog/views/column_left.inc.php
    includes/templates/default.catalog/views/index.html
    includes/templates/default.catalog/views/index.inc.php
    includes/templates/default.catalog/views/listing_category.inc.php
    includes/templates/default.catalog/views/listing_product.inc.php
    includes/templates/default.catalog/views/pagination.inc.php
    includes/templates/default.catalog/views/printable_order_copy.inc.php
    includes/templates/default.catalog/views/printable_packing_slip.inc.php
    install/upgrade_patches/1.2.inc.php
    install/upgrade_patches/1.2.sql
    pages/ajax/cart.json.inc.php
    pages/ajax/checkout_cart.html.inc.php
    pages/ajax/checkout_customer.html.inc.php
    pages/ajax/checkout_payment.html.inc.php
    pages/ajax/checkout_shipping.html.inc.php
    pages/ajax/checkout_summary.html.inc.php
    pages/ajax/get_address.json.inc.php
    pages/ajax/index.html
    pages/ajax/option_values.json.inc.php
    pages/ajax/zones.json.inc.php
    pages/feeds/index.html
    pages/feeds/sitemap.xml.inc.php
    pages/categories.inc.php
    pages/category.inc.php
    pages/checkout.inc.php
    pages/create_account.inc.php
    pages/customer_service.inc.php
    pages/edit_account.inc.php
    pages/error_document.inc.php
    pages/index.html
    pages/index.inc.php
    pages/information.inc.php
    pages/login.inc.php
    pages/logout.inc.php
    pages/manufacturer.inc.php
    pages/manufacturers.inc.php
    pages/order_history.inc.php
    pages/order_process.inc.php
    pages/order_success.inc.php
    pages/printable_order_copy.inc.php
    pages/product.inc.php
    pages/push_jobs.inc.php
    pages/regional_settings.inc.php
    pages/search.inc.php
    vqmod/logs/index.html
    vqmod/vqcache/index.html
    vqmod/xml/index.html
    vqmod/.htaccess
    vqmod/readme.txt
    vqmod/vqmod.php
  
  Deleted Files:
    
    ajax/cart.json.php
    ajax/checkout_cart.html.php
    ajax/checkout_customer.html.php
    ajax/checkout_payment.html.php
    ajax/checkout_shipping.html.php
    ajax/checkout_summary.html.php
    ajax/get_address.json.php
    ajax/option_values.json.php
    ajax/zones.json.php
    feeds/sitemap.xml.php
    includes/boxes/account.inc.php
    includes/boxes/also_purchased_products.inc.php
    includes/boxes/campaigns.inc.php
    includes/boxes/cart.inc.php
    includes/boxes/categories.inc.php
    includes/boxes/category_tree.inc.php
    includes/boxes/filter.inc.php
    includes/boxes/footer_categories.inc.php
    includes/boxes/footer_information.inc.php
    includes/boxes/footer_manufacturers.inc.php
    includes/boxes/latest_products.inc.php
    includes/boxes/login.inc.php
    includes/boxes/logotypes.inc.php
    includes/boxes/manufacturers.inc.php
    includes/boxes/most_popular.inc.php
    includes/boxes/region.inc.php
    includes/boxes/search.inc.php
    includes/boxes/similar_products.inc.php
    includes/boxes/site_links.inc.php
    includes/boxes/site_menu.inc.php
    includes/boxes/slider.inc.php
    includes/library/lib_seo_links.inc.php
    includes/modules/seo_links/url_category.inc.php
    includes/modules/seo_links/url_customer_service.inc.php
    includes/modules/seo_links/url_information.inc.php
    includes/modules/seo_links/url_manufacturer.inc.php
    includes/modules/seo_links/url_product.inc.php
    includes/modules/seo_links/url_search.inc.php
    includes/printable_order_copy.inc.php
    includes/printable_packing_slip.inc.php
    categories.php
    category.php
    checkout.php
    create_account.php
    customer_service.php
    edit_account.php
    error_document.php
    information.php
    login.php
    logout.php
    manufacturer.php
    manufacturers.php
    order_history.php
    order_process.php
    order_success.php
    printable_order_copy.php
    product.php
    push_jobs.php
    search.php
    select_region.php
    
  Deleted Folders:
  
    ajax/
    feeds/
    includes/modules/seo_links/
  
  Modified Files:
  
    .htaccess (See install/htaccess)
    includes/config.inc.php (See install/config)
  
### LiteCart 1.1.2 to 1.1.2.1
  
  (No MySQL Changes)
  
  (No Deleted Files)
  
### LiteCart 1.1.1 to 1.1.2

  MySQL Changes:
  
    ALTER TABLE `lc_languages` ADD `mysql_collation` VARCHAR(32) NOT NULL AFTER `charset`;
    
  Deleted Files:
  
    ext/jquery/jquery-1.10.2.min.js
    ext/jquery/jquery-1.10.2.min.map
  
### LiteCart 1.1.0.1 to 1.1.1
  
  MySQL Changes:
  
    ALTER TABLE `lc_currencies` ADD `number` VARCHAR(3) NOT NULL AFTER `code`;
    
    UPDATE `lc_currencies` SET `number` = '978' WHERE `code` = 'EUR' LIMIT 1;
    UPDATE `lc_currencies` SET `number` = '840' WHERE `code` = 'USD' LIMIT 1;
    
    ALTER TABLE `lc_order_statuses_info` ADD `email_message` VARCHAR(2048) NOT NULL AFTER `description`;
    
    ALTER TABLE `lc_orders` CHANGE `currency_value` `currency_value` DECIMAL(11,4) NOT NULL;
    ALTER TABLE `lc_orders` CHANGE `payment_due` `payment_due` DECIMAL(11,4) NOT NULL;
    ALTER TABLE `lc_orders` CHANGE `tax_total` `tax_total` DECIMAL(11,4) NOT NULL;
    
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
    VALUES ('general', 'global', 'Catalog Only Mode', 'Disables the cart and checkout features leaving only a browsable catalog.', 'catalog_only_mode', '0', 'toggle("t/f")', 17, NOW(), NOW());
    
    ALTER TABLE `lc_slides` CHANGE `caption` `caption` VARCHAR(512);
    
    ALTER TABLE `lc_tax_classes` ADD `code` VARCHAR(32) NOT NULL AFTER `id`;
    
    ALTER TABLE `lc_tax_rates` ADD `code` VARCHAR(32) NOT NULL AFTER `geo_zone_id`;
    
  New Files:
  
    admin/reports.app/most_shopping_customers.inc.php
    admin/reports.app/most_sold_products.inc.php
  
  Deleted Files:
  
    ext/jqplot/plugins/jqplot.barRenderer.js
    ext/jqplot/plugins/jqplot.BezierCurveRenderer.js
    ext/jqplot/plugins/jqplot.blockRenderer.js
    ext/jqplot/plugins/jqplot.bubbleRenderer.js
    ext/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js
    ext/jqplot/plugins/jqplot.canvasAxisTickRenderer.js
    ext/jqplot/plugins/jqplot.canvasOverlay.js
    ext/jqplot/plugins/jqplot.canvasTextRenderer.js
    ext/jqplot/plugins/jqplot.categoryAxisRenderer.js
    ext/jqplot/plugins/jqplot.ciParser.js
    ext/jqplot/plugins/jqplot.cursor.js
    ext/jqplot/plugins/jqplot.dateAxisRenderer.js
    ext/jqplot/plugins/jqplot.donutRenderer.js
    ext/jqplot/plugins/jqplot.dragable.js
    ext/jqplot/plugins/jqplot.enhancedLegendRenderer.js
    ext/jqplot/plugins/jqplot.funnelRenderer.js
    ext/jqplot/plugins/jqplot.highlighter.js
    ext/jqplot/plugins/jqplot.json2.js
    ext/jqplot/plugins/jqplot.logAxisRenderer.js
    ext/jqplot/plugins/jqplot.mekkoAxisRenderer.js
    ext/jqplot/plugins/jqplot.mekkoRenderer.js
    ext/jqplot/plugins/jqplot.meterGaugeRenderer.js
    ext/jqplot/plugins/jqplot.mobile.js
    ext/jqplot/plugins/jqplot.ohlcRenderer.js
    ext/jqplot/plugins/jqplot.pieRenderer.js
    ext/jqplot/plugins/jqplot.pointLabels.js
    ext/jqplot/plugins/jqplot.pyramidAxisRenderer.js
    ext/jqplot/plugins/jqplot.pyramidGridRenderer.js
    ext/jqplot/plugins/jqplot.pyramidRenderer.js
    ext/jqplot/plugins/jqplot.trendline.js
    ext/jqplot/excanvas.js
    ext/jqplot/jquery.jqplot.css
    ext/jqplot/jquery.jqplot.js
    ext/jqplot/jquery.js
    ext/jqplot/jquery.min.js
  
### LiteCart 1.1 to 1.1.0.1
  
  MySQL Changes:
  
    ALTER TABLE `lc_products` CHANGE `image` `image` VARCHAR(256);
    
    UPDATE `lc_settings` set `value` = '0' where `key`= 'regional_settings_screen_enabled';
  
  (No New Files)
  
  (No Deleted Files)
  
### LiteCart 1.0.1.6 to 1.1
  
	MySQL Changes:
  
    INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
    VALUES ('', 'global', 'Catalog Template Settings', '', 'store_template_catalog_settings', '', '', '', NOW(), NOW()),
    ('listings', 'local', 'Max Age for New Products', 'Display the new sticker for products younger than the give age. Example: 1 month or 14 days', 'new_products_max_age', '1 month', 'input()', 14, NOW(), NOW());
    
    ALTER TABLE `lc_categories` ADD `list_style` VARCHAR(32) NOT NULL AFTER `code`;
    
    ALTER TABLE `lc_categories` ADD `dock` VARCHAR(32) NOT NULL AFTER `list_style`;
    
    ALTER TABLE `lc_categories` ADD INDEX (`dock`);
    
    UPDATE `lc_settings` SET `setting_group_key` = 'defaults', `key` = 'default_display_prices_including_tax' WHERE `key` = 'display_prices_including_tax' LIMIT 1;
    
    UPDATE `lc_countries` SET `currency_code` = 'AFN' WHERE `iso_code_2` = 'AF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ALL' WHERE `iso_code_2` = 'AL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DZD' WHERE `iso_code_2` = 'DZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'AS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'AD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AOA' WHERE `iso_code_2` = 'AO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'AI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'AQ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'AG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ARS' WHERE `iso_code_2` = 'AR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AMD' WHERE `iso_code_2` = 'AM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AWG' WHERE `iso_code_2` = 'AW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'AU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'AT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AZN' WHERE `iso_code_2` = 'AZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BSD' WHERE `iso_code_2` = 'BS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BHD' WHERE `iso_code_2` = 'BH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BDT' WHERE `iso_code_2` = 'BD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BBD' WHERE `iso_code_2` = 'BB' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BYR' WHERE `iso_code_2` = 'BY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'BE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BZD' WHERE `iso_code_2` = 'BZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'BJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BMD' WHERE `iso_code_2` = 'BM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BTN' WHERE `iso_code_2` = 'BT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BOB' WHERE `iso_code_2` = 'BO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BAM' WHERE `iso_code_2` = 'BA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BWP' WHERE `iso_code_2` = 'BW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` = 'BV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BRL' WHERE `iso_code_2` = 'BR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'IO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BND' WHERE `iso_code_2` = 'BN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BGN' WHERE `iso_code_2` = 'BG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'BF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'BIF' WHERE `iso_code_2` = 'BI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KHR' WHERE `iso_code_2` = 'KH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'CM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CAD' WHERE `iso_code_2` = 'CA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CVE' WHERE `iso_code_2` = 'CV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KYD' WHERE `iso_code_2` = 'KY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'CF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'TD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CLP' WHERE `iso_code_2` = 'CL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CNY' WHERE `iso_code_2` = 'CN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'CX' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'CC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'COP' WHERE `iso_code_2` = 'CO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KMF' WHERE `iso_code_2` = 'KM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'CG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'CK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CRC' WHERE `iso_code_2` = 'CR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'CI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HRK' WHERE `iso_code_2` = 'HR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CUP' WHERE `iso_code_2` = 'CU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'CY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CZK' WHERE `iso_code_2` = 'CZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` = 'DK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DJF' WHERE `iso_code_2` = 'DJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'DM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DOP' WHERE `iso_code_2` = 'DO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'TP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ECS' WHERE `iso_code_2` = 'EC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EGP' WHERE `iso_code_2` = 'EG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SVC' WHERE `iso_code_2` = 'SV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'GQ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ERN' WHERE `iso_code_2` = 'ER' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'EE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ETB' WHERE `iso_code_2` = 'ET' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'FKP' WHERE `iso_code_2` = 'FK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` = 'FO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'FJD' WHERE `iso_code_2` = 'FJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'FI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'FR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'FX' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'GF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` = 'PF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'TF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'GA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GMD' WHERE `iso_code_2` = 'GM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GEL' WHERE `iso_code_2` = 'GE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'DE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GHS' WHERE `iso_code_2` = 'GH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GIP' WHERE `iso_code_2` = 'GI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'GR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` = 'GL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'GD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'GP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'GU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'QTQ' WHERE `iso_code_2` = 'GT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GNF' WHERE `iso_code_2` = 'GN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GWP' WHERE `iso_code_2` = 'GW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GYD' WHERE `iso_code_2` = 'GY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HTG' WHERE `iso_code_2` = 'HT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'HM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HNL' WHERE `iso_code_2` = 'HN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HKD' WHERE `iso_code_2` = 'HK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'HUF' WHERE `iso_code_2` = 'HU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ISK' WHERE `iso_code_2` = 'IS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'INR' WHERE `iso_code_2` = 'IN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'IDR' WHERE `iso_code_2` = 'ID' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'IRR' WHERE `iso_code_2` = 'IR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'IQD' WHERE `iso_code_2` = 'IQ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'IE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ILS' WHERE `iso_code_2` = 'IL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'IT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'JMD' WHERE `iso_code_2` = 'JM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'JPY' WHERE `iso_code_2` = 'JP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'JOD' WHERE `iso_code_2` = 'JO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KZT' WHERE `iso_code_2` = 'KZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KES' WHERE `iso_code_2` = 'KE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'KI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KPW' WHERE `iso_code_2` = 'KP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KRW' WHERE `iso_code_2` = 'KR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KWD' WHERE `iso_code_2` = 'KW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'KGS' WHERE `iso_code_2` = 'KG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LAK' WHERE `iso_code_2` = 'LA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LVL' WHERE `iso_code_2` = 'LV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LBP' WHERE `iso_code_2` = 'LB' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LSL' WHERE `iso_code_2` = 'LS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LRD' WHERE `iso_code_2` = 'LR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LYD' WHERE `iso_code_2` = 'LY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CHF' WHERE `iso_code_2` = 'LI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LTL' WHERE `iso_code_2` = 'LT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'LU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MOP' WHERE `iso_code_2` = 'MO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MKD' WHERE `iso_code_2` = 'MK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MGF' WHERE `iso_code_2` = 'MG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MWK' WHERE `iso_code_2` = 'MW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MYR' WHERE `iso_code_2` = 'MY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MVR' WHERE `iso_code_2` = 'MV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'ML' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'MT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'MH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'MQ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MRO' WHERE `iso_code_2` = 'MR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MUR' WHERE `iso_code_2` = 'MU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'YT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MXN' WHERE `iso_code_2` = 'MX' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'FM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MDL' WHERE `iso_code_2` = 'MD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'MC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MNT' WHERE `iso_code_2` = 'MN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'MS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MAD' WHERE `iso_code_2` = 'MA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MZN' WHERE `iso_code_2` = 'MZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MMK' WHERE `iso_code_2` = 'MM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NAD' WHERE `iso_code_2` = 'NA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'NR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NPR' WHERE `iso_code_2` = 'NP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'NL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ANG' WHERE `iso_code_2` = 'AN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` = 'NC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'NZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NIO' WHERE `iso_code_2` = 'NI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'NE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NGN' WHERE `iso_code_2` = 'NG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'NU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'NF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'MP' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` = 'NO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'OMR' WHERE `iso_code_2` = 'OM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PKR' WHERE `iso_code_2` = 'PK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'PW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PAB' WHERE `iso_code_2` = 'PA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PGK' WHERE `iso_code_2` = 'PG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PYG' WHERE `iso_code_2` = 'PY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PEN' WHERE `iso_code_2` = 'PE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PHP' WHERE `iso_code_2` = 'PH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'PN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'PLN' WHERE `iso_code_2` = 'PL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'PT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'PR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'QAR' WHERE `iso_code_2` = 'QA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'RE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'RON' WHERE `iso_code_2` = 'RO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'RUB' WHERE `iso_code_2` = 'RU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'RWF' WHERE `iso_code_2` = 'RW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'KN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'LC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'VC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'WST' WHERE `iso_code_2` = 'WS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'SM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'STD' WHERE `iso_code_2` = 'ST' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SAR' WHERE `iso_code_2` = 'SA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'SN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SCR' WHERE `iso_code_2` = 'SC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SLL' WHERE `iso_code_2` = 'SL' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SGD' WHERE `iso_code_2` = 'SG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'SK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'SI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SBD' WHERE `iso_code_2` = 'SB' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SOS' WHERE `iso_code_2` = 'SO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ZAR' WHERE `iso_code_2` = 'ZA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GBP' WHERE `iso_code_2` = 'GS' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'ES' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'LKR' WHERE `iso_code_2` = 'LK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SHP' WHERE `iso_code_2` = 'SH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'PM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SDG' WHERE `iso_code_2` = 'SD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SRD' WHERE `iso_code_2` = 'SR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` = 'SJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SZL' WHERE `iso_code_2` = 'SZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SEK' WHERE `iso_code_2` = 'SE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'CHF' WHERE `iso_code_2` = 'CH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'SYP' WHERE `iso_code_2` = 'SY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TWD' WHERE `iso_code_2` = 'TW' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TJS' WHERE `iso_code_2` = 'TJ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TZS' WHERE `iso_code_2` = 'TZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'THB' WHERE `iso_code_2` = 'TH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'TG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'TK' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TOP' WHERE `iso_code_2` = 'TO' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TTD' WHERE `iso_code_2` = 'TT' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TND' WHERE `iso_code_2` = 'TN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TRY' WHERE `iso_code_2` = 'TR' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'TMT' WHERE `iso_code_2` = 'TM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'TC' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'TV' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'UGX' WHERE `iso_code_2` = 'UG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'UAH' WHERE `iso_code_2` = 'UA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'AED' WHERE `iso_code_2` = 'AE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'GBP' WHERE `iso_code_2` = 'GB' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'US' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'UM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'UYU' WHERE `iso_code_2` = 'UY' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'UZS' WHERE `iso_code_2` = 'UZ' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'VUV' WHERE `iso_code_2` = 'VU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'VA' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'VEF' WHERE `iso_code_2` = 'VE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'VND' WHERE `iso_code_2` = 'VN' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'VG' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'VI' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` = 'WF' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'MAD' WHERE `iso_code_2` = 'EH' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'YER' WHERE `iso_code_2` = 'YE' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'YUM' WHERE `iso_code_2` = 'YU' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'CD' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ZMW' WHERE `iso_code_2` = 'ZM' LIMIT 1;
    UPDATE `lc_countries` SET `currency_code` = 'ZWD' WHERE `iso_code_2` = 'ZW' LIMIT 1;
    
    UPDATE `lc_countries` SET `phone_code` = '93' WHERE `iso_code_2` = 'AF' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '355' WHERE `iso_code_2` = 'AL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '213' WHERE `iso_code_2` = 'DZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1684' WHERE `iso_code_2` = 'AS' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '376' WHERE `iso_code_2` = 'AD' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '244' WHERE `iso_code_2` = 'AO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1264' WHERE `iso_code_2` = 'AI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '672' WHERE `iso_code_2` = 'AQ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1268' WHERE `iso_code_2` = 'AG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '54' WHERE `iso_code_2` = 'AR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '374' WHERE `iso_code_2` = 'AM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '297' WHERE `iso_code_2` = 'AW' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '61' WHERE `iso_code_2` = 'AU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '43' WHERE `iso_code_2` = 'AT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '994' WHERE `iso_code_2` = 'AZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1242' WHERE `iso_code_2` = 'BS' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '973' WHERE `iso_code_2` = 'BH' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '880' WHERE `iso_code_2` = 'BD' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1246' WHERE `iso_code_2` = 'BB' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '375' WHERE `iso_code_2` = 'BY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '32' WHERE `iso_code_2` = 'BE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '501' WHERE `iso_code_2` = 'BZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '229' WHERE `iso_code_2` = 'BJ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1441' WHERE `iso_code_2` = 'BM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '975' WHERE `iso_code_2` = 'BT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '591' WHERE `iso_code_2` = 'BO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '387' WHERE `iso_code_2` = 'BA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '267' WHERE `iso_code_2` = 'BW' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '55' WHERE `iso_code_2` = 'BR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1284' WHERE `iso_code_2` = 'VG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '673' WHERE `iso_code_2` = 'BN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '359' WHERE `iso_code_2` = 'BG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '226' WHERE `iso_code_2` = 'BF' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '95' WHERE `iso_code_2` = 'MM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '257' WHERE `iso_code_2` = 'BI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '855' WHERE `iso_code_2` = 'KH' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '237' WHERE `iso_code_2` = 'CM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1' WHERE `iso_code_2` = 'CA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '238' WHERE `iso_code_2` = 'CV' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1345' WHERE `iso_code_2` = 'KY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '236' WHERE `iso_code_2` = 'CF' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '235' WHERE `iso_code_2` = 'TD' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '56' WHERE `iso_code_2` = 'CL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '86' WHERE `iso_code_2` = 'CN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '61' WHERE `iso_code_2` = 'CX' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '61' WHERE `iso_code_2` = 'CC' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '57' WHERE `iso_code_2` = 'CO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '269' WHERE `iso_code_2` = 'KM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '682' WHERE `iso_code_2` = 'CK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '506' WHERE `iso_code_2` = 'CR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '385' WHERE `iso_code_2` = 'HR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '53' WHERE `iso_code_2` = 'CU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '357' WHERE `iso_code_2` = 'CY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '420' WHERE `iso_code_2` = 'CZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '243' WHERE `iso_code_2` = 'CD' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '45' WHERE `iso_code_2` = 'DK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '253' WHERE `iso_code_2` = 'DJ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1767' WHERE `iso_code_2` = 'DM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1809' WHERE `iso_code_2` = 'DO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '593' WHERE `iso_code_2` = 'EC' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '20' WHERE `iso_code_2` = 'EG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '503' WHERE `iso_code_2` = 'SV' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '240' WHERE `iso_code_2` = 'GQ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '291' WHERE `iso_code_2` = 'ER' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '372' WHERE `iso_code_2` = 'EE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '251' WHERE `iso_code_2` = 'ET' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '500' WHERE `iso_code_2` = 'FK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '298' WHERE `iso_code_2` = 'FO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '679' WHERE `iso_code_2` = 'FJ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '358' WHERE `iso_code_2` = 'FI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '33' WHERE `iso_code_2` = 'FR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '689' WHERE `iso_code_2` = 'PF' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '241' WHERE `iso_code_2` = 'GA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '220' WHERE `iso_code_2` = 'GM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '995' WHERE `iso_code_2` = 'GE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '49' WHERE `iso_code_2` = 'DE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '233' WHERE `iso_code_2` = 'GH' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '350' WHERE `iso_code_2` = 'GI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '30' WHERE `iso_code_2` = 'GR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '299' WHERE `iso_code_2` = 'GL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1473' WHERE `iso_code_2` = 'GD' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1671' WHERE `iso_code_2` = 'GU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '502' WHERE `iso_code_2` = 'GT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '224' WHERE `iso_code_2` = 'GN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '245' WHERE `iso_code_2` = 'GW' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '592' WHERE `iso_code_2` = 'GY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '509' WHERE `iso_code_2` = 'HT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '39' WHERE `iso_code_2` = 'VA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '504' WHERE `iso_code_2` = 'HN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '852' WHERE `iso_code_2` = 'HK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '36' WHERE `iso_code_2` = 'HU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '354' WHERE `iso_code_2` = 'IS' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '91' WHERE `iso_code_2` = 'IN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '62' WHERE `iso_code_2` = 'ID' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '98' WHERE `iso_code_2` = 'IR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '964' WHERE `iso_code_2` = 'IQ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '353' WHERE `iso_code_2` = 'IE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '44' WHERE `iso_code_2` = 'IM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '972' WHERE `iso_code_2` = 'IL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '39' WHERE `iso_code_2` = 'IT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '225' WHERE `iso_code_2` = 'CI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1876' WHERE `iso_code_2` = 'JM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '81' WHERE `iso_code_2` = 'JP' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '962' WHERE `iso_code_2` = 'JO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '7' WHERE `iso_code_2` = 'KZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '254' WHERE `iso_code_2` = 'KE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '686' WHERE `iso_code_2` = 'KI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '965' WHERE `iso_code_2` = 'KW' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '996' WHERE `iso_code_2` = 'KG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '856' WHERE `iso_code_2` = 'LA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '371' WHERE `iso_code_2` = 'LV' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '961' WHERE `iso_code_2` = 'LB' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '266' WHERE `iso_code_2` = 'LS' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '231' WHERE `iso_code_2` = 'LR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '218' WHERE `iso_code_2` = 'LY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '423' WHERE `iso_code_2` = 'LI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '370' WHERE `iso_code_2` = 'LT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '352' WHERE `iso_code_2` = 'LU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '853' WHERE `iso_code_2` = 'MO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '389' WHERE `iso_code_2` = 'MK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '261' WHERE `iso_code_2` = 'MG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '265' WHERE `iso_code_2` = 'MW' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '60' WHERE `iso_code_2` = 'MY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '960' WHERE `iso_code_2` = 'MV' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '223' WHERE `iso_code_2` = 'ML' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '356' WHERE `iso_code_2` = 'MT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '692' WHERE `iso_code_2` = 'MH' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '222' WHERE `iso_code_2` = 'MR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '230' WHERE `iso_code_2` = 'MU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '262' WHERE `iso_code_2` = 'YT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '52' WHERE `iso_code_2` = 'MX' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '691' WHERE `iso_code_2` = 'FM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '373' WHERE `iso_code_2` = 'MD' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '377' WHERE `iso_code_2` = 'MC' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '976' WHERE `iso_code_2` = 'MN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '382' WHERE `iso_code_2` = 'ME' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1664' WHERE `iso_code_2` = 'MS' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '212' WHERE `iso_code_2` = 'MA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '258' WHERE `iso_code_2` = 'MZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '264' WHERE `iso_code_2` = 'NA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '674' WHERE `iso_code_2` = 'NR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '977' WHERE `iso_code_2` = 'NP' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '31' WHERE `iso_code_2` = 'NL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '599' WHERE `iso_code_2` = 'AN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '687' WHERE `iso_code_2` = 'NC' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '64' WHERE `iso_code_2` = 'NZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '505' WHERE `iso_code_2` = 'NI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '227' WHERE `iso_code_2` = 'NE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '234' WHERE `iso_code_2` = 'NG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '683' WHERE `iso_code_2` = 'NU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '672' WHERE `iso_code_2` = 'NF' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '850' WHERE `iso_code_2` = 'KP' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1670' WHERE `iso_code_2` = 'MP' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '47' WHERE `iso_code_2` = 'NO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '968' WHERE `iso_code_2` = 'OM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '92' WHERE `iso_code_2` = 'PK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '680' WHERE `iso_code_2` = 'PW' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '507' WHERE `iso_code_2` = 'PA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '675' WHERE `iso_code_2` = 'PG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '595' WHERE `iso_code_2` = 'PY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '51' WHERE `iso_code_2` = 'PE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '63' WHERE `iso_code_2` = 'PH' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '870' WHERE `iso_code_2` = 'PN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '48' WHERE `iso_code_2` = 'PL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '351' WHERE `iso_code_2` = 'PT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1' WHERE `iso_code_2` = 'PR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '974' WHERE `iso_code_2` = 'QA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '242' WHERE `iso_code_2` = 'CG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '40' WHERE `iso_code_2` = 'RO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '7' WHERE `iso_code_2` = 'RU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '250' WHERE `iso_code_2` = 'RW' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '590' WHERE `iso_code_2` = 'BL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '290' WHERE `iso_code_2` = 'SH' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1869' WHERE `iso_code_2` = 'KN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1758' WHERE `iso_code_2` = 'LC' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1599' WHERE `iso_code_2` = 'MF' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '508' WHERE `iso_code_2` = 'PM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1784' WHERE `iso_code_2` = 'VC' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '685' WHERE `iso_code_2` = 'WS' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '378' WHERE `iso_code_2` = 'SM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '239' WHERE `iso_code_2` = 'ST' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '966' WHERE `iso_code_2` = 'SA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '221' WHERE `iso_code_2` = 'SN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '381' WHERE `iso_code_2` = 'RS' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '248' WHERE `iso_code_2` = 'SC' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '232' WHERE `iso_code_2` = 'SL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '65' WHERE `iso_code_2` = 'SG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '421' WHERE `iso_code_2` = 'SK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '386' WHERE `iso_code_2` = 'SI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '677' WHERE `iso_code_2` = 'SB' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '252' WHERE `iso_code_2` = 'SO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '27' WHERE `iso_code_2` = 'ZA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '82' WHERE `iso_code_2` = 'KR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '34' WHERE `iso_code_2` = 'ES' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '94' WHERE `iso_code_2` = 'LK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '249' WHERE `iso_code_2` = 'SD' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '597' WHERE `iso_code_2` = 'SR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '268' WHERE `iso_code_2` = 'SZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '46' WHERE `iso_code_2` = 'SE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '41' WHERE `iso_code_2` = 'CH' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '963' WHERE `iso_code_2` = 'SY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '886' WHERE `iso_code_2` = 'TW' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '992' WHERE `iso_code_2` = 'TJ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '255' WHERE `iso_code_2` = 'TZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '66' WHERE `iso_code_2` = 'TH' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '670' WHERE `iso_code_2` = 'TL' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '228' WHERE `iso_code_2` = 'TG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '690' WHERE `iso_code_2` = 'TK' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '676' WHERE `iso_code_2` = 'TO' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1868' WHERE `iso_code_2` = 'TT' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '216' WHERE `iso_code_2` = 'TN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '90' WHERE `iso_code_2` = 'TR' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '993' WHERE `iso_code_2` = 'TM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1649' WHERE `iso_code_2` = 'TC' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '688' WHERE `iso_code_2` = 'TV' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '256' WHERE `iso_code_2` = 'UG' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '380' WHERE `iso_code_2` = 'UA' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '971' WHERE `iso_code_2` = 'AE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '44' WHERE `iso_code_2` = 'GB' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1' WHERE `iso_code_2` = 'US' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '598' WHERE `iso_code_2` = 'UY' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '1340' WHERE `iso_code_2` = 'VI' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '998' WHERE `iso_code_2` = 'UZ' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '678' WHERE `iso_code_2` = 'VU' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '58' WHERE `iso_code_2` = 'VE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '84' WHERE `iso_code_2` = 'VN' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '681' WHERE `iso_code_2` = 'WF' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '967' WHERE `iso_code_2` = 'YE' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '260' WHERE `iso_code_2` = 'ZM' LIMIT 1;
    UPDATE `lc_countries` SET `phone_code` = '263' WHERE `iso_code_2` = 'ZW' LIMIT 1;
    
  Regular expressions for the new system model syntax:
  (Can be used with i.e. Notepad++ for updating add-ons)
  
    # $system->library->method( to library::method(
    Search: \$(?:GLOBALS\['system'\]|system|this->system)->([a-z]+)->([a-z|_]+)\(
    Replace: $1::${2}\(

    # $system->library->param to library::$param
    Search: \$(?:GLOBALS\['system'\]|system|this->system)->([a-z]+)->([a-z|_]+)(\[|\)|;|,|\s)
    Replace: $1::\$${2}${3}

    # $this->param to self::$param (Library modules)
    Search: \$this->([a-z|_]+)(\[|\)|;|,|\s)
    Replace: self::\$${1}${2}

    # $this->method( to self::method( (Library modules)
    Search: \$this->([a-z|_]+)(\()
    Replace: self::${1}${2}
    
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
  
### LiteCart 1.0.1.5 to 1.0.1.6

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
    
### LiteCart 1.0.1.4 to 1.0.1.5

  (No MySQL Changes)
  
  New RewriteRule for products.php in ~/.htacces:
  
    RewriteRule ^(?:[a-z]{2}/)?(?:.*-c-([0-9]+)/)?.*-p-([0-9]+)$ product.php?category_id=$1&product_id=$2&%{QUERY_STRING} [L]
    
  New Files:
  
    ~/admin/customers.app/mailchimp.png
    ~/admin/modules.app/run_job.inc.php
    
  Deleted Files:
  
    ~/includes/modules/jobs/job_currency_updater.inc.php
    
### LiteCart 1.0.1.3 to 1.0.1.4

  (No MySQL Changes)
  
  (No New Files)
  
  (No Deleted Files)
  
### LiteCart 1.0.1.2 to 1.0.1.3

  (No MySQL Changes)
  
  New Files:
  
    ~/ext/jquery/jquery-1.10.2.min.js
    ~/ext/jquery/jquery-migrate-1.2.1.min.js
    ~/images/icons/16x16/calendar.png
    
  Deleted Files:
  
    ~/ext/jquery/jquery-1.9.1.min.js
    ~/ext/jquery/jquery-migrate-1.1.1.min.js
    ~/includes/functions/error.inc.php
  
### LiteCart 1.0.1. to 1.0.1.2

  (No MySQL Changes)
  
### LiteCart 1.0 to 1.0.1
  
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
  