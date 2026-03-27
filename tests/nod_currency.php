<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## Currencies loaded
		########################################################################

		if (empty(currency::$currencies)) {
			throw new Exception('No currencies loaded');
		}

		########################################################################
		## Selected currency exists
		########################################################################

		if (empty(currency::$selected['code'])) {
			throw new Exception('No currency selected');
		}

		$store_currency = settings::get('store_currency_code');

		if (!isset(currency::$currencies[$store_currency])) {
			throw new Exception('Store currency '. $store_currency .' not in currencies list');
		}

		########################################################################
		## currency::calculate — same currency returns same value
		########################################################################

		$result = currency::calculate(100, $store_currency, $store_currency);

		if (abs($result - 100) > 0.01) {
			throw new Exception('currency::calculate same-to-same should return input, got '. $result);
		}

		########################################################################
		## currency::calculate — zero returns zero
		########################################################################

		$result = currency::calculate(0, $store_currency, $store_currency);

		if ($result != 0) {
			throw new Exception('currency::calculate zero should return zero');
		}

		########################################################################
		## currency::format_raw — returns numeric string
		########################################################################

		$raw = currency::format_raw(99.99, $store_currency);

		if (!is_numeric($raw)) {
			throw new Exception('currency::format_raw should return numeric string, got '. var_export($raw, true));
		}

		########################################################################
		## currency::format — returns formatted string with prefix/suffix
		########################################################################

		$formatted = currency::format(99.99, false, $store_currency);

		if (empty($formatted) || !is_string($formatted)) {
			throw new Exception('currency::format should return non-empty string');
		}

		########################################################################
		## currency::format — zero handling
		########################################################################

		$formatted_zero = currency::format(0, false, $store_currency);

		if (strpos($formatted_zero, '0') === false) {
			throw new Exception('currency::format of zero should contain 0');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
