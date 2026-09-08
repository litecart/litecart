<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/pages/account/reset_password.inc.php
	*/

	header('X-Robots-Tag: noindex');

	document::$title[] = t('title_reset_password', 'Reset Password');

	breadcrumbs::add(t('title_account', 'Account'));
	breadcrumbs::add(t('title_reset_password', 'Reset Password'), document::ilink('account/reset_password'));

	if (!empty($_POST['reset_password'])) {

		$has_code = !empty($_REQUEST['verification_code']);
		$rate_limit_action = $has_code ? 'password_reset_failed' : 'password_reset_requested';

		try {

			// Rate limit checking
			if (database::query(
				"select count(*) as num_attempts from ". DB_PREFIX ."rate_limiting
				where action = '". $rate_limit_action ."'
				and (
					ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
					". (!empty($_REQUEST['email']) ? "or (scope_type = 'email' and scope_key = '". database::input($_REQUEST['email']) ."')" : '') ."
				)
				and created_at >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'"
			)->fetch('num_attempts') > 3) {
				throw new Exception(t('error_too_many_attempts', 'Too many failed attempts. Please try again later.'));
			}

			if (empty($_REQUEST['email'])) {
				throw new Exception(t('error_must_provide_email_address', 'You must provide an email address'));
			}

			$customer = database::query(
				"select * from ". DB_PREFIX ."customers
				where email = '". database::input($_REQUEST['email']) ."'
				limit 1;"
			)->fetch();

			if (!empty($_REQUEST['verification_code'])) {

				// Unknown or inactive accounts surface as a generic "invalid verification code" error.
				if (!$customer || empty($customer['status'])) {
					throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
				}

				if (!isset(security::$data['verification']['code'])) {
					throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
				}

				if ($_REQUEST['verification_code'] != security::$data['verification']['code']) {
					throw new Exception(t('error_incorrect_verification_code', 'Incorrect verification code'));
				}

				if (security::$data['verification']['expires'] < time()) {
					throw new Exception(t('error_verification_code_expired', 'The verification code has expired'));
				}

				if (empty($_POST['new_password'])) {
					throw new Exception(t('error_must_provide_password', 'You must provide a password'));
				}

				if (empty($_POST['confirmed_password'])) {
					throw new Exception(t('error_must_confirm_password', 'You must confirm your password'));
				}

				if ($_POST['new_password'] != $_POST['confirmed_password']) {
					throw new Exception(t('error_passwords_did_not_match', 'Passwords did not match'));
				}

				if (!f::password_check_strength($_POST['new_password'], 6)) {
					throw new Exception(t('error_password_not_strong_enough', 'The password is not strong enough'));
				}
			}

			if (settings::get('captcha') && !f::captcha_validate('reset_password')) {
				throw new Exception(t('error_invalid_captcha', 'Invalid CAPTCHA given'));
			}

			// Process

			if (empty($_REQUEST['verification_code'])) {

				// Uniform-response branch: never leak whether the email belongs to a known or active account.
				if ($customer && !empty($customer['status'])) {

					$verification_token = [
						'code' => bin2hex(random_bytes(24)),
						'expires' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
					];

					database::query(
						"update ". DB_PREFIX ."customers
						set verification_token = '". database::input(f::format_json($verification_token, false)) ."'
						where id = ". (int)$customer['id'] ."
						limit 1;"
					);

					$customer = new ent_customer($customer['id']);
					$customer->send_email('reset_password', [
						'{code}' => $verification_token['code'],
						'{link}' => document::ilink('account/reset_password', [
							'email' => $customer['email'],
							'verification_code' => $verification_token['code']
						]),
					]);

					database::insert('rate_limiting', [
						'action' => 'password_reset_requested',
						'ip_address' => $_SERVER['REMOTE_ADDR'],
						'hostname' => reverse_dns($_SERVER['REMOTE_ADDR']),
						'user_agent' => $_SERVER['HTTP_USER_AGENT'],
						'scope_type' => 'email',
						'scope_key' => $customer['email'],
						'created_at' => date('Y-m-d H:i:s'),
					]);

				} else {
					// Timing-neutral dummy path so unknown/inactive accounts respond in the same ballpark as real sends.
					usleep(random_int(200000, 500000));
				}

				notices::add('success', t('success_reset_password_email_sent_uniform', 'If an account exists for this email, instructions have been sent.'));
				redirect(document::ilink('account/reset_password', ['email' => $_REQUEST['email'], 'verification_code' => '']), 303);
				exit;

			} else {

				$customer = new ent_customer($customer['id']);
				$customer->set_password($_POST['new_password']);
				$customer->data['sessions_expiry'] = date('Y-m-d H:i:s');
				$customer->save();

				// Clear failed-reset attempts for this IP and email after a successful reset.
				database::query(
					"delete from ". DB_PREFIX ."rate_limiting
					where action = 'password_reset_failed'
					and (
						ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
						". (!empty($_REQUEST['email']) ? "or (scope_type = 'email' and scope_key = '". database::input($_REQUEST['email']) ."')" : '') ."
					)"
				);

				notices::add('success', t('success_new_password_set', 'Your new password has been set. You may now sign in.'));
				redirect(document::ilink('account/sign_in', ['email' => $customer->data['email']]), 303);
				exit;
			}

		} catch (Exception $e) {

			// Record failed attempts for rate-limiting
			database::insert('rate_limiting', [
				'action' => $rate_limit_action,
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'hostname' => reverse_dns($_SERVER['REMOTE_ADDR']),
				'user_agent' => $_SERVER['HTTP_USER_AGENT'],
				'scope_type' => !empty($_REQUEST['email']) ? 'email' : null,
				'scope_key' => !empty($_REQUEST['email']) ? $_REQUEST['email'] : null,
				'created_at' => date('Y-m-d H:i:s'),
			]);

			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/reset_password.inc.php');
	echo $_page->render();
