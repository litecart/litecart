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

		try {

			if (empty($_REQUEST['email'])) {
				throw new Exception(t('error_must_provide_email_address', 'You must provide an email address'));
			}

			$customer = database::query(
				"select * from ". DB_TABLE_PREFIX ."customers
				where email = '". database::input($_REQUEST['email']) ."'
				limit 1;"
			)->fetch();

			if (!empty($_REQUEST['verification_code'])) {

				// Unknown or inactive accounts surface as a generic "invalid verification code" error.
				if (!$customer || empty($customer['status'])) {
					throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
				}

				if (!isset(session::$data['security']['verification']['code'])) {
					throw new Exception(t('error_invalid_verification_code', 'Invalid verification code'));
				}

				if ($_REQUEST['verification_code'] != session::$data['security']['verification']['code']) {
					throw new Exception(t('error_incorrect_verification_code', 'Incorrect verification code'));
				}

				if (session::$data['security']['verification']['expires'] < time()) {
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

			if (settings::get('captcha_enabled') && !f::captcha_validate('reset_password')) {
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
						"update ". DB_TABLE_PREFIX ."customers
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

				notices::add('success', t('success_new_password_set', 'Your new password has been set. You may now sign in.'));
				redirect(document::ilink('account/sign_in', ['email' => $customer->data['email']]), 303);
				exit;
			}

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/reset_password.inc.php');
	echo $_page->render();
