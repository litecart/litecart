<?php

	// Payment validation stub for checkout confirmation flow

	if (empty(session::$data['checkout']['order'])) return;

	$order = &session::$data['checkout']['order'];

	if (empty($order->payment->selected['id'])) {
		notices::add('errors', t('error_no_payment_method_selected', 'No payment method selected'));
	}
