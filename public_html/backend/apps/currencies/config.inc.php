<?php

	return [
		'name' => t('title_currencies', 'Currencies'),
		'group' => 'regional',
		'default' => 'currencies',
		'priority' => 0,

		'theme' => [
			'color' => '#ecae06',
			'icon' => 'icon-money-coins',
		],

		'menu' => [],

		'docs' => [
			'currencies' => 'currencies.inc.php',
			'edit_currency' => 'edit_currency.inc.php',
		],
	];
