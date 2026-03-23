<?php

	return [
		'f:sitemap/index.xml' => [
			'pattern' => '#^(feeds/)?site(map|index)\.xml(\.gz)?$#', // Backwards compatibility with feeds/sitemap.xml
			'controller' => 'app://frontend/pages/sitemap.inc.php',
			'params' => '',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
		],
		'f:sitemap/*.xml' => [
			'pattern' => '#^sitemap/(index|sitemap-\d+)\.xml(\.gz)?$#',
			'controller' => 'app://frontend/pages/sitemap.inc.php',
			'params' => '',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
		],
	];
