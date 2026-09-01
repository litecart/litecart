<?php

	return [
		'name' => t('title_email', 'Email'),
		'group' => 'website',
		'default' => 'drafts',
		'priority' => 0,

		'theme' => [
			'color' => '#0ab9cb',
			'icon' => 'icon-envelope',
		],

		'menu' => [
			[
				'title' => t('title_imap_client', 'IMAP Client'),
				'doc' => 'imap_client',
			],
			[
				'title' => t('title_drafts', 'Drafts'),
				'doc' => 'drafts',
			],
			[
				'title' => t('title_scheduled', 'Scheduled'),
				'doc' => 'scheduled',
			],
			[
				'title' => t('title_sent', 'Sent'),
				'doc' => 'sent',
			],
			[
				'title' => t('title_failed', 'Failed'),
				'doc' => 'failed',
			],
		],

		'docs' => [
			'drafts' => 'drafts.inc.php',
			'scheduled' => 'scheduled.inc.php',
			'sent' => 'sent.inc.php',
			'view' => 'view.inc.php',
			'edit_email' => 'edit_email.inc.php',
			'failed' => 'failed.inc.php',
			'imap_client' => 'imap_client.inc.php',
			'imap_view' => 'imap_view.inc.php',
		],
	];

