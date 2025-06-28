<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/account/sign_in.inc.php
	 */

	header('X-Robots-Tag: noindex');

	document::$title[] = t('title_sign_in', 'Sign In');

	breadcrumbs::add(t('title_sign_in', 'Sign In'), document::ilink('account/sign_in'));

	if (!settings::get('accounts_enabled')) {
		echo t('error_accounts_are_disabled', 'Accounts are disabled');
		return;
	}

	if (!$_POST) {
		$_POST['email'] = customer::$data['email'];
	}

	if (customer::check_login()) {
		notices::add('notices', t('text_already_logged_in', 'You are already logged in'));
	}

	if (!empty($_POST['sign_in'])) {

		try {

			if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				throw new Exception(t('error_must_provide_email', 'You must provide an email address'));
			}

			if (empty($_POST['password'])) {
				throw new Exception(t('error_must_provide_password', 'You must provide a password'));
			}

			$customer = database::query(
				"select * from ". DB_TABLE_PREFIX ."customers
				where lower(email) = '". database::input(strtolower($_POST['email'])) ."'
				limit 1;"
			)->fetch(function($customer){
				$customer['known_ips'] = preg_split('#\s*,\s*#', $customer['known_ips'], -1, PREG_SPLIT_NO_EMPTY);
				return $customer;
			});

			if (!$customer) {
				throw new Exception(t('error_email_not_found_in_database', 'The email does not exist in our database'));
			}

			if (!$customer['status']) {
				throw new Exception(t('error_customer_account_disabled_or_not_activated', 'The customer account is disabled or not activated'));
			}

			if ($customer['blocked_until'] && strtotime($customer['blocked_until']) > time()) {
				throw new Exception(sprintf(t('error_account_is_blocked', 'The account is blocked until %s'), functions::datetime_format('datetime', $customer['blocked_until'])));
			}

			if (!password_verify($_POST['password'], $customer['password_hash'])) {

				if (++$customer['login_attempts'] < 3) {

					database::query(
						"update ". DB_TABLE_PREFIX ."customers
						set login_attempts = login_attempts + 1
						where id = ". (int)$customer['id'] ."
						limit 1;"
					);

					throw new Exception(t('error_wrong_email_password_combination', 'Wrong combination of email and password or the account does not exist'));

				} else {

					database::query(
						"update ". DB_TABLE_PREFIX ."customers
						set login_attempts = 0,
						blocked_until = '". date('Y-m-d H:i:00', strtotime('+15 minutes')) ."'
						where id = ". (int)$customer['id'] ."
						limit 1;"
					);

					throw new Exception(strtr(t('error_this_account_has_been_temporarily_blocked_n_minutes', 'This account has been temporarily blocked %n minutes'), ['%n' => 15, '%d' => 15]));
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

			array_unshift($customer['known_ips'], $_SERVER['REMOTE_ADDR']);
			$customer['known_ips'] = array_unique($customer['known_ips']);

			if (count($customer['known_ips']) > 5) {
				array_pop($customer['known_ips']);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."customers
				set known_ips = '". database::input(implode(',', $administrator['known_ips'])) ."'
					last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
					last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
					last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
					last_login = '". date('Y-m-d H:i:s') ."'
					login_attempts = 0,
					total_logins = total_logins + 1,
				where id = ". (int)$customer['id'] ."
				limit 1;"
			);

			customer::load($customer['id']);

			session::$data['customer_security_timestamp'] = time();
			session::regenerate_id();

			if (!empty($_POST['remember_me'])) {
				$checksum = sha1($customer['email'] . $customer['password_hash'] . $_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ?: ''));
				header('Set-Cookie: customer_remember_me='. $customer['email'] .':'. $checksum .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; HttpOnly; SameSite=Lax', false);
			}

			notices::add('success', strtr(t('success_logged_in_as_user', 'You are now logged in as %firstname %lastname.'), [
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

			redirect($redirect_url);
			exit;

		} catch (Exception $e) {
			http_response_code(401); // Troublesome with HTTP Auth (e.g. .htpasswd)
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/sign_in.inc.php');
	echo $_page->render();
