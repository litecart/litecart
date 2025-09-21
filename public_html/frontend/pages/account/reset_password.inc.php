<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/account/reset_password.inc.php
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

			if (!$customer) {
				throw new Exception(t('error_email_not_in_database', 'The email address does not exist in our database.'));
			}

			if (empty($customer['status'])) {
				throw new Exception(t('error_account_inactive', 'Your account is inactive, contact customer support'));
			}

			if (!empty($_REQUEST['reset_token'])) {

				if (!$reset_token = json_decode($customer['password_reset_token'], true)) {
					throw new Exception(t('error_invalid_reset_token', 'Invalid reset token'));
				}

				if ($_REQUEST['reset_token'] != $reset_token['token']) {
					throw new Exception(t('error_incorrect_reset_token', 'Incorrect reset token'));
				}

				if (strtotime($reset_token['expires']) < time()) {
					throw new Exception(t('error_reset_token_expired', 'The reset token has expired'));
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

				if (!functions::password_check_strength($_POST['new_password'], 6)) {
					throw new Exception(t('error_password_not_strong_enough', 'The password is not strong enough'));
				}
			}

			if (settings::get('captcha_enabled') && !functions::captcha_validate('reset_password')) {
				throw new Exception(t('error_invalid_captcha', 'Invalid CAPTCHA given'));
			}

			// Process

			if (empty($_REQUEST['reset_token'])) {

				$reset_token = [
					'token' => substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', 10)), 0, 8),
					'expires' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
				];

				database::query(
					"update ". DB_TABLE_PREFIX ."customers
					set password_reset_token = '". database::input(functions::format_json($reset_token, false)) ."'
					where id = ". (int)$customer['id'] ."
					limit 1;"
				);

				$aliases = [
					'{email}' => $customer['email'],
					'{store_name}' => settings::get('store_name'),
					'{token}' => $reset_token['token'],
					'{link}' => document::ilink('account/reset_password', ['email' => $customer['email'], 'reset_token' => $reset_token['token']]),
				];

				$subject = t('title_reset_password', 'Reset Password');
				$message = strtr(implode("\r\n", [
					t('email_body_reset_password_intro', "You recently requested to reset your password for {store_name}."),
					t('email_body_reset_password_ignore', "If you did not request a password reset, please ignore this email."),
					t('email_body_reset_password_instruction', "Visit the link below to reset your password:"),
					"",
					"{link}",
					"",
					t('email_body_reset_password_token', "Reset Token: {token}")
				]), $aliases);

				(new ent_email())
					->add_recipient($customer['email'], $customer['firstname'] .' '. $customer['lastname'])
					->set_subject($subject)
					->add_body($message)
					->send();

				notices::add('success', t('success_reset_password_email_sent', 'An email with instructions has been sent to your email address.'));
				redirect(document::ilink('account/reset_password', ['email' => $_REQUEST['email'], 'reset_token' => '']));
				exit;

			} else {

				$customer = new ent_customer($customer['id']);
				$customer->set_password($_POST['new_password']);
				$customer->data['password_reset_token'] = '';

				notices::add('success', t('success_new_password_set', 'Your new password has been set. You may now sign in.'));
				redirect(document::ilink('account/sign_in', ['email' => $customer->data['email']]));
				exit;

			}

		} catch (Exception $e) {
			notices::add('errors', $e->getMessage());
		}
	}

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/account/reset_password.inc.php');
	echo $_page->render();
