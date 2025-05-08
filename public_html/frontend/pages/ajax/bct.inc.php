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

		customer::log([
			'type' => 'banner_click',
			'description' => 'User clicked a banner',
			'data' => [
				'banner_id' => $_POST['banner_id'],
			],
			'expires_at' => strtotime('+12 months'),
		]);

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		die('Error: '. $e->getMessage());
	}
