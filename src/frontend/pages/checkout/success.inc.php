<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout/success.inc.php
	 */

	header('X-Robots-Tag: noindex');

	if (settings::get('catalog_only_mode')) {
		return;
	}

	document::$title[] = t('title_order_success', 'Order Success');

	breadcrumbs::add(t('title_checkout', 'Checkout'), document::ilink('checkout/index'));
	breadcrumbs::add(t('title_order_success', 'Order Success'), document::ilink());

	try {

		if (!empty($_GET['order_id'])) {

			$order = database::query(
				"select id from ". DB_PREFIX ."orders
				where id = ". (int)$_GET['order_id'] ."
				limit 1;"
			)->fetch();

			if (!$order) {
				throw new Exception('Invalid order_id', 404);
			}

		} else if (!empty($_GET['order_no'])) {

			$order = database::query(
				"select id from ". DB_PREFIX ."orders
				where no = '". database::input($_GET['order_no']) ."'
				limit 1;"
			)->fetch();

			if (!$order) {
				throw new Exception('Invalid order_no', 404);
			}
		}

		$order = new ent_order($order['id']);

	} catch (Exception $e) {
		http_response_code(404);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/success.inc.php');

	$_page->snippets = [
		'order' => $order->data,
		'printable_link' => document::ilink('printable_order_copy', ['order_no' => $order->data['no'], 'public_key' => $order->data['public_key']]),
	];

	echo $_page->render();
