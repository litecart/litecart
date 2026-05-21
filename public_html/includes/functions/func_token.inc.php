<?php

	function token_create_remember(int $id, string $password_hash, int $expiry_days=30): string {

		if (!defined('HMAC_KEY_REMEMBER_ME')) return '';

		$exp = time() + $expiry_days * 86400;
		$payload = $id . ':' . $exp . ':' . substr($password_hash, 0, 16);
		$sig = hash_hmac('sha256', $payload, HMAC_KEY_REMEMBER_ME);

		return base64_encode(f::format_json([
			'id' => $id,
			'exp' => $exp,
			'sig' => $sig,
		], ''));
	}

	function token_verify_remember(string $cookie_value, string $password_hash): int|false {

		if (!defined('HMAC_KEY_REMEMBER_ME')) return false;

		$decoded = base64_decode($cookie_value, true);
		if ($decoded === false) return false;

		$token = json_decode($decoded, true);
		if (!is_array($token) || empty($token['id']) || empty($token['exp']) || empty($token['sig'])) {
			return false;
		}

		if ($token['exp'] < time()) return false;

		$payload = $token['id'] . ':' . $token['exp'] . ':' . substr($password_hash, 0, 16);
		$expected_sig = hash_hmac('sha256', $payload, HMAC_KEY_REMEMBER_ME);

		if (!hash_equals($expected_sig, $token['sig'])) return false;

		return $token['id'];
	}
