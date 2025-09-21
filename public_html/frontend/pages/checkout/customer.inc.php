<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/checkout/customer.inc.php
	 */

	header('X-Robots-Tag: noindex');


	if (settings::get('catalog_only_mode')) {
		return;
	}

	if (empty(session::$data['checkout']['order'])) {
		return;
	}

	$order = &session::$data['checkout']['order'];

	if (empty($order->data['items'])) {
		redirect(document::ilink('shopping_cart'));
		exit;
	}

	if (file_get_contents('php://input') == '') {
		$_POST['customer'] = $order->data['customer'];

		if (!empty($_POST['company'])) {
			$_POST['customer']['type'] = 'business';
		} else {
			$_POST['customer']['type'] = 'individual';
		}
	}

	if (!empty($_POST['autosave']) || !empty($_POST['save_customer_details'])) {

		if (isset($_POST['customer']['email'])) {
			$_POST['customer']['email'] = strtolower($_POST['customer']['email']);
		}

		try {

			if (empty($_POST['customer']['email']) || !filter_var($_POST['customer']['email'], FILTER_VALIDATE_EMAIL)) {
				throw new Exception(t('error_must_provide_email', 'You must provide an email address'));
			}

			if (settings::get('accounts_enabled') && !empty($_POST['sign_up'])) {

				if (!functions::validate_email($_POST['customer']['email'])) {
					throw new Exception(t('error_invalid_email', 'The email address is invalid'));
				}

				if (!database::query(
					"select id from ". DB_TABLE_PREFIX ."customers
					where email = '". database::input($_POST['customer']['email']) ."'
					limit 1;"
				)->num_rows) {

					if (empty($_POST['password'])) {
						throw new Exception(t('error_must_provide_password', 'You must provide a password'));
					}

					if (!isset($_POST['confirmed_password']) || $_POST['password'] != $_POST['confirmed_password']) {
						throw new Exception(t('error_passwords_do_not_match', 'The passwords do not match'));
					}
				}
			}

			if (empty($_POST['customer']['firstname'])) {
				throw new Exception(t('error_must_provide_firstname', 'You must provide a first name'));
			}

			if (empty($_POST['customer']['lastname'])) {
				throw new Exception(t('error_must_provide_lastname', 'You must provide a last name'));
					}

			if (empty($_POST['customer']['address1'])) {
				throw new Exception(t('error_must_provide_address1', 'You must provide an address'));
				}

			if (empty($_POST['customer']['city'])) {
				throw new Exception(t('error_must_provide_city', 'You must provide a city'));
			}

			if (empty($_POST['customer']['country_code'])) {
				throw new Exception(t('error_must_provide_country', 'You must provide a country'));
		}

			if (empty($_POST['customer']['postcode'])) {
				throw new Exception(t('error_must_provide_postcode', 'You must provide a postcode'));
			}

			if (empty($_POST['customer']['phone'])) {
				throw new Exception(t('error_must_provide_phone', 'You must provide a phone number'));
			}

			if (isset($_POST['customer']['email'])) {
				$_POST['customer']['email'] = strtolower($_POST['customer']['email']);
			}

			if (!empty($_POST['different_shipping_address'])) {

				if (empty($_POST['shipping_address']['firstname'])) {
					throw new Exception(t('error_must_provide_firstname', 'You must provide a first name'));
				}

				if (empty($_POST['shipping_address']['lastname'])) {
					throw new Exception(t('error_must_provide_lastname', 'You must provide a last name'));
				}

				if (empty($_POST['shipping_address']['address1'])) {
					throw new Exception(t('error_must_provide_address1', 'You must provide an address'));
				}

				if (empty($_POST['shipping_address']['city'])) {
					throw new Exception(t('error_must_provide_city', 'You must provide a city'));
				}

				if (empty($_POST['shipping_address']['postcode'])) {
					throw new Exception(t('error_must_provide_postcode', 'You must provide a postcode'));
				}

				if (empty($_POST['shipping_address']['country_code'])) {
					throw new Exception(t('error_must_provide_country', 'You must provide a country'));
				}

				if (empty($_POST['shipping_address']['phone'])) {
					throw new Exception(t('error_must_provide_phone', 'You must provide a phone number'));
				}
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

			$result = (new mod_customer)->validate($_POST['customer']);

			if (!empty($result['error'])) {
				throw new Exception($result['error']);
			}

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
		] as $field) {

			if (settings::get('customer_shipping_address') && !empty($order->data['different_shipping_address'])) {

				if (isset($_POST['shipping_address'][$field])) {
						$order->data['customer']['shipping_address'][$field] = $_POST['shipping_address'][$field];
				} else {
						$order->data['customer']['shipping_address'][$field] = null;
				}

			} else {

				if (isset($_POST['customer'][$field])) {
						$order->data['customer']['shipping_address'][$field] = $_POST['customer'][$field];
				} else {
						$order->data['customer']['shipping_address'][$field] = null;
				}
			}
		}

			// Create customer account
			if (settings::get('accounts_enabled') && empty($order->data['customer']['id']) && !empty($order->data['customer']['email'])) {
				if (!empty($_POST['sign_up'])) {

					if (!database::query(
						"select id from ". DB_TABLE_PREFIX ."customers
						where email = '". database::input($_POST['customer']['email']) ."'
						limit 1;"
					)->num_rows) {

						$customer = new ent_customer();
						$customer->data = array_replace($customer->data, array_intersect_key($order->data['customer'], $customer->data));
						$customer->set_password($_POST['password']);
						$customer->save();

						$customer->send_email();

						notices::add('success', t('success_account_has_been_created', 'A customer account has been created that will let you keep track of orders.'));

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

			// Save details to account
			if (customer::check_login() && !empty($_POST['save_to_account'])) {
				$customer = new ent_customer(customer::$data['id']);
				$customer->data = array_replace_recursive(array_intersect_key($order->data['customer'], $customer->data));
				$customer->save();
			}

			if (!empty($_POST['newsletter'])) {
				database::query(
					"insert into ". DB_TABLE_PREFIX ."newsletter_recipients (
						subscribed, email, ip_address, hostname, user_agent, created_at
					)
					values (
						1,
						'". database::input($_POST['customer']['email']) ."',
						'". database::input($_SERVER['REMOTE_ADDR']) ."',
						'". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
						'". database::input($_SERVER['HTTP_USER_AGENT']) ."',
						'". date('Y-m-d H:i:s') ."')
					on duplicate key update
						subscribed = 1,
						ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
						hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
						user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
						updated_at = '". date('Y-m-d H:i:s') ."';"
				);
			}

			redirect(document::ilink('checkout/index'));
			exit;

		} catch(Exception $e) {
			die($e->getMessage());
			notices::add('errors', $e->getMessage());
		}
	}

	$account_exists = false;
	if (settings::get('accounts_enabled')) {
		if (!$order->data['customer']['id'] && !$order->data['customer']['email']) {
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
		'privacy_policy' => settings::get('privacy_policy'),
	];

	// Determine if we have both a privacy policy
	if ($privacy_policy_id = settings::get('privacy_policy')) {
		$_page->snippets['privacy_policy_consent'] = t('consent:privacy_policy', 'I have read the <a href="{privacy_policy_link}" target="_blank">Privacy Policy</a> and I consent.');

		// Set link to privacy policy
		$_page->snippets['privacy_policy_consent'] = strtr($_page->snippets['privacy_policy_consent'], [
			'{privacy_policy_link}' => document::href_ilink('information', ['page_id' => $privacy_policy_id]),
		]);
	}

	echo $_page;

	// Don't process layout if this is an ajax request
	if (is_ajax_request()) {
		exit;
	}
