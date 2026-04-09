<?php

	// Shorthand f:: for functions:: (class_alias() doesn't work here)
	class f {
		public static function __callstatic($function, $arguments) {
			return forward_static_call_array(['functions', $function], $arguments);
		}
	}

	// Shorthand q() for database::query()
	function q(...$args){
		return forward_static_call_array(['database', 'query'], $args);
	}

	// Shorthand t() for language::translate()
	function t(...$args) {
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
	function reload($status_code=302) {

		if (!in_array($status_code, [301, 302, 303, 307, 308])) {
			trigger_error('Unsupported response status code for redirect ('. (int)$status_code .')');
		}

		header('Location: '. $_SERVER['REQUEST_URI'], $status_code);
		exit;
	}

	// Checks if variables are not set, null, (bool)false, (int)0, (float)0, (string)"", (string)"0", (string)"0.00", (array)[], or array with nil nodes
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

	// Returns value for variable or falls back to a substituting value on nil(). Similar to !empty($var) ? $var : $fallback1 ?: $fallback2
	function fallback(&...$fallbacks) { // ... as of PHP 5.6
		foreach ($fallbacks as $fallback) {
			if (!nil($fallback)) return $fallback;
		}
		return end($fallbacks);
	}

	// Check if variable indicates a truthy value
	function is_true($string) {
		//return (!empty($string) && preg_match('#^(1|true|yes|on|active|enabled)$#i', $string));
		return filter_var($string, FILTER_VALIDATE_BOOLEAN);
	}

	// Check if variable indicates a falsy value
	function is_false($string) {
		//return (empty($string) || preg_match('#^(|0|false|no|none|off|inactive|disabled)$#i', $string));
		return !filter_var($string, FILTER_VALIDATE_BOOLEAN);
	}

	function is_binary($string) {
		return preg_match('#[^\x09\x0A\x0D\x20-\x7E]#', $string) === 1;
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

	// Output any variable to the browser console
	function console_dump(...$vars) { // ... as of PHP 5.6
		foreach ($vars as $var) {

			// Determine if output can be shown as a table
			if (is_array($var) && count($var) > 0 && array_reduce($var, function($carry, $item) { return $carry || (is_array($item) && count($item) > 0); }, false)) {
				echo '<script>console.table('. json_encode($var, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .');</script>';
				continue;
			}

			// Output as regular log
			echo '<script>console.log('. json_encode($var, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .');</script>';
		}
	}
