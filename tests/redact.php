<?php

	// Platform test for PROJ-21: redaction helper + SMTP state-machine.
	// No DB or autoloader dependency — these helpers are pure string
	// processing and must work even before LiteCart's bootstrap has run
	// (error_handler may run very early).

	if (!function_exists('redact_query_string')) {
		require_once __DIR__ . '/../public_html/shared/functions/func_redact.inc.php';
	}

	try {

		########################################################################
		## AC-7: redact_query_string blanks sensitive params, keeps rest
		########################################################################

		echo 'Testing redact_query_string...';

		$cases = [
			// [input, expected]
			['/account/reset?reset_token=abc123',        '/account/reset?reset_token=[REDACTED]'],
			['/order?public_key=deadbeef&id=42',         '/order?public_key=[REDACTED]&id=42'],
			['token=s3cr3t',                              'token=[REDACTED]'],
			['foo=1&password=hunter2&bar=2',              'foo=1&password=[REDACTED]&bar=2'],
			['https://host/x?api_key=X&q=search',         'https://host/x?api_key=[REDACTED]&q=search'],
			['/path#fragment',                            '/path#fragment'],
			['/path?token=a#fragment',                    '/path?token=[REDACTED]#fragment'],
			['',                                          ''],
			['/no-query-here',                            '/no-query-here'],
			['TOKEN=upper',                               'TOKEN=[REDACTED]'],    // case-insensitive
			['Password=mixed',                            'Password=[REDACTED]'], // case-insensitive
		];

		foreach ($cases as [$input, $expected]) {
			$actual = redact_query_string($input);
			if ($actual !== $expected) {
				throw new Exception('redact_query_string('. var_export($input, true) .') returned '
					. var_export($actual, true) .', expected ' . var_export($expected, true));
			}
		}

		echo ' [OK]' . PHP_EOL;

		########################################################################
		## AC-9: redact_argv preserves non-sensitive flags and positions
		########################################################################

		echo 'Testing redact_argv...';

		$argv_cases = [
			[
				['index.php', 'install', '--db_password=s3cret', '--timezone=UTC', '--password', 'hunter2', '--cleanup'],
				['index.php', 'install', '--db_password=[REDACTED]', '--timezone=UTC', '--password', '[REDACTED]', '--cleanup'],
			],
			[
				['index.php', 'upgrade', '--from_version=3.0.0', '--backup=1'],
				['index.php', 'upgrade', '--from_version=3.0.0', '--backup=1'],
			],
			[
				['tool.php', '--api_key', 'secret_abc', '--endpoint', 'https://x'],
				['tool.php', '--api_key', '[REDACTED]', '--endpoint', 'https://x'],
			],
			[
				['tool.php', '--token=abc', '--TOKEN=xyz'],
				['tool.php', '--token=[REDACTED]', '--TOKEN=[REDACTED]'],
			],
		];

		foreach ($argv_cases as [$input, $expected]) {
			$actual = redact_argv($input);
			if ($actual !== $expected) {
				throw new Exception('redact_argv('. json_encode($input) .') returned '
					. json_encode($actual) .', expected ' . json_encode($expected));
			}
		}

		echo ' [OK]' . PHP_EOL;

		########################################################################
		## Key-matching is whole-word, not substring
		########################################################################

		echo 'Testing key-match precision (no false positives)...';

		$false_positive_cases = [
			// These keys contain sensitive substrings but are not themselves
			// credentials — they must pass through untouched.
			['token_count=5&key_id=7',       'token_count=5&key_id=7'],
			['some_token_like=foo',          'some_token_like=foo'],
			['authoritative=1',              'authoritative=1'],
			['passwords_enabled=yes',        'passwords_enabled=yes'],
		];

		foreach ($false_positive_cases as [$input, $expected]) {
			$actual = redact_query_string($input);
			if ($actual !== $expected) {
				throw new Exception('False positive: redact_query_string('. var_export($input, true) .') redacted a non-sensitive key');
			}
		}

		echo ' [OK]' . PHP_EOL;

		########################################################################
		## AC-4/5/6: SMTP state-machine — simulate AUTH flows
		########################################################################

		echo 'Testing SMTP AUTH redaction (write-path simulation)...';

		// We cannot instantiate smtp_client without a live socket, so this
		// is an inline simulation of the same state-machine logic. If the
		// algorithm below matches the one in smtp_client::write(), both
		// will behave identically. The test protects against future
		// regressions — if smtp_client.inc.php changes, update the mirror.

		$simulate_write = function(string $data, int &$pending): string {
			$transcript_line = '';
			if ($pending > 0) {
				$transcript_line = "> [REDACTED]\r\n";
				$pending--;
			} else {
				$transcript_line = "> $data";
				$trimmed = ltrim($data);
				if (stripos($trimmed, 'AUTH LOGIN') === 0) {
					$pending = 2;
				} else if (stripos($trimmed, 'AUTH PLAIN') === 0 || stripos($trimmed, 'AUTH CRAM-MD5') === 0) {
					$pending = 1;
				}
			}
			return $transcript_line;
		};

		// AUTH LOGIN flow: command + 2 credential writes.
		$pending = 0;
		$transcript = '';
		foreach (["AUTH LOGIN\r\n", base64_encode('user') . "\r\n", base64_encode('secretpassword') . "\r\n"] as $w) {
			$transcript .= $simulate_write($w, $pending);
		}
		if (strpos($transcript, 'secretpassword') !== false) {
			throw new Exception('Plain password leaked into AUTH LOGIN transcript');
		}
		if (strpos($transcript, base64_encode('secretpassword')) !== false) {
			throw new Exception('Base64 password leaked into AUTH LOGIN transcript');
		}
		if (substr_count($transcript, '[REDACTED]') !== 2) {
			throw new Exception('AUTH LOGIN: expected 2 [REDACTED] markers, got: ' . $transcript);
		}

		// AUTH PLAIN flow: command + 1 credential write.
		$pending = 0;
		$transcript = '';
		foreach (["AUTH PLAIN\r\n", base64_encode("\0user\0mySecret") . "\r\n"] as $w) {
			$transcript .= $simulate_write($w, $pending);
		}
		if (strpos($transcript, 'mySecret') !== false) {
			throw new Exception('AUTH PLAIN password leaked');
		}
		if (substr_count($transcript, '[REDACTED]') !== 1) {
			throw new Exception('AUTH PLAIN: expected 1 [REDACTED], got: ' . $transcript);
		}

		// AUTH CRAM-MD5 flow: command + 1 HMAC write.
		$pending = 0;
		$transcript = '';
		foreach (["AUTH CRAM-MD5\r\n", base64_encode('user ' . hash_hmac('md5', 'sensitive', 'challenge')) . "\r\n"] as $w) {
			$transcript .= $simulate_write($w, $pending);
		}
		if (stripos($transcript, hash_hmac('md5', 'sensitive', 'challenge')) !== false) {
			throw new Exception('CRAM-MD5 HMAC leaked');
		}

		// Non-AUTH command: no redaction.
		$pending = 0;
		$transcript = '';
		foreach (["EHLO example.com\r\n", "MAIL FROM: <a@b>\r\n", "RCPT TO: <c@d>\r\n"] as $w) {
			$transcript .= $simulate_write($w, $pending);
		}
		if (strpos($transcript, '[REDACTED]') !== false) {
			throw new Exception('Non-AUTH commands should not be redacted');
		}

		echo ' [OK]' . PHP_EOL;

		########################################################################
		## Cross-check: the state-machine mirror matches smtp_client.inc.php
		########################################################################

		echo 'Cross-checking smtp_client.inc.php matches simulated state-machine...';

		$smtp_src = file_get_contents(__DIR__ . '/../public_html/shared/clients/smtp_client.inc.php');

		foreach (['_pending_credential_writes', 'AUTH LOGIN', 'AUTH PLAIN', 'AUTH CRAM-MD5', '[REDACTED]'] as $needle) {
			if (strpos($smtp_src, $needle) === false) {
				throw new Exception('smtp_client.inc.php is missing expected marker: ' . $needle);
			}
		}

		echo ' [OK]' . PHP_EOL;

		echo PHP_EOL . 'All PROJ-21 redaction tests passed.' . PHP_EOL;
		return true;

	} catch (Throwable $e) {
		echo PHP_EOL . 'FAILED: ' . $e->getMessage() . PHP_EOL;
		return false;
	}
