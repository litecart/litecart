<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout/cart.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$layout = 'checkout';

	if (settings::get('catalog_only_mode')) {
		return;
	}

	if (empty(cart::$items)) {

		echo implode(PHP_EOL, [
			'<div id="content">',
			'  <p>'. language::translate('description_no_items_in_cart', 'There are no items in your cart.') .'</p>',
			'  <div><a class="btn btn-default" href="'. document::href_ilink('') .'">'. language::translate('title_back', 'Back') .'</a>',
			'</div>',
		]);

		return;
	}

	$box_checkout_cart = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_checkout_cart.inc.php');

	$box_checkout_cart->snippets = [
		'items' => [],
		'subtotal' => cart::$total['value'],
		'subtotal_tax' => cart::$total['tax'],
	];

	foreach (cart::$items as $key => $item) {
		$box_checkout_cart->snippets['items'][$key] = [
			'product_id' => $item['product_id'],
			'link' => document::ilink('product', ['product_id' => $item['product_id']]),
			'image' => $item['image'] ? 'storage://images/' . $item['image'] : '',
			'name' => $item['name'],
			'sku' => $item['sku'],
			'gtin' => $item['gtin'],
			'taric' => $item['taric'],
			'options' => [],
			'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
			'price' => $item['price'],
			'tax' => $item['tax'],
			'tax_class_id' => $item['tax_class_id'],
			'quantity' => $item['quantity'],
			'quantity_min' => $item['quantity_min'],
			'quantity_max' => $item['quantity_max'],
			'quantity_step' => $item['quantity_step'],
			'quantity_unit' => $item['quantity_unit'],
			'weight' => $item['weight'],
			'weight_unit' => $item['weight_unit'],
			'length' => $item['length'],
			'width' => $item['width'],
			'height' => $item['height'],
			'length_unit' => $item['length_unit'],
			'error' => $item['error'],
		];

		if (!empty($item['options'])) {
			$item['options'] = array_map(function($key, $value) {
				return $key .': '. $value;
			}, array_keys($item['options']), $item['options']);
		}
	}

	echo $box_checkout_cart;

	// Don't process layout if this is an ajax request
	if (is_ajax_request()) {
		exit;
	}
