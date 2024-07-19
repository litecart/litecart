<?php

	$manifest = [
		'name' => settings::get('store_name'),
		'start_url' => document::ilink(''),
		'display' => 'standalone',
		'background_color' => '#e6e8ec',
		'icons' => [
			[
				'src' => document::rlink('storage://images/favicons/favicon.ico'),
				'sizes' => '32x32 48x48 64x64 96x96',
			],
			[
				'src' => document::rlink('storage://images/favicons/favicon-128x128.png'),
				'sizes' => '128x128',
			],
			[
				'src' => document::rlink('storage://images/favicons/favicon-192x192.png'),
				'sizes' => '192x192',
			],
			[
				'src' => document::rlink('storage://images/favicons/favicon-256x256.png'),
				'sizes' => '256x256',
			]
		],
	];

	header('Content-Type: application/manifest+json; charset='. mb_http_output());
	echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	exit;
