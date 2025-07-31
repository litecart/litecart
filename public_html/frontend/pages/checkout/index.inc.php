<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$layout = 'checkout';

	try {

		// Halt on no order in session
		if (empty(session::$data['checkout']['order'])) {
			throw new Exception(t('error_no_order_in_session', 'No order in session'), 404);
		}

		// Halt on no items
		if (empty(session::$data['checkout']['order']->data['items'])) {
			throw new Exception(t('error_order_has_no_items', 'The order has no items to checkout'), 404);
		}

		// Redirect to customer details if not sufficient
		if ($validation_error = session::$data['checkout']['order']->validate('customer')) {
			notices::add('notices', t('error_we_need_some_additional_info_from_you', 'We need some additional information from you'));
			redirect(document::ilink('checkout/customer'));
			exit;
		}

		// Connect session order to shorthand variable
		$order = &session::$data['checkout']['order'];

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		notices::add('errors', $e->getMessage());
		redirect(document::ilink('shopping_cart'));
	}

	if (settings::get('catalog_only_mode')) {
		notice::add('errors', t('warning_no_checkout_in_catalog_only_mode', 'The store is currently in catalog mode only and cannot accept orders.'));
		return;
	}

	document::$title[] = t('checkout:head_title', 'Checkout');

	breadcrumbs::add(t('title_checkout', 'Checkout'), document::ilink());

	// Select shipping
	if (!empty($_POST['select_shipping'])) {
		$order->shipping->select($_POST['shipping_option']['id'], $_POST);

		if (!empty($order->shipping->selected['incoterm'])) {
			$order->data['incoterm'] = $order->shipping->selected['incoterm'];
		}

		if (route::$selected['route'] != 'f:checkout/process') {
			reload();
			exit;
		}
	}

	// Select payment
	if (!empty($_POST['select_payment'])) {
		$order->payment->select($_POST['payment_option']['id'], $_POST);

		if (route::$selected['route'] != 'f:checkout/process') {
			reload();
			exit;
		}
	}

	// Prepare shipping options
	if (!empty($order->shipping->selected['id'])) {
		if (array_search($order->shipping->selected['id'], array_column($order->shipping->options(), 'id')) === false) {
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

	// Prepare payment options
	if (!empty($order->payment->selected['id'])) {
		if (array_search($order->payment->selected['id'], array_column($order->payment->options(), 'id')) === false) {
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


	// If Confirm Order button was pressed
	if (isset($_POST['confirm'])) {

		try {

			if (empty(session::$data['checkout']['order'])) {
				notices::add('errors', t('error_no_order_in_session', 'No order in session'));
				redirect(document::ilink('checkout/index'));
				exit;
			}

			$session_order = &session::$data['checkout']['order'];

			customer::log([
				'type' => 'checkout_confirm',
				'description' => 'User confirmed order to begin checkout',
				'data' => [
					'order_id' => $session_order->data['order_id'],
					'products' => array_filter(array_column($session_order->data['items'], 'product_id')),
					'shipping_option_id' => $session_order->data['shipping_option']['id'],
					'payment_option_id' => $session_order->data['payment_option']['id'],
					'total_amount' => $session_order->data['total'],
				],
				'expires_at' => strtotime('+12 months'),
			]);

			ob_start();
			include_once 'app://frontend/pages/checkout/customer.inc.php';
			include_once 'app://frontend/pages/checkout/shipping.inc.php';
			include_once 'app://frontend/pages/checkout/payment.inc.php';
			include_once 'app://frontend/pages/checkout/summary.inc.php';
			ob_clean();

			if (!empty(notices::$data['errors'])) {
				redirect(document::ilink('checkout/index'));
				exit;
			}

			$payment = new mod_payment();
			if ($payment->options($session_order)) {

				if (empty($payment->selected)) {
					notices::add('errors', t('error_no_payment_method_selected', 'No payment method selected'));
					redirect(document::ilink('checkout/index'));
					exit;
				}

				if ($payment_error = $payment->pre_check($session_order)) {
					notices::add('errors', $payment_error);
					redirect(document::ilink('checkout/index'));
					exit;
				}

				if (!empty($_POST['comments'])) {
					$session_order->data['comments']['session'] = [
						'author' => 'customer',
						'text' => $_POST['comments'],
					];
				}

				if ($gateway = $payment->transfer($session_order, (string)document::ilink('checkout/process'))) {

					if (!empty($gateway['error'])) {
						notices::add('errors', $gateway['error']);
						redirect(document::ilink('checkout/index'));
						exit;
					}

					if (!empty($gateway['method'])) {
						switch (strtoupper($gateway['method'])) {

							case 'POST':

								document::$template = 'blank';

								echo '<div>'. t('title_redirecting', 'Redirecting') .'...</div>' . PHP_EOL
									 . '<form name="gateway_form" method="post" action="'. fallback($gateway['action'], document::ilink('checkout/process')) .'">' . PHP_EOL;

								if (is_array($gateway['fields'])) {
									foreach ($gateway['fields'] as $key => $value) echo functions::form_input_hidden($key, $value) . PHP_EOL;
								} else {
									echo $gateway['fields'];
								}

								echo '</form>' . PHP_EOL
									 . '<script>' . PHP_EOL;

								if (!empty($gateway['delay'])) {
									echo implode(PHP_EOL, [
										'  setTimeout(function() {',
										'    document.forms["gateway_form"].submit();',
										'  }, '. ($gateway['delay'] * 1000) .');',
									]) . PHP_EOL;
								} else {
									echo '  document.forms["gateway_form"].submit();' . PHP_EOL;
								}

								echo '</script>';
								exit;

							case 'HTML':

								document::$template = 'blank';

								echo $gateway['content'];
								return;

							case 'GET':
							default:

								redirect(fallback($gateway['action'], document::ilink('checkout/process')));
								exit;
						}
					}
				}
			}

			$session_order->data['processable'] = true;
			redirect(document::ilink('checkout/process'));
			exit;

		} catch (Exception $e) {

			customer::log([
				'type' => 'checkout_failure',
				'description' => 'Checkout failed due to an error',
				'data' => [
					'order_id' => $session_order->data['order_id'],
					'products' => array_filter(array_column($session_order->data['items'], 'product_id')),
					'shipping_option_id' => $session_order->data['shipping_option']['id'],
					'payment_option_id' => $session_order->data['payment_option']['id'],
					'total_amount' => $session_order->data['total'],
					'error' => $e->getMessage(),
				],
				'expires_at' => strtotime('+12 months'),
			]);

			notices::add('errors', $e->getMessage());
		}
	}

	// Load an existing order
	if (!empty($_GET['order_id']) && !empty($_GET['public_key'])) {

		try {

			$order = database::fetch(
				"select * from ". DB_TABLE_PREFIX ."orders
				where id = ". (int)$_GET['order_id'] ."
				and public_key = '". database::input($_GET['public_key']) ."'
				limit 1;"
			)->fetch();

			if (!$order) {
				http_response_code(404);
				include 'app://frontend/pages/error_document.inc.php';
				return;
			}

			if ($order['order_status_id']) {
				http_response_code(403);
				notices::add('errors', t('error_order_already_processed', 'This order has already been processed'));
				return;
			}

			session::$data['checkout']['order'] = $order;

			redirect(document::ilink('checkout/index'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}

	} else {
		$order = &session::$data['checkout']['order'];
	}

	// Do we have an existing order in the session?
	if (!empty(session::$data['checkout']['order']->data['id'])) {
		$resume_id = session::$data['checkout']['order']->data['id'];
	}

	$order = &session::$data['checkout']['order'];

	if (!empty($shipping->data['selected'])) {
		$order->data['shipping_option'] = $shipping->data['selected'];
		$order->data['incoterm'] = $shipping->data['selected']['incoterm'];
	}

	if (!empty($payment->selected)) {
		$order->data['payment_option'] = $payment->selected;
	}

	$order->data['processable'] = false; // Whether or not it is allowed to be processed in checkout/process

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/index.inc.php');

	$_page->snippets = [
		//'error' => $session_order->validate($shipping, $payment),
		'order' => $order->data,
		'shipping_options' => $order->shipping->options(),
		'payment_options' => $order->payment->options(),
		'consent' => null, // Placeholder for consent
		'error' => $order->validate(),
		'confirm' => fallback($order->payment->selected['confirm'], t('title_confirm_order', 'Confirm Order')),
	];

	// Determine if we have terms of purchase
	if ($terms_of_purchase_id = settings::get('terms_of_purchase')) {
		$_page->snippets['consent'] = t('consent:terms_of_purchase', 'I have read the <a href="{terms_of_purchase_link}" target="_blank">Terms of Purchase</a> and I consent.');

		// Set link to terms of purchase
		$_page->snippets['consent'] = strtr($_page->snippets['consent'], [
			'{terms_of_purchase_link}' => document::href_ilink('information', ['page_id' => $terms_of_purchase_id]),
		]);
	}

	// Log the event
	customer::log(
		[
			'type' => 'checkout',
			'description' => 'User is checking out',
			'data' => [
				'order_id' => $order->data['id'],
				'products' => array_filter(array_column($order->data['items'], 'product_id')),
				'shipping_option_id' => $order->data['shipping_option']['id'],
				'payment_option_id' => $order->data['payment_option']['id'],
				'total_amount' => $order->data['total'],
			],
			'expires_at' => strtotime('+12 months'),
		]
	);

	echo $_page;

	// Don't process layout if this is an ajax request
	if (is_ajax_request()) {
		exit;
	}