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
			'rewrite' => function(ent_link $link, $language_code) {
				$link->path = 'checkout/'; // Remove index file for site root
				return $link;
			}
		],
	];
