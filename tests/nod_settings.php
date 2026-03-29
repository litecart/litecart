<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## settings::get — known setting
		########################################################################

		$store_name = settings::get('store_name');

		if (empty($store_name)) {
			throw new Exception('settings::get failed to retrieve store_name');
		}

		########################################################################
		## settings::get — store currency code
		########################################################################

		$currency_code = settings::get('store_currency_code');

		if (empty($currency_code) || !preg_match('#^[A-Z]{3}$#', $currency_code)) {
			throw new Exception('settings::get returned invalid store_currency_code: '. var_export($currency_code, true));
		}

		########################################################################
		## settings::get — nonexistent key with fallback
		########################################################################

		$result = settings::get('nonexistent_setting_key_12345', 'default_value');

		if ($result !== 'default_value') {
			throw new Exception('settings::get fallback failed: got '. var_export($result, true));
		}

		########################################################################
		## settings::set — temporary override
		########################################################################

		$original = settings::get('store_name');
		settings::set('store_name', 'Test Store Override');

		if (settings::get('store_name') !== 'Test Store Override') {
			throw new Exception('settings::set failed to override value');
		}

		// Restore
		settings::set('store_name', $original);

		if (settings::get('store_name') !== $original) {
			throw new Exception('settings::set failed to restore original value');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
