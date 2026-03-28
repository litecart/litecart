<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## escape_html
		########################################################################

		if (f::escape_html('<script>alert("xss")</script>') !== '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;') {
			throw new Exception('escape_html failed to escape HTML tags');
		}

		$escaped = f::escape_html("it's");
		if (strpos($escaped, "'") !== false) {
			throw new Exception('escape_html did not escape single quotes: '. $escaped);
		}

		if (f::escape_html('') !== '') {
			throw new Exception('escape_html failed on empty string');
		}

		if (f::escape_html(null) !== '') {
			throw new Exception('escape_html failed on null');
		}

		########################################################################
		## escape_attr
		########################################################################

		if (strpos(f::escape_attr("line1\nline2"), '\n') === false) {
			throw new Exception('escape_attr failed to escape newlines');
		}

		########################################################################
		## escape_js
		########################################################################

		$js_input = 'He said "hello" and \'bye\'';
		$js_escaped = f::escape_js($js_input);

		if (strpos($js_escaped, '"') !== false && strpos($js_escaped, '\"') === false) {
			throw new Exception('escape_js failed to escape double quotes');
		}

		########################################################################
		## escape_mysql
		########################################################################

		$sql_input = "Robert'; DROP TABLE users;--";
		$sql_escaped = f::escape_mysql($sql_input);

		if (strpos($sql_escaped, "\\'") === false) {
			throw new Exception('escape_mysql failed to escape single quote');
		}

		########################################################################
		## escape_mysql_like
		########################################################################

		$like_input = "100% match_test";
		$like_escaped = f::escape_mysql_like($like_input);

		if (strpos($like_escaped, '\\_') === false) {
			throw new Exception('escape_mysql_like failed to escape underscore');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
