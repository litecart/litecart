<?php

	header('X-Robots-Tag: noindex');

	try  {

		if (empty(customer::$data['id'])) {
			throw new Exception(t('error_not_signed_in', 'You are not currently signed in.'), 401);
		}

		customer::log([
			'type' => 'sign_out',
			'description' => 'User signed out',
			'expires_at' => strtotime('+12 months'),
		]);

		cart::reset();
		customer::reset();

		session::regenerate_id();
		session::rotate_csrf_token();
		session::$data['cart']['uid'] = null;

		header('Set-Cookie: cart[uid]=; Path='. WS_DIR_APP .'; Max-Age=-1; SameSite=Lax', false);

		if (!empty($_COOKIE['customer_remember_me'])) {
			header('Set-Cookie: customer_remember_me=; Path='. WS_DIR_APP .'; Max-Age=-1; HttpOnly; SameSite=Lax', false);
		}

		// Headless requests
		if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
			header('Content-Type: application/json; charset='. language::$selected['charset']);
			echo f::format_json([
				'success' => true,
				'message' => t('description_logged_out', 'You are now logged out.'),
			]);
			exit;
		}

		notices::add('success', t('description_logged_out', 'You are now logged out.'));

		redirect(document::ilink(''), 303);
		exit;

	} catch (Exception $e) {

		// Headless requests
		if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
			header('Content-Type: application/json; charset='. language::$selected['charset']);
			echo f::format_json([
				'error' => $e->getMessage(),
			]);
			exit;
		}

		notices::add('errors', $e->getMessage());

		http_response_code($e->getCode() ?: 400);
		redirect(document::ilink('account/sign_in'), 303);
		exit;
	}
