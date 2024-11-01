<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_shopping_cart.inc.php
	 */

	if (settings::get('catalog_only_mode')) return;

	$box_shopping_cart = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_shopping_cart.inc.php');

	$box_shopping_cart->snippets = [
		'items' => [],
		'link' => document::ilink('shopping_cart'),
		'num_items' => count(cart::$items),
		'subtotal' => cart::$items,
	];

	foreach (cart::$items as $key => $item) {
		$item['image'] = 'storage://images/' . fallback($item['image'], 'no_image.png');
		$box_shopping_cart->snippets['items'][$key] = $item;
	}

	if (!empty(customer::$data['display_prices_including_tax'])) {
		$box_shopping_cart->snippets['subtotal'] = currency::format(cart::$total['value'] + cart::$total['tax']);
	} else {
		$box_shopping_cart->snippets['subtotal'] = currency::format(cart::$total['value']);
	}

	echo $box_shopping_cart->render();
