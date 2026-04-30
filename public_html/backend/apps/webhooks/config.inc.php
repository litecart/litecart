<?php

	return [
		'name' => t('title_webhooks', 'Webhooks'),
		'default' => 'webhooks',
		'group' => 'system',
		'priority' => 0,

		'theme' => [
			'color' => '#4dcac3',
			'icon' => 'icon-link',
		],

		'menu' => [],

		'docs' => [
			'edit_webhook' => 'edit_webhook.inc.php',
			'webhooks' => 'webhooks.inc.php',
		],
	];
