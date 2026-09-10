<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/pages/account/edit.inc.php
	*/

	header('X-Robots-Tag: noindex');
	document::$head_tags['noindex'] = '<meta name="robots" content="noindex">';

	customer::require_login();

	document::$title[] = t('title_edit_account', 'Edit Account');

	if (!settings::get('accounts_enabled')) {
		echo t('error_accounts_are_disabled', 'Accounts are disabled');
		return;
	}

	breadcrumbs::add(t('title_account', 'Account'), '');
	breadcrumbs::add(t('title_edit_account', 'Edit Account'), document::ilink('account/edit'));

	$customer = new ent_customer(customer::$data['id']);

	if (!$_POST) {
		$_POST = $customer->data;
		if (!empty($_POST['company'])) {
			$_POST['customer']['type'] = 'business';
		} else {
			$_POST['customer']['type'] = 'individual';
		}
	}

	if (isset($_POST['save_account'])) {

		try {

			// TOTP enroll/confirm/disable
			if (!empty($_POST['totp_setup']) || !empty($_POST['totp_confirm']) || !empty($_POST['totp_disable'])) {

				if (!empty($_POST['totp_setup'])) {
					session::$data['totp_pending_secret'] = f::totp_generate_secret();
					reload();
					exit;
				}

				if (!empty($_POST['totp_confirm'])) {

					if (empty(session::$data['totp_pending_secret'])) {
						throw new Exception(t('error_totp_setup_expired', 'TOTP setup session expired. Please try again.'));
					}

					if (empty($_POST['totp_code']) || !f::totp_verify_code(session::$data['totp_pending_secret'], $_POST['totp_code'])) {
						throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
					}

					database::query(
						"update ". DB_PREFIX ."customers
						set totp_secret = '". database::input(session::$data['totp_pending_secret']) ."',
							two_factor_auth = 1
						where id = ". (int)$customer->data['id'] ."
						limit 1;"
					);

					unset(session::$data['totp_pending_secret']);
					$customer = new ent_customer(customer::$data['id']);
					notices::add('success', t('success_totp_enabled', 'TOTP has been enabled'));
					reload();
					exit;
				}

				if (!empty($_POST['totp_disable'])) {

					if (empty($_POST['totp_disable_password']) || !password_verify($_POST['totp_disable_password'], customer::$data['password_hash'])) {
						throw new Exception(t('error_wrong_password', 'Wrong password'));
					}

					database::query(
						"update ". DB_PREFIX ."customers
						set totp_secret = null
						where id = ". (int)$customer->data['id'] ."
						limit 1;"
					);

					unset(session::$data['totp_pending_secret']);
					$customer = new ent_customer(customer::$data['id']);
					notices::add('success', t('success_totp_disabled', 'TOTP has been disabled'));
					reload();
					exit;
				}
			}

			if (isset($_POST['email'])) {
				$_POST['email'] = strtolower($_POST['email']);
			}

			if (database::query(
				"select id from ". DB_PREFIX ."customers
				where email = '". database::input($_POST['email']) ."'
				and id != ". (int)$customer->data['id'] ."
				limit 1;"
			)->num_rows) {
				throw new Exception(t('error_email_already_registered', 'The email address already exists in our customer database'));
			}

			if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				throw new Exception(t('error_must_provide_email', 'You must provide an email address'));
			}

			if (!password_verify($_POST['password'], customer::$data['password_hash'])) {
				throw new Exception(t('error_wrong_password', 'Wrong password'));
			}

			if (!empty($_POST['new_password'])) {

				if (empty($_POST['confirmed_password'])) {
					throw new Exception(t('error_must_confirm_password', 'You must confirm your password'));
				}

				if (isset($_POST['new_password']) && isset($_POST['confirmed_password']) && $_POST['new_password'] != $_POST['confirmed_password']) {
					throw new Exception(t('error_passwords_missmatch', 'The passwords did not match.'));
				}

				if (!f::password_check_strength($_POST['new_password'])) {
					throw new Exception(t('error_password_not_strong_enough', 'The password is not strong enough'));
				}
			}

			if (isset($_POST['email'])) {
				$customer->data['email'] = $_POST['email'];
			}

			if (!empty($_POST['new_password'])) {
				$customer->set_password($_POST['new_password']);
			}

			$customer->data['sessions_expiry'] = date('Y-m-d H:i:s');
			$customer->save();

			customer::load($customer->data['id']);

			session::regenerate_id();
			security::$data['timestamp'] = strtotime($customer->data['sessions_expiry']);

			customer::log([
				'type' => 'edit_account_security',
				'description' => 'User edited account security details',
				'expires_at' => strtotime('+1 month'),
			]);

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	if (isset($_POST['save_details'])) {

		try {

			if (!isset($_POST['different_shipping_address'])) {
				$_POST['different_shipping_address'] = 0;
			}

			if (!isset($_POST['newsletter'])) {
				$_POST['newsletter'] = 0;
			}

			if (empty($_POST['firstname'])) {
				throw new Exception(t('error_must_provide_firstname', 'You must provide a first name'));
			}

			if (empty($_POST['lastname'])) {
				throw new Exception(t('error_must_provide_lastname', 'You must provide a last name'));
			}

			if (empty($_POST['address1'])) {
				throw new Exception(t('error_must_provide_address1', 'You must provide an address'));
			}

			if (empty($_POST['city'])) {
				throw new Exception(t('error_must_provide_city', 'You must provide a city'));
			}

			if (empty($_POST['postcode']) && !empty($_POST['country_code']) && reference::country($_POST['country_code'])->postcode_format) {
				throw new Exception(t('error_must_provide_postcode', 'You must provide a postcode'));
			}

			if (empty($_POST['country_code'])) {
				throw new Exception(t('error_must_select_country', 'You must select a country'));
			}

			if (empty($_POST['zone_code']) && settings::get('customer_field_zone') && reference::country($_POST['country_code'])->zones) {
				throw new Exception(t('error_must_select_zone', 'You must select a zone'));
			}

			if (!empty($_POST['different_shipping_address']) && settings::get('customer_shipping_address')) {

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

				if (empty($_POST['shipping_address']['postcode']) &&!empty($_POST['shipping_address']['country_code'])) {
					if (reference::country($_POST['shipping_address']['country_code'])->postcode_format) {
						throw new Exception(t('error_must_provide_postcode', 'You must provide a postcode'));
					}
				}

				if (empty($_POST['shipping_address']['country_code'])) {
					throw new Exception(t('error_must_select_country', 'You must select a country'));
				}

				if (empty($_POST['shipping_address']['zone_code']) && settings::get('customer_field_zone') && reference::country($_POST['shipping_address']['country_code'])->zones) {
					throw new Exception(t('error_must_select_zone', 'You must select a zone'));
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
				'different_shipping_address',
				'newsletter',
			] as $field) {
				if (isset($_POST[$field])) {
					$customer->data[$field] = $_POST[$field];
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
				if (isset($_POST['shipping_address'][$field]) && !empty($_POST['different_shipping_address'])) {
					$customer->data['shipping_address'][$field] = $_POST['shipping_address'][$field];
				} else {
					$customer->data['shipping_address'][$field] = '';
				}
			}

			$customer->save();
			customer::$data = $customer->data;

			customer::log([
				'type' => 'edit_account_details',
				'description' => 'User edited account details',
				'expires_at' => strtotime('+1 month'),
			]);

			// Headless requests
			if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
				header('Content-Type: application/json;charset='. mb_http_output());
				echo f::format_json(['success' => true]);
				exit;
			}

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {

			http_response_code(400);

			// Headless requests
			if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
				header('Content-Type: application/json;charset='. mb_http_output());
				echo f::format_json(['error' => $e->getMessage()]);
				exit;
			}

			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'. settings::get('template') .'/pages/account/edit.inc.php');

	$_page->snippets = [
		'id' => $customer->data['id'],
		'email' => $customer->data['email'],
		'firstname' => $customer->data['firstname'],
		'lastname' => $customer->data['lastname'],
		'company' => $customer->data['company'],
		'tax_id' => $customer->data['tax_id'],
		'address1' => $customer->data['address1'],
		'address2' => $customer->data['address2'],
		'postcode' => $customer->data['postcode'],
		'city' => $customer->data['city'],
		'country_code' => $customer->data['country_code'],
		'zone_code' => $customer->data['zone_code'],
		'phone' => $customer->data['phone'],
		'different_shipping_address' => $customer->data['different_shipping_address'],
		'newsletter' => $customer->data['newsletter'],
		'shipping_address' => $customer->data['shipping_address'],
	];

	// Headless requests
	if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
		header('Content-Type: application/json;charset='. mb_http_output());
		echo f::format_json($_page->snippets);
		exit;
	}

	echo $_page->render();
