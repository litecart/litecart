<?php

	return [
		'f:' => [
			'pattern' => '#^(index)?$#',
			'controller' => 'app://frontend/pages/index.inc.php',
			'params' => '',
			'options' => [
				'redirect' => true,
			],
			'rewrite' => function(ent_link $link, $language_code) {
				$link->path = ''; // Remove index file for site root
				return $link;
			}
		],
	];
