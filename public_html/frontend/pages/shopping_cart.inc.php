<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/shopping_cart.inc.php
	 */

	header('X-Robots-Tag: noindex');

	if (settings::get('catalog_only_mode')) return;

	document::$title[] = language::translate('title_shopping_cart', 'Shopping Cart');

	breadcrumbs::add(language::translate('title_shopping_cart', 'Shopping Cart'), document::ilink('shopping_cart'));

	functions::draw_lightbox();

	if (empty(cart::$items)) {
		echo implode(PHP_EOL, [
			'<div id="content">',
			'  <p>'. language::translate('description_no_items_in_cart', 'There are no items in your cart.') .'</p>',
			'  <div><a class="btn btn-default" href="'. document::href_ilink('') .'">'. language::translate('title_back', 'Back') .'</a></div>',
			'</div>',
		]);
		return;
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/shopping_cart.inc.php');

	$_page->snippets = [
		'items' => [],
		'subtotal' => [
			'value' => cart::$total['value'],
			'tax' => cart::$total['tax'],
		],
		'display_prices_including_tax' => cart::$cart->data['display_prices_including_tax'],
		'error' => false,
	];

	foreach (currency::$currencies as $currency) {
		if (!empty(administrator::$data['id']) || $currency['status'] == 1) {
			$_page->snippets['currencies'][] = $currency;
		}
	}

	foreach (language::$languages as $language) {
		if (!empty(administrator::$data['id']) || $language['status'] == 1) {
			$_page->snippets['languages'][] = $language;
		}
	}

	// Cart
	foreach (cart::$items as $key => $item) {
		$_page->snippets['items'][$key] = [
			'product_id' => $item['product_id'],
			'stock_option_id' => $item['stock_option_id'],
			'name' => $item['name'],
			'sku' => $item['sku'],
			'image' => $item['image'] ? 'storage://images/' . $item['image'] : '',
			'link' => document::ilink('product', ['product_id' => $item['product_id']]),
			'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
			'price' => $item['price'],
			'final_price' => $item['final_price'],
			'tax' => $item['tax'],
			'tax_class_id' => $item['tax_class_id'],
			'quantity' => (float)$item['quantity'],
			'quantity_unit_name' => $item['quantity_unit_name'],
			'quantity_min' => $item['quantity_min'],
			'quantity_max' => $item['quantity_max'],
			'quantity_step' => $item['quantity_step'],
			'error' => fallback($item['error']),
		];
	}

	echo $_page->render();
