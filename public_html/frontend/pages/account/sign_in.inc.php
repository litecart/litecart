<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/account/sign_in.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$title[] = language::translate('title_sign_in', 'Sign In');

	breadcrumbs::add(language::translate('title_sign_in', 'Sign In'), document::ilink('account/sign_in'));

	if (!settings::get('accounts_enabled')) {
		echo language::translate('error_accounts_are_disabled', 'Accounts are disabled');
		return;
	}

	if (!$_POST) {
		$_POST['email'] = customer::$data['email'];
	}

	if (!empty(customer::$data['id'])) {
		notices::add('notices', language::translate('text_already_logged_in', 'You are already logged in'));
	}

	if (!empty($_POST['sign_in'])) {

		try {

			if (empty($_POST['email'])) {
				throw new Exception(language::translate('error_must_enter_your_email_Address', 'You must enter your email address'));
			}

			if (empty($_POST['password'])) {
				throw new Exception(language::translate('error_must_enter_your_password', 'You must enter your password'));
			}

			$customer = database::query(
				"select * from ". DB_TABLE_PREFIX ."customers
				where lower(email) = '". database::input(strtolower($_POST['email'])) ."'
				limit 1;"
			)->fetch();

			if (!$customer) {
				throw new Exception(language::translate('error_email_not_found_in_database', 'The email does not exist in our database'));
			}

			if (!$customer['status']) {
				throw new Exception(language::translate('error_customer_account_disabled_or_not_activated', 'The customer account is disabled or not activated'));
			}

			if ($customer['date_blocked_until'] && strtotime($customer['date_blocked_until']) > time()) {
				throw new Exception(sprintf(language::translate('error_account_is_blocked', 'The account is blocked until %s'), language::strftime('datetime', $customer['date_blocked_until'])));
			}

			if (!password_verify($_POST['password'], $customer['password_hash'])) {

				if (++$customer['login_attempts'] < 3) {

					database::query(
						"update ". DB_TABLE_PREFIX ."customers
						set login_attempts = login_attempts + 1
						where id = ". (int)$customer['id'] ."
						limit 1;"
					);

					throw new Exception(language::translate('error_wrong_email_password_combination', 'Wrong combination of email and password or the account does not exist'));

				} else {

					database::query(
						"update ". DB_TABLE_PREFIX ."customers
						set login_attempts = 0,
						date_blocked_until = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
						where id = ". (int)$customer['id'] ."
						limit 1;"
					);

					throw new Exception(strtr(language::translate('error_this_account_has_been_temporarily_blocked_n_minutes', 'This account has been temporarily blocked %n minutes'), ['%n' => 15, '%d' => 15]));
				}
			}

			if (password_needs_rehash($customer['password_hash'], PASSWORD_DEFAULT)) {
				database::query(
					"update ". DB_TABLE_PREFIX ."customers
					set password_hash = '". database::input(password_hash($_POST['password'], PASSWORD_DEFAULT)) ."'
					where id = ". (int)$customer['id'] ."
					limit 1;"
				);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."customers
				set last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
					last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
					last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
					login_attempts = 0,
					num_logins = num_logins + 1,
					date_login = '". date('Y-m-d H:i:s') ."'
				where id = ". (int)$customer['id'] ."
				limit 1;"
			);

			customer::load($customer['id']);

			session::$data['customer_security_timestamp'] = time();
			session::regenerate_id();

			if (!empty($_POST['remember_me'])) {
				$checksum = sha1($customer['email'] . $customer['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : ''));
				header('Set-Cookie: customer_remember_me='. $customer['email'] .':'. $checksum .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; HttpOnly; SameSite=Lax', false);
			}

			notices::add('success', strtr(language::translate('success_logged_in_as_user', 'You are now logged in as %firstname %lastname.'), [
				'%email' => customer::$data['email'],
				'%firstname' => customer::$data['firstname'],
				'%lastname' => customer::$data['lastname'],
			]));

			if (!empty($_POST['redirect_url'])) {
				$redirect_url = new ent_link($_POST['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('f:');
			}

			header('Location: '. $redirect_url);
			exit;

		} catch (Exception $e) {
			http_response_code(401); // Troublesome with HTTP Auth (e.g. .htpasswd)
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/sign_in.inc.php');
	echo $_page->render();
