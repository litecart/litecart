<?php

	// Query the database
	function q(...$args){
		return forward_static_call_array(['database', 'query'], $args);
	}

	// Output a translation
	function t(...$args){
		return forward_static_call_array(['language', 'translate'], $args);
	}

	// Redirect to a URL and stop script execution
	function redirect($url=null, $status_code=302) {

		if (!$url) {
			$url = $_SERVER['REQUEST_URI'];
		}

		header('Location: '. $url, $status_code);
		exit;
	}

	// Stop script execution and reload the current page
	function reload() {
		header('Location: '. $_SERVER['REQUEST_URI'], 302);
		exit;
	}

	// Checks if variables are not set, null, (bool)false, (int)0, (float)0.00, (string)"", (string)"0", (string)"0.00", (array)[], or array with nil nodes
	function nil(&...$args) { // ... as of PHP 5.6

		foreach ($args as $arg) {

			if (is_array($arg)) {
				foreach ($arg as $node) {
					if (!nil($node)) return !1;
				}
			}

			if (!empty($arg) || (is_numeric($arg) && (float)$arg != 0)) return !1;
		}

		return !0;
	}

	// Returns value for variable or falls back to a substituting value on nil(). Similar to $var ?? $fallback ?: $fallback
	function fallback(&$var, $fallback=null) {
		if (!nil($var)) return $var;
		return $fallback;
	}

	// Check if variable indicates a truthy value
	function is_true($string) {
		//return (!empty($string) && preg_match('#^(1|true|yes|on|active|enabled)$#i', $string));
		return filter_var($string, FILTER_VALIDATE_BOOLEAN);
	}

	// Check if variable indicates a falsy value
	function is_false($string) {
		//return (empty($string) || preg_match('#^(0|false|no|off|inactive|disabled)$#i', $string));
		return !filter_var($string, FILTER_VALIDATE_BOOLEAN);
	}

	// Attempt to determine if the request was loaded via JavaScript
	function is_ajax_request() {

		// Using sec-fetch-mode header
		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode
		if (isset($_SERVER['HTTP_SEC_FETCH_MODE']) && strtolower($_SERVER['HTTP_SEC_FETCH_MODE']) != 'navigate') {
			return true;
		}

		// Using X-Requested-With header
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}

		return false;
	}