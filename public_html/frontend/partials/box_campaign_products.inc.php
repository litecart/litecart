<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_campaign_products.inc.php
	 */

	if (!settings::get('box_campaign_products_num_items')) return;

	$box_campaign_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_campaign_products.inc.php');

	$box_campaign_products_cache_token = cache::token('box_campaign_products', ['language', 'currency']);
	if (!$box_campaign_products->snippets['products'] = cache::get($box_campaign_products_cache_token)) {

		$box_campaign_products->snippets['products'] = functions::catalog_products_query([
			'campaign' => true,
			'sort' => 'random',
			'limit' => settings::get('box_campaign_products_num_items'),
		])->fetch_all();

		cache::set($box_campaign_products_cache_token, $box_campaign_products->snippets['products']);
	}

	if (!$box_campaign_products->snippets['products']) return;

	echo $box_campaign_products->render();
