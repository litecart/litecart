<?php

	try {

		if (!isset($_GET['country_code'])) {
			throw new Exception('Missing country_code', 400);
		}

		if (!preg_match('#^[a-zA-Z]{2}$#', $_GET['country_code'])) {
			throw new Exception('Invalid country_code', 400);
		}

		$result = database::query(
			"select code, name
			from ". DB_TABLE_PREFIX ."zones
			where country_code = '". database::input($_GET['country_code']) ."'
			order by name asc;"
		)->fetch_all();

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-type: application/json; charset='. mb_http_output());
	echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
