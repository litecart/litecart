<?php

	perform_action('delete', [
		FS_DIR_ADMIN . 'orders.app/edit_order_item.php',
	]);

	perform_action('modify', [
		FS_DIR_APP . 'includes/config.inc.php' => [
			[
				'search'  => "  define('DB_TABLE_CATEGORIES_IMAGES',                 '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'categories_images`');" . PHP_EOL,
				'replace' => "",
			],
		],
	]);
