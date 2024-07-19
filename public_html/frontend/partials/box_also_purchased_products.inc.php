<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_also_purchased_products.inc.php
	 */

	if (empty($_GET['product_id'])) return;
	if (!settings::get('box_also_purchased_products_num_items')) return;

	functions::draw_lightbox();

	$box_also_purchased_products_cache_token = cache::token('box_also_purchased_products', [$_GET['product_id'], 'language', 'prices'], 'file');
	if (cache::capture($box_also_purchased_products_cache_token)) {

		$also_purchased_products = reference::product($_GET['product_id'])->also_purchased_products;

		if (!empty($also_purchased_products)) {

			$also_purchased_products = array_slice($also_purchased_products, 0, settings::get('box_also_purchased_products_num_items')*3, true);

			$box_also_purchased_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_also_purchased_products.inc.php');

			$box_also_purchased_products->snippets['products'] = functions::catalog_products_query([
				'products' => array_keys($also_purchased_products),
				'sort' => 'random',
				'limit' => settings::get('box_also_purchased_products_num_items'),
			])->fetch_all();

			if ($box_also_purchased_products->snippets['products']) {
				echo $box_also_purchased_products->render();
			}
		}

		cache::end_capture($box_also_purchased_products_cache_token);
	}
