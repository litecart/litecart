<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/account/edit.inc.php
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
	}

	if (isset($_POST['save_account'])) {

		try {

			if (isset($_POST['email'])) {
				$_POST['email'] = strtolower($_POST['email']);
			}

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."customers
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

				if (!functions::password_check_strength($_POST['password'])) {
					throw new Exception(t('error_password_not_strong_enough', 'The password is not strong enough'));
				}
			}

			if (isset($_POST[$field])) {
				$customer->data['email'] = $_POST['email'];
			}

			if (!empty($_POST['new_password'])) {
				$customer->set_password($_POST['new_password']);
			}

			$customer->data['password_reset_token'] = '';
			$customer->data['date_expire_sessions'] = date('Y-m-d H:i:s');
			$customer->save();

			customer::load($customer->data['id']);

			session::regenerate_id();
			session::$data['customer_security_timestamp'] = strtotime($customer->data['date_expire_sessions']);

			customer::log([
				'type' => 'edit_account_security',
				'description' => 'User edited account security details',
				'expires_at' => strtotime('+12 months'),
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
				'expires_at' => strtotime('+12 months'),
			]);

			notices::add('success', t('success_changes_saved', 'Changes saved'));
			reload();
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'. settings::get('template') .'/pages/account/edit.inc.php');
	echo $_page->render();
