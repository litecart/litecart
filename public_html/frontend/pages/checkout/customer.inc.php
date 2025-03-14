<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout/customer.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$layout = 'checkout';

	if (settings::get('catalog_only_mode')) {
		return;
	}

	if (empty(session::$data['checkout']['order'])) {
		return;
	}

	$order = &session::$data['checkout']['order'];

	if (empty($order->data['items'])) {
		return;
	}

	if (file_get_contents('php://input') == '') {
		foreach ($order->data['customer'] as $key => $value) {
			$_POST['customer'][$key] = $value;
		}
	}

	if (!empty($_POST['autosave']) || !empty($_POST['save_customer_details'])) {

		if (isset($_POST['customer']['email'])) {
			$_POST['customer']['email'] = strtolower($_POST['customer']['email']);
		}

		if (!isset($_POST['different_shipping_address'])) {
			$_POST['different_shipping_address'] = 0;
		}

		if (!isset($_POST['customer']['zone_code'])) {
			$_POST['customer']['zone_code'] = '';
		}

		if (!isset($_POST['shipping_address']['zone_code'])) {
			$_POST['shipping_address']['zone_code'] = '';
		}

		if (empty($_POST['customer']['type']) || $_POST['customer']['type'] == 'individual') {
			$_POST['customer']['company'] = '';
			$_POST['customer']['tax_id'] = '';
		}

		// Validate
		if (!empty($_POST['save_customer_details'])) { // <-- Button is pressed
			if (settings::get('accounts_enabled') && !empty($_POST['sign_up'])) {

				try {

					if (empty($_POST['customer']['email']) || !filter_var($_POST['customer']['email'], FILTER_VALIDATE_EMAIL)) {
						throw new Exception(language::translate('error_missing_email', 'You must enter an email address'));
					}

					if (!functions::validate_email($_POST['customer']['email'])) {
						throw new Exception(language::translate('error_invalid_email', 'The email address is invalid'));
					}

					if (!database::query(
						"select id from ". DB_TABLE_PREFIX ."customers
						where email = '". database::input($_POST['customer']['email']) ."'
						limit 1;"
					)->num_rows) {

						if (empty($_POST['password'])) {
							throw new Exception(language::translate('error_missing_password', 'You must enter a password'));
						}

						if (!isset($_POST['confirmed_password']) || $_POST['password'] != $_POST['confirmed_password']) {
							throw new Exception(language::translate('error_passwords_missmatch', 'The passwords did not match.'));
						}
					}

					$mod_customer = new mod_customer();
					$result = $mod_customer->validate($_POST['customer']);

					if (!empty($result['error'])) {
						throw new Exception($result['error']);
					}

				} catch(Exception $e) {
					notices::add('errors', $e->getMessage());
				}
			}
		}

		// Customer
		foreach ([
			'tax_id',
			'company',
			'firstname',
			'lastname',
			'address1',
			'address2',
			'postcode',
			'city',
			'country_code',
			'zone_code',
			'phone',
			'email',
			'different_shipping_address',
		] as $field) {
			if (isset($_POST['customer'][$field])) {
				$order->data['customer'][$field] = $_POST['customer'][$field];
			}
		}

		// Shipping address
		foreach ([
			'tax_id',
			'company',
			'firstname',
			'lastname',
			'address1',
			'address2',
			'postcode',
			'city',
			'country_code',
			'zone_code',
			'phone',
		] as $field) {
			if (settings::get('customer_shipping_address') && !empty($order->data['different_shipping_address'])) {
				if (isset($_POST['shipping_address'][$field])) {
					$order->data['shipping_address'][$field] = $_POST['shipping_address'][$field];
				} else {
					$order->data['shipping_address'][$field] = '';
				}
			} else {
				if (isset($_POST['customer'][$field])) {
					$order->data['shipping_address'][$field] = $_POST['customer'][$field];
				} else {
					$order->data['shipping_address'][$field] = '';
				}
			}
		}

		if (empty(notices::$data['errors'])) {

			// Save details to account
			if (!empty(customer::$data['id']) && !empty($_POST['save_to_account'])) {
				$customer = new ent_customer(customer::$data['id']);
				$customer->data = array_replace_recursive(array_intersect_key($order->data['customer'], $customer->data));
				$customer->save();
			}

			// Create customer account
			if (settings::get('accounts_enabled') && empty($order->data['customer']['id']) && !empty($order->data['customer']['email'])) {
				if (settings::get('register_guests') || !empty($_POST['sign_up'])) {

					if (!database::query(
						"select id from ". DB_TABLE_PREFIX ."customers
						where email = '". database::input($_POST['customer']['email']) ."'
						limit 1;"
					)->num_rows) {

						$customer = new ent_customer();
						$customer->data = array_replace($customer->data, array_intersect_key($order->data['customer'], $customer->data));

						$customer->set_password($_POST['password']);

						$customer->save();

						$aliases = [
							'%store_name' => settings::get('store_name'),
							'%store_link' => document::ilink(''),
							'%customer_firstname' => $_POST['customer']['firstname'],
							'%customer_lastname' => $_POST['customer']['lastname'],
							'%customer_email' => $_POST['customer']['email'],
							'%customer_password' => $_POST['password']
						];

						$subject = language::translate('email_subject_customer_account_created', 'Customer Account Created');
						$message = strtr(language::translate('email_account_created', "Welcome %customer_firstname %customer_lastname to %store_name!\r\n\r\nYour account has been created. You can now make purchases in our online store and keep track of history.\r\n\r\nLogin using your email address %customer_email.\r\n\r\n%store_name\r\n\r\n%store_link"), $aliases);

						$email = new ent_email();
						$email->add_recipient($_POST['customer']['email'], $_POST['customer']['firstname'] .' '. $_POST['customer']['lastname'])
									->set_subject($subject)
									->add_body($message)
									->send();

						notices::add('success', language::translate('success_account_has_been_created', 'A customer account has been created that will let you keep track of orders.'));

						database::query(
							"update ". DB_TABLE_PREFIX ."customers
							set last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
								last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
								last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."'
							where id = ". (int)$customer->data['id'] ."
							limit 1;"
						);

						customer::load($customer->data['id']);
					}
				}
			}

			if (!empty($_POST['newsletter'])) {
				database::query(
					"insert ignore into ". DB_TABLE_PREFIX ."newsletter_recipients
					(email, date_created)
					values ('". database::input($_POST['customer']['email']) ."', '". date('Y-m-d H:i:s') ."');"
				);
			}
		}
	}

	$account_exists = false;
	if (settings::get('accounts_enabled')) {
		if (empty($order->data['customer']['id']) && !empty($order->data['customer']['email'])) {
			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."customers
				where email = '". database::input($order->data['customer']['email']) ."'
				limit 1;"
			)->num_rows) {
				$account_exists = true;
			}
		}
	}

	$subscribed_to_newsletter = false;

	if (!empty($order->data['customer']['email'])) {
		if (database::query(
			"select id from ". DB_TABLE_PREFIX ."newsletter_recipients
			where lower(email) = lower('". database::input($order->data['customer']['email']) ."')
			limit 1;"
		)->num_rows) {
			$subscribed_to_newsletter = true;
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/checkout/customer.inc.php');

	$_page->snippets = [
		'order' => $order->data,
		'account_exists' => $account_exists,
		'subscribed_to_newsletter' => $subscribed_to_newsletter,
	];

	echo $_page;

	// Don't process layout if this is an ajax request
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		exit;
	}
