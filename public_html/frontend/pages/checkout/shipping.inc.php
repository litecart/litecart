<?php

	// Shipping validation stub for checkout confirmation flow

	if (empty(session::$data['checkout']['order'])) return;

	$order = &session::$data['checkout']['order'];

	if (empty($order->shipping->selected['id'])) {
		notices::add('errors', t('error_no_shipping_method_selected', 'No shipping method selected'));
	}
