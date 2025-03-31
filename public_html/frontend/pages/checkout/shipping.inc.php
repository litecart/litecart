<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout/shipping.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$layout = 'checkout';

	if (settings::get('catalog_only_mode')) {
		return;
	}

	if (!empty(session::$data['checkout']['order'])) {
		$order = &session::$data['checkout']['order'];
	} else {
		return;
	}

	if (!$order->data['items']) {
		return;
	}

	if (!$options = $order->shipping->options()) {
		throw new Exception('No shipping options available');
		return;
	}

	if (!empty($_POST['select_shipping'])) {
		$order->shipping->select($_POST['shipping_option']['id'], $_POST);

		if (!empty($order->shipping->selected['incoterm'])) {
			$order->data['incoterm'] = $order->shipping->selected['incoterm'];
		}

		if (route::$selected['route'] != 'f:checkout/process') {
			header('Location: '. $_SERVER['REQUEST_URI']);
			exit;
		}
	}

	if (!empty($order->shipping->selected['id'])) {
		if (array_search($order->shipping->selected['id'], array_column($options, 'id')) === false) {
			$order->shipping->selected = []; // Clear option no longer being present
		} else {
			$order->shipping->select($order->shipping->selected['id'], $order->shipping->selected['userdata']); // Reinstate a present option
		}
	}

	if (empty($order->shipping->selected['id'])) {
		if ($cheapest = $order->shipping->cheapest($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {
			$order->shipping->select($cheapest['id'], $_POST);
		}
	}

	if (!empty($order->shipping->selected)) {
		$_POST['shipping_option'] = $order->shipping->selected;
	}

	$box_checkout_shipping = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/shipping.inc.php');

	$box_checkout_shipping->snippets = [
		'selected' => $order->shipping->selected,
		'options' => $options,
	];

	echo $box_checkout_shipping;

	// Don't process layout if this is an ajax request
	if (is_ajax_request()) {
		exit;
	}

