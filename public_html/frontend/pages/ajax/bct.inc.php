<?php

	// Banner Click Tracking

	try {

		if (empty($_POST['banner_id'])) {
			throw new Exception('Missing banner_id');
		}

		database::query(
			"update ". DB_TABLE_PREFIX ."banners
			set total_clicks = total_clicks + 1
			where status
			and id = ". (int)$_POST['banner_id'] ."
			limit 1;"
		);

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		die('Error: '. $e->getMessage());
	}
