<?php

	try {

		if (!isset($_GET['group_id'])) {
			throw new Exception('Missing group_id');
		}

		$attribute_group = database::query(
			"select * from ". DB_TABLE_PREFIX ."attribute_groups
			where id = ". (int)$_GET['group_id'] ."
			limit 1;"
		)->fetch();

		if (!$attribute_group) {
			throw new Exception('Invalid group_id');
		}

		$result = database::query(
			"select av.id, json_value(av.name, '$.".database::input(language::$selected['code'])."') as name
			from ". DB_TABLE_PREFIX ."attribute_values av
			where av.group_id = ". (int)$_GET['group_id'] ."
			order by ". (($attribute_group['sort'] == 'alphabetical') ? "cast(name as unsigned), name" : "av.priority") .";"
		)->fetch_all();

	} catch(Exception $e) {
		http_response_code(400);
		$result = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-type: application/json; charset='. mb_http_output());
	echo functions::format_json($result);
	exit;
