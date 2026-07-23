<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/pages/checkout.inc.php
	*/

	header('X-Robots-Tag: noindex');

	if (settings::get('catalog_only_mode')) return;

	document::$title[] = t('title_checkout', 'Checkout');

	breadcrumbs::add(t('title_checkout', 'Checkout'), document::ilink('checkout'));

	try {

		if (settings::get('catalog_only_mode')) {
			throw new Exception(t('warning_no_checkout_in_catalog_only_mode', 'The store is currently in catalog only mode and will not accept orders.'));
		}

		if (empty(session::$data['checkout_order'])) {
			notices::add('errors', t('error_checkout_order_not_found', 'Checkout order not found'));
			redirect(document::ilink('shopping_cart'));
			exit;
		}

		// Connect order to session
		$order = new ent_order();
		$order->data = &session::$data['checkout_order'];

		// Don't resume an already processed order
		if (!empty($order->data['id'])) {
			if (!database::query(
				"select * from ". DB_TABLE_PREFIX ."orders
				where id = ". (int)$order->data['id'] ."
				and order_status_id is not null
				and created_at > '". date('Y-m-d H:i:s', strtotime('-15 minutes')) ."'
				limit 1;"
			)->num_rows) {
				$order->data['id'] = null;
				$order->data['no'] = null;
				$order->data['public_key'] = null;
			}
		}

		if (empty($order->data['items'])) {
			throw new Exception(t('error_order_appears_empty', 'The order appears empty'), 404);
		}

		if (!$_POST) {
			$_POST = $order->data;
			$_POST['comments'] = '';
		}

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		notices::add('errors', $e->getMessage());
	}

	if (isset($_POST['update'])) {

		try {

			if (!empty($_POST['items'])) {

				foreach ($_POST['items'] as $key => $item) {

					if (!isset($item['quantity'])) continue;

					$quantity = (float)$item['quantity'];

					if ($quantity < 0) {
						throw new Exception(t('error_invalid_quantity', 'Invalid quantity'));
					}

					if ($quantity > 0) {
						$order->update_item($key, ['quantity' => $quantity]);
					} else {
						$order->remove_item($key);
					}
				}
			}

			if (!empty($_POST['shipping_option']['id'])) {

				$shipping_options = (new mod_shipping(
					items: $order->data['items'],
					currency_code: $order->data['currency_code'],
					customer: $order->data['customer']
				))->options;

				if (!empty($shipping_options[$_POST['shipping_option']['id']]['error'])) {
					throw new Exception($shipping_options[$_POST['shipping_option']['id']]['error']);
				}

				if (empty($shipping_options[$_POST['shipping_option']['id']])) {
					throw new Exception(t('error_invalid_shipping_option', 'Invalid shipping option') . '(ID: '. $_POST['shipping_option']['id'] .', Available: '. implode(', ', array_keys($shipping_options)) .')');
				}

				$order->data['shipping_option']['id'] = $_POST['shipping_option']['id'];
				$order->data['shipping_option']['fee'] = $shipping_options[$_POST['shipping_option']['id']]['fee'];
				$order->data['shipping_option']['tax'] = $shipping_options[$_POST['shipping_option']['id']]['tax'];
				$order->data['shipping_option']['tax_class_id'] = $shipping_options[$_POST['shipping_option']['id']]['tax_class_id'];
			}

			notices::add('success', t('success_cart_updated', 'Your cart has been updated successfully'));
			reload();
			exit;

		} catch (Exception $e) {
			die($e->getMessage());
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['confirm_purchase'])) {

		try {

			$order->data['comments']['session'] = [
				'author' => 'customer',
				'text' => $_POST['comments'] ?? '',
				'hidden' => false,
			];

			// Abort if customer details are not sufficient
			if ($validation_error = $order->validate(['customer'])) {
				notices::add('notices', t('error_we_need_some_additional_info_from_you', 'We need some additional information from you'));
				reload();
				exit;
			}

			$shipping = new mod_shipping(
				items: $order->data['items'],
				currency_code: $order->data['currency_code'],
				customer: $order->data['customer']
			);

			$shipping_options = $shipping->options();

			if (!empty($_POST['shipping_option']['id'])) {

				if (!empty($shipping_options[$_POST['shipping_option']['id']]['error'])) {
					throw new Exception($shipping_options[$_POST['shipping_option']['id']]['error']);
				}

				$matched_shipping_option = [];
				foreach ($shipping_options as $option) {
					if ($option['id'] == $_POST['shipping_option']['id']) {
						$matched_shipping_option = $option;
						break;
					}
				}

				if (!$matched_shipping_option) {
					throw new Exception(t('error_invalid_shipping_option', 'Invalid shipping option'). '(ID: '. $_POST['shipping_option']['id'] .')');
				}

				$order->data['shipping_option']['id'] = $_POST['shipping_option']['id'];
				$order->data['shipping_option']['name'] = $matched_shipping_option['name'];
				$order->data['shipping_option']['fee'] = $matched_shipping_option['fee'];
				$order->data['shipping_option']['tax'] = $matched_shipping_option['tax'];
				$order->data['shipping_option']['tax_class_id'] = $matched_shipping_option['tax_class_id'];

			} else {
				if ($shipping_options) {
					throw new Exception(t('error_shipping_option_required', 'Shipping option required'));
				}
			}

			$payment = new mod_payment($order);
			$payment_options = $payment->options();

			if ($error = $order->validate()) {
				throw new Exception($error);
			}

			$order->data['order_status_id'] = settings::get('default_order_status_id');
			$order->data['incoterm'] = settings::get('default_incoterm');
			//$order->data['payment_terms'] = '';

      if (!empty($_POST['comments'])) {
        $order->data['comments']['session'] = [
          'author' => 'customer',
          'text' => $_POST['comments'],
        ];
      }

			$order->save();

			// Initiate payment
			$payment = new mod_payment($order);

			if ($payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) {

				if (empty($payment->data['selected'])) {
					throw new Exception(t('error_no_payment_method_selected', 'No payment method selected'));
				}

				if ($payment_error = $payment->pre_check($order)) {

					$order->data['comments'][] = [
						'author' => 'system',
						'text' => 'Payment Precheck Error: '. $payment_error,
						'hidden' => true,
					];

					throw new Exception($payment_error);
				}

				// Update the order
				$order->save();

        if ($gateway = $payment->transfer($order)) {

          if (!empty($gateway['error'])) {
            $order->data['comments'][] = [
              'author' => 'system',
              'text' => 'Payment Transfer Error: '. $gateway['error'],
              'hidden' => true,
            ];

            throw new Exception($gateway['error']);
          }

          if (!empty($gateway['method'])) {
            switch (strtoupper($gateway['method'])) {

              case 'POST':

                echo '<p>'. t('title_redirecting', 'Redirecting') .'...</p>' . PHP_EOL
                   . '<form name="gateway_form" method="post" action="'. (!empty($gateway['action']) ? $gateway['action'] : document::ilink('order_process')) .'">' . PHP_EOL;

                if (is_array($gateway['fields'])) {

                  foreach ($gateway['fields'] as $key => $value) {
										echo '  ' . functions::form_draw_hidden_field($key, $value) . PHP_EOL;
									}

                } else {
                  echo $gateway['fields'];
                }

                echo '</form>' . PHP_EOL
                   . '<script>' . PHP_EOL;

                if (!empty($gateway['delay'])) {
                  echo implode(PHP_EOL, [
										'  var t=setTimeout(function(){',
                    '    document.forms["gateway_form"].submit();',
                    '  }, '. ((float)$gateway['delay'] * 1000) .');',
                  ]);
                } else {
                  echo '  document.forms["gateway_form"].submit();' . PHP_EOL;
                }

                echo '</script>';
                exit;

              case 'HTML':
                echo $gateway['content'];
                require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
                exit;

              case 'GET':
                header('Location: '. (!empty($gateway['action']) ? $gateway['action'] : document::ilink('order_process')));
                exit;

              default:
                throw new Exception('Undefined method ('. $gateway['method'] .')');
            }
          }
        }
      }

			redirect(document::ilink('checkout/verify'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Output

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/index.inc.php');

	$_page->snippets = [
		'items' => [],
		'subtotal' => [
			'value' => $order->data['subtotal'],
			'tax' => $order->data['subtotal_tax'],
		],
		'customer' => $order->data['customer'],
		'shipping_options' => [],
		'cheapest_shipping_fee' => null,
		'display_prices_including_tax' => $order->data['display_prices_including_tax'],
		'box_also_purchased_products' => null,
		'sufficient_customer_details' => $validation_error = $order->validate('customer') ? false : true,
		'error' => $order->validate(),
	];

	foreach (currency::$currencies as $currency) {
		if (administrator::check_login() || $currency['status'] == 1) {
			$_page->snippets['currencies'][] = $currency;
		}
	}

	foreach (language::$languages as $language) {
		if (administrator::check_login() || $language['status'] == 1) {
			$_page->snippets['languages'][] = $language;
		}
	}

	// Cart
	foreach ($order->data['items'] as $item) {
		$_page->snippets['items'][] = [
			'product_id' => $item['product_id'],
			'name' => $item['name'],
			'code' => $item['code'],
			'image' => [
				'original' => 'storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg'),
				'thumbnail' => f::image_thumbnail('storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg'), 64, 0, 'product'),
				'thumbnail_2x' => f::image_thumbnail('storage://images/'. ($item['image'] ?  $item['image'] : 'no_image.svg'), 128, 0, 'product'),
			],
			'link' => document::ilink('f:product', ['product_id' => $item['product_id']]),
			'regular_price' => [
				'display_value' => tax::get_price($item['regular_price'], $item['tax_class_id'], $order->data['display_prices_including_tax'], $order->data['customer']),
				'value' => $item['regular_price'] ?? null,
				'tax' => tax::get_tax($item['regular_price'], $item['tax_class_id'], $order->data['customer']) ?? null,
			],
			'final_price' => [
				'display_value' => tax::get_price($item['final_price'], $item['tax_class_id'], $order->data['display_prices_including_tax'], $order->data['customer']),
				'value' => $item['final_price'] ?? null,
				'tax' => tax::get_tax($item['final_price'], $item['tax_class_id'], $order->data['customer']) ?? null,
			],
			'discount' => [
				'display_value' => tax::get_price($item['discount'], $item['tax_class_id'], $order->data['display_prices_including_tax'], $order->data['customer']),
				'value' => $item['discount'] ?? null,
				'tax' => tax::get_tax($item['discount'], $item['tax_class_id'], $order->data['customer']) ?? null,
			],
			'tax_class_id' => $item['tax_class_id'] ?? null,
			'quantity' => (float)$item['quantity'],
			'sum' => $item['sum'] ?? null,
			'sum_tax' => $item['sum_tax'] ?? null,
			'error' => $item['error'] ?? null,
		];
	}

	// Order Total
	$_page->snippets['subtotal'] = [
		'display_value' => tax::get_price($order->data['subtotal'], null, $order->data['display_prices_including_tax'], $order->data['customer']),
		'value' => $order->data['subtotal'],
		'tax' => $order->data['subtotal_tax'],
	];

	$_page->snippets['total_discount'] = [
		'display_value' => tax::get_price($order->data['discount'], null, $order->data['display_prices_including_tax'], $order->data['customer']),
		'value' => $order->data['discount'],
		'tax' => $order->data['discount_tax'],
	];

	$_page->snippets['shipping'] = [
		'display_value' => $order->data['display_prices_including_tax'] ? $order->data['shipping_option']['fee'] + $order->data['shipping_option']['tax'] : $order->data['shipping_option']['fee'],
		'value' => (float)$order->data['shipping_option']['fee'],
		'tax' => (float)$order->data['shipping_option']['tax'],
	];

	$order->refresh_total();

	$_page->snippets['total'] = [
		'value' => $order->data['total'],
		'tax' => $order->data['total_tax'],
	];

	// Shipping Options
	$mod_shipping = new mod_shipping(
		items: $order->data['items'],
		currency_code: $order->data['currency_code'],
		customer: $order->data['customer'],
		selected: $order->data['shipping_option'] ?? []
	);

	$shipping_options = $mod_shipping->options();

	if (empty($order->data['shipping_option']['id'])) {
		if ($cheapest_shipping = $mod_shipping->cheapest()) {
			$_POST['shipping_option'] = $cheapest_shipping['module_id'] . ':' . $cheapest_shipping['option_id'];
		}
	}

	$_page->snippets['shipping_options'] = $shipping_options;

	// Payment Options
	$mod_payment = new mod_payment($order, $order->data['payment_option'] ?? []);

	$_page->snippets['payment_options'] = $mod_payment->options();

	// Consents
  $privacy_policy_id = settings::get('privacy_policy');
  $terms_of_purchase_id = settings::get('terms_of_purchase');

  switch(true) {

    case ($terms_of_purchase_id && $privacy_policy_id):
      $_page->snippets['consent'] = t('consent:privacy_policy_and_terms_of_purchase', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
      break;

    case ($privacy_policy_id):
      $_page->snippets['consent'] = t('consent:privacy_policy', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and I consent.');
      break;

    case ($terms_of_purchase_id):
      $_page->snippets['consent'] = t('consent:terms_of_purchase', 'I have read the <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
      break;

		default:
			$_page->snippets['consent'] = '';
  }

	echo $_page;
