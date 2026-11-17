<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/partials/box_popular_products.inc.php
	*/

	if (!settings::get('box_popular_products_num_items')) {
		return;
	}

	$box_popular_products_cache_token = cache::token('box_popular_products', ['language', 'prices'], 'file');
	if (!$products = cache::get($box_popular_products_cache_token)) {

		$products = f::catalog_products_query([
			'sort' => 'popularity',
			'limit' => settings::get('box_popular_products_num_items') * 2,
		])->fetch_all();

		shuffle($products);

		$products = array_slice($products, 0, settings::get('box_popular_products_num_items'));

		cache::set($box_popular_products_cache_token, $products);
	}

	if (!$products) {
		return;
	}

	$box_popular_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_popular_products.inc.php', [
		'products' => $products,
	]);

	echo $box_popular_products->render();
