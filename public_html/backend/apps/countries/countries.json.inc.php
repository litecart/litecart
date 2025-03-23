<?php

	try {

		if (!empty($_REQUEST['query'])) {
			$sql_find = [
				"iso_code_2 = '". database::input($_REQUEST['query']) ."'",
				"name like '%". database::input($_REQUEST['query']) ."%'",
			];
		}

		// Rows, Total Number of Rows, Total Number of Pages
		$result = database::query(
			"select id, iso_code_2, name from ". DB_TABLE_PREFIX ."countries
			". (!empty($sql_find) ? "where (". implode(" or ", $sql_find) .")" : "") ."
			order by name
			limit 15;"
		)->fetch_all();

	} catch (Exception $e) {
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-Type: application/json; charset='.mb_http_output());
	echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
