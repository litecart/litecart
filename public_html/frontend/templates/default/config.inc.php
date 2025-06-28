<?php

		// Template settings are accessed by document::$settings['key']

	return [
		[
			'key' => 'sidebar_parallax_effect',
			'default_value' => '1',
			'title' => t('template:title_sidebar_parallax_effect', 'Sidebar Parallax Effect'),
			'description' => t('template:description_sidebar_parallax_effect', 'Enables or disables the sidebar parallax effect.'),
			'function' => 'toggle("e/d")',
		],
		[
			'key' => 'scroll_up',
			'default_value' => '1',
			'title' => t('template:title_scroll_up', 'Scroll Up'),
			'description' => t('template:description_scroll_up', 'Displays a clickable icon in the bottom right corner to scroll back to top.'),
			'function' => 'toggle("e/d")',
		],
	];
