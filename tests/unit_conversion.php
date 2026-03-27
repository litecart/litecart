<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## Weight conversion
		########################################################################

		// kg to g
		$result = weight::convert(1, 'kg', 'g');
		if (abs($result - 1000) > 1) {
			throw new Exception('Weight: 1 kg should be ~1000 g, got '. $result);
		}

		// kg to lb
		$result = weight::convert(1, 'kg', 'lb');
		if (abs($result - 2.2046) > 0.01) {
			throw new Exception('Weight: 1 kg should be ~2.2 lb, got '. $result);
		}

		// Same unit returns same value
		$result = weight::convert(5.5, 'kg', 'kg');
		if ($result !== 5.5) {
			throw new Exception('Weight: same unit conversion should return input');
		}

		// Zero returns zero
		$result = weight::convert(0, 'kg', 'lb');
		if ($result !== 0) {
			throw new Exception('Weight: zero should return zero');
		}

		// Invalid unit returns false
		$result = @weight::convert(1, 'kg', 'invalid');
		if ($result !== false) {
			throw new Exception('Weight: invalid unit should return false');
		}

		########################################################################
		## Length conversion
		########################################################################

		// m to cm
		$result = length::convert(1, 'm', 'cm');
		if (abs($result - 100) > 1) {
			throw new Exception('Length: 1 m should be ~100 cm, got '. $result);
		}

		// m to ft
		$result = length::convert(1, 'm', 'ft');
		if (abs($result - 3.2808) > 0.01) {
			throw new Exception('Length: 1 m should be ~3.28 ft, got '. $result);
		}

		// km to mi
		$result = length::convert(1, 'km', 'mi');
		// 1 km = 0.001 m base, 1 mi = 0.00062137 m base
		// conversion: 1 * (0.00062137 / 0.001) = 0.62137
		if (abs($result - 0.62137) > 0.01) {
			throw new Exception('Length: 1 km should be ~0.62 mi, got '. $result);
		}

		########################################################################
		## Volume conversion
		########################################################################

		// L to gal (US)
		$result = volume::convert(1, 'L', 'gal');
		if (abs($result - 0.26417) > 0.01) {
			throw new Exception('Volume: 1 L should be ~0.264 gal, got '. $result);
		}

		// L to cm3 (mL)
		$result = volume::convert(1, 'L', 'cm3');
		if (abs($result - 1000) > 1) {
			throw new Exception('Volume: 1 L should be ~1000 cm3, got '. $result);
		}

		########################################################################
		## Unit definitions
		########################################################################

		// Verify all unit classes have required keys
		foreach (['length', 'weight', 'volume'] as $class) {
			foreach ($class::$units as $code => $unit) {
				if (!isset($unit['name']) || !isset($unit['unit']) || !isset($unit['value']) || !isset($unit['decimals'])) {
					throw new Exception(ucfirst($class) .': unit "'. $code .'" missing required keys');
				}
			}
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
