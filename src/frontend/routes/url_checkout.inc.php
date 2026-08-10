<?php

	return [
		'f:checkout' => [
			'pattern' => '#^checkout(/(index)?)?$#',
			'controller' => 'app://frontend/pages/checkout/index.inc.php',
			'params' => '',
			'endpoint' => 'frontend',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(type_url $link, string $language_code): ?type_url {
				$link->path = 'checkout/'; // Remove index file for site root
				return $link;
			}
		],
	];
