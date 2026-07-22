<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		$store_currency = (string)settings::get('store_currency_code');

		########################################################################
		## TC-01: Single-amount constructor stores normalised to store currency
		########################################################################

		$price = new type_money(100, $store_currency);

		if ($price->currency !== $store_currency) {
			throw new Exception('TC-01: currency not stored (got "'. $price->currency .'")');
		}

		if (abs($price->in($store_currency) - 100) > 0.001) {
			throw new Exception('TC-01: amount mismatch in store currency (got '. $price->in($store_currency) .')');
		}

		if ($price->mode !== type_money::MODE_CONVERTED) {
			throw new Exception('TC-01: expected mode=converted, got "'. $price->mode .'"');
		}

		########################################################################
		## TC-02: Fixed-amounts-per-currency constructor stores verbatim
		########################################################################

		$price = new type_money([
			$store_currency => 100,
			'EUR' => 92,
			'SEK' => 1050
		]);

		if ($price->mode !== type_money::MODE_FIXED) {
			throw new Exception('TC-02: expected mode=fixed, got "'. $price->mode .'"');
		}

		if (!$price->is_fixed) {
			throw new Exception('TC-02: is_fixed should be true');
		}

		if ($price->in('EUR') !== 92.0) {
			throw new Exception('TC-02: fixed EUR amount should be 92.0 verbatim (got '. $price->in('EUR') .')');
		}

		if ($price->in('SEK') !== 1050.0) {
			throw new Exception('TC-02: fixed SEK amount should be 1050.0 verbatim (got '. $price->in('SEK') .')');
		}

		########################################################################
		## TC-03: Fixed-mode falls back to conversion for unlisted currencies
		########################################################################

		$price = new type_money([$store_currency => 100, 'EUR' => 92]);
		$gbp_amount = $price->in('GBP'); // not in the map — must convert from store entry

		// We can't assert an exact rate, but the result must be a positive float
		if (!is_float($gbp_amount) || $gbp_amount <= 0) {
			throw new Exception('TC-03: GBP fallback conversion should yield a positive float (got '. var_export($gbp_amount, true) .')');
		}

		########################################################################
		## TC-04: convert() returns a NEW type_money (immutable, chainable)
		########################################################################

		$usd = new type_money(100, $store_currency);
		$eur = $usd->convert('EUR');

		if (!($eur instanceof type_money)) {
			throw new Exception('TC-04: convert() must return a type_money instance');
		}

		if ($eur === $usd) {
			throw new Exception('TC-04: convert() must return a new object, not the same reference');
		}

		if ($eur->currency !== 'EUR') {
			throw new Exception('TC-04: converted instance should be in EUR (got "'. $eur->currency .'")');
		}

		// Chainable through format()
		if (!is_string($usd->convert('EUR')->format())) {
			throw new Exception('TC-04: chain convert()->format() must yield a string');
		}

		########################################################################
		## TC-05: with_amount() returns a new type_money in the same currency
		########################################################################

		$price = new type_money(100, $store_currency);
		$doubled = $price->with_amount(200);

		if ($doubled === $price || abs($doubled->in($store_currency) - 200) > 0.001) {
			throw new Exception('TC-05: with_amount() must produce a fresh, updated instance');
		}

		if ($doubled->currency !== $store_currency) {
			throw new Exception('TC-05: with_amount() should preserve the currency');
		}

		########################################################################
		## TC-06: type_money copy constructor clones all components
		########################################################################

		$original = new type_money([$store_currency => 100, 'EUR' => 92]);
		$copy = new type_money($original);

		if ($copy->mode !== $original->mode || $copy->currencies !== $original->currencies) {
			throw new Exception('TC-06: copy constructor must clone mode + currencies');
		}

		########################################################################
		## TC-07: jsonSerialize round-trips through json_encode / json_decode
		########################################################################

		$original = new type_money([$store_currency => 100, 'EUR' => 92, 'SEK' => 1050]);
		$serialized = json_encode($original);
		$decoded = json_decode($serialized, true);
		$rebuilt = new type_money($decoded);

		if ($rebuilt->mode !== $original->mode) {
			throw new Exception('TC-07: mode lost across json round-trip');
		}

		if ($rebuilt->in('EUR') !== 92.0 || $rebuilt->in('SEK') !== 1050.0) {
			throw new Exception('TC-07: fixed amounts lost across json round-trip');
		}

		########################################################################
		## TC-08: Magic setters reject mutation (immutable contract)
		########################################################################

		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_WARNING);

		try {
			$price = new type_money(100, $store_currency);
			$price->amount = 200;
			throw new Exception('TC-08: expected E_USER_WARNING on direct property mutation');
		} catch (ErrorException $ex) {
			if (!str_contains($ex->getMessage(), 'immutable')) {
				throw new Exception('TC-08: wrong warning message: '. $ex->getMessage());
			}
		} finally {
			restore_error_handler();
		}

		########################################################################
		## TC-09: __toString() defaults to format() in origin currency
		########################################################################

		$price = new type_money(100, $store_currency);
		$rendered = (string)$price;

		if ($rendered === '') {
			throw new Exception('TC-09: __toString() should not yield empty string');
		}

		// Expect the rendered string to contain the formatted amount somewhere
		if (strpos($rendered, '100') === false && strpos($rendered, '100.00') === false) {
			throw new Exception('TC-09: __toString() output should reflect the amount (got "'. $rendered .'")');
		}

		########################################################################
		## TC-10: Unknown component access triggers warning
		########################################################################

		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_WARNING);

		try {
			$price = new type_money(100, $store_currency);
			$_ = $price->unknown_property;
			throw new Exception('TC-10: expected E_USER_WARNING on unknown component');
		} catch (ErrorException $ex) {
			if (!str_contains($ex->getMessage(), 'Unknown money component')) {
				throw new Exception('TC-10: wrong warning message: '. $ex->getMessage());
			}
		} finally {
			restore_error_handler();
		}

		########################################################################
		## TC-11: currencies getter returns the list of stored currency codes
		########################################################################

		$price = new type_money([$store_currency => 100, 'EUR' => 92, 'SEK' => 1050]);

		if (!in_array($store_currency, $price->currencies) || !in_array('EUR', $price->currencies)) {
			throw new Exception('TC-11: currencies list missing entries');
		}

		########################################################################
		## TC-12: Zero amount handled gracefully
		########################################################################

		$zero = new type_money(0, $store_currency);

		if ($zero->in($store_currency) !== 0.0) {
			throw new Exception('TC-12: zero amount not preserved');
		}

		if ((string)$zero === '') {
			throw new Exception('TC-12: zero should still format to a non-empty string');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
