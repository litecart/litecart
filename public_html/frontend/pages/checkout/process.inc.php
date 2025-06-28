<?php

	header('X-Robots-Tag: noindex');

	document::$layout = 'checkout';

	if (settings::get('catalog_only_mode')) {
		return;
	}

	if (empty(session::$data['checkout']['order'])) {
		notices::add('errors', t('error_no_order_in_session', 'No order in session'));
		redirect(document::ilink('checkout/index'));
		exit;
	}

	$order = &session::$data['checkout']['order'];

	if (empty($order->data['processable'])) {
		notices::add('errors', 'The shopping cart is not yet processable for creating an order');
		redirect(document::ilink('checkout/index'));
		exit;
	}

	if ($error_message = $order->validate()) {
		notices::add('errors', $error_message);
		redirect(document::ilink('checkout/index'));
		exit;
	}

	// If there is an amount to pay
	if (currency::format_raw($order->data['total'], $order->data['currency_code'], $order->data['currency_value']) > 0) {

		// Refresh the shopping cart if it's in the database in case a callback have tampered with it
		if (!empty($order->data['id'])) {
			$order->load($order->data['id']);
		}

		// Verify transaction
		if ($payment->modules && count($payment->options($order)) > 0) {
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
				redirect(document::ilink('checkout/index'));
				exit;
			}
		}
	}

	// Save order
	$order = new ent_order();

	$fields = [
		'customer',
		'currency_code',
		'language_code',
		'shipping_option',
		'payment_option',
	];

	$order->data = array_replace($order->data, array_intersect_key($order->data, array_flip($fields)));
	$order->data['currency_value'] = currency::$currencies[$order->data['currency_code']]['value'];
	$order->data['unread'] = true;

	// Set items
	foreach ($order->data['items'] as $item) {
		$order->add_item($item);
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

	$order->save();

	customer::log([
		'type' => 'checkout_success',
		'description' => 'User completed checkout successfully',
		'data' => [
			'order_id' => $order->data['order_id'],
			'products' => array_filter(array_column($order->data['items'], 'product_id')),
			'shipping_option_id' => $order->data['shipping_option']['id'],
			'payment_option_id' => $order->data['payment_option']['id'],
			'total_amount' => $order->data['total'],
		],
		'expires_at' => strtotime('+12 months'),
	]);

	// Clean up cart
	cart::clear();

	session::$data['checkout']['order'] = null;

	// Send order confirmation email
	if (settings::get('send_order_confirmation')) {
		$bccs = [];

		if (settings::get('email_order_copy')) {
			foreach (preg_split('#[\s;,]+#', settings::get('email_order_copy')) as $email) {
				if (empty($email)) continue;
				$bccs[] = $email;
			}
		}

		$order->email_order_copy($order->data['customer']['email'], $bccs, $order->data['language_code']);
	}

	// Run after process operations
	$order->shipping->after_process($order);
	$order->payment->after_process($order);

	$order_modules->after_process($order);

	redirect(document::ilink('checkout/success', ['order_id' => $order->data['id'], 'public_key' => $order->data['public_key']]));
	exit;
