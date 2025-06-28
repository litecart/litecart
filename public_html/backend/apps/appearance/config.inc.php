<?php

	return [
		'name' => t('title_appearance', 'Appearance'),
		'group' => 'website',
		'default' => 'edit_styling',
		'priority' => 0,

		'theme' => [
			'color' => '#e54d80',
			'icon' => 'icon-palette',
		],

		'menu' => [
			[
				'title' => t('title_edit_styling', 'Edit Styling'),
				'doc' => 'edit_styling',
				'params' => [],
			],
			[
				'title' => t('title_favicon', 'Favicon'),
				'doc' => 'favicon',
				'params' => [],
			],
			[
				'title' => t('title_images', 'Images'),
				'doc' => 'images',
				'params' => [],
			],
			[
				'title' => t('title_template', 'Template'),
				'doc' => 'template',
				'params' => [],
			],
		],

		'docs' => [
			'edit_styling' => 'edit_styling.inc.php',
			'favicon' => 'favicon.inc.php',
			'images' => 'images.inc.php',
			'template' => 'template.inc.php',
			'template_settings' => 'template_settings.inc.php',
		],
	];
