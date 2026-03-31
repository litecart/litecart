<?php

return [
	'name' => t('title_data_studio', 'Data Studio'),
	'default' => 'database',
	'group' => 'system',
	'priority' => 0,

	'theme' => [
		'color' => '#89abdd',
		'icon' => 'icon-server',
	],

	'menu' => [
		[
			'title' => t('title_database', 'Database'),
			'doc' => 'database',
			'params' => [],
		],
		[
			'title' => t('title_import_data', 'Import Data'),
			'doc' => 'import',
			'params' => [],
		],
		[
			'title' => t('title_export_data', 'Export Data'),
			'doc' => 'export',
			'params' => [],
		],
	],

	'docs' => [
		'database' => 'database.inc.php',
		'table' => 'table.inc.php',
		'edit_row' => 'edit_row.inc.php',
		'edit_table' => 'edit_table.inc.php',
		'pretty_print' => 'pretty_print.inc.php',
		'import' => 'import.inc.php',
		'confirm' => 'confirm.inc.php',
		'export' => 'export.inc.php',
	],
];
