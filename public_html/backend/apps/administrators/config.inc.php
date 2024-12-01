<?php

	return [
		'name' => language::translate('title_administrators', 'Administrators'),
		'default' => 'administrators',
		'priority' => 0,

		'theme' => [
			'color' => '#fd9114',
			'icon' => 'icon-star',
		],

		'menu' => [],

		'docs' => [
			'administrators' => 'administrators.inc.php',
			'edit_administrator' => 'edit_administrator.inc.php',
		],
	];
