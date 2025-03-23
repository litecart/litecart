<?php

	if (empty($_REQUEST['page']) || !is_numeric($_REQUEST['page']) || $_REQUEST['page'] < 1) {
		$_REQUEST['page'] = 1;
	}

	if (empty($_GET['language_code'])) {
		$_GET['language_code'] = language::$selected['code'];
	}

	if (!empty($_REQUEST['query'])) {
		$sql_find = [
			"si.id = '". database::input($_REQUEST['query']) ."'",
			"si.sku like '". database::input($_REQUEST['query']) ."%'",
			"si.mpn like '". database::input($_REQUEST['query']) ."%'",
			"si.gtin like '". database::input($_REQUEST['query']) ."%'",
			"sii.name like '%". database::input($_REQUEST['query']) ."%'",
			"b.name like '%". database::input($_REQUEST['query']) ."%'",
		];
	}

	$stock_items = database::query(
		"select si.*, sii.name, b.name as brand_name from ". DB_TABLE_PREFIX ."stock_items si
		left join ". DB_TABLE_PREFIX ."stock_items_info sii on (sii.stock_item_id = si.id and sii.language_code = '". database::input($_GET['language_code']) ."')
		left join ". DB_TABLE_PREFIX ."brands b on (b.id = si.id)
		". (!empty($sql_find) ? "where (". implode(" or ", $sql_find) .")" : "") ."
		order by si.sku, b.name, sii.name
		limit 15;"
	)->fetch_all();

	foreach ($stock_items as $i => $stock_item) {
		$stock_item['date_updated'] = language::strftime('date', $stock_item['date_updated']);
		$stock_item['date_created'] = language::strftime('date', $stock_item['date_created']);
		$stock_items[$i] = $stock_item;
	}

	ob_clean();
	header('Content-Type: application/json');
	echo json_encode($stock_items, JSON_UNESCAPED_SLASHES);
	exit;
