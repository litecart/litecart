<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_similar_products.inc.php
	 */

	if (empty($_GET['product_id'])) return;
	if (!settings::get('box_similar_products_num_items')) return;

	$product = reference::product($_GET['product_id']);

	$box_similar_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_similar_products.inc.php');

	$box_similar_products_cache_token = cache::token('box_similar_products', [$_GET['product_id'], fallback($_GET['category_id'], implode('), ', array_keys($product->categories))), 'language', 'prices'], 'file');
	if (!$box_similar_products->snippets['products'] = cache::get($box_similar_products_cache_token)) {

		$box_similar_products->snippets['products'] = functions::catalog_products_search_query([
			'product_name' => $product->name,
			'categories' => isset($_GET['category_id']) ? [$_GET['category_id']] : array_keys($product->categories),
			'brands' => [$product->brand_id],
			'exclude_products' => [$product->id],
			'keywords' => $product->keywords,
			'limit' => settings::get('box_similar_products_num_items'),
		])->fetch_all();

		cache::set($box_similar_products_cache_token, $box_similar_products->snippets['products']);
	}

	if (!$box_similar_products->snippets['products']) return;

	functions::draw_lightbox();

	echo $box_similar_products->render();
