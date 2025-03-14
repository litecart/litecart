<?php

	// Delete some files
	perform_action('delete', [
		FS_DIR_APP . 'includes/boxes/box_region.inc.php',
		FS_DIR_APP . 'includes/templates/default.catalog/views/box_region.inc.php',
	]);

	database::query(
		"select * from ". DB_TABLE_PREFIX ."newsletter_recipients
		where client_ip != '' and hostname = ''
		order by date_created desc
		limit 1000;"
	)->each(function($recipient) {
		database::query(
			"update ". DB_TABLE_PREFIX ."newsletter_recipients
			set hostname = '". database::input(gethostbyaddr($recipient['client_ip'])) ."'
			where id = ". (int)$recipient['id'] ."
			limit 1;"
		);
	});
