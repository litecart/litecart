<?php

	/**
	 * Security headers for installer and upgrader HTML responses.
	 * This lives inside install/ because nod_document cannot be loaded
	 * before the installer has written storage/config.inc.php.
	 */

	/**
	 * Returns the per-request CSP nonce, generating it lazily on first
	 * call. Templates can echo this value into <script nonce="..."> to
	 * satisfy the strict CSP.
	 */
	function install_csp_nonce() {
		static $nonce = null;
		if ($nonce === null) {
			$nonce = bin2hex(random_bytes(16));
		}
		return $nonce;
	}

	/**
	 * Detect whether the current request arrived over HTTPS, honouring
	 * forwarded-proto headers from TLS-terminating reverse proxies.
	 */
	function install_request_is_secure() {
		if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
			return true;
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
			return true;
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
			return true;
		}
		return false;
	}

	/**
	 * Emit the full header set. Safe to call once per request; becomes a
	 * no-op if headers have already been flushed.
	 */
	function install_send_security_headers() {
		if (headers_sent()) {
			return;
		}

		$nonce = install_csp_nonce();

		$csp_directives = [
			"default-src 'self'",
			"script-src 'self' 'nonce-$nonce'",
			"style-src 'self' 'nonce-$nonce' 'unsafe-inline'", // 'unsafe-inline' kept for legacy inline style attributes on install wizard
			"img-src 'self' data:",
			"font-src 'self' data:",
			"connect-src 'self'",
			"form-action 'self'",
			"frame-ancestors 'none'",
			"base-uri 'self'",
		];
		header('Content-Security-Policy: ' . implode('; ', $csp_directives));

		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: DENY');
		header('Referrer-Policy: same-origin');

		if (install_request_is_secure()) {
			header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
		}
	}
