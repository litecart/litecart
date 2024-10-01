<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/account/sign_up.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$title[] = language::translate('title_sign_up', 'Sign Up');

	breadcrumbs::add(language::translate('title_sign_up', 'Sign Up'), document::ilink('account/sign_up'));

	if (!settings::get('accounts_enabled')) {
		echo language::translate('error_accounts_are_disabled', 'Accounts are disabled');
		return;
	}

	if (!$_POST) {
		$_POST = customer::$data;
	}

	if (!empty(customer::$data['id'])) {
		notices::add('errors', language::translate('error_already_logged_in', 'You are already logged in'));
	}

	if (!empty($_POST['sign_up'])) {

		try {

			if (isset($_POST['email'])) {
				$_POST['email'] = strtolower($_POST['email']);
			}

			if (empty($_POST['newsletter'])) $_POST['newsletter'] = 0;

			if (empty($_POST['email'])) {
				throw new Exception(language::translate('error_missing_email', 'You must enter an email address.'));
			}

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."customers
				where email = '". database::input($_POST['email']) ."'
				limit 1;"
			)->num_rows) {
				throw new Exception(language::translate('error_email_already_registered', 'The email address already exists in our customer database. Please login or select a different email address.'));
			}

			if (empty($_POST['password'])) {
				throw new Exception(language::translate('error_missing_password', 'You must enter a password.'));
			}

			if (!functions::password_check_strength($_POST['password'])) {
				throw new Exception(language::translate('error_password_not_strong_enough', 'The password is not strong enough'));
			}

			if (empty($_POST['confirmed_password'])) {
				throw new Exception(language::translate('error_missing_confirmed_password', 'You must confirm your password'));
			}

			if ($_POST['confirmed_password'] != $_POST['password']) {
				throw new Exception(language::translate('error_passwords_missmatch', 'The passwords did not match'));
			}

			if (empty($_POST['firstname'])) {
				throw new Exception(language::translate('error_missing_firstname', 'You must enter a first name.'));
			}

			if (empty($_POST['lastname'])) {
				throw new Exception(language::translate('error_missing_lastname', 'You must enter a last name.'));
			}

				//if (empty($_POST['address1'])) {
				//  throw new Exception(language::translate('error_missing_address1', 'You must enter an address.'));
				//}

				//if (empty($_POST['city'])) {
				//  throw new Exception(language::translate('error_missing_city', 'You must enter a city.'));
				//}

				//if (empty($_POST['postcode']) && !empty($_POST['country_code']) && reference::country($_POST['country_code'])->postcode_format){
				//  throw new Exception(language::translate('error_missing_postcode', 'You must enter a postcode.'));
				//}

			if (empty($_POST['country_code'])) {
				throw new Exception(language::translate('error_missing_country', 'You must select a country.'));
			}

			if (empty($_POST['zone_code']) && settings::get('customer_field_zone') && reference::country($_POST['country_code'])->zones) {
				throw new Exception(language::translate('error_missing_zone', 'You must select a zone.'));
			}

			if (!empty($_POST['tax_id']) && reference::country($_POST['country_code'])->postcode_format) {
				if (!preg_match('#'. reference::country($_POST['country_code'])->tax_id_format .'#i', $_POST['tax_id'])) {
					throw new Exception(language::translate('error_invalid_tax_id_format', 'Invalid tax id format'));
				}
			}

			if (!empty($_POST['postcode']) && reference::country($_POST['country_code'])->postcode_format) {
				if (!preg_match('#'. reference::country($_POST['country_code'])->postcode_format .'#i', $_POST['postcode'])) {
					throw new Exception(language::translate('error_invalid_postcode_format', 'Invalid postcode format'));
				}
			}

			if (settings::get('captcha_enabled') && !functions::captcha_validate('sign_up')) {
				throw new Exception(language::translate('error_invalid_captcha', 'Invalid CAPTCHA given'));
			}

			$mod_customer = new mod_customer();
			$result = $mod_customer->validate($_POST);
			if (!empty($result['error'])) throw new Exception($result['error']);



			$address = new ent_customer_address();

			foreach ([
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
				if (isset($_POST[$field])) {
					$address->data[$field] = $_POST[$field];
				}
			}

			$address->save();

			$customer = new ent_customer();

			$customer->data['status'] = 1;
			$customer->data['default_billing_address_id'] = $address->data['id'];
			$customer->data['default_shipping_address_id'] = $address->data['id'];

			foreach ([
				'email',
				'newsletter',
			] as $field) {
				if (isset($_POST[$field])) {
					$customer->data[$field] = $_POST[$field];
				}
			}

			$customer->set_password($_POST['password']);

			$customer->save();

			database::query(
				"update ". DB_TABLE_PREFIX ."customers
				set last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
					last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
					last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."'
				where id = ". (int)$customer->data['id'] ."
				limit 1;"
			);

			customer::load($customer->data['id']);

			$aliases = [
				'%store_name' => settings::get('store_name'),
				'%store_link' => document::ilink(''),
				'%customer_id' => $customer->data['id'],
				'%customer_firstname' => $customer->data['firstname'],
				'%customer_lastname' => $customer->data['lastname'],
				'%customer_email' => $customer->data['email'],
			];

			$subject = language::translate('email_subject_customer_account_created', 'Customer Account Created');
			$message = strtr(language::translate('email_account_created', "Welcome %customer_firstname %customer_lastname to %store_name!\r\n\r\nYour account has been created. You can now make purchases in our online store and keep track of history.\r\n\r\nLogin using your email address %customer_email.\r\n\r\n%store_name\r\n\r\n%store_link"), $aliases);

			$email = new ent_email();
			$email->add_recipient($_POST['email'], $_POST['firstname'] .' '. $_POST['lastname'])
						->set_subject($subject)
						->add_body($message)
						->send();

			notices::add('success', language::translate('success_your_customer_account_has_been_created', 'Your customer account has been created.'));
			header('Location: '. document::ilink(''));
			exit;

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/sign_up.inc.php');

	$_page->snippets = [
		'consent' => null,
	];

	if ($privacy_policy_id = settings::get('privacy_policy')) {

		$aliases = [
			'%privacy_policy_link' => document::href_ilink('information', ['page_id' => $privacy_policy_id]),
		];

		$_page->snippets['consent'] = strtr(language::translate('consent:privacy_policy', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and I consent.'), $aliases);
	}

	echo $_page->render();
