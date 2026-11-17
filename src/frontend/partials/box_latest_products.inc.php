<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/partials/box_latest_products.inc.php
	*/

	if (!settings::get('box_latest_products_num_items')) {
		return;
	}

	$box_latest_products_cache_token = cache::token('box_latest_products', ['language', 'currency', 'prices']);
	if (!$products = cache::get($box_latest_products_cache_token)) {

			$products = f::catalog_products_query([
				'sort' => 'date',
				'limit' => settings::get('box_latest_products_num_items'),
			])->fetch_all();

		cache::set($box_latest_products_cache_token, $products);
	}

	if (!$products) {
		return;
	}

	$box_latest_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_latest_products.inc.php', [
		'products' => $products,
	]);

	echo $box_latest_products->render();
