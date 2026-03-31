<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## string_split
		########################################################################

		$result = f::string_split('a, b,,c,');

		if ($result !== ['a', 'b', 'c']) {
			throw new Exception('string_split failed to split and trim comma-separated values');
		}

		$result = f::string_split('');

		if ($result !== []) {
			throw new Exception('string_split failed on empty string');
		}

		########################################################################
		## string_ellipsis — truncate
		########################################################################

		$result = f::string_ellipsis('Hello World', 5);

		if ($result !== 'Hello…') {
			throw new Exception('string_ellipsis failed to truncate: got "'. $result .'"');
		}

		########################################################################
		## string_ellipsis — no truncation needed
		########################################################################

		$result = f::string_ellipsis('Short', 10);

		if ($result !== 'Short') {
			throw new Exception('string_ellipsis truncated a short string');
		}

		########################################################################
		## string_ellipsis — empty string
		########################################################################

		$result = f::string_ellipsis('', 5);

		if ($result !== '') {
			throw new Exception('string_ellipsis failed on empty string');
		}

		########################################################################
		## string_ellipsis — negative length (end truncation)
		########################################################################

		$result = f::string_ellipsis('Hello World', -5);

		if ($result !== '…World') {
			throw new Exception('string_ellipsis failed with negative length: got "'. $result .'"');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
