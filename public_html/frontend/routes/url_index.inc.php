<?php

	return [
		'f:' => [
			'pattern' => '#^(index)?$#',
			'controller' => 'app://frontend/pages/index.inc.php',
			'params' => '',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(type_url $link, string $language_code): ?type_url {
				$link->path = ''; // Remove index file for site root
				return $link;
			}
		],
	];
