<?php

	return [
		'name' => t('title_webhooks', 'Webhooks'),
		'default' => 'webhooks',
		'group' => 'system',
		'theme' => [
			'icon' => 'icon-webhooks',
			'color' => '#565a52',
		],
		'menu' => [
			'webhooks' => [
				'title' => t('title_webhooks', 'Webhooks'),
				'doc' => 'webhooks',
			],
			'requests' => [
				'title' => t('title_webhook_requests', 'Webhook Requests'),
				'doc' => 'requests',
			],
		],
		'docs' => [
			'webhooks' => 'webhooks.inc.php',
			'requests' => 'requests.inc.php',
			'edit_webhook' => 'edit_webhook.inc.php',
			'edit_request' => 'edit_request.inc.php',
		],
	];
