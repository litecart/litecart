<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	// Minimal server context required by the scheme/host default-fill logic
	$_SERVER['HTTP_HOST']   = 'localhost';
	$_SERVER['SERVER_PORT'] = '80';
	unset($_SERVER['HTTPS']);

	try {

		########################################################################
		## TC-01: Constructor parses scheme, host, and path
		########################################################################

		$url = new type_url('https://example.com/path/to/page');

		if ($url->scheme !== 'https') {
			throw new Exception('TC-01: scheme not parsed (got "'. $url->scheme .'")');
		}

		if ($url->host !== 'example.com') {
			throw new Exception('TC-01: host not parsed (got "'. $url->host .'")');
		}

		if ($url->path !== '/path/to/page') {
			throw new Exception('TC-01: path not parsed (got "'. $url->path .'")');
		}

		########################################################################
		## TC-02: Constructor parses query string into an associative array
		########################################################################

		$url = new type_url('https://example.com/search?q=hello&page=2');

		if (!is_array($url->query)) {
			throw new Exception('TC-02: query should be an array');
		}

		if ($url->query['q'] !== 'hello' || $url->query['page'] !== '2') {
			throw new Exception('TC-02: query params not parsed correctly');
		}

		########################################################################
		## TC-03: Constructor parses fragment
		########################################################################

		$url = new type_url('https://example.com/page#section');

		if ($url->fragment !== 'section') {
			throw new Exception('TC-03: fragment not parsed (got "'. $url->fragment .'")');
		}

		########################################################################
		## TC-04: Constructor parses user, pass, and port
		########################################################################

		$url = new type_url('https://user:pass@example.com:8080/path');

		if ($url->user !== 'user') {
			throw new Exception('TC-04: user not parsed (got "'. $url->user .'")');
		}

		if ($url->pass !== 'pass') {
			throw new Exception('TC-04: pass not parsed (got "'. $url->pass .'")');
		}

		if ((string)$url->port !== '8080') {
			throw new Exception('TC-04: port not parsed (got "'. $url->port .'")');
		}

		########################################################################
		## TC-05: Constructor accepts an array of components
		########################################################################

		$url = new type_url([
			'scheme' => 'https',
			'host'   => 'example.com',
			'path'   => '/test',
		]);

		if ($url->scheme !== 'https' || $url->host !== 'example.com' || $url->path !== '/test') {
			throw new Exception('TC-05: array constructor failed');
		}

		########################################################################
		## TC-06: __toString builds a plain URL correctly
		########################################################################

		$url = new type_url('https://example.com/path/to/page');
		$str = (string) $url;

		if ($str !== 'https://example.com/path/to/page') {
			throw new Exception('TC-06: __toString failed (got "'. $str .'")');
		}

		########################################################################
		## TC-07: __toString includes query string and fragment
		########################################################################

		$url = new type_url('https://example.com/search?q=hello&page=2#results');
		$str = (string) $url;

		if (strpos($str, 'q=hello') === false || strpos($str, 'page=2') === false) {
			throw new Exception('TC-07: __toString missing query params (got "'. $str .'")');
		}

		if (strpos($str, '#results') === false) {
			throw new Exception('TC-07: __toString missing fragment (got "'. $str .'")');
		}

		########################################################################
		## TC-08: __toString includes user:pass credentials
		########################################################################

		$url = new type_url('https://admin:secret@example.com/dashboard');
		$str = (string) $url;

		if (strpos($str, 'admin:secret@') === false) {
			throw new Exception('TC-08: __toString missing credentials (got "'. $str .'")');
		}

		########################################################################
		## TC-09: __toString includes a non-standard port
		########################################################################

		$url = new type_url('https://example.com:8080/path');
		$str = (string) $url;

		if (strpos($str, ':8080') === false) {
			throw new Exception('TC-09: __toString missing non-standard port (got "'. $str .'")');
		}

		########################################################################
		## TC-10: set_query() adds individual query parameters
		########################################################################

		$url = new type_url('https://example.com/search');
		$url->set_query('q', 'hello');
		$url->set_query('page', '1');
		$str = (string) $url;

		if (strpos($str, 'q=hello') === false) {
			throw new Exception('TC-10: set_query() failed to set q param (got "'. $str .'")');
		}

		if (strpos($str, 'page=1') === false) {
			throw new Exception('TC-10: set_query() failed to set page param (got "'. $str .'")');
		}

		########################################################################
		## TC-11: set_query() returns $this for chaining
		########################################################################

		$url = new type_url('https://example.com/path');
		$result = $url->set_query('a', '1');

		if (!($result instanceof type_url)) {
			throw new Exception('TC-11: set_query() should return $this');
		}

		########################################################################
		## TC-12: unset_query() removes a single parameter by key
		########################################################################

		$url = new type_url('https://example.com/search?q=hello&page=2');
		$url->unset_query('page');
		$str = (string) $url;

		if (strpos($str, 'page=2') !== false) {
			throw new Exception('TC-12: unset_query() failed to remove page param (got "'. $str .'")');
		}

		if (strpos($str, 'q=hello') === false) {
			throw new Exception('TC-12: unset_query() removed the wrong param');
		}

		########################################################################
		## TC-13: unset_query() with an array removes matched key-value pairs
		########################################################################

		$url = new type_url('https://example.com/search?a=1&b=2&c=3');
		$url->unset_query(['a' => '1', 'b' => '2']);
		$str = (string) $url;

		if (strpos($str, 'a=1') !== false || strpos($str, 'b=2') !== false) {
			throw new Exception('TC-13: unset_query() array form failed to remove matched params (got "'. $str .'")');
		}

		if (strpos($str, 'c=3') === false) {
			throw new Exception('TC-13: unset_query() array form removed an unmatched param');
		}

		########################################################################
		## TC-14: path setter resolves .. segments
		########################################################################

		$url = new type_url('https://example.com/a/b/c');
		$url->path = '/a/b/../d';

		if ($url->path !== '/a/d') {
			throw new Exception('TC-14: path .. resolution failed (got "'. $url->path .'")');
		}

		########################################################################
		## TC-15: path setter resolves multiple .. segments
		########################################################################

		$url = new type_url('https://example.com/');
		$url->path = '/a/b/c/../../d';

		if ($url->path !== '/a/d') {
			throw new Exception('TC-15: path multiple .. resolution failed (got "'. $url->path .'")');
		}

		########################################################################
		## TC-16: query setter accepts a query string and converts to array
		########################################################################

		$url = new type_url('https://example.com/path');
		$url->query = 'foo=bar&baz=qux';

		if (!is_array($url->query)) {
			throw new Exception('TC-16: query setter did not parse string to array');
		}

		if ($url->query['foo'] !== 'bar' || $url->query['baz'] !== 'qux') {
			throw new Exception('TC-16: query setter parsed wrong values');
		}

		########################################################################
		## TC-17: query setter also accepts an array directly
		########################################################################

		$url = new type_url('https://example.com/path');
		$url->query = ['key' => 'value'];

		if (!is_array($url->query) || $url->query['key'] !== 'value') {
			throw new Exception('TC-17: query setter failed for array input');
		}

		########################################################################
		## TC-18: reset() clears fragment and query
		########################################################################

		$url = new type_url('https://example.com/path?q=1#section');
		$url->reset();

		if ($url->fragment !== '') {
			throw new Exception('TC-18: reset() did not clear fragment (got "'. $url->fragment .'")');
		}

		if ($url->query !== []) {
			throw new Exception('TC-18: reset() did not clear query');
		}

		########################################################################
		## TC-19: scheme setter defaults to http when host differs from HTTP_HOST
		########################################################################

		$url = new type_url([
			'host' => 'other.com',
			'path' => '/page',
		]);
		$url->scheme = '';  // Trigger default-fill: host != HTTP_HOST → http

		if ($url->scheme !== 'http') {
			throw new Exception('TC-19: scheme default should be http for external host (got "'. $url->scheme .'")');
		}

		########################################################################
		## TC-20: host setter defaults to HTTP_HOST when empty
		########################################################################

		$url = new type_url(['scheme' => 'https', 'path' => '/page']);
		$url->host = '';

		if ($url->host !== 'localhost') {
			throw new Exception('TC-20: host setter did not fall back to HTTP_HOST (got "'. $url->host .'")');
		}

		########################################################################
		## TC-21: jsonSerialize() returns the same string as __toString()
		########################################################################

		$url = new type_url('https://example.com/path?q=1');
		$json = json_encode($url);

		if (json_decode($json) !== (string)$url) {
			throw new Exception('TC-21: jsonSerialize() mismatch (got '. $json .')');
		}

		########################################################################
		## TC-22: Unknown component triggers E_USER_WARNING
		########################################################################

		$warning_triggered = false;
		set_error_handler(function() use (&$warning_triggered) { $warning_triggered = true; }, E_USER_WARNING);
		$_ = (new type_url('https://example.com/'))->nonExistent;
		restore_error_handler();

		if (!$warning_triggered) {
			throw new Exception('TC-22: Expected E_USER_WARNING for unknown component');
		}

		########################################################################
		## TC-23: Setting an unknown component triggers E_USER_WARNING
		########################################################################

		$warning_triggered = false;
		set_error_handler(function() use (&$warning_triggered) { $warning_triggered = true; }, E_USER_WARNING);
		$url = new type_url('https://example.com/');
		$url->nonExistent = 'value';
		restore_error_handler();

		if (!$warning_triggered) {
			throw new Exception('TC-23: Expected E_USER_WARNING for setting unknown component');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
