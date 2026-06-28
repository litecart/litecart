<?php

	/*
		Redaction helpers for log output.

		LiteCart writes request URIs, HTTP referers, CLI argv, and (via callers)
		other free-form strings into storage/logs/errors.log. Any secret
		carried in those strings (reset tokens, order public keys, install
		passwords, API keys) ends up in the log in clear. These helpers
		replace the sensitive values with the literal string [REDACTED]
		before the caller writes to the log.

		The helpers are pure string processing — no I/O, no side effects —
		so they are cheap to call on every log line.
	*/

	/*
		Keys whose value should ALWAYS be redacted when they appear as the
		full key name. Matched case-insensitively against the complete key
		(after url-decode / without the leading dashes). Covers short tokens
		that are meaningful only as complete words — "token" and "auth" in
		particular are too generic to allow as substrings.
	*/
	const REDACT_SENSITIVE_EXACT = [
		'password', 'passwd', 'secret', 'credential', 'token', 'auth',
		'api_key', 'apikey', 'reset_token', 'public_key',
	];

	/*
		Additional unambiguous "credential" words that are ALSO checked against
		individual segments of a key (split on `_` and `-`). This allows
		`db_password`, `admin_password`, `smtp_secret` etc. to be redacted
		without maintaining an exhaustive list of compound keys. Kept small
		on purpose — "token" and "key" are deliberately NOT in here, because
		`token_count`, `cache_key`, `foreign_key_id` etc. must pass through.
	*/
	const REDACT_SENSITIVE_SEGMENTS = [
		'password', 'passwd', 'secret', 'credential',
	];

	const REDACT_PLACEHOLDER = '[REDACTED]';

	/*
		Return true when $key matches a REDACT_SENSITIVE_EXACT entry or when
		any `_` / `-`-separated segment of $key matches a
		REDACT_SENSITIVE_SEGMENTS entry. All comparisons case-insensitive.
	*/
	function redact_key_is_sensitive(string $key): bool {
		$needle = strtolower((string)$key);
		if ($needle === '') return false;

		foreach (REDACT_SENSITIVE_EXACT as $pattern) {
			if ($needle === $pattern) return true;
		}

		$segments = preg_split('#[_-]+#', $needle);
		foreach ($segments as $segment) {
			foreach (REDACT_SENSITIVE_SEGMENTS as $pattern) {
				if ($segment === $pattern) return true;
			}
		}

		return false;
	}

	/*
		Redact sensitive parameter values in a URL or a bare query string.

		Input forms supported:
			"/path?a=1&token=secret"   → "/path?a=1&token=[REDACTED]"
			"token=secret&a=1"         → "token=[REDACTED]&a=1"
			"https://host/x?token=s"   → "https://host/x?token=[REDACTED]"

		Values are matched against REDACT_SENSITIVE_KEYS case-insensitively.
		Unknown parameters pass through unchanged. Fragment (#...) is
		preserved but its contents are not rewritten (browsers never send
		fragments to the server, so they don't appear in REQUEST_URI).
	*/
	function redact_query_string(string $url_or_query): string {
		$input = (string)$url_or_query;
		if ($input === '') return $input;

		// Split off fragment (kept as-is).
		$fragment = '';
		if (($hash_pos = strpos($input, '#')) !== false) {
			$fragment = substr($input, $hash_pos);
			$input = substr($input, 0, $hash_pos);
		}

		// Split off a URL prefix if present — we only rewrite the query part.
		$prefix = '';
		$query = $input;
		if (($q_pos = strpos($input, '?')) !== false) {
			$prefix = substr($input, 0, $q_pos + 1);
			$query = substr($input, $q_pos + 1);
		} else if (!preg_match('#^[^=&]+=#', $input)) {
			// No query structure and no URL marker → nothing to redact.
			return $input . $fragment;
		}

		if ($query === '') return $prefix . $fragment;

		$pairs = explode('&', $query);
		foreach ($pairs as $i => $pair) {
			$eq = strpos($pair, '=');
			if ($eq === false) continue; // bare flag, no value
			$key = substr($pair, 0, $eq);
			if (redact_key_is_sensitive(urldecode($key))) {
				$pairs[$i] = $key . '=' . REDACT_PLACEHOLDER;
			}
		}

		return $prefix . implode('&', $pairs) . $fragment;
	}

	/*
		Redact sensitive values in a CLI argv array.

		Supports the three forms getopt() accepts:
			--name=value    → value replaced
			--name value    → value in next slot replaced
			-x value        → value in next slot replaced

		Positional arguments (those not preceded by an option flag) pass
		through untouched. Flags without values (e.g. "--cleanup") pass
		through untouched.

		Input:  ['install.php', '--db_password=s3cret', '--timezone=UTC', '--password', 'x']
		Output: ['install.php', '--db_password=[REDACTED]', '--timezone=UTC', '--password', '[REDACTED]']
	*/
	function redact_argv(array $argv): array {
		$out = [];
		$redact_next = false;
		foreach ($argv as $arg) {
			if ($redact_next) {
				$out[] = REDACT_PLACEHOLDER;
				$redact_next = false;
				continue;
			}
			// --name=value  (long option with inline value)
			if (preg_match('#^--([A-Za-z0-9_-]+)=(.*)$#s', $arg, $m)) {
				if (redact_key_is_sensitive($m[1])) {
					$out[] = '--' . $m[1] . '=' . REDACT_PLACEHOLDER;
				} else {
					$out[] = $arg;
				}
				continue;
			}
			// --name  or  -x  (long or short option without inline value)
			if (preg_match('#^-{1,2}([A-Za-z0-9_-]+)$#', $arg, $m)) {
				$out[] = $arg;
				if (redact_key_is_sensitive($m[1])) {
					$redact_next = true;
				}
				continue;
			}
			// Positional or unrecognised token
			$out[] = $arg;
		}
		return $out;
	}

	/*
		Convenience wrapper for error_handler: takes the raw argv-as-string
		that `implode(' ', $argv)` would produce and returns the redacted
		equivalent. Implemented by running redact_argv on the array form.
	*/
	function redact_argv_line(array $argv): string {
		return implode(' ', redact_argv($argv));
	}
