<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## datetime_format — basic format specifiers
		########################################################################

		$timestamp = mktime(14, 30, 45, 6, 15, 2025);

		// Year
		$result = f::datetime_format('%Y', $timestamp);
		if ($result !== '2025') {
			throw new Exception('datetime_format %Y failed: got "'. $result .'"');
		}

		// Month (zero-padded)
		$result = f::datetime_format('%m', $timestamp);
		if ($result !== '06') {
			throw new Exception('datetime_format %m failed: got "'. $result .'"');
		}

		// Day (zero-padded)
		$result = f::datetime_format('%d', $timestamp);
		if ($result !== '15') {
			throw new Exception('datetime_format %d failed: got "'. $result .'"');
		}

		// Hour (24h)
		$result = f::datetime_format('%H', $timestamp);
		if ($result !== '14') {
			throw new Exception('datetime_format %H failed: got "'. $result .'"');
		}

		// Minute
		$result = f::datetime_format('%M', $timestamp);
		if ($result !== '30') {
			throw new Exception('datetime_format %M failed: got "'. $result .'"');
		}

		// Second
		$result = f::datetime_format('%S', $timestamp);
		if ($result !== '45') {
			throw new Exception('datetime_format %S failed: got "'. $result .'"');
		}

		########################################################################
		## datetime_format — ISO date
		########################################################################

		$result = f::datetime_format('%F', $timestamp);
		if ($result !== '2025-06-15') {
			throw new Exception('datetime_format %F (ISO date) failed: got "'. $result .'"');
		}

		########################################################################
		## datetime_format — string timestamp input
		########################################################################

		$result = f::datetime_format('%Y-%m-%d', '2025-12-25');
		if ($result !== '2025-12-25') {
			throw new Exception('datetime_format with string input failed: got "'. $result .'"');
		}

		########################################################################
		## datetime_format — escaped percent
		########################################################################

		$result = f::datetime_format('100%%', $timestamp);
		if ($result !== '100%') {
			throw new Exception('datetime_format escaped percent failed: got "'. $result .'"');
		}

		########################################################################
		## datetime_format — null returns current time
		########################################################################

		$result = f::datetime_format('%Y');
		if ($result !== date('Y')) {
			throw new Exception('datetime_format with null timestamp failed');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
