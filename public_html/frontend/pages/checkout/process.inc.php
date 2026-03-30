<?php

	header('X-Robots-Tag: noindex');

	document::$layout = 'checkout';

	if (settings::get('catalog_only_mode')) {
		return;
	}

	if (empty(session::$data['checkout']['order'])) {
		notices::add('errors', t('error_no_order_in_session', 'No order in session'));
		redirect(document::ilink('checkout/index'), 302);
		exit;
	}

	$order = &session::$data['checkout']['order'];

	if (empty($order->data['processable'])) {
		notices::add('errors', 'The shopping cart is not yet processable for creating an order');
		redirect(document::ilink('checkout/index'), 302);
		exit;
	}

	if ($error_message = $order->validate()) {
		notices::add('errors', $error_message);
		redirect(document::ilink('checkout/index'), 303);
		exit;
	}

	// If there is an amount to pay
	if (currency::format_raw($order->data['total'], $order->data['currency_code'], $order->data['currency_value']) > 0) {

		// Refresh the shopping cart if it's in the database in case a callback have tampered with it
		if (!empty($order->data['id'])) {
			$order->load($order->data['id']);
		}

		$payment_options = $order->payment->options($order);

		// Verify transaction
		if ($payment_options) {
			$result = $order->payment->verify($order);

			// If payment error
			if (!empty($result['error'])) {

				customer::log([
					'type' => 'checkout_failure',
					'description' => 'User failed payment verification during checkout',
					'data' => [
						'order_id' => $order->data['order_id'],
						'products' => array_filter(array_column($order->data['items'], 'product_id')),
						'shipping_option_id' => $order->data['shipping_option']['id'],
						'payment_option_id' => $order->data['payment_option']['id'],
						'total_amount' => $order->data['total'],
						'error' => $result['error'],
					],
					'expires_at' => strtotime('+12 months'),
				]);

				notices::add('errors', $result['error']);
				redirect(document::ilink('checkout/index'), 303);
				exit;
			}
		}
	}

	// Save order
	$session_order = $order;
	$order = new ent_order();

	$fields = [
		'customer',
		'currency_code',
		'language_code',
		'shipping_option',
		'payment_option',
		'weight_unit'
	];

	$order->data = array_replace($order->data, array_intersect_key($session_order->data, array_flip($fields)));
	$order->data['currency_value'] = currency::$currencies[$order->data['currency_code']]['value'];
	$order->data['display_prices_including_tax'] = !empty($session_order->data['display_prices_including_tax']);
	$order->data['unread'] = true;
	$order->shipping = $session_order->shipping;
	$order->payment = $session_order->payment;

	// Set items
	foreach ($session_order->data['lines'] as $item) {
		$order->add_line($item, !empty($item['stock_items']) ? $item['stock_items'] : []);
	}

	// Set order status id
	if (isset($result['order_status_id'])) {
		$order->data['order_status_id'] = $result['order_status_id'];
	}

	// Set transaction id
	if (isset($result['transaction_id'])) {
		$order->data['payment_transaction_id'] = $result['transaction_id'];
	}

	// Set transaction date
	if (isset($result['receipt_url'])) {
		$order->data['payment_receipt_url'] = $result['receipt_url'];
	}

	// Set payment terms
	if (isset($result['payment_terms'])) {
		$order->data['payment_terms'] = $result['payment_terms'];
	}

	// Set transaction date
	if (isset($result['date_paid'])) {
		$order->data['date_paid'] = $result['date_paid'];
	}

	$order->refresh_total();
	$order->save();

	customer::log([
		'type' => 'checkout_success',
		'description' => 'User completed checkout successfully',
		'data' => [
			'order_id' => $order->data['id'],
			'products' => array_filter(array_column($order->data['items'], 'product_id')),
			'shipping_option_id' => $order->data['shipping_option']['id'],
			'payment_option_id' => $order->data['payment_option']['id'],
			'total_amount' => $order->data['total'],
		],
		'expires_at' => strtotime('+12 months'),
	]);

	// Clean up cart
	cart::clear();

	// Send order confirmation email
	if (settings::get('send_order_confirmation')) {
		$bccs = [];

		if (settings::get('email_order_copy')) {
			foreach (preg_split('#[\s;,]+#', settings::get('email_order_copy')) as $email) {
				if (empty($email)) continue;
				$bccs[] = $email;
			}
		}

		$order->send_order_copy($order->data['customer']['email'], [], $bccs, $order->data['language_code']);
	}

	// Run after process operations
	$order->shipping->after_process($order);
	$order->payment->after_process($order);

	$redirect_url = document::ilink('checkout/success', ['order_id' => $order->data['id'], 'public_key' => $order->data['public_key']]);

	session::$data['checkout']['order'] = null;

	redirect($redirect_url, 303);
	exit;
