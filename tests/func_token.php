<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		if (!defined('HMAC_KEY_REMEMBER_ME')) {
			define('HMAC_KEY_REMEMBER_ME', str_repeat('a', 64));
		}

		$user_id = 42;
		$password_hash = password_hash('test_password', PASSWORD_DEFAULT);

		########################################################################
		## token_create_remember — creates valid token
		########################################################################

		$token = f::token_create_remember($user_id, $password_hash, 30);

		if (empty($token)) {
			throw new Exception('token_create_remember returned empty token');
		}

		// Verify it's valid base64
		$decoded = base64_decode($token, true);
		if ($decoded === false) {
			throw new Exception('token_create_remember returned invalid base64');
		}

		// Verify it's valid JSON
		$payload = json_decode($decoded, true);
		if (!is_array($payload) || !isset($payload['id']) || !isset($payload['exp']) || !isset($payload['sig'])) {
			throw new Exception('token_create_remember has invalid payload structure');
		}

		if ($payload['id'] !== $user_id) {
			throw new Exception('token_create_remember stored wrong user ID');
		}

		########################################################################
		## token_verify_remember — verifies valid token
		########################################################################

		$verified_id = f::token_verify_remember($token, $password_hash);

		if ($verified_id !== $user_id) {
			throw new Exception('token_verify_remember returned wrong ID: '. var_export($verified_id, true));
		}

		########################################################################
		## token_verify_remember — rejects tampered token
		########################################################################

		$tampered = base64_encode(f::format_json([
			'id' => 99,
			'exp' => time() + 86400,
			'sig' => 'fake_signature',
		], ''));

		$result = f::token_verify_remember($tampered, $password_hash);

		if ($result !== false) {
			throw new Exception('token_verify_remember accepted tampered token');
		}

		########################################################################
		## token_verify_remember — rejects expired token
		########################################################################

		$expired = f::token_create_remember($user_id, $password_hash, -1);
		$result = f::token_verify_remember($expired, $password_hash);

		if ($result !== false) {
			throw new Exception('token_verify_remember accepted expired token');
		}

		########################################################################
		## token_verify_remember — rejects wrong password hash
		########################################################################

		$wrong_hash = password_hash('different_password', PASSWORD_DEFAULT);
		$result = f::token_verify_remember($token, $wrong_hash);

		if ($result !== false) {
			throw new Exception('token_verify_remember accepted wrong password hash');
		}

		########################################################################
		## token_verify_remember — rejects garbage input
		########################################################################

		if (f::token_verify_remember('not_base64!!!', $password_hash) !== false) {
			throw new Exception('token_verify_remember accepted garbage input');
		}

		if (f::token_verify_remember('', $password_hash) !== false) {
			throw new Exception('token_verify_remember accepted empty string');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
