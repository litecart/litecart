<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout.inc.php
	 */

	header('X-Robots-Tag: noindex');
	document::$layout = 'checkout';

	if (settings::get('catalog_only_mode')) return;

	document::$title[] = language::translate('checkout:head_title', 'Checkout');

	breadcrumbs::add(language::translate('title_checkout', 'Checkout'));

	// If Confirm Order button was pressed
	if (isset($_POST['confirm'])) {

		try {

			if (empty(session::$data['checkout']['shopping_cart'])) {
				notices::add('errors', 'Missing order object');
				header('Location: '. document::ilink('checkout/index'));
				exit;
			}

			$shopping_cart = &session::$data['checkout']['shopping_cart'];

			ob_start();
			include_once 'app://frontend/pages/checkout/customer.inc.php';
			include_once 'app://frontend/pages/checkout/shipping.inc.php';
			include_once 'app://frontend/pages/checkout/payment.inc.php';
			include_once 'app://frontend/pages/checkout/summary.inc.php';
			ob_end_clean();

			if (!empty(notices::$data['errors'])) {
				header('Location: '. document::ilink('checkout/index'));
				exit;
			}

			$payment = new mod_payment();
			if ($payment->options($shopping_cart)) {

				if (empty($payment->selected)) {
					notices::add('errors', language::translate('error_no_payment_method_selected', 'No payment method selected'));
					header('Location: '. document::ilink('checkout/index'));
					exit;
				}

				if ($payment_error = $payment->pre_check($shopping_cart)) {
					notices::add('errors', $payment_error);
					header('Location: '. document::ilink('checkout/index'));
					exit;
				}

				if (!empty($_POST['comments'])) {
					$shopping_cart->data['comments']['session'] = [
						'author' => 'customer',
						'text' => $_POST['comments'],
					];
				}

				if ($gateway = $payment->transfer($shopping_cart, (string)document::ilink('checkout/process'))) {

					if (!empty($gateway['error'])) {
						notices::add('errors', $gateway['error']);
						header('Location: '. document::ilink('checkout/index'));
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
									echo '  let t=setTimeout(function(){' . PHP_EOL
										 . '    document.forms["gateway_form"].submit();' . PHP_EOL
										 . '  }, '. ($gateway['delay']*1000) .');' . PHP_EOL;
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

								header('Location: '. fallback($gateway['action'], document::ilink('checkout/process')));
								exit;
						}
					}
				}
			}

			$shopping_cart->data['processable'] = true;
			header('Location: '. document::ilink('checkout/process'));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	// Load an existing order
	if (!empty($_GET['cart_uid']) && !empty($_GET['public_key'])) {

		if (!empty($_GET['cart_uid'])) {
			session::$data['checkout']['shopping_cart'] = new ent_shopping_cart($_GET['cart_uid']);
		}

		if (empty($shopping_cart->data['id']) || $_GET['public_key'] != $shopping_cart->data['public_key']) {
			http_response_code(404);
			include 'app://frontend/pages/error_document.inc.php';
			return;
		}

	} else {
		session::$data['checkout']['shopping_cart'] = new ent_shopping_cart(session::$data['cart_uid']);
	}

	$mod_checkout = new mod_checkout();

	$shopping_cart = &session::$data['checkout']['shopping_cart'];
	$shopping_cart->data['processable'] = false; // Whether or not it is allowed to be processed in checkout/process
	$shopping_cart->data['express_checkout'] = $mod_checkout->options($shopping_cart);

	functions::draw_lightbox();

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout.inc.php');
	echo $_page->render();
