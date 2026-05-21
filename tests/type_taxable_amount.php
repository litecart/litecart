<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		$store_currency = (string)settings::get('store_currency_code');

		########################################################################
		## TC-01: Basic construction with tax_class_id
		########################################################################

		$amount = new type_taxable_amount(100, $store_currency, 1);

		if ($amount->tax_class_id !== 1) {
			throw new Exception('TC-01: tax_class_id not stored (got '. var_export($amount->tax_class_id, true) .')');
		}

		if ($amount->currency !== $store_currency) {
			throw new Exception('TC-01: currency not stored (got "'. $amount->currency .'")');
		}

		if (abs($amount->net - 100) > 0.001) {
			throw new Exception('TC-01: net amount wrong (got '. $amount->net .')');
		}

		########################################################################
		## TC-02: tax_class_id = null behaves like a plain type_money
		########################################################################

		$amount = new type_taxable_amount(100, $store_currency, null);

		if ($amount->tax_class_id !== null) {
			throw new Exception('TC-02: tax_class_id should be null');
		}

		// With no tax_class_id, gross must equal net and tax must be 0.
		if (abs($amount->gross - $amount->net) > 0.001) {
			throw new Exception('TC-02: gross should equal net when tax_class_id is null (net='. $amount->net .', gross='. $amount->gross .')');
		}

		if ($amount->tax !== 0.0) {
			throw new Exception('TC-02: tax should be 0 when tax_class_id is null (got '. $amount->tax .')');
		}

		if ($amount->tax_rate !== 0.0) {
			throw new Exception('TC-02: tax_rate should be 0 when tax_class_id is null (got '. $amount->tax_rate .')');
		}

		########################################################################
		## TC-03: jsonSerialize includes tax_class_id alongside money payload
		########################################################################

		$amount = new type_taxable_amount(100, $store_currency, 42);
		$serialized = $amount->jsonSerialize();

		if (!is_array($serialized)) {
			throw new Exception('TC-03: jsonSerialize should return an array');
		}

		if (!array_key_exists('tax_class_id', $serialized)) {
			throw new Exception('TC-03: serialized payload missing tax_class_id key');
		}

		if ($serialized['tax_class_id'] !== 42) {
			throw new Exception('TC-03: tax_class_id not preserved in serialization (got '. var_export($serialized['tax_class_id'], true) .')');
		}

		// Money payload still present from parent::jsonSerialize().
		foreach (['amounts', 'mode', 'origin_currency'] as $key) {
			if (!array_key_exists($key, $serialized)) {
				throw new Exception('TC-03: serialized payload missing parent key "'. $key .'"');
			}
		}

		########################################################################
		## TC-04: JSON round-trip preserves tax_class_id and money
		########################################################################

		$original = new type_taxable_amount(100, $store_currency, 7);
		$json = json_encode($original);
		$decoded = json_decode($json, true);
		$rebuilt = new type_taxable_amount($decoded);

		if ($rebuilt->tax_class_id !== 7) {
			throw new Exception('TC-04: tax_class_id lost across json round-trip (got '. var_export($rebuilt->tax_class_id, true) .')');
		}

		if (abs($rebuilt->net - 100) > 0.001) {
			throw new Exception('TC-04: net amount lost across json round-trip (got '. $rebuilt->net .')');
		}

		########################################################################
		## TC-05: Copy constructor preserves tax_class_id
		########################################################################

		$original = new type_taxable_amount(50, $store_currency, 3);
		$copy = new type_taxable_amount($original);

		if ($copy->tax_class_id !== 3) {
			throw new Exception('TC-05: tax_class_id not cloned (got '. var_export($copy->tax_class_id, true) .')');
		}

		if (abs($copy->net - 50) > 0.001) {
			throw new Exception('TC-05: net amount not cloned (got '. $copy->net .')');
		}

		########################################################################
		## TC-06: Construction from type_money (without tax_class) keeps null
		########################################################################

		$money = new type_money(75, $store_currency);
		$amount = new type_taxable_amount($money);

		if ($amount->tax_class_id !== null) {
			throw new Exception('TC-06: tax_class_id should default to null when constructed from type_money (got '. var_export($amount->tax_class_id, true) .')');
		}

		if (abs($amount->net - 75) > 0.001) {
			throw new Exception('TC-06: net amount not carried from type_money (got '. $amount->net .')');
		}

		########################################################################
		## TC-07: Customer-aware variants exist and accept a customer arg
		########################################################################

		$amount = new type_taxable_amount(100, $store_currency, null);

		// With tax_class_id null these must still return floats (not error).
		$gross_for = $amount->gross_for('store');
		$tax_for = $amount->tax_for('store');

		if (!is_float($gross_for) || abs($gross_for - 100) > 0.001) {
			throw new Exception('TC-07: gross_for() should return net when tax_class_id is null (got '. var_export($gross_for, true) .')');
		}

		if (!is_float($tax_for) || $tax_for !== 0.0) {
			throw new Exception('TC-07: tax_for() should return 0.0 when tax_class_id is null (got '. var_export($tax_for, true) .')');
		}

		########################################################################
		## TC-08: Unknown component still triggers warning via parent::__get
		########################################################################

		set_error_handler(function($severity, $message) {
			throw new ErrorException($message, 0, $severity);
		}, E_USER_WARNING);

		try {
			$amount = new type_taxable_amount(100, $store_currency, 1);
			$_ = $amount->unknown_component;
			throw new Exception('TC-08: expected E_USER_WARNING on unknown component');
		} catch (ErrorException $ex) {
			if (!str_contains($ex->getMessage(), 'Unknown money component')) {
				throw new Exception('TC-08: wrong warning message: '. $ex->getMessage());
			}
		} finally {
			restore_error_handler();
		}

		########################################################################
		## TC-09: __toString() falls through to type_money's format()
		########################################################################

		$amount = new type_taxable_amount(100, $store_currency, 1);
		$rendered = (string)$amount;

		if ($rendered === '') {
			throw new Exception('TC-09: __toString() should not yield empty string');
		}

		if (strpos($rendered, '100') === false && strpos($rendered, '100.00') === false) {
			throw new Exception('TC-09: __toString() should reflect the net amount (got "'. $rendered .'")');
		}

		########################################################################
		## TC-10: Fixed-amounts-per-currency mode round-trips with tax_class_id
		########################################################################

		$amount = new type_taxable_amount([$store_currency => 100, 'EUR' => 92], null, 5);

		if ($amount->tax_class_id !== 5) {
			throw new Exception('TC-10: tax_class_id not preserved in fixed-map mode');
		}

		if ($amount->in('EUR') !== 92.0) {
			throw new Exception('TC-10: fixed EUR amount lost (got '. $amount->in('EUR') .')');
		}

		$serialized = json_encode($amount);
		$rebuilt = new type_taxable_amount(json_decode($serialized, true));

		if ($rebuilt->tax_class_id !== 5 || $rebuilt->in('EUR') !== 92.0) {
			throw new Exception('TC-10: round-trip lost data in fixed-map mode');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;

	} finally {
		// No DB writes — no rollback needed
	}
