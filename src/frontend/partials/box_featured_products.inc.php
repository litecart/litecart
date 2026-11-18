<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/partials/box_featured_products.inc.php
	*/

	if (!settings::get('box_featured_products_num_items')) {
		return;
	}

	$box_featured_products_cache_token = cache::token('box_featured_products', ['language', 'prices'], 'file');
	if (!$featured_products = cache::get($box_featured_products_cache_token)) {

		$featured_products = f::catalog_products_query([
			'featured' => true,
			'sort' => 'random',
			'limit' => settings::get('box_featured_products_num_items'),
		])->fetch_all();

		cache::set($box_featured_products_cache_token, $featured_products);
	}

	if (!$featured_products) {
		return;
	}

	$box_featured_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_featured_products.inc.php', [
		'products' => $featured_products,
	]);

	echo $box_featured_products->render();
