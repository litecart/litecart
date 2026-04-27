<?php

	$config = [
		'name' => t('title_localization', 'Localization'),
		'group' => 'system',
		'default' => 'countries/countries',
		'priority' => 0,

		'theme' => [
			'color' => '#21a9d2',
			'icon' => 'icon-globe',
		],

		'menu' => [
			[
				'title' => t('title_countries', 'Countries'),
				'doc' => 'countries/countries',
				'params' => [],
			],
			[
				'title' => t('title_currencies', 'Currencies'),
				'doc' => 'currencies/currencies',
				'params' => [],
			],
			[
				'title' => t('title_geo_zones', 'Geo Zones'),
				'doc' => 'geo_zones/geo_zones',
				'params' => [],
			],
			[
				'title' => t('title_languages', 'Languages'),
				'doc' => 'languages/languages',
				'params' => [],
			],
			[
				'title' => t('title_storage_encoding', 'Storage Encoding'),
				'doc' => 'languages/storage_encoding',
				'params' => [],
			],
			[
				'title' => t('title_translations', 'Translations'),
				'doc' => 'translations/translations',
				'params' => [],
			],
			[
				'title' => t('title_scan_for_translations', 'Scan For Translations'),
				'doc' => 'translations/scan',
				'params' => [],
			],
			[
				'title' => t('title_csv_import_export_translations', 'CSV Import/Export Translations'),
				'doc' => 'translations/csv',
				'params' => [],
			],
			[
				'title' => t('title_tax_rates', 'Tax Rates'),
				'doc' => 'tax/tax_rates',
				'params' => [],
			],
			[
				'title' => t('title_tax_classes', 'Tax Classes'),
				'doc' => 'tax/tax_classes',
				'params' => [],
			],
		],

		'docs' => [
			'countries/countries' => 'countries/countries.inc.php',
			'countries/countries.json' => 'countries/countries.json.inc.php',
			'countries/edit_country' => 'countries/edit_country.inc.php',
			'countries/zones.json' => 'countries/zones.json.inc.php',
			'currencies/currencies' => 'currencies/currencies.inc.php',
			'currencies/currencies.json' => 'currencies/currencies.json.inc.php',
			'currencies/edit_currency' => 'currencies/edit_currency.inc.php',
			'geo_zones/geo_zones' => 'geo_zones/geo_zones.inc.php',
			'geo_zones/edit_geo_zone' => 'geo_zones/edit_geo_zone.inc.php',
			'languages/languages' => 'languages/languages.inc.php',
			'languages/edit_language' => 'languages/edit_language.inc.php',
			'languages/storage_encoding' => 'languages/storage_encoding.inc.php',
			'tax/tax_classes' => 'tax/tax_classes.inc.php',
			'tax/edit_tax_class' => 'tax/edit_tax_class.inc.php',
			'tax/tax_rates' => 'tax/tax_rates.inc.php',
			'tax/tax_rates.json' => 'tax/tax_rates.json.inc.php',
			'tax/edit_tax_rate' => 'tax/edit_tax_rate.inc.php',
			'translations/translations' => 'translations/translations.inc.php',
			'translations/auto_translate' => 'translations/auto_translate.inc.php',
			'translations/translate.json' => 'translations/translate.json.inc.php',
			'translations/scan' => 'translations/scan.inc.php',
			'translations/csv' => 'translations/csv.inc.php',
		],
	];

	return $config;
