<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout/payment.inc.php
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

	if (!$options = $order->payment->options($order)) {
		return;
	}

	if (!empty($_POST['select_payment'])) {
		$order->payment->select($_POST['payment_option']['id'], $_POST);

		if (route::$selected['route'] != 'f:checkout/process') {
			header('Location: '. $_SERVER['REQUEST_URI']);
			exit;
		}
	}

	if (!empty($order->payment->selected['id'])) {
		if (array_search($order->payment->selected['id'], array_column($options, 'id')) === false) {
			$order->payment->selected = []; // Clear option no longer being present
		} else {
			$order->payment->select($order->payment->selected['id'], $order->payment->selected['userdata']); // Reinstate a present option
		}
	}

	if (empty($order->payment->selected['id'])) {
		if ($cheapest = $order->payment->cheapest($order)) {
			$order->payment->select($cheapest['id']);
		}
	}

	if (!empty($order->payment->selected)) {
		$_POST['payment_option'] = $order->payment->selected;
	}

	$box_checkout_payment = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/payment.inc.php');

	$box_checkout_payment->snippets = [
		'selected' => $order->payment->selected,
		'options' => $options,
	];

	echo $box_checkout_payment;

	// Don't process layout if this is an ajax request
	if (is_ajax_request()) {
		exit;
	}
