<?php

	perform_action('delete', [
		FS_DIR_ADMIN . 'orders.app/printable_packing_slip.php',
		FS_DIR_ADMIN . 'orders.app/printable_order_copy.php',
		FS_DIR_ADMIN . 'sales.widget/',
		FS_DIR_STORAGE . 'data/errors.log',
		FS_DIR_STORAGE . 'data/performance.log',
		FS_DIR_STORAGE . 'images/icons/',
		FS_DIR_APP . 'ext/fancybox/',
		FS_DIR_APP . 'ext/jqplot/',
		FS_DIR_APP . 'ext/responsiveslider/',
		FS_DIR_APP . 'ext/jquery/jquery-1.12.4.min.js',
		FS_DIR_APP . 'ext/jquery/jquery-migrate-1.4.1.min.js',
		FS_DIR_APP . 'ext/jquery/jquery.animate_from_to-1.0.min.js',
		FS_DIR_APP . 'ext/jquery/jquery.cookie.min.js',
		FS_DIR_APP . 'ext/jquery/jquery.tabs.js',
		FS_DIR_APP . 'ext/select2/',
		FS_DIR_APP . 'includes/boxes/box_account.inc.php',
		FS_DIR_APP . 'includes/boxes/box_search.inc.php',
		FS_DIR_APP . 'includes/boxes/box_most_popular_products.inc.php',
		FS_DIR_APP . 'includes/modules/order_action/',
		FS_DIR_APP . 'includes/modules/order_success/',
		FS_DIR_APP . 'includes/modules/mod_order_action.inc.php',
		FS_DIR_APP . 'includes/modules/mod_order_success.inc.php',
		FS_DIR_APP . 'includes/templates/default.admin/images/fancybox/',
		FS_DIR_APP . 'includes/templates/default.admin/images/loader.png',
		FS_DIR_APP . 'includes/templates/default.admin/styles/',
		FS_DIR_APP . 'includes/templates/default.admin/views/doc.inc.php',
		FS_DIR_APP . 'includes/templates/default.admin/views/login.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/fonts/',
		FS_DIR_APP . 'includes/templates/default.catalog/cursors/',
		FS_DIR_APP . 'includes/templates/default.catalog/images/fancybox/',
		FS_DIR_APP . 'includes/templates/default.catalog/images/cart.png',
		FS_DIR_APP . 'includes/templates/default.catalog/images/cart_filled.png',
		FS_DIR_APP . 'includes/templates/default.catalog/images/loader.png',
		FS_DIR_APP . 'includes/templates/default.catalog/styles/',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_account.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_category.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_create_account.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_customer_service.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_edit_account.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_information.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_login.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_manufacturer.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_manufacturers.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_most_popular_products.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_order_history.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_order_success.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_page.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_regional_settings.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_search.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_search_results.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_slider.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/index.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/printable_order_copy.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/printable_packing_slip.inc.php',
		FS_DIR_APP . 'includes/column_left.inc.php',
	]);

	perform_action('modify', [
		FS_DIR_APP . 'includes/config.inc.php' => [
			[
				'search'  => "  define('WS_DIR_INCLUDES',    WS_DIR_HTTP_HOME . 'includes/');" . PHP_EOL,
				'replace' => "  define('WS_DIR_INCLUDES',    WS_DIR_HTTP_HOME . 'includes/');" . PHP_EOL
									 . "  define('WS_DIR_LOGS',        WS_DIR_HTTP_HOME . 'logs/');" . PHP_EOL,
			],
			[
			'search'  => "  ini_set('error_log', FS_DIR_HTTP_ROOT . WS_DIR_DATA . 'errors.log');" . PHP_EOL,
			'replace' => "  ini_set('error_log', FS_DIR_HTTP_ROOT . WS_DIR_LOGS . 'errors.log');" . PHP_EOL,
			],
			[
				'search'  => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'manufacturers_info`');",
				'replace' => "  define('DB_TABLE_MANUFACTURERS_INFO',                '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'manufacturers_info`');" . PHP_EOL
									 . "  define('DB_TABLE_MODULES',                           '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'modules`');",
			],
			[
				'search'  => "  define('DB_TABLE_SLIDES',                            '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'slides`');",
				'replace' => "  define('DB_TABLE_SLIDES',                            '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'slides`');" . PHP_EOL
									 . "  define('DB_TABLE_SLIDES_INFO',                       '`'. DB_DATABASE .'`.`'. DB_TABLE_PREFIX . 'slides_info`');",
			],
		],
	], 'abort');

	perform_action('modify', [
		FS_DIR_APP . '.htaccess' => [
			[
				'search'  => '<FilesMatch "\.(css|js)$">',
				'replace' => '<FilesMatch "\.(css|js|svg)$">',
			],
			[
				'search'  => '<FilesMatch "\.(css|gif|ico|jpg|jpeg|js|pdf|png|ttf)$">',
				'replace' => '<FilesMatch "\.(css|gif|ico|jpg|jpeg|js|pdf|png|svg|ttf)$">',
			],
		],
	]);

	// Delete Deprecated Modules
	$module_types_query = database::query(
		"select * from ". DB_TABLE_PREFIX ."settings
		where `key` in ('order_action_modules', 'order_success_modules');"
	);

	while ($module_type = database::fetch($module_types_query)) {

		foreach (explode(';', $module_type['value']) as $module) {
			database::query(
				"delete from ". DB_TABLE_PREFIX ."settings
				where `key` = '". database::input($module) ."';"
			);
		}

		database::query(
			"delete from ". DB_TABLE_PREFIX ."settings
			where `key` = '". database::input($module_type['key']) ."'
			limit 1;"
		);
	}

	// Migrate Modules
	database::query(
		"update ". DB_TABLE_PREFIX ."settings
		set `key` = 'job_modules'
		where `key` = 'jobs_modules';"
	);

	$installed_modules_query = database::query(
		"select * from ". DB_TABLE_PREFIX ."settings
		where `key` in ('job_modules', 'customer_modules', 'order_modules', 'order_total_modules', 'shipping_modules', 'payment_modules');"
	);

	while ($installed_modules = database::fetch($installed_modules_query)) {

		foreach (explode(';', $installed_modules['value']) as $module) {

			$module = database::query(
				"select * from ". DB_TABLE_PREFIX ."settings
				where `key` = '". database::input($module) ."'
				limit 1;"
			)->fetch();

			if (!$module) continue;

			$type = preg_replace('#^(.*)_modules$#', '$1', $installed_modules['key']);
			$module['settings'] = unserialize($module['value']);

			if (isset($module['settings']['status'])) {
				$status = in_array(strtolower($module['settings']['status']), ['1', 'active', 'enabled', 'on', 'true', 'yes']) ? 1 : 0;
			} else {
				$status = 1;
			}

			if (isset($module['settings']['priority'])) {
				$priority = (int)$module['settings']['priority'];
			} else if (isset($module['settings']['sort_order'])) {
				$priority = (int)$module['settings']['sort_order'];
			} else {
				$priority = 0;
			}

			mb_convert_variables('UTF-8', '', $module['settings']);

			database::query(
				"insert into `". DB_DATABASE ."`.`". DB_TABLE_PREFIX . "modules`
				(module_id, type, status, settings, priority, date_updated, date_created)
				values ('". database::input($module['key']) ."', '". database::input($type) ."', ". (int)$status .", '". database::input(json_encode($module['settings'])) ."', ". (int)$priority .", '". $module['date_updated'] ."', '". $module['date_created'] ."');"
			);

			database::query(
				"delete from ". DB_TABLE_PREFIX ."settings
				where `key` = '". database::input($module['key']) ."'
				limit 1;"
			);
		}
	}

	// Collect all languages
	$all_languages = database::query(
		"select code from ". DB_TABLE_PREFIX ."languages
		where status
		order by priority, name;"
	)->fetch_all('code');

	// Update slides
	$slides_query = database::query(
		"select * from ". DB_TABLE_PREFIX ."slides;"
	);

	while ($slide = database::fetch($slides_query)) {

		if (!empty($slide['language_code'])) {
			$languages = [$slide['language_code']];
		} else {
			$languages = $all_languages;
		}

		foreach ($languages as $language_code) {
			database::query(
				"insert into `". DB_DATABASE ."`.`". DB_TABLE_PREFIX . "slides_info`
				(slide_id, language_code, caption, link)
				values (". (int)$slide['id'] .", '". database::input($language_code) ."', '". database::input($slide['caption']) ."', '". database::input($slide['link']) ."');"
			);
		}
	}

	database::query(
		"alter table ". DB_TABLE_PREFIX ."slides
		change column `language_code` `languages` varchar(32) not null,
		drop column `caption`,
		drop column `link`;"
);
