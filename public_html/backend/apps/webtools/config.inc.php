<?php

	return [
		'name' => language::translate('title_webtools', 'Webtools'),
		'default' => 'redirects',
		'priority' => 0,

		'theme' => [
			'color' => '#5f657c',
			'icon' => 'icon-screwdriver-wrench',
		],

		'menu' => [
			[
				'title' => language::translate('title_redirects', 'Redirects'),
				'doc' => 'redirects',
				'params' => [],
			],
			[
				'title' => language::translate('title_site_tags', 'Site Tags'),
				'doc' => 'site_tags',
				'params' => [],
			],
			[
				'title' => language::translate('title_third_parties', 'Third Parties'),
				'doc' => 'third_parties',
				'params' => [],
			],
		],

		'docs' => [
			'edit_redirect' => 'edit_redirect.inc.php',
			'redirects' => 'redirects.inc.php',

			'edit_site_tag' => 'edit_site_tag.inc.php',
			'site_tags' => 'site_tags.inc.php',

			'third_parties' => 'third_parties.inc.php',
			'edit_third_party' => 'edit_third_party.inc.php',
		],
	];
