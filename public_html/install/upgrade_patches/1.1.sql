INSERT INTO `lc_settings` (`setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`)
VALUES ('', 'global', 'Catalog Template Settings', '', 'store_template_catalog_settings', '', '', '', NOW(), NOW()),
('listings', 'local', 'Max Age for New Products', 'Display the new sticker for products younger than the give age. Example: 1 month or 14 days', 'new_products_max_age', '1 month', 'input()', 14, NOW(), NOW());
-- --------------------------------------------------------
ALTER TABLE `lc_categories` ADD `list_style` VARCHAR(32) NOT NULL AFTER `code`;
-- --------------------------------------------------------
ALTER TABLE `lc_categories` ADD `dock` VARCHAR(32) NOT NULL AFTER `list_style`;
-- --------------------------------------------------------
ALTER TABLE `lc_categories` ADD INDEX (`dock`);
-- --------------------------------------------------------
UPDATE `lc_settings` SET `setting_group_key` = 'defaults', `key` = 'default_display_prices_including_tax' WHERE `key` = 'display_prices_including_tax' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AFN' WHERE `iso_code_2` = 'AF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ALL' WHERE `iso_code_2` = 'AL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'DZD' WHERE `iso_code_2` = 'DZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'AS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'AD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AOA' WHERE `iso_code_2` = 'AO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'AI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'AQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'AG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ARS' WHERE `iso_code_2` = 'AR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AMD' WHERE `iso_code_2` = 'AM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AWG' WHERE `iso_code_2` = 'AW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'AU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'AT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AZN' WHERE `iso_code_2` = 'AZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BSD' WHERE `iso_code_2` = 'BS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BHD' WHERE `iso_code_2` = 'BH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BDT' WHERE `iso_code_2` = 'BD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BBD' WHERE `iso_code_2` = 'BB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BYR' WHERE `iso_code_2` = 'BY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'BE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BZD' WHERE `iso_code_2` = 'BZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'BJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BMD' WHERE `iso_code_2` = 'BM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BTN' WHERE `iso_code_2` = 'BT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BOB' WHERE `iso_code_2` = 'BO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BAM' WHERE `iso_code_2` = 'BA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BWP' WHERE `iso_code_2` = 'BW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` = 'BV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BRL' WHERE `iso_code_2` = 'BR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'IO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BND' WHERE `iso_code_2` = 'BN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BGN' WHERE `iso_code_2` = 'BG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'BF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'BIF' WHERE `iso_code_2` = 'BI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KHR' WHERE `iso_code_2` = 'KH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'CM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CAD' WHERE `iso_code_2` = 'CA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CVE' WHERE `iso_code_2` = 'CV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KYD' WHERE `iso_code_2` = 'KY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'CF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'TD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CLP' WHERE `iso_code_2` = 'CL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CNY' WHERE `iso_code_2` = 'CN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'CX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'CC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'COP' WHERE `iso_code_2` = 'CO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KMF' WHERE `iso_code_2` = 'KM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'CG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'CK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CRC' WHERE `iso_code_2` = 'CR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'CI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'HRK' WHERE `iso_code_2` = 'HR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CUP' WHERE `iso_code_2` = 'CU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'CY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CZK' WHERE `iso_code_2` = 'CZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` = 'DK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'DJF' WHERE `iso_code_2` = 'DJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'DM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'DOP' WHERE `iso_code_2` = 'DO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'TP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ECS' WHERE `iso_code_2` = 'EC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EGP' WHERE `iso_code_2` = 'EG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SVC' WHERE `iso_code_2` = 'SV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'GQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ERN' WHERE `iso_code_2` = 'ER' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'EE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ETB' WHERE `iso_code_2` = 'ET' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'FKP' WHERE `iso_code_2` = 'FK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` = 'FO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'FJD' WHERE `iso_code_2` = 'FJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'FI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'FR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'FX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'GF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` = 'PF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'TF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'GA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GMD' WHERE `iso_code_2` = 'GM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GEL' WHERE `iso_code_2` = 'GE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'DE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GHS' WHERE `iso_code_2` = 'GH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GIP' WHERE `iso_code_2` = 'GI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'GR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'DKK' WHERE `iso_code_2` = 'GL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'GD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'GP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'GU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'QTQ' WHERE `iso_code_2` = 'GT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GNF' WHERE `iso_code_2` = 'GN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GWP' WHERE `iso_code_2` = 'GW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GYD' WHERE `iso_code_2` = 'GY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'HTG' WHERE `iso_code_2` = 'HT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'HM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'HNL' WHERE `iso_code_2` = 'HN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'HKD' WHERE `iso_code_2` = 'HK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'HUF' WHERE `iso_code_2` = 'HU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ISK' WHERE `iso_code_2` = 'IS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'INR' WHERE `iso_code_2` = 'IN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'IDR' WHERE `iso_code_2` = 'ID' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'IRR' WHERE `iso_code_2` = 'IR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'IQD' WHERE `iso_code_2` = 'IQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'IE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ILS' WHERE `iso_code_2` = 'IL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'IT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'JMD' WHERE `iso_code_2` = 'JM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'JPY' WHERE `iso_code_2` = 'JP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'JOD' WHERE `iso_code_2` = 'JO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KZT' WHERE `iso_code_2` = 'KZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KES' WHERE `iso_code_2` = 'KE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'KI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KPW' WHERE `iso_code_2` = 'KP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KRW' WHERE `iso_code_2` = 'KR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KWD' WHERE `iso_code_2` = 'KW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'KGS' WHERE `iso_code_2` = 'KG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'LAK' WHERE `iso_code_2` = 'LA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'LVL' WHERE `iso_code_2` = 'LV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'LBP' WHERE `iso_code_2` = 'LB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'LSL' WHERE `iso_code_2` = 'LS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'LRD' WHERE `iso_code_2` = 'LR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'LYD' WHERE `iso_code_2` = 'LY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CHF' WHERE `iso_code_2` = 'LI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'LTL' WHERE `iso_code_2` = 'LT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'LU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MOP' WHERE `iso_code_2` = 'MO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MKD' WHERE `iso_code_2` = 'MK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MGF' WHERE `iso_code_2` = 'MG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MWK' WHERE `iso_code_2` = 'MW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MYR' WHERE `iso_code_2` = 'MY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MVR' WHERE `iso_code_2` = 'MV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'ML' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'MT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'MH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'MQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MRO' WHERE `iso_code_2` = 'MR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MUR' WHERE `iso_code_2` = 'MU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'YT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MXN' WHERE `iso_code_2` = 'MX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'FM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MDL' WHERE `iso_code_2` = 'MD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'MC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MNT' WHERE `iso_code_2` = 'MN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'MS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MAD' WHERE `iso_code_2` = 'MA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MZN' WHERE `iso_code_2` = 'MZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MMK' WHERE `iso_code_2` = 'MM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NAD' WHERE `iso_code_2` = 'NA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'NR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NPR' WHERE `iso_code_2` = 'NP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'NL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ANG' WHERE `iso_code_2` = 'AN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` = 'NC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'NZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NIO' WHERE `iso_code_2` = 'NI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'NE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NGN' WHERE `iso_code_2` = 'NG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'NU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'NF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'MP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` = 'NO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'OMR' WHERE `iso_code_2` = 'OM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'PKR' WHERE `iso_code_2` = 'PK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'PW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'PAB' WHERE `iso_code_2` = 'PA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'PGK' WHERE `iso_code_2` = 'PG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'PYG' WHERE `iso_code_2` = 'PY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'PEN' WHERE `iso_code_2` = 'PE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'PHP' WHERE `iso_code_2` = 'PH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'PN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'PLN' WHERE `iso_code_2` = 'PL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'PT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'PR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'QAR' WHERE `iso_code_2` = 'QA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'RE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'RON' WHERE `iso_code_2` = 'RO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'RUB' WHERE `iso_code_2` = 'RU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'RWF' WHERE `iso_code_2` = 'RW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'KN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'LC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XCD' WHERE `iso_code_2` = 'VC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'WST' WHERE `iso_code_2` = 'WS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'SM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'STD' WHERE `iso_code_2` = 'ST' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SAR' WHERE `iso_code_2` = 'SA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'SN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SCR' WHERE `iso_code_2` = 'SC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SLL' WHERE `iso_code_2` = 'SL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SGD' WHERE `iso_code_2` = 'SG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'SK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'SI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SBD' WHERE `iso_code_2` = 'SB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SOS' WHERE `iso_code_2` = 'SO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ZAR' WHERE `iso_code_2` = 'ZA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GBP' WHERE `iso_code_2` = 'GS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'ES' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'LKR' WHERE `iso_code_2` = 'LK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SHP' WHERE `iso_code_2` = 'SH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'PM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SDG' WHERE `iso_code_2` = 'SD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SRD' WHERE `iso_code_2` = 'SR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NOK' WHERE `iso_code_2` = 'SJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SZL' WHERE `iso_code_2` = 'SZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SEK' WHERE `iso_code_2` = 'SE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'CHF' WHERE `iso_code_2` = 'CH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'SYP' WHERE `iso_code_2` = 'SY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'TWD' WHERE `iso_code_2` = 'TW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'TJS' WHERE `iso_code_2` = 'TJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'TZS' WHERE `iso_code_2` = 'TZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'THB' WHERE `iso_code_2` = 'TH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XOF' WHERE `iso_code_2` = 'TG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'NZD' WHERE `iso_code_2` = 'TK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'TOP' WHERE `iso_code_2` = 'TO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'TTD' WHERE `iso_code_2` = 'TT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'TND' WHERE `iso_code_2` = 'TN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'TRY' WHERE `iso_code_2` = 'TR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'TMT' WHERE `iso_code_2` = 'TM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'TC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AUD' WHERE `iso_code_2` = 'TV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'UGX' WHERE `iso_code_2` = 'UG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'UAH' WHERE `iso_code_2` = 'UA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'AED' WHERE `iso_code_2` = 'AE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'GBP' WHERE `iso_code_2` = 'GB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'US' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'UM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'UYU' WHERE `iso_code_2` = 'UY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'UZS' WHERE `iso_code_2` = 'UZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'VUV' WHERE `iso_code_2` = 'VU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'EUR' WHERE `iso_code_2` = 'VA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'VEF' WHERE `iso_code_2` = 'VE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'VND' WHERE `iso_code_2` = 'VN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'VG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'USD' WHERE `iso_code_2` = 'VI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XPF' WHERE `iso_code_2` = 'WF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'MAD' WHERE `iso_code_2` = 'EH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'YER' WHERE `iso_code_2` = 'YE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'YUM' WHERE `iso_code_2` = 'YU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'XAF' WHERE `iso_code_2` = 'CD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ZMW' WHERE `iso_code_2` = 'ZM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `currency_code` = 'ZWD' WHERE `iso_code_2` = 'ZW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '93' WHERE `iso_code_2` = 'AF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '355' WHERE `iso_code_2` = 'AL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '213' WHERE `iso_code_2` = 'DZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1684' WHERE `iso_code_2` = 'AS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '376' WHERE `iso_code_2` = 'AD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '244' WHERE `iso_code_2` = 'AO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1264' WHERE `iso_code_2` = 'AI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '672' WHERE `iso_code_2` = 'AQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1268' WHERE `iso_code_2` = 'AG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '54' WHERE `iso_code_2` = 'AR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '374' WHERE `iso_code_2` = 'AM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '297' WHERE `iso_code_2` = 'AW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '61' WHERE `iso_code_2` = 'AU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '43' WHERE `iso_code_2` = 'AT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '994' WHERE `iso_code_2` = 'AZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1242' WHERE `iso_code_2` = 'BS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '973' WHERE `iso_code_2` = 'BH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '880' WHERE `iso_code_2` = 'BD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1246' WHERE `iso_code_2` = 'BB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '375' WHERE `iso_code_2` = 'BY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '32' WHERE `iso_code_2` = 'BE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '501' WHERE `iso_code_2` = 'BZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '229' WHERE `iso_code_2` = 'BJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1441' WHERE `iso_code_2` = 'BM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '975' WHERE `iso_code_2` = 'BT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '591' WHERE `iso_code_2` = 'BO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '387' WHERE `iso_code_2` = 'BA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '267' WHERE `iso_code_2` = 'BW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '55' WHERE `iso_code_2` = 'BR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1284' WHERE `iso_code_2` = 'VG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '673' WHERE `iso_code_2` = 'BN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '359' WHERE `iso_code_2` = 'BG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '226' WHERE `iso_code_2` = 'BF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '95' WHERE `iso_code_2` = 'MM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '257' WHERE `iso_code_2` = 'BI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '855' WHERE `iso_code_2` = 'KH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '237' WHERE `iso_code_2` = 'CM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1' WHERE `iso_code_2` = 'CA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '238' WHERE `iso_code_2` = 'CV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1345' WHERE `iso_code_2` = 'KY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '236' WHERE `iso_code_2` = 'CF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '235' WHERE `iso_code_2` = 'TD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '56' WHERE `iso_code_2` = 'CL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '86' WHERE `iso_code_2` = 'CN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '61' WHERE `iso_code_2` = 'CX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '61' WHERE `iso_code_2` = 'CC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '57' WHERE `iso_code_2` = 'CO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '269' WHERE `iso_code_2` = 'KM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '682' WHERE `iso_code_2` = 'CK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '506' WHERE `iso_code_2` = 'CR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '385' WHERE `iso_code_2` = 'HR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '53' WHERE `iso_code_2` = 'CU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '357' WHERE `iso_code_2` = 'CY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '420' WHERE `iso_code_2` = 'CZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '243' WHERE `iso_code_2` = 'CD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '45' WHERE `iso_code_2` = 'DK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '253' WHERE `iso_code_2` = 'DJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1767' WHERE `iso_code_2` = 'DM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1809' WHERE `iso_code_2` = 'DO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '593' WHERE `iso_code_2` = 'EC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '20' WHERE `iso_code_2` = 'EG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '503' WHERE `iso_code_2` = 'SV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '240' WHERE `iso_code_2` = 'GQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '291' WHERE `iso_code_2` = 'ER' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '372' WHERE `iso_code_2` = 'EE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '251' WHERE `iso_code_2` = 'ET' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '500' WHERE `iso_code_2` = 'FK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '298' WHERE `iso_code_2` = 'FO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '679' WHERE `iso_code_2` = 'FJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '358' WHERE `iso_code_2` = 'FI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '33' WHERE `iso_code_2` = 'FR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '689' WHERE `iso_code_2` = 'PF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '241' WHERE `iso_code_2` = 'GA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '220' WHERE `iso_code_2` = 'GM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '995' WHERE `iso_code_2` = 'GE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '49' WHERE `iso_code_2` = 'DE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '233' WHERE `iso_code_2` = 'GH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '350' WHERE `iso_code_2` = 'GI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '30' WHERE `iso_code_2` = 'GR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '299' WHERE `iso_code_2` = 'GL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1473' WHERE `iso_code_2` = 'GD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1671' WHERE `iso_code_2` = 'GU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '502' WHERE `iso_code_2` = 'GT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '224' WHERE `iso_code_2` = 'GN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '245' WHERE `iso_code_2` = 'GW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '592' WHERE `iso_code_2` = 'GY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '509' WHERE `iso_code_2` = 'HT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '39' WHERE `iso_code_2` = 'VA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '504' WHERE `iso_code_2` = 'HN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '852' WHERE `iso_code_2` = 'HK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '36' WHERE `iso_code_2` = 'HU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '354' WHERE `iso_code_2` = 'IS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '91' WHERE `iso_code_2` = 'IN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '62' WHERE `iso_code_2` = 'ID' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '98' WHERE `iso_code_2` = 'IR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '964' WHERE `iso_code_2` = 'IQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '353' WHERE `iso_code_2` = 'IE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '44' WHERE `iso_code_2` = 'IM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '972' WHERE `iso_code_2` = 'IL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '39' WHERE `iso_code_2` = 'IT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '225' WHERE `iso_code_2` = 'CI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1876' WHERE `iso_code_2` = 'JM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '81' WHERE `iso_code_2` = 'JP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '962' WHERE `iso_code_2` = 'JO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '7' WHERE `iso_code_2` = 'KZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '254' WHERE `iso_code_2` = 'KE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '686' WHERE `iso_code_2` = 'KI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '965' WHERE `iso_code_2` = 'KW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '996' WHERE `iso_code_2` = 'KG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '856' WHERE `iso_code_2` = 'LA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '371' WHERE `iso_code_2` = 'LV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '961' WHERE `iso_code_2` = 'LB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '266' WHERE `iso_code_2` = 'LS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '231' WHERE `iso_code_2` = 'LR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '218' WHERE `iso_code_2` = 'LY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '423' WHERE `iso_code_2` = 'LI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '370' WHERE `iso_code_2` = 'LT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '352' WHERE `iso_code_2` = 'LU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '853' WHERE `iso_code_2` = 'MO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '389' WHERE `iso_code_2` = 'MK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '261' WHERE `iso_code_2` = 'MG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '265' WHERE `iso_code_2` = 'MW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '60' WHERE `iso_code_2` = 'MY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '960' WHERE `iso_code_2` = 'MV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '223' WHERE `iso_code_2` = 'ML' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '356' WHERE `iso_code_2` = 'MT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '692' WHERE `iso_code_2` = 'MH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '222' WHERE `iso_code_2` = 'MR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '230' WHERE `iso_code_2` = 'MU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '262' WHERE `iso_code_2` = 'YT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '52' WHERE `iso_code_2` = 'MX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '691' WHERE `iso_code_2` = 'FM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '373' WHERE `iso_code_2` = 'MD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '377' WHERE `iso_code_2` = 'MC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '976' WHERE `iso_code_2` = 'MN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '382' WHERE `iso_code_2` = 'ME' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1664' WHERE `iso_code_2` = 'MS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '212' WHERE `iso_code_2` = 'MA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '258' WHERE `iso_code_2` = 'MZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '264' WHERE `iso_code_2` = 'NA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '674' WHERE `iso_code_2` = 'NR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '977' WHERE `iso_code_2` = 'NP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '31' WHERE `iso_code_2` = 'NL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '599' WHERE `iso_code_2` = 'AN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '687' WHERE `iso_code_2` = 'NC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '64' WHERE `iso_code_2` = 'NZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '505' WHERE `iso_code_2` = 'NI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '227' WHERE `iso_code_2` = 'NE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '234' WHERE `iso_code_2` = 'NG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '683' WHERE `iso_code_2` = 'NU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '672' WHERE `iso_code_2` = 'NF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '850' WHERE `iso_code_2` = 'KP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1670' WHERE `iso_code_2` = 'MP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '47' WHERE `iso_code_2` = 'NO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '968' WHERE `iso_code_2` = 'OM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '92' WHERE `iso_code_2` = 'PK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '680' WHERE `iso_code_2` = 'PW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '507' WHERE `iso_code_2` = 'PA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '675' WHERE `iso_code_2` = 'PG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '595' WHERE `iso_code_2` = 'PY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '51' WHERE `iso_code_2` = 'PE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '63' WHERE `iso_code_2` = 'PH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '870' WHERE `iso_code_2` = 'PN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '48' WHERE `iso_code_2` = 'PL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '351' WHERE `iso_code_2` = 'PT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1' WHERE `iso_code_2` = 'PR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '974' WHERE `iso_code_2` = 'QA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '242' WHERE `iso_code_2` = 'CG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '40' WHERE `iso_code_2` = 'RO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '7' WHERE `iso_code_2` = 'RU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '250' WHERE `iso_code_2` = 'RW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '590' WHERE `iso_code_2` = 'BL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '290' WHERE `iso_code_2` = 'SH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1869' WHERE `iso_code_2` = 'KN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1758' WHERE `iso_code_2` = 'LC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1599' WHERE `iso_code_2` = 'MF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '508' WHERE `iso_code_2` = 'PM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1784' WHERE `iso_code_2` = 'VC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '685' WHERE `iso_code_2` = 'WS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '378' WHERE `iso_code_2` = 'SM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '239' WHERE `iso_code_2` = 'ST' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '966' WHERE `iso_code_2` = 'SA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '221' WHERE `iso_code_2` = 'SN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '381' WHERE `iso_code_2` = 'RS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '248' WHERE `iso_code_2` = 'SC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '232' WHERE `iso_code_2` = 'SL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '65' WHERE `iso_code_2` = 'SG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '421' WHERE `iso_code_2` = 'SK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '386' WHERE `iso_code_2` = 'SI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '677' WHERE `iso_code_2` = 'SB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '252' WHERE `iso_code_2` = 'SO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '27' WHERE `iso_code_2` = 'ZA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '82' WHERE `iso_code_2` = 'KR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '34' WHERE `iso_code_2` = 'ES' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '94' WHERE `iso_code_2` = 'LK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '249' WHERE `iso_code_2` = 'SD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '597' WHERE `iso_code_2` = 'SR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '268' WHERE `iso_code_2` = 'SZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '46' WHERE `iso_code_2` = 'SE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '41' WHERE `iso_code_2` = 'CH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '963' WHERE `iso_code_2` = 'SY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '886' WHERE `iso_code_2` = 'TW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '992' WHERE `iso_code_2` = 'TJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '255' WHERE `iso_code_2` = 'TZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '66' WHERE `iso_code_2` = 'TH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '670' WHERE `iso_code_2` = 'TL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '228' WHERE `iso_code_2` = 'TG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '690' WHERE `iso_code_2` = 'TK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '676' WHERE `iso_code_2` = 'TO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1868' WHERE `iso_code_2` = 'TT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '216' WHERE `iso_code_2` = 'TN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '90' WHERE `iso_code_2` = 'TR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '993' WHERE `iso_code_2` = 'TM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1649' WHERE `iso_code_2` = 'TC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '688' WHERE `iso_code_2` = 'TV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '256' WHERE `iso_code_2` = 'UG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '380' WHERE `iso_code_2` = 'UA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '971' WHERE `iso_code_2` = 'AE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '44' WHERE `iso_code_2` = 'GB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1' WHERE `iso_code_2` = 'US' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '598' WHERE `iso_code_2` = 'UY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '1340' WHERE `iso_code_2` = 'VI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '998' WHERE `iso_code_2` = 'UZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '678' WHERE `iso_code_2` = 'VU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '58' WHERE `iso_code_2` = 'VE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '84' WHERE `iso_code_2` = 'VN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '681' WHERE `iso_code_2` = 'WF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '967' WHERE `iso_code_2` = 'YE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '260' WHERE `iso_code_2` = 'ZM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET `phone_code` = '263' WHERE `iso_code_2` = 'ZW' LIMIT 1;