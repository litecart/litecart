<?php

	if (empty($_REQUEST['page']) || !is_numeric($_REQUEST['page'])) {
		$_REQUEST['page'] = 1;
	}

	if (empty($_GET['language_code'])) {
		$_GET['language_code'] = language::$selected['code'];
	}

	if (empty($_GET['currency_code'])){
		$_GET['currency_code'] = currency::$selected['code'];
	}

	if (empty($_GET['currency_value'])) {
		$_GET['currency_value'] = currency::$currencies[$_GET['currency_code']]['value'];
	}

	if (!empty($_REQUEST['query'])) {
		$sql_find = [
			"p.id = '". database::input($_REQUEST['query']) ."'",
			"p.code like '". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
			"find_in_set(p.keywords, '". database::input($_REQUEST['query']) ."')",
			"p.sku like '". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
			"p.mpn like '". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
			"p.gtin like '". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
			"pi.name like '%". addcslashes(database::input($_REQUEST['query']), '%_') ."%'",
		];
	}

	$products = database::query(
		"select p.id, p.code, pi.name, pp.price, pso.total_quantity as quantity, oi.total_reserved as reserved, p.date_created

		from ". DB_TABLE_PREFIX ."products p

		left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input($_GET['language_code']) ."')

		left join (
			select product_id, if(JSON_VALUE(price, '$.". database::input($_GET['currency_code']) ."') != 0, JSON_VALUE(price, '$.". database::input($_GET['currency_code']) ."') * ". (float)$_GET['currency_value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
			from ". DB_TABLE_PREFIX ."products_prices
		) pp on (pp.product_id = p.id)

		left join (
			select product_id, sum(si.quantity) as total_quantity, count(*) as num_stock_options
			from ". DB_TABLE_PREFIX ."products_stock_options pso
			left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
			group by product_id
		) pso on (pso.product_id = p.id)

		left join (
			select oi.product_id, sum(oi.quantity) as total_reserved from ". DB_TABLE_PREFIX ."orders_items oi
			left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
			where o.order_status_id in (
				select id from ". DB_TABLE_PREFIX ."order_statuses
				where stock_action = 'reserve'
			)
			group by oi.product_id
		) oi on (oi.product_id = p.id)

		". (!empty($sql_find) ? "where (". implode(" or ", $sql_find) .")" : "") ."
		order by pi.name
		limit 15;"
	)->fetch_all(function($product) {
		return [
			'id' => $product['id'],
			'name' => $product['name'],
			'code' => $product['code'],
			'sku' => $product['sku'],
			'gtin' => $product['gtin'],
			'price' => [
				'formatted' => currency::format($product['price'], true, $_GET['currency_code'], $_GET['currency_value']),
				'value' => (float)$product['price'],
			],
			'thumbnail_url' => document::rlink(functions::image_thumbnail('storage://images/'. $product['image'], 64, 64)),
			'quantity' => (float)$product['quantity'],
			'reserved' => (float)$product['reserved'],
			'num_stock_options' => (float)$product['num_stock_options'],
			'date_created' => language::strftime('date', $product['date_created']),
		];
	});

	ob_clean();
	header('Content-Type: application/json');
	echo json_encode($products, JSON_UNESCAPED_SLASHES);
	exit;
