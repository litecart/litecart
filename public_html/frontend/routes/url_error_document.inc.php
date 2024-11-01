<?php

	return [
		'f:error_document' => [
			'pattern' => '#^error_document$#',
			'controller' => 'app://frontend/pages/error_document.inc.php',
			'params' => '',
			'options' => [
				'redirect' => false,
			],
		],
	];
