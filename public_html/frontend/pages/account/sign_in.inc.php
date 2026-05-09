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
				where email = '". database::input(strtolower($_POST['email'])) ."'
				limit 1;"
			)->fetch();

			if (!$customer) {
				// Dummy password_verify to prevent timing-based user enumeration
				password_verify($_POST['password'], '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234');
				throw new Exception(t('error_wrong_email_password_combination', 'Wrong combination of email and password or the account does not exist'));
			}

			if (!$customer['status']) {
				throw new Exception(t('error_wrong_email_password_combination', 'Wrong combination of email and password or the account does not exist'));
			}

			if ($customer['blocked_until'] && strtotime($customer['blocked_until']) > time()) {
				throw new Exception(strtr(t('error_account_is_blocked', 'The account is blocked until {datetime}'), [
					'{datetime}' => f::datetime_format('datetime', $customer['blocked_until'])
				]));
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

					throw new Exception(strtr(t('error_this_account_has_been_temporarily_blocked_n_minutes', 'This account has been temporarily blocked {n} minutes'), [
						'{n}' => 15
					]));
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

			$customer['known_ips'] = f::string_split($customer['known_ips']);
			$customer['known_fingerprints'] = f::string_split($customer['known_fingerprints']);

			array_unshift($customer['known_ips'], $_SERVER['REMOTE_ADDR']);
			$customer['known_ips'] = array_slice(array_unique($customer['known_ips']), 0, 10);

			if (!empty(session::$data['fingerprint'])) {
				array_unshift($customer['known_fingerprints'], session::$data['fingerprint']);
				$customer['known_fingerprints'] = array_slice(array_unique($customer['known_fingerprints']), 0, 10);
			}

			database::query(
				"update ". DB_TABLE_PREFIX ."customers
				set known_ips = '". database::input(implode(',', $customer['known_ips'])) ."',
					known_fingerprints = '". database::input(implode(',', $customer['known_fingerprints'])) ."',
					last_ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."',
					last_hostname = '". database::input(gethostbyaddr($_SERVER['REMOTE_ADDR'])) ."',
					last_user_agent = '". database::input($_SERVER['HTTP_USER_AGENT']) ."',
					last_login = '". date('Y-m-d H:i:s') ."',
					login_attempts = 0,
					total_logins = total_logins + 1
				where id = ". (int)$customer['id'] ."
				limit 1;"
			);

			customer::load($customer['id']);

			session::$data['security']['timestamp'] = time();
			session::regenerate_id();
			session::rotate_csrf_token();

			if (!empty($_POST['remember_me']) && defined('HMAC_KEY_REMEMBER_ME')) {
				$token = f::token_create_remember($customer['id'], $customer['password_hash']);
				header('Set-Cookie: customer_remember_me='. $token .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+30 days')) .'; HttpOnly; SameSite=Lax' . (!empty($_SERVER['HTTPS']) ? '; Secure' : ''), false);
			}

			// Headless requests
			if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
				header('Content-Type: application/json;charset='. mb_http_output());
				echo f::format_json([
					'success' => true,
					'id' => customer::$data['id'],
					'email' => customer::$data['email'],
					'firstname' => customer::$data['firstname'],
					'lastname' => customer::$data['lastname'],
				]);
				exit;
			}

			notices::add('success', strtr(t('success_logged_in_as_user', 'You are now logged in as {firstname} {lastname}.'), [
				'{email}' => customer::$data['email'],
				'{firstname}' => customer::$data['firstname'],
				'{lastname}' => customer::$data['lastname'],
			]));

			if (!empty($_POST['redirect_url'])) {
				$redirect_url = new ent_link($_POST['redirect_url']);
				$redirect_url->host = '';
			} else {
				$redirect_url = document::ilink('f:');
			}

			redirect($redirect_url, 303);
			exit;

		} catch (Exception $e) {

			session::$data['security']['failed_authentications']++;

			http_response_code(401);

			// Headless requests
			if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
				header('Content-Type: application/json;charset='. mb_http_output());
				echo f::format_json(['error' => $e->getMessage()]);
				exit;
			}

			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/sign_in.inc.php');

	// Headless requests
	if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
		header('Content-Type: application/json;charset='. mb_http_output());
		echo f::format_json([
			'is_logged_in' => customer::check_login(),
			'id' => customer::$data['id'],
			'email' => customer::$data['email'],
			'firstname' => customer::$data['firstname'],
			'lastname' => customer::$data['lastname'],
		]);
		exit;
	}

	echo $_page->render();
