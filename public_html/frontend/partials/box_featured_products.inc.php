<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_featured_products.inc.php
	 */

	if (!settings::get('box_featured_products_num_items')) return;

	$box_featured_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_featured_products.inc.php');

	$box_featured_products_cache_token = cache::token('box_featured_products', ['language', 'prices'], 'file');
	if (!$box_featured_products->snippets['products'] = cache::get($box_featured_products_cache_token)) {

		$products = functions::catalog_products_query([
			'featured' => true,
			'sort' => 'random',
			'limit' => settings::get('box_featured_products_num_items'),
		])->fetch_all();

		$box_featured_products->snippets['products'] = [];
		foreach ($products as $listing_product) {
			$box_featured_products->snippets['products'][] = $listing_product;
		}

		cache::set($box_featured_products_cache_token, $box_featured_products->snippets['products']);
	}

	if (!$box_featured_products->snippets['products']) return;

	echo $box_featured_products->render();
