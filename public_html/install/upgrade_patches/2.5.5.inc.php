<?php

	// Delete some files
	perform_action('delete', [
		FS_DIR_APP . 'includes/boxes/box_region.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_region.inc.php',
	]);

	$newletter_recipients_query = database::query(
		"select * from ". DB_TABLE_PREFIX ."newsletter_recipients
		where client_ip != '' and hostname = ''
		order by date_created desc
		limit 1000;"
	);

	while ($recipient = database::fetch($newletter_recipients_query)) {
		database::query(
			"update ". DB_TABLE_PREFIX ."newsletter_recipients
			set hostname = '". database::input(gethostbyaddr($recipient['client_ip'])) ."'
			where id = ". (int)$recipient['id'] ."
			limit 1;"
		);
	}

	// Copy Payson (Sweden only)
	$setting_query = database::query(
		"select * from ". DB_TABLE_PREFIX ."settings
		where `key`= 'store_country_code'
		limit 1;"
	);

	$setting = database::fetch($setting_query);

	if ($setting['value'] == 'SE') {
		perform_action('copy', [
			FS_DIR_APP . 'install/data/SE/public_html/includes/modules/payment/pm_payson.inc.php' => FS_DIR_APP . 'includes/modules/payment/pm_payson.inc.php',
			FS_DIR_APP . 'install/data/SE/public_html/ext/payson/' => FS_DIR_APP . 'ext/payson/',
		]);
	}
