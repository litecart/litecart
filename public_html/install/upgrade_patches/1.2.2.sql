DROP TABLE `lc_seo_links_cache`;
-- --------------------------------------------------------
DELETE FROM `lc_settings` where `key` = 'cache_clear_seo_links';
-- --------------------------------------------------------
UPDATE `lc_settings_groups` SET `priority` = '60' WHERE `key` = 'advanced';
-- --------------------------------------------------------
INSERT INTO `lc_settings_groups` (`key`, `name`, `description`, `priority`) VALUES ('security', 'Security', 'Site security and protection against threats', 70);
-- --------------------------------------------------------
INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('security', 'global', 'Session Hijacking Protection', 'Destroy sessions that were signed for a different IP address and user agent.', 'security_session_hijacking', '1', 'toggle("e/d")', '2', NOW(), NOW()),
('security', 'global', 'Blacklist', 'Deny blacklisted clients access to the site.', 'security_blacklist', '1', 'toggle("e/d")', 10, NOW(), NOW()),
('security', 'global', 'HTTP POST Protection', 'Prevent incoming HTTP POST data from external sites by checking for valid form tickets.', 'security_http_post', '1', 'toggle("e/d")', 11, NOW(), NOW()),
('security', 'global', 'Bad Bot Trap', 'Catch bad behaving bots from crawling your website.', 'security_bot_trap', '0', 'toggle("e/d")', 12, NOW(), NOW()),
('security', 'global', 'Cross-site Scripting (XSS) Detection', 'Detect common XSS attacks and prevent access to the site.', 'security_xss', '1', 'toggle("e/d")', 13, NOW(), NOW()),
('checkout', 'global', 'Round Amounts', 'Round currency amounts to prevent hidden decimals.', 'round_amounts', '0', 'toggle()', 13, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_delivery_statuses_info` CHANGE `name` `name` VARCHAR(64) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_sold_out_statuses_info` CHANGE `name` `name` VARCHAR(64) NOT NULL;
-- --------------------------------------------------------
ALTER TABLE `lc_countries` ADD `iso_code_1` VARCHAR(3) NOT NULL AFTER `domestic_name`;
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '004' WHERE iso_code_2 = 'AF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '248' WHERE iso_code_2 = 'AX';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '008' WHERE iso_code_2 = 'AL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '012' WHERE iso_code_2 = 'DZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '016' WHERE iso_code_2 = 'AS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '020' WHERE iso_code_2 = 'AD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '024' WHERE iso_code_2 = 'AO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '660' WHERE iso_code_2 = 'AI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '010' WHERE iso_code_2 = 'AQ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '028' WHERE iso_code_2 = 'AG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '032' WHERE iso_code_2 = 'AR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '051' WHERE iso_code_2 = 'AM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '533' WHERE iso_code_2 = 'AW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '036' WHERE iso_code_2 = 'AU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '040' WHERE iso_code_2 = 'AT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '031' WHERE iso_code_2 = 'AZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '044' WHERE iso_code_2 = 'BS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '048' WHERE iso_code_2 = 'BH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '050' WHERE iso_code_2 = 'BD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '052' WHERE iso_code_2 = 'BB';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '112' WHERE iso_code_2 = 'BY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '056' WHERE iso_code_2 = 'BE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '084' WHERE iso_code_2 = 'BZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '204' WHERE iso_code_2 = 'BJ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '060' WHERE iso_code_2 = 'BM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '064' WHERE iso_code_2 = 'BT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '068' WHERE iso_code_2 = 'BO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '535' WHERE iso_code_2 = 'BQ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '070' WHERE iso_code_2 = 'BA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '072' WHERE iso_code_2 = 'BW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '074' WHERE iso_code_2 = 'BV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '076' WHERE iso_code_2 = 'BR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '086' WHERE iso_code_2 = 'IO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '096' WHERE iso_code_2 = 'BN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '100' WHERE iso_code_2 = 'BG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '854' WHERE iso_code_2 = 'BF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '108' WHERE iso_code_2 = 'BI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '116' WHERE iso_code_2 = 'KH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '120' WHERE iso_code_2 = 'CM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '124' WHERE iso_code_2 = 'CA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '132' WHERE iso_code_2 = 'CV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '136' WHERE iso_code_2 = 'KY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '140' WHERE iso_code_2 = 'CF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '148' WHERE iso_code_2 = 'TD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '152' WHERE iso_code_2 = 'CL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '156' WHERE iso_code_2 = 'CN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '162' WHERE iso_code_2 = 'CX';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '166' WHERE iso_code_2 = 'CC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '170' WHERE iso_code_2 = 'CO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '174' WHERE iso_code_2 = 'KM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '178' WHERE iso_code_2 = 'CG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '180' WHERE iso_code_2 = 'CD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '184' WHERE iso_code_2 = 'CK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '188' WHERE iso_code_2 = 'CR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '384' WHERE iso_code_2 = 'CI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '191' WHERE iso_code_2 = 'HR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '192' WHERE iso_code_2 = 'CU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '531' WHERE iso_code_2 = 'CW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '196' WHERE iso_code_2 = 'CY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '203' WHERE iso_code_2 = 'CZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '208' WHERE iso_code_2 = 'DK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '262' WHERE iso_code_2 = 'DJ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '212' WHERE iso_code_2 = 'DM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '214' WHERE iso_code_2 = 'DO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '218' WHERE iso_code_2 = 'EC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '818' WHERE iso_code_2 = 'EG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '222' WHERE iso_code_2 = 'SV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '226' WHERE iso_code_2 = 'GQ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '232' WHERE iso_code_2 = 'ER';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '233' WHERE iso_code_2 = 'EE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '231' WHERE iso_code_2 = 'ET';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '238' WHERE iso_code_2 = 'FK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '234' WHERE iso_code_2 = 'FO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '242' WHERE iso_code_2 = 'FJ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '246' WHERE iso_code_2 = 'FI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '250' WHERE iso_code_2 = 'FR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '254' WHERE iso_code_2 = 'GF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '258' WHERE iso_code_2 = 'PF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '260' WHERE iso_code_2 = 'TF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '266' WHERE iso_code_2 = 'GA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '270' WHERE iso_code_2 = 'GM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '268' WHERE iso_code_2 = 'GE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '276' WHERE iso_code_2 = 'DE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '288' WHERE iso_code_2 = 'GH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '292' WHERE iso_code_2 = 'GI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '300' WHERE iso_code_2 = 'GR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '304' WHERE iso_code_2 = 'GL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '308' WHERE iso_code_2 = 'GD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '312' WHERE iso_code_2 = 'GP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '316' WHERE iso_code_2 = 'GU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '320' WHERE iso_code_2 = 'GT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '831' WHERE iso_code_2 = 'GG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '324' WHERE iso_code_2 = 'GN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '624' WHERE iso_code_2 = 'GW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '328' WHERE iso_code_2 = 'GY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '332' WHERE iso_code_2 = 'HT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '334' WHERE iso_code_2 = 'HM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '336' WHERE iso_code_2 = 'VA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '340' WHERE iso_code_2 = 'HN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '344' WHERE iso_code_2 = 'HK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '348' WHERE iso_code_2 = 'HU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '352' WHERE iso_code_2 = 'IS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '356' WHERE iso_code_2 = 'IN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '360' WHERE iso_code_2 = 'ID';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '364' WHERE iso_code_2 = 'IR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '368' WHERE iso_code_2 = 'IQ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '372' WHERE iso_code_2 = 'IE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '833' WHERE iso_code_2 = 'IM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '376' WHERE iso_code_2 = 'IL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '380' WHERE iso_code_2 = 'IT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '388' WHERE iso_code_2 = 'JM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '392' WHERE iso_code_2 = 'JP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '832' WHERE iso_code_2 = 'JE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '400' WHERE iso_code_2 = 'JO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '398' WHERE iso_code_2 = 'KZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '404' WHERE iso_code_2 = 'KE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '296' WHERE iso_code_2 = 'KI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '408' WHERE iso_code_2 = 'KP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '410' WHERE iso_code_2 = 'KR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '414' WHERE iso_code_2 = 'KW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '417' WHERE iso_code_2 = 'KG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '418' WHERE iso_code_2 = 'LA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '428' WHERE iso_code_2 = 'LV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '422' WHERE iso_code_2 = 'LB';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '426' WHERE iso_code_2 = 'LS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '430' WHERE iso_code_2 = 'LR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '434' WHERE iso_code_2 = 'LY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '438' WHERE iso_code_2 = 'LI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '440' WHERE iso_code_2 = 'LT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '442' WHERE iso_code_2 = 'LU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '446' WHERE iso_code_2 = 'MO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '807' WHERE iso_code_2 = 'MK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '450' WHERE iso_code_2 = 'MG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '454' WHERE iso_code_2 = 'MW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '458' WHERE iso_code_2 = 'MY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '462' WHERE iso_code_2 = 'MV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '466' WHERE iso_code_2 = 'ML';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '470' WHERE iso_code_2 = 'MT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '584' WHERE iso_code_2 = 'MH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '474' WHERE iso_code_2 = 'MQ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '478' WHERE iso_code_2 = 'MR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '480' WHERE iso_code_2 = 'MU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '175' WHERE iso_code_2 = 'YT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '484' WHERE iso_code_2 = 'MX';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '583' WHERE iso_code_2 = 'FM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '498' WHERE iso_code_2 = 'MD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '492' WHERE iso_code_2 = 'MC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '496' WHERE iso_code_2 = 'MN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '499' WHERE iso_code_2 = 'ME';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '500' WHERE iso_code_2 = 'MS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '504' WHERE iso_code_2 = 'MA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '508' WHERE iso_code_2 = 'MZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '104' WHERE iso_code_2 = 'MM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '516' WHERE iso_code_2 = 'NA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '520' WHERE iso_code_2 = 'NR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '524' WHERE iso_code_2 = 'NP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '528' WHERE iso_code_2 = 'NL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '540' WHERE iso_code_2 = 'NC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '554' WHERE iso_code_2 = 'NZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '558' WHERE iso_code_2 = 'NI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '562' WHERE iso_code_2 = 'NE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '566' WHERE iso_code_2 = 'NG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '570' WHERE iso_code_2 = 'NU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '574' WHERE iso_code_2 = 'NF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '580' WHERE iso_code_2 = 'MP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '578' WHERE iso_code_2 = 'NO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '512' WHERE iso_code_2 = 'OM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '586' WHERE iso_code_2 = 'PK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '585' WHERE iso_code_2 = 'PW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '275' WHERE iso_code_2 = 'PS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '591' WHERE iso_code_2 = 'PA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '598' WHERE iso_code_2 = 'PG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '600' WHERE iso_code_2 = 'PY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '604' WHERE iso_code_2 = 'PE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '608' WHERE iso_code_2 = 'PH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '612' WHERE iso_code_2 = 'PN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '616' WHERE iso_code_2 = 'PL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '620' WHERE iso_code_2 = 'PT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '630' WHERE iso_code_2 = 'PR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '634' WHERE iso_code_2 = 'QA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '638' WHERE iso_code_2 = 'RE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '642' WHERE iso_code_2 = 'RO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '643' WHERE iso_code_2 = 'RU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '646' WHERE iso_code_2 = 'RW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '652' WHERE iso_code_2 = 'BL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '654' WHERE iso_code_2 = 'SH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '659' WHERE iso_code_2 = 'KN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '662' WHERE iso_code_2 = 'LC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '663' WHERE iso_code_2 = 'MF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '666' WHERE iso_code_2 = 'PM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '670' WHERE iso_code_2 = 'VC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '882' WHERE iso_code_2 = 'WS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '674' WHERE iso_code_2 = 'SM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '678' WHERE iso_code_2 = 'ST';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '682' WHERE iso_code_2 = 'SA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '686' WHERE iso_code_2 = 'SN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '688' WHERE iso_code_2 = 'RS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '690' WHERE iso_code_2 = 'SC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '694' WHERE iso_code_2 = 'SL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '702' WHERE iso_code_2 = 'SG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '534' WHERE iso_code_2 = 'SX';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '703' WHERE iso_code_2 = 'SK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '705' WHERE iso_code_2 = 'SI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '090' WHERE iso_code_2 = 'SB';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '706' WHERE iso_code_2 = 'SO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '710' WHERE iso_code_2 = 'ZA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '239' WHERE iso_code_2 = 'GS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '728' WHERE iso_code_2 = 'SS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '724' WHERE iso_code_2 = 'ES';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '144' WHERE iso_code_2 = 'LK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '729' WHERE iso_code_2 = 'SD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '740' WHERE iso_code_2 = 'SR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '744' WHERE iso_code_2 = 'SJ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '748' WHERE iso_code_2 = 'SZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '752' WHERE iso_code_2 = 'SE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '756' WHERE iso_code_2 = 'CH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '760' WHERE iso_code_2 = 'SY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '158' WHERE iso_code_2 = 'TW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '762' WHERE iso_code_2 = 'TJ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '834' WHERE iso_code_2 = 'TZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '764' WHERE iso_code_2 = 'TH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '626' WHERE iso_code_2 = 'TL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '768' WHERE iso_code_2 = 'TG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '772' WHERE iso_code_2 = 'TK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '776' WHERE iso_code_2 = 'TO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '780' WHERE iso_code_2 = 'TT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '788' WHERE iso_code_2 = 'TN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '792' WHERE iso_code_2 = 'TR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '795' WHERE iso_code_2 = 'TM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '796' WHERE iso_code_2 = 'TC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '798' WHERE iso_code_2 = 'TV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '800' WHERE iso_code_2 = 'UG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '804' WHERE iso_code_2 = 'UA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '784' WHERE iso_code_2 = 'AE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '826' WHERE iso_code_2 = 'GB';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '840' WHERE iso_code_2 = 'US';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '581' WHERE iso_code_2 = 'UM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '858' WHERE iso_code_2 = 'UY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '860' WHERE iso_code_2 = 'UZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '548' WHERE iso_code_2 = 'VU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '862' WHERE iso_code_2 = 'VE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '704' WHERE iso_code_2 = 'VN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '092' WHERE iso_code_2 = 'VG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '850' WHERE iso_code_2 = 'VI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '876' WHERE iso_code_2 = 'WF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '732' WHERE iso_code_2 = 'EH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '887' WHERE iso_code_2 = 'YE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '894' WHERE iso_code_2 = 'ZM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '716' WHERE iso_code_2 = 'ZW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '890' WHERE iso_code_2 = 'YU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '530' WHERE iso_code_2 = 'AN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '249' WHERE iso_code_2 = 'FX';
-- --------------------------------------------------------
UPDATE `lc_countries` SET iso_code_1 = '626' WHERE iso_code_2 = 'TP';