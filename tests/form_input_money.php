<?php

	include_once __DIR__.'/../public_html/shared/app_header.inc.php';

	try {

		$store_currency = (string)settings::get('store_currency_code');

		########################################################################
		## TC-01: Bare float still works (backwards compatibility)
		########################################################################

		$html = f::form_input_money('price', $store_currency, 99.99);

		if (!is_string($html) || $html === '') {
			throw new Exception('TC-01: expected non-empty HTML string');
		}

		if (strpos($html, 'name="price"') === false) {
			throw new Exception('TC-01: rendered input must carry the form field name');
		}

		if (strpos($html, '99.99') === false) {
			throw new Exception('TC-01: rendered output must contain the bare-float value (got: '. $html .')');
		}

		if (strpos($html, $store_currency) === false) {
			throw new Exception('TC-01: rendered output must contain the currency code');
		}

		########################################################################
		## TC-02: type_money instance accepted as the value
		########################################################################

		$price = new type_money(99.99, $store_currency);
		$html = f::form_input_money('price', $store_currency, $price);

		if (strpos($html, '99.99') === false) {
			throw new Exception('TC-02: type_money value must be rendered as a numeric amount (got: '. $html .')');
		}

		if (strpos($html, 'name="price"') === false) {
			throw new Exception('TC-02: form field name must be preserved when type_money is passed');
		}

		########################################################################
		## TC-03: type_money + null currency_code derives currency from the instance
		########################################################################

		$price = new type_money(42.50, $store_currency);
		$html = f::form_input_money('price', null, $price);

		if (strpos($html, $store_currency) === false) {
			throw new Exception('TC-03: currency_code should be derived from type_money when null is passed (got: '. $html .')');
		}

		if (strpos($html, '42.50') === false) {
			throw new Exception('TC-03: type_money value must render with null currency_code (got: '. $html .')');
		}

		########################################################################
		## TC-04: Empty string as $input still renders an empty value (template stub usage)
		########################################################################

		$html = f::form_input_money('products[__index__][price]['. $store_currency .']', $store_currency, '', 'style="width: 125px;"');

		if (!is_string($html) || $html === '') {
			throw new Exception('TC-04: template-stub call with empty $input must still render');
		}

		if (strpos($html, 'value=""') === false) {
			throw new Exception('TC-04: empty $input must render value="" (got: '. $html .')');
		}

		if (strpos($html, 'style="width: 125px;"') === false) {
			throw new Exception('TC-04: legacy string-attribute syntax must still be honoured');
		}

		########################################################################
		## TC-05: No E_USER_DEPRECATED warning on normal call patterns
		########################################################################

		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_DEPRECATED);

		try {
			$html = f::form_input_money('price', $store_currency, 99.99);
			$html = f::form_input_money('price', $store_currency, new type_money(99.99, $store_currency));
			$html = f::form_input_money('price', null, new type_money(99.99, $store_currency));
		} catch (ErrorException $ex) {
			throw new Exception('TC-05: unexpected E_USER_DEPRECATED on normal call: '. $ex->getMessage());
		} finally {
			restore_error_handler();
		}

		########################################################################
		## TC-06: Legacy swap-hack signature no longer reorders args
		########################################################################
		##
		## Before the refactor: form_input_money('USD', 'price', $v) triggered the
		## regex-sniff swap and emitted E_USER_DEPRECATED. After the refactor: no
		## swap, $name is treated literally as 'USD' (a valid form field name).
		########################################################################

		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_DEPRECATED);

		try {
			$html = f::form_input_money('USD', 'price', 99.99);
		} catch (ErrorException $ex) {
			throw new Exception('TC-06: deprecation-swap hack must be removed (got: '. $ex->getMessage() .')');
		} finally {
			restore_error_handler();
		}

		if (strpos($html, 'name="USD"') === false) {
			throw new Exception('TC-06: $name must be honoured verbatim post-refactor (got: '. $html .')');
		}

		########################################################################
		## TC-07: Currency-specific decimals applied to type_money value
		########################################################################

		if (!empty(currency::$currencies[$store_currency]['decimals'])) {

			$decimals = (int)currency::$currencies[$store_currency]['decimals'];
			$price = new type_money(100, $store_currency);
			$html = f::form_input_money('price', $store_currency, $price);

			$expected = number_format(100, $decimals, '.', '');

			if (strpos($html, $expected) === false) {
				throw new Exception('TC-07: type_money amount must be formatted to currency decimals (expected "'. $expected .'", got: '. $html .')');
			}
		}

		########################################################################
		## TC-08: Array-style attributes still work alongside type_money
		########################################################################

		$price = new type_money(50, $store_currency);
		$html = f::form_input_money('price', $store_currency, $price, ['data-test' => 'ok', 'readonly' => 'readonly']);

		if (strpos($html, 'data-test="ok"') === false) {
			throw new Exception('TC-08: array attributes must merge through (got: '. $html .')');
		}

		if (strpos($html, 'readonly') === false) {
			throw new Exception('TC-08: readonly attribute must merge through');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
