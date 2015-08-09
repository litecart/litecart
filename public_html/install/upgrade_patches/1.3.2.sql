ALTER TABLE `lc_order_statuses` CHANGE `icon` `icon` VARCHAR(24) NOT NULL, CHANGE `color` `color` VARCHAR(7) NOT NULL;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-chain-broken', color = '#c0c0c0' WHERE id = 1 AND icon = 0;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-clock-o', color = '#d7d96f' WHERE id = 2 AND icon = 0;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-cog', color = '#ffa851' WHERE id = 3 AND icon = 0;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-truck', color = '#99cc66' WHERE id = 4 AND icon = 0;
-- --------------------------------------------------------
UPDATE `lc_order_statuses` SET icon = 'fa-times', color = '#ff6666' WHERE id = 5 AND icon = 0;
-- --------------------------------------------------------
ALTER TABLE `lc_countries` ADD `language_code` VARCHAR(2) NOT NULL AFTER postcode_required;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ca' WHERE iso_code_2 = 'AD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'AE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fa' WHERE iso_code_2 = 'AF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'AG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'AI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sq' WHERE iso_code_2 = 'AL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'hy' WHERE iso_code_2 = 'AM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pt' WHERE iso_code_2 = 'AO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'AR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'AS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'de' WHERE iso_code_2 = 'AT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'AU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'nl' WHERE iso_code_2 = 'AW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sv' WHERE iso_code_2 = 'AX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'az' WHERE iso_code_2 = 'AZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'bs' WHERE iso_code_2 = 'BA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'BB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'bn' WHERE iso_code_2 = 'BD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'nl' WHERE iso_code_2 = 'BE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'BF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'bg' WHERE iso_code_2 = 'BG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'BH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'BI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'BJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'BL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'BM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ms' WHERE iso_code_2 = 'BN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'BO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'nl' WHERE iso_code_2 = 'BQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pt' WHERE iso_code_2 = 'BR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'BS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'dz' WHERE iso_code_2 = 'BT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'BW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'be' WHERE iso_code_2 = 'BY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'BZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'CA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ms' WHERE iso_code_2 = 'CC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'CD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'CF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'CG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'de' WHERE iso_code_2 = 'CH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'CI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'CK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'CL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'CM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'zh' WHERE iso_code_2 = 'CN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'CO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'CR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'CU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pt' WHERE iso_code_2 = 'CV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'nl' WHERE iso_code_2 = 'CW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'CX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'el' WHERE iso_code_2 = 'CY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'cs' WHERE iso_code_2 = 'CZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'de' WHERE iso_code_2 = 'DE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'DJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'da' WHERE iso_code_2 = 'DK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'DM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'DO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'DZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'EC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'et' WHERE iso_code_2 = 'EE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'EG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'EH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'aa' WHERE iso_code_2 = 'ER' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'ES' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'am' WHERE iso_code_2 = 'ET' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fi' WHERE iso_code_2 = 'FI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'FJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'FK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'FM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fo' WHERE iso_code_2 = 'FO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'FR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'GA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ka' WHERE iso_code_2 = 'GE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'GF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'kl' WHERE iso_code_2 = 'GL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'GN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'GP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'GQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'el' WHERE iso_code_2 = 'GR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'GT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pt' WHERE iso_code_2 = 'GW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'GY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'zh' WHERE iso_code_2 = 'HK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'HN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'hr' WHERE iso_code_2 = 'HR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ht' WHERE iso_code_2 = 'HT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'hu' WHERE iso_code_2 = 'HU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'id' WHERE iso_code_2 = 'ID' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'IE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'he' WHERE iso_code_2 = 'IL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'IM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'IN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'IO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'IQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fa' WHERE iso_code_2 = 'IR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'is' WHERE iso_code_2 = 'IS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'it' WHERE iso_code_2 = 'IT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'JE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'JM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'JO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ja' WHERE iso_code_2 = 'JP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'KE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ky' WHERE iso_code_2 = 'KG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'km' WHERE iso_code_2 = 'KH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'KI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'KM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'KN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ko' WHERE iso_code_2 = 'KP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ko' WHERE iso_code_2 = 'KR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sq' WHERE iso_code_2 = 'XK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'KW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'KY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'kk' WHERE iso_code_2 = 'KZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'lo' WHERE iso_code_2 = 'LA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'LB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'LC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'de' WHERE iso_code_2 = 'LI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'si' WHERE iso_code_2 = 'LK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'LR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'LS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'lt' WHERE iso_code_2 = 'LT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'lb' WHERE iso_code_2 = 'LU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'lv' WHERE iso_code_2 = 'LV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'LY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'MA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'MC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ro' WHERE iso_code_2 = 'MD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sr' WHERE iso_code_2 = 'ME' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'MF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'MG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'mh' WHERE iso_code_2 = 'MH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'mk' WHERE iso_code_2 = 'MK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'ML' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'my' WHERE iso_code_2 = 'MM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'mn' WHERE iso_code_2 = 'MN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'zh' WHERE iso_code_2 = 'MO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'tl' WHERE iso_code_2 = 'MP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'MQ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'MR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'MS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'mt' WHERE iso_code_2 = 'MT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'MU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'dv' WHERE iso_code_2 = 'MV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ny' WHERE iso_code_2 = 'MW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'MX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ms' WHERE iso_code_2 = 'MY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pt' WHERE iso_code_2 = 'MZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'NA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'NC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'NE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'NF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'NG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'NI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'nl' WHERE iso_code_2 = 'NL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'no' WHERE iso_code_2 = 'NO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ne' WHERE iso_code_2 = 'NP' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'na' WHERE iso_code_2 = 'NR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'NU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'NZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'OM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'PA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'PE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'PF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'PG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'tl' WHERE iso_code_2 = 'PH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ur' WHERE iso_code_2 = 'PK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pl' WHERE iso_code_2 = 'PL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'PM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'PN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'PR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'PS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pt' WHERE iso_code_2 = 'PT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'PW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'PY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'QA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'RE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ro' WHERE iso_code_2 = 'RO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sr' WHERE iso_code_2 = 'RS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ru' WHERE iso_code_2 = 'RU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'rw' WHERE iso_code_2 = 'RW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'SA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'SB' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'SC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'SD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'SS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sv' WHERE iso_code_2 = 'SE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'SG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'SH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sl' WHERE iso_code_2 = 'SI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'no' WHERE iso_code_2 = 'SJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sk' WHERE iso_code_2 = 'SK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'SL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'it' WHERE iso_code_2 = 'SM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'SN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'so' WHERE iso_code_2 = 'SO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'nl' WHERE iso_code_2 = 'SR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pt' WHERE iso_code_2 = 'ST' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'SV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'nl' WHERE iso_code_2 = 'SX' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'SY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'SZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'TC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'TD' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'TF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'TG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'th' WHERE iso_code_2 = 'TH' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'tg' WHERE iso_code_2 = 'TJ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'TK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'pt' WHERE iso_code_2 = 'TL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'tk' WHERE iso_code_2 = 'TM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'TN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'to' WHERE iso_code_2 = 'TO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'tr' WHERE iso_code_2 = 'TR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'TT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'TV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'zh' WHERE iso_code_2 = 'TW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sw' WHERE iso_code_2 = 'TZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'uk' WHERE iso_code_2 = 'UA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'UG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'UM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'US' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'UY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'uz' WHERE iso_code_2 = 'UZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'la' WHERE iso_code_2 = 'VA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'VC' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'es' WHERE iso_code_2 = 'VE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'VG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'VI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'vi' WHERE iso_code_2 = 'VN' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'bi' WHERE iso_code_2 = 'VU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'WF' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'sm' WHERE iso_code_2 = 'WS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'ar' WHERE iso_code_2 = 'YE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'fr' WHERE iso_code_2 = 'YT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'zu' WHERE iso_code_2 = 'ZA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'ZM' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'en' WHERE iso_code_2 = 'ZW' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'cu' WHERE iso_code_2 = 'CS' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET language_code = 'nl' WHERE iso_code_2 = 'AN' LIMIT 1;
-- --------------------------------------------------------
ALTER TABLE `lc_countries` ADD COLUMN `tax_id_format` VARCHAR(64) NOT NULL AFTER `iso_code_3`;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(AT)?(U[A-Z\d]{8})$' WHERE iso_code_2 = 'AT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(BE)?(0)?(\d{9})$' WHERE iso_code_2 = 'BE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(BG)?(\d{9,10})$' WHERE iso_code_2 = 'BG' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(CY)?(\d{8}[A-Z]{1})$' WHERE iso_code_2 = 'CY' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(CZ)?(\d{8,10})$' WHERE iso_code_2 = 'CZ' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(DE)?(\d{9})$' WHERE iso_code_2 = 'DE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(DK)?(\d{8})$' WHERE iso_code_2 = 'DK' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(EE)?(\d{9})$' WHERE iso_code_2 = 'EE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(EL)?(\d{9})$' WHERE iso_code_2 = 'EL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(ES)?([A-Z]\d{7}[A-Z]|\d{8}[A-Z]|[A-Z]\d{8})$' WHERE iso_code_2 = 'ES' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(FI)?(\d{8})$' WHERE iso_code_2 = 'FI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(FR)?(([A-Z]{2}|\d{2})\d{9})$' WHERE iso_code_2 = 'FR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(GB)?(\d{9}|\d{12}|(GD|HA)\d{3})$' WHERE iso_code_2 = 'HA' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(HU)?(\d{8})$' WHERE iso_code_2 = 'HU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(HR)?(\d{11})$' WHERE iso_code_2 = 'HR' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(IE)?(\d{7}[A-Z]{2})$' WHERE iso_code_2 = 'IE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(IT)?(\d{11})$' WHERE iso_code_2 = 'IT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(LT)?((\d{9}|\d{12}))$' WHERE iso_code_2 = 'LT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(LU)?(\d{8})$' WHERE iso_code_2 = 'LU' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(LV)?(\d{11})$' WHERE iso_code_2 = 'LV' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(MT)?(\d{8})$' WHERE iso_code_2 = 'MT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(NL)?(\d{9}B\d{2})$' WHERE iso_code_2 = 'NL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(PL)?(\d{10})$' WHERE iso_code_2 = 'PL' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(PT)?(\d{9})$' WHERE iso_code_2 = 'PT' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(RO)?(\d{2,10})$' WHERE iso_code_2 = 'RO' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(SE)?(16|19|20)?([0-9]{6})(?:-)?([0-9]{4})?(01)?$' WHERE iso_code_2 = 'SE' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(SI)?(\d{8})$' WHERE iso_code_2 = 'SI' LIMIT 1;
-- --------------------------------------------------------
UPDATE `lc_countries` SET tax_id_format = '^(SK)?(\d{10})$' WHERE iso_code_2 = 'SK' LIMIT 1;
-- --------------------------------------------------------
ALTER TABLE `lc_countries` ADD COLUMN `postcode_format` VARCHAR(512) NOT NULL AFTER `address_format`;
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'BY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'CN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'IN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'KZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'KG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'MN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'RO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'RU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'SG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'TJ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'TM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{6}' WHERE iso_code_2 = 'UZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}[\\-]?\\d{3}' WHERE iso_code_2 = 'BR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}([ \\-]\\d{4})?' WHERE iso_code_2 = 'US';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'DZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'BA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'KH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'HR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'DO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'EG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'EE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'FI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'DE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'GT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'ID';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'IQ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'IL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'IT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'JO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'KE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'KW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'LA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'LT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'MY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'MV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'MX';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'MA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'NP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'PK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'SA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'SN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'SO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'ES';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'LK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'TH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'TR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'UA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'UY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'YU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{5}' WHERE iso_code_2 = 'ZM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}([\\-]\\d{3})?' WHERE iso_code_2 = 'PT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'AU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'AT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'AZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'BD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'BE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'BG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'CV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'CK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'CY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'DK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'ET';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'GE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'GW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'HT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'HM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'HU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'LV';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'LR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'LU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'MK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'MD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'NE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'NO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'PY';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'PH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'SI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'ZA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'SJ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'CH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'TN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'VE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4,5}|\\d{3}-\\d{4}' WHERE iso_code_2 = 'CR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}[\\-]\\d{3}' WHERE iso_code_2 = 'KR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}[ ]?\\d{2}' WHERE iso_code_2 = 'CZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}[ ]?\\d{2}' WHERE iso_code_2 = 'GR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}[ ]?\\d{2}' WHERE iso_code_2 = 'SK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}[ ]?\\d{2}' WHERE iso_code_2 = 'SE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}-\\d{4}' WHERE iso_code_2 = 'JP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}(\\d{2})?' WHERE iso_code_2 = 'TW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}' WHERE iso_code_2 = 'FO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}' WHERE iso_code_2 = 'GN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}' WHERE iso_code_2 = 'IS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}' WHERE iso_code_2 = 'LS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}' WHERE iso_code_2 = 'MG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{3}' WHERE iso_code_2 = 'PG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{2}[ ]?\\d{3}' WHERE iso_code_2 = 'FR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{2}-\\d{3}' WHERE iso_code_2 = 'PL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '[HLMS]\\d{3}' WHERE iso_code_2 = 'SZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '[ABCEGHJKLMNPRSTVXY]\\d[ABCEGHJ-NPRSTV-Z][ ]?\\d[ABCEGHJ-NPRSTV-Z]\\d' WHERE iso_code_2 = 'CA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '[A-Z]{2}[ ]?\\d{4}' WHERE iso_code_2 = 'BN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '[A-Z]{2}[ ]?[A-Z0-9]{2}' WHERE iso_code_2 = 'BM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '[0-9]{4}' WHERE iso_code_2 = 'NL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = 'TKCA 1ZZ' WHERE iso_code_2 = 'TC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = 'SIQQ 1ZZ' WHERE iso_code_2 = 'GS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = 'PCRN 1ZZ' WHERE iso_code_2 = 'PN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = 'GIR[ ]?0AA|((AB|AL|B|BA|BB|BD|BH|BL|BN|BR|BS|BT|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(\\d[\\dA-Z]?[ ]?\\d[ABD-HJLN-UW-Z]{2}))|BFPO[ ]?\\d{1,4}' WHERE iso_code_2 = 'GB';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = 'FIQQ 1ZZ' WHERE iso_code_2 = 'FK';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '\\d{4}' WHERE iso_code_2 = 'NZ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = 'BBND 1ZZ' WHERE iso_code_2 = 'IO';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = 'AD\\d{3}' WHERE iso_code_2 = 'AD';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '9[78][01]\\d{2}' WHERE iso_code_2 = 'GP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '9[78]5\\d{2}' WHERE iso_code_2 = 'PM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '9[78]4\\d{2}' WHERE iso_code_2 = 'RE';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '9[78]3\\d{2}' WHERE iso_code_2 = 'GF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '9[78]2\\d{2}' WHERE iso_code_2 = 'MQ';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '988\\d{2}' WHERE iso_code_2 = 'NC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '987\\d{2}' WHERE iso_code_2 = 'PF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '986\\d{2}' WHERE iso_code_2 = 'WF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '980\\d{2}' WHERE iso_code_2 = 'MC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '976\\d{2}' WHERE iso_code_2 = 'YT';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '969[67]\\d([ \\-]\\d{4})?' WHERE iso_code_2 = 'MH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '969[123]\\d([ \\-]\\d{4})?' WHERE iso_code_2 = 'GU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '9695[012]([ \\-]\\d{4})?' WHERE iso_code_2 = 'MP';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '96940' WHERE iso_code_2 = 'PW';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '96799' WHERE iso_code_2 = 'AS';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '6799' WHERE iso_code_2 = 'CC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '6798' WHERE iso_code_2 = 'CX';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '4789\\d' WHERE iso_code_2 = 'SM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '39\\d{2}' WHERE iso_code_2 = 'GL';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '2899' WHERE iso_code_2 = 'NF';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '00[679]\\d{2}([ \\-]\\d{4})?' WHERE iso_code_2 = 'PR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '008(([0-4]\\d)|(5[01]))([ \\-]\\d{4})?' WHERE iso_code_2 = 'VI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '00120' WHERE iso_code_2 = 'VA';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(\\d{6})?' WHERE iso_code_2 = 'NG';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(\\d{4}([ ]?\\d{4})?)?' WHERE iso_code_2 = 'LB';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(\\d{3}[A-Z]{2}\\d{3})?' WHERE iso_code_2 = 'MU';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '([A-Z]\\d{4}[A-Z]|(?:[A-Z]{2})?\\d{6})?' WHERE iso_code_2 = 'EC';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '([A-HJ-NP-Z])?\\d{4}([A-Z]{3})?' WHERE iso_code_2 = 'AR';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(PC )?\\d{3}' WHERE iso_code_2 = 'OM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(BB\\d{5})?' WHERE iso_code_2 = 'BB';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(ASCN|STHL) 1ZZ' WHERE iso_code_2 = 'SH';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(?:\\d{5})?' WHERE iso_code_2 = 'HN';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(9694[1-4])([ \\-]\\d{4})?' WHERE iso_code_2 = 'FM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(948[5-9])|(949[0-7])' WHERE iso_code_2 = 'LI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '(37)?\\d{4}' WHERE iso_code_2 = 'AM';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '((\\d{4}-)?\\d{3}-\\d{3}(-\\d{1})?)?' WHERE iso_code_2 = 'NI';
-- --------------------------------------------------------
UPDATE `lc_countries` SET postcode_format = '((1[0-2]|[2-9])\\d{2})?' WHERE iso_code_2 = 'BH';
-- --------------------------------------------------------
ALTER TABLE `lc_countries` CHANGE COLUMN `address_format` `address_format` VARCHAR(128) NOT NULL AFTER `tax_id_format`;
-- --------------------------------------------------------
ALTER TABLE `lc_products` ADD COLUMN `gtin` VARCHAR(32) NOT NULL AFTER `upc`;
-- --------------------------------------------------------
UPDATE `lc_products` SET `gtin` = `upc`;
-- --------------------------------------------------------
ALTER TABLE `lc_products` CHANGE COLUMN `upc` `upc` VARCHAR(32) NOT NULL COMMENT 'Deprecated';