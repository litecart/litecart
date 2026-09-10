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

		$has_code = !empty($_POST['code']) && !empty($_POST['email']);
		$rate_limit_action = $has_code ? 'password_reset_failed' : 'password_reset_requested';

		try {

			if (empty($_POST['email'])) {
				throw new Exception(t('error_must_provide_email_address', 'You must provide an email address'));
			}

			// Rate limit checking
			if (database::query(
				"select count(*) as num_attempts from ". DB_PREFIX ."rate_limiting
				where action = '". $rate_limit_action ."'
				and (
					ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
					". (!empty($_POST['email']) ? "or (scope_type = 'email' and scope_key = '". database::input($_POST['email']) ."')" : '') ."
				)
				and created_at >= '". date('Y-m-d H:i:s', strtotime('-5 minutes')) ."'"
			)->fetch('num_attempts') > 3) {
				throw new Exception(t('error_too_many_attempts', 'Too many failed attempts. Please try again later.'));
			}

			$customer = database::query(
				"select * from ". DB_PREFIX ."customers
				where email = '". database::input($_POST['email']) ."'
				limit 1;"
			)->fetch();

			// Unknown or inactive accounts surface as a generic "invalid verification code" error.
			if (!$customer || empty($customer['status'])) {
				throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
			}

			if ($has_code) {

				$verification = session::$data['security.customer']['verification']['password_reset'] ?? null;

				if (empty($verification) || ($verification['email'] ?? null) !== $customer['email']) {
					throw new Exception(t('error_invalid_reset_code', 'Invalid or expired verification code. Please request a new one.'));
				}

				if (time() > strtotime($verification['expires'])) {
					unset(session::$data['security.customer']['verification']['password_reset']);
					throw new Exception(t('error_reset_code_expired', 'The verification code has expired. Please request a new one.'));
				}

				if ($_POST['code'] !== (string)$verification['code']) {
					throw new Exception(t('error_incorrect_reset_code', 'Incorrect verification code'));
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

			if (!$has_code) {

				// Step 1: generate a 6-digit code and email it.
				$code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

				session::$data['security.customer']['verification']['password_reset'] = [
					'email' => $customer['email'],
					'code' => $code,
					'expires' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
					'attempts' => 0,
				];

				$aliases = [
					'{email}' => $customer['email'],
					'{store_name}' => settings::get('store_name'),
					'{code}' => $code,
				];

				$subject = t('title_reset_password', 'Reset Password');
				$message = strtr(t('email_body_reset_password', implode("\r\n", [
					'You recently requested to reset your password for {store_name}. If you did not request a password reset, please ignore this email.',
					'',
					'Your verification code is: {code}',
					'',
					'This code will expire in 15 minutes.'
				])), $aliases);

				(new ent_email())
					->add_recipient($customer['email'], $customer['firstname'] .' '. $customer['lastname'])
					->set_subject($subject)
					->add_body($message)
					->send();

				database::insert('rate_limiting', [
					'action' => 'password_reset_requested',
					'ip_address' => $_SERVER['REMOTE_ADDR'],
					'hostname' => reverse_dns($_SERVER['REMOTE_ADDR']),
					'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					'scope_type' => 'email',
					'scope_key' => $customer['email'],
					'created_at' => date('Y-m-d H:i:s'),
				]);

				notices::add('success', t('success_reset_password_email_sent_uniform', 'If an account exists for this email, instructions have been sent.'));
				redirect(document::ilink('account/reset_password', ['email' => $_POST['email']]), 303);
				exit;

			} else {

				$customer = new ent_customer($customer['id']);
				$customer->set_password($_POST['new_password']);
				$customer->data['sessions_expiry'] = date('Y-m-d H:i:s');
				$customer->save();

				unset(session::$data['security.customer']['verification']['password_reset']);

				// Clear failed-reset attempts for this IP and email after a successful reset.
				database::query(
					"delete from ". DB_PREFIX ."rate_limiting
					where action = 'password_reset_failed'
					and (
						ip_address = '". database::input($_SERVER['REMOTE_ADDR']) ."'
						". (!empty($_POST['email']) ? "or (scope_type = 'email' and scope_key = '". database::input($_POST['email']) ."')" : '') ."
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
				'scope_type' => !empty($_POST['email']) ? 'email' : null,
				'scope_key' => !empty($_POST['email']) ? $_POST['email'] : null,
				'created_at' => date('Y-m-d H:i:s'),
			]);
			
			// Timing-neutral dummy path so unknown/inactive accounts respond in the same ballpark as real sends.
			usleep(random_int(200000, 500000));

			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/reset_password.inc.php');
	echo $_page->render();
