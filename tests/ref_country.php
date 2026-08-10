<?php

	include_once __DIR__.'/../src/shared/app_header.inc.php';

	try {

		########################################################################
		## Load country by ISO code
		########################################################################

		// Note: ref_country uses lazy loading via __get (property access).
		// ArrayAccess offsetGet does NOT trigger _load(), so use -> syntax.

		$us = reference::country('US');

		if (empty($us->name)) {
			throw new Exception('ref_country failed to load US country name');
		}

		if ($us->iso_code_2 !== 'US') {
			throw new Exception('ref_country US has wrong iso_code_2: '. $us->iso_code_2);
		}

		########################################################################
		## Country has currency code
		########################################################################

		if ($us->currency_code !== 'USD') {
			throw new Exception('ref_country US should have currency_code USD, got '. $us->currency_code);
		}

		########################################################################
		## Load zones (may be empty if seed data has no zones)
		########################################################################

		$zones = $us->zones;

		if (!is_array($zones)) {
			throw new Exception('ref_country zones should return an array');
		}

		########################################################################
		## Load another country
		########################################################################

		$de = reference::country('DE');

		if ($de->currency_code !== 'EUR') {
			throw new Exception('ref_country DE should have currency_code EUR, got '. $de->currency_code);
		}

		########################################################################
		## Invalid country returns empty data
		########################################################################

		$invalid = reference::country('XX');

		if (!empty($invalid->name)) {
			throw new Exception('ref_country XX should return empty name for non-existent country');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
