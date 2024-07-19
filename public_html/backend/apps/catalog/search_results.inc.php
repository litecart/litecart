<?php

	$results = [];

	// Products

	$code_regex = functions::format_regex_code($query);
	$query_fulltext = functions::escape_mysql_fulltext($_GET['query']);

	$products = database::query(
		"select p.id, p.default_category_id, pi.name,
		(
				if(p.id = '". database::input($query) ."', 10, 0)
				+ (match(pi.name) against ('". database::input($query_fulltext) ."' in boolean mode))
				+ (match(pi.short_description) against ('". database::input($query_fulltext) ."' in boolean mode) / 2)
				+ (match(pi.description) against ('". database::input($query_fulltext) ."' in boolean mode) / 3)
				+ if(pi.name like '%". database::input($query) ."%', 3, 0)
				+ if(pi.short_description like '%". database::input($query) ."%', 2, 0)
				+ if(pi.description like '%". database::input($query) ."%', 1, 0)
				+ if(p.code regexp '". database::input($code_regex) ."', 5, 0)
				+ if (p.id in (
					select product_id from ". DB_TABLE_PREFIX ."products_options_stock
					where sku regexp '". database::input($code_regex) ."'
				), 5, 0)
		) as relevance

		from ". DB_TABLE_PREFIX ."products p

		left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

		having relevance > 0
		order by relevance desc, id asc
		limit 5;"
	)->fetch_all();

	if ($products) {

		$result = [
			'name' => language::translate('title_products', 'Products'),
			'results' => [],
		];

		foreach ($products as $product) {
			$result['results'][] = [
				'id' => $product['id'],
				'title' => $product['name'],
				'description' => $product['default_category_id'] ? reference::category($product['default_category_id'])->name : '['.language::translate('title_root', 'Root').']',
				'link' => document::ilink($app.'/edit_product', ['product_id' => $product['id']]),
			];
		}

		$results[] = $result;
	}

	// Stock Items

	$result = [
		'name' => language::translate('title_stock_items', 'Stock Items'),
		'results' => [],
	];

	$code_regex = functions::format_regex_code($query);
	$query_fulltext = functions::escape_mysql_fulltext($_GET['query']);

	$stock_items = database::query(
		"select s.id, s.sku, si.name, (
			if(s.id = '". database::input($query) ."', 10, 0)
			+ (match(si.name) against ('". database::input($query_fulltext) ."' in boolean mode))
			+ if(si.name like '%". database::input($query) ."%', 3, 0)
			+ if(s.sku regexp '". database::input($code_regex) ."', 5, 0)
			+ if(s.mpn regexp '". database::input($code_regex) ."', 5, 0)
			+ if(s.gtin regexp '". database::input($code_regex) ."', 5, 0)
			+ if (s.id in (
				select stock_item_id from ". DB_TABLE_PREFIX ."stock_items_references
				where stock_item_id in (
					select id from ". DB_TABLE_PREFIX ."stock_items_references
					where code regexp '". database::input($code_regex) ."'
				)
			), 5, 0)
		) as relevance

		from ". DB_TABLE_PREFIX ."stock_items s

		left join ". DB_TABLE_PREFIX ."stock_items_info si on (si.stock_item_id = s.id and si.language_code = '". database::input(language::$selected['code']) ."')

		having relevance > 0
		order by relevance desc, id asc
		limit 5;"
	)->fetch_all();

	if ($stock_items) {

		$result = [
			'name' => language::translate('title_stok_items', 'Stock Items'),
			'results' => [],
		];

		foreach ($stock_items as $stock_item) {
			$result['results'][] = [
				'id' => $stock_item['id'],
				'title' => $stock_item['name'],
				'description' => $stock_item['sku'],
				'link' => document::ilink($app.'/edit_stock_item', ['stock_item_id' => $stock_item['id']]),
			];
		}

		$results[] = $result;
	}

	return $results;
