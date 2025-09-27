<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/shopping_cart.inc.php
	 */

	header('X-Robots-Tag: noindex');

	if (settings::get('catalog_only_mode')) return;

	document::$title[] = t('title_shopping_cart', 'Shopping Cart');

	breadcrumbs::add(t('title_shopping_cart', 'Shopping Cart'), document::ilink('shopping_cart'));

	if (!cart::$items) {

		echo implode(PHP_EOL, [
			'<main id="content" class="container">',
			'  <p>'. t('description_no_items_in_cart', 'There are no items in your cart.') .'</p>',
			'  <div><a class="btn btn-default" href="'. document::href_ilink('') .'">'. t('title_back', 'Back') .'</a></div>',
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

			// Resume incomplete order in session
			if (!empty($resume_id)) {
				if (database::query(
					"select * from ". DB_TABLE_PREFIX ."orders
					where id = ". (int)$resume_id ."
					and order_status_id is null
					and created_at > '". date('Y-m-d H:i:s', strtotime('-15 minutes')) ."'
					limit 1;"
				)->num_rows) {
					session::$data['checkout']['order'] = new ent_order($resume_id);
					session::$data['checkout']['order']->reset();
				} else {
					session::$data['checkout']['order'] = new ent_order();
				}
			} else {
				session::$data['checkout']['order'] = new ent_order();
			}

			$order = &session::$data['checkout']['order'];

			// Build Order
			$order->data['weight_unit'] = settings::get('store_weight_unit');
			$order->data['currency_code'] = currency::$selected['code'];
			$order->data['currency_value'] = currency::$currencies[currency::$selected['code']]['value'];
			$order->data['language_code'] = language::$selected['code'];
			$order->data['customer'] = customer::$data;
			$order->data['display_prices_including_tax'] = !empty(customer::$data['display_prices_including_tax']) ? true : false;
			$order->data['utm_data'] = fallback(session::$data['utm'], []);

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
				$order->add_line($item, $item['stock_items']);
			}

			if ($_POST['checkout'] == 'standard') {

				redirect(document::ilink('checkout/index'));
				exit;

			} else if (in_array($_POST['checkout'], array_column($checkouts, 'id'))) {

				$mod_checkout = new mod_checkout();
				$checkouts = $mod_checkout->options();

				if (!in_array($_POST['checkout'], array_column($checkouts, 'module_id'))) {
					throw new Exception(t('error_unknown_checkout_method', 'Unknown checkout method'));
				}

				session::$data['checkout']['type'] = $_POST['checkout'];

				$mod_checkout->select($_POST['checkout']);
				$mod_checkout->process($order);

				redirect(document::ilink('checkout/verify_checkout'));
				exit;

			} else {
				throw new Exception(t('error_unknown_checkout_method', 'Unknown checkout method'));
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
		'cheapest_shipping_fee' => null,
		'display_prices_including_tax' => customer::$data['display_prices_including_tax'],
		'error' => false,
		'box_also_purchased_products' => null,
	];

	foreach (currency::$currencies as $currency) {
		if (administrator::check_login() || $currency['status'] == 1) {
			$_page->snippets['currencies'][] = $currency;
		}
	}

	foreach (language::$languages as $language) {
		if (administrator::check_login() || $language['status'] == 1) {
			$_page->snippets['languages'][] = $language;
		}
	}

	// Cheapest shipping
	if (settings::get('display_cheapest_shipping')) {

		$tmp_order = (object)[
			'data' => [
				'items' => array_map(function($item) {
					return [
						'product_id' => $item['product_id'],
						'stock_option_id' => $item['stock_option_id'],
						'name' => $item['name'],
						'sku' => $item['sku'],
						'image' => $item['image'],
						'quantity' => $item['quantity'],
						'price' => $item['price'],
						'tax_class_id' => $item['tax_class_id'],
						'tax' => $item['tax'],
						'weight' => $item['weight'],
						'weight_unit' => $item['weight_unit'],
						'length' => $item['length'],
						'width' => $item['width'],
						'height' => $item['height'],
						'length_unit' => $item['length_unit'],
					];
				}, cart::$items),
				'subtotal' => cart::$total['value'],
				'subtotal_tax' => cart::$total['tax'],
				'customer' => customer::$data,
				'currency_code' => currency::$selected['code'],
			],
		];

		$cheapest_shipping = (new mod_shipping)->cheapest($tmp_order);

		if ($cheapest_shipping) {
			$_page->snippets['cheapest_shipping'] = $cheapest_shipping;
			var_dump($_page->snippets['cheapest_shipping']);
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

	// Also purchased products
	if (settings::get('box_also_purchased_products_num_items')) {

		$box_also_purchased_products = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_also_purchased_products.inc.php');

		$product_ids = database::query(
			"select ol.product_id, sum(ol.quantity) as num_purchases from ". DB_TABLE_PREFIX ."orders_lines ol
			left join ". DB_TABLE_PREFIX ."products p on (p.id = ol.product_id)
			where p.status
			and (ol.product_id != 0 and ol.product_id not in ('". implode("', '", database::input(array_column(cart::$items, 'product_id'))) ."'))
			and order_id in (
				select distinct order_id as id from ". DB_TABLE_PREFIX ."orders_lines
				where product_id in ('". implode("', '", database::input(array_column(cart::$items, 'product_id'))) ."')
			)
			group by ol.product_id
			order by num_purchases desc
			limit ". (settings::get('box_also_purchased_products_num_items') * 3) .";"
		)->fetch_all('product_id');

		if ($product_ids) {
			$box_also_purchased_products->snippets['products'] = functions::catalog_products_query([
				'products' => array_keys($product_ids),
				'sort' => 'random',
				'limit' => settings::get('box_also_purchased_products_num_items'),
			])->fetch_all();
		}

		if (!empty($box_also_purchased_products->snippets['products'])) {
			$_page->snippets['box_also_purchased_products'] = $box_also_purchased_products->render();
		}
	}

	// Express checkout
	$_page->snippets['checkouts'] = (new mod_checkout)->options(cart::$items, customer::$data);

	echo $_page;
