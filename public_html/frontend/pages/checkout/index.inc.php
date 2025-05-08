<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$layout = 'checkout';

	if (settings::get('catalog_only_mode')) {
		notice::add('errors', language::translate('warning_no_checkout_in_catalog_only_mode', 'The store is currently in catalog mode only and cannot accept orders.'));
		return;
	}

	document::$title[] = language::translate('checkout:head_title', 'Checkout');

	breadcrumbs::add(language::translate('title_checkout', 'Checkout'), document::ilink());

	// If Confirm Order button was pressed
	if (isset($_POST['confirm'])) {

		try {

			if (empty(session::$data['checkout']['order'])) {
				notices::add('errors', language::translate('error_no_order_in_session', 'No order in session'));
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
					notices::add('errors', language::translate('error_no_payment_method_selected', 'No payment method selected'));
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

								echo '<div>'. language::translate('title_redirecting', 'Redirecting') .'...</div>' . PHP_EOL
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
				notices::add('errors', language::translate('error_order_already_processed', 'This order has already been processed'));
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

	if (!empty($payment->data['selected'])) {
		$order->data['payment_option'] = $payment->data['selected'];
	}

	$order->data['processable'] = false; // Whether or not it is allowed to be processed in checkout/process

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/index.inc.php');

	$_page->snippets = [
		//'error' => $session_order->validate($shipping, $payment),
		'selected_shipping' => null,
		'selected_payment' => null,
		'consent' => null,
		'confirm' => !empty($order->payment->data['selected']['confirm']) ? $order->payment->data['selected']['confirm'] : language::translate('title_confirm_order', 'Confirm Order'),
	];

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
