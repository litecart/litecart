<?php

	if (empty(session::$data['checkout']['order'])) {
		notices::add('errors', t('error_missing_order_in_session', 'Missing order in session'));
		redirect(document::ilink('checkout/index'));
		exit;
	}

	if (empty(session::$data['checkout']['type']) || session::$data['checkout']['type'] != 'express') {
		notices::add('errors', 'Invalid checkout type');
		redirect(document::ilink('shopping_cart'));
		exit;
	}

	$order = &session::$data['checkout']['order'];

	$mod_checkout = new mod_checkout();
	$mod_checkout->select($order->data['id']);


	if ($error_message = $mod_checkout->verify($order)) {

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

		notices::add('errors', $error_message);

		redirect(document::ilink('shopping_cart'));
		exit;
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
