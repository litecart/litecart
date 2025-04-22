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

	if (!cart::$items) {

		echo implode(PHP_EOL, [
			'<main id="content" class="container">',
			'  <p>'. language::translate('description_no_items_in_cart', 'There are no items in your cart.') .'</p>',
			'  <div><a class="btn btn-default" href="'. document::href_ilink('') .'">'. language::translate('title_back', 'Back') .'</a></div>',
			'</main>',
		]);

		return;
	}

	if (!$_POST) {
		$_POST = [
			'email' => customer::$data['email'],
			'country_code' => customer::$data['country_code'],
			'postcode' => customer::$data['postcode'],
		];
	}

	if (isset($_POST['checkout'])) {

		try {

			// Do we have an existing order in the session?
			if (!empty(session::$data['checkout']['order']->data['id'])) {
				$resume_id = session::$data['checkout']['order']->data['id'];
			}

			$order = new ent_order();

			// Resume incomplete order in session
			if (!empty($resume_id)) {
				if (database::query(
					"select * from ". DB_TABLE_PREFIX ."orders
					where id = ". (int)$resume_id ."
					and order_status_id is null
					and date_created > '". date('Y-m-d H:i:s', strtotime('-15 minutes')) ."'
					limit 1;"
				)->num_rows) {
					session::$data['checkout']['order'] = new ent_order($resume_id);
					session::$data['checkout']['order']->reset();
					session::$data['checkout']['order']->data['id'] = $resume_id;
				}
			}

			// Build Order
			$order->data['weight_unit'] = settings::get('store_weight_unit');
			$order->data['currency_code'] = currency::$selected['code'];
			$order->data['currency_value'] = currency::$currencies[currency::$selected['code']]['value'];
			$order->data['language_code'] = language::$selected['code'];
			$order->data['customer'] = customer::$data;
			$order->data['display_prices_including_tax'] = !empty(customer::$data['display_prices_including_tax']) ? true : false;

			foreach ([
				'email',
				'country_code',
				'postcode',
			] as $field) {
				if (isset($_POST['customer'][$field])) {
					$order->data['customer'][$field] = $_POST['customer'][$field];
				}
			}

			foreach (cart::$items as $item) {
				$order->add_item($item);
			}

			session::$data['checkout']['order'] = $order;
			$order = &session::$data['checkout']['order'];

 	  	// Collect scraps
			 if (empty(customer::$data['id'])) {
				customer::$data = array_replace(customer::$data, array_intersect_key(array_filter(array_diff_key($_POST, array_flip(['id']))), customer::$data));
			}


			if ($_POST['checkout'] == 'standard') {

				header('Location: '. document::ilink('checkout/index'));
				exit;

			} else if (in_array($_POST['checkout'], array_column($checkouts, 'id'))) {

				$mod_checkout = new mod_checkout();
				$checkouts = $mod_checkout->options();

				if (!in_array($_POST['checkout'], array_column($checkouts, 'module_id'))) {
					throw new Exception(language::translate('error_unknown_checkout_method', 'Unknown checkout method'));
				}

				session::$data['checkout']['type'] = $_POST['checkout'];

				$mod_checkout->select($_POST['checkout']);
				$mod_checkout->process($order);

				header('Location: '. document::ilink('checkout/verify_checkout'));
				exit;

			} else {
				throw new Exception(language::translate('error_unknown_checkout_method', 'Unknown checkout method'));
			}

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Output

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/shopping_cart.inc.php');

	$_page->snippets = [
		'items' => [],
		'subtotal' => [
			'value' => cart::$total['value'],
			'tax' => cart::$total['tax'],
		],
		'display_prices_including_tax' => customer::$data['display_prices_including_tax'],
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
			'image' => [
				'original' => 'storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg'),
				'thumbnail' => functions::image_thumbnail('storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg'), 64, 0, 'product'),
				'thumbnail_2x' => functions::image_thumbnail('storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg'), 128, 0, 'product'),
			],
			'link' => document::ilink('product', ['product_id' => $item['product_id']]),
			'display_price' => customer::$data['display_prices_including_tax'] ? $item['price'] + $item['tax'] : $item['price'],
			'price' => $item['price'],
			'final_price' => fallback($item['price'], 0),
			'tax' => fallback($item['tax'], 0),
			'discount' => fallback($item['discount'], 0),
			'discount_tax' => fallback($item['discount_tax'], 0),
			'tax_class_id' => fallback($item['tax_class_id']),
			'quantity' => (float)$item['quantity'],
			'quantity_unit_name' => fallback($item['quantity_unit_name'], 0),
			'quantity_min' => fallback($item['quantity_min'], 0),
			'quantity_max' => fallback($item['quantity_max'], 0),
			'quantity_step' => fallback($item['quantity_step'], 0),
			'sum' => fallback($item['sum'], 0),
			'sum_tax' => fallback($item['sum_tax'], 0),
			'error' => fallback($item['error']),
		];
	}

	// Express checkout
	$_page->snippets['checkouts'] = (new mod_checkout)->options(cart::$items, customer::$data);

	echo $_page;
