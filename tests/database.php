<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## Test database connection and query execution
		########################################################################

		echo 'Testing database connection and query execution...';

		$result = database::query('SELECT 1 AS test');
		$row = $result->fetch();

		if ($row['test'] != 1) {
			throw new Exception('Expected query result to be 1, got '. $row['test']);
		}

		########################################################################
		## Test database parameter binding and prepared statements
		########################################################################

		$result = database::prepare(
			"SELECT :foo AS foo;"
		)->bind([
			'foo' => 'bar'
		])->fetch();

		if ($result['foo'] != 'bar') {
			throw new Exception('Expected query result to be "bar", got '. $result['foo']);
		}

		########################################################################
		## Test paginated results
		########################################################################

		$rows = database::prepare(
			"SELECT n AS id, CONCAT('User ', n) AS name, CONCAT('user', n, '@example.com') AS email
			FROM (
				SELECT @row := @row + 1 AS n
				FROM information_schema.columns, (SELECT @row := 0) r
				LIMIT 50
			) t;"
		)->fetch_page(null, null, $page, 25, $num_rows, $num_pages);

		if (count($rows) != 25) {
			throw new Exception('Expected 25 rows on first page, got '. count($rows));
		}

		if ($num_rows != 50) {
			throw new Exception('Expected 50 total rows, but got '. $num_rows);
		}

		if ($num_pages != 2) {
			throw new Exception('Expected 2 pages, got '. $num_pages);
		}

		return true;

	} catch (Exception $e) {

		echo ' [FAILED]' . PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	}
