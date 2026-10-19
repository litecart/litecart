<?php

	return [
		'name' => t('title_integrations', 'Integrations'),
		'default' => 'ai',
		'group' => 'system',
		'theme' => [
			'icon' => 'icon-ai',
			'color' => '#565a52',
		],
		'menu' => [
			'instructions' => [
				'title' => t('title_ai_access', 'AI Access'),
				'doc' => 'ai',
			],
			'api' => [
				'title' => t('title_api_access', 'API Access'),
				'doc' => 'api',
			],
			'access_keys' => [
				'title' => t('title_access_keys', 'Access Keys'),
				'doc' => 'access_keys',
			],
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
			'ai' => 'ai.inc.php',
			'access_keys' => 'access_keys.inc.php',
			'edit_access_key' => 'edit_access_key.inc.php',
			'api' => 'api.inc.php',
			'webhooks' => 'webhooks.inc.php',
			'requests' => 'requests.inc.php',
			'edit_webhook' => 'edit_webhook.inc.php',
			'edit_request' => 'edit_request.inc.php',
		],
	];