<?php

	// TOTP (RFC 6238) helper functions — no external dependencies

	function totp_generate_secret(int $length = 20): string {

		$bytes = random_bytes($length);
		return totp_base32_encode($bytes);
	}

	function totp_generate_code(string $secret, ?int $time = null, int $digits = 6, int $period = 30): string {

		if ($time === null) $time = time();

		$counter = pack('J', intdiv($time, $period));
		$key = totp_base32_decode($secret);
		$hash = hash_hmac('sha1', $counter, $key, true);

		$offset = ord($hash[19]) & 0x0F;
		$code = (
			((ord($hash[$offset]) & 0x7F) << 24) |
			((ord($hash[$offset + 1]) & 0xFF) << 16) |
			((ord($hash[$offset + 2]) & 0xFF) << 8) |
			(ord($hash[$offset + 3]) & 0xFF)
		) % pow(10, $digits);

		return str_pad($code, $digits, '0', STR_PAD_LEFT);
	}

	function totp_verify_code(string $secret, string $code, int $window = 1, int $period = 30): bool {

		$time = time();

		for ($i = -$window; $i <= $window; $i++) {
			$expected = totp_generate_code($secret, $time + ($i * $period));
			if (hash_equals($expected, $code)) {
				return true;
			}
		}

		return false;
	}

	function totp_build_uri(string $secret, string $account, string $issuer): string {

		return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($account) .'?'. http_build_query([
			'secret' => $secret,
			'issuer' => $issuer,
			'algorithm' => 'SHA1',
			'digits' => 6,
			'period' => 30,
		]);
	}

	function totp_base32_encode(string $data): string {

		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$binary = '';

		foreach (str_split($data) as $char) {
			$binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
		}

		$encoded = '';
		foreach (str_split($binary, 5) as $chunk) {
			$chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
			$encoded .= $alphabet[bindec($chunk)];
		}

		return $encoded;
	}

	function totp_base32_decode(string $data): string {

		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$binary = '';

		foreach (str_split(strtoupper($data)) as $char) {
			$index = strpos($alphabet, $char);
			if ($index === false) continue;
			$binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
		}

		$decoded = '';
		foreach (str_split($binary, 8) as $byte) {
			if (strlen($byte) < 8) break;
			$decoded .= chr(bindec($byte));
		}

		return $decoded;
	}
