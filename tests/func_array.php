<?php

	include_once __DIR__.'/../public_html/includes/app_header.inc.php';

	try {

		########################################################################
		## array_between
		########################################################################

		$result = f::array_between([1, 5, 10, 15, 20], 5, 16);

		if (!in_array(5, $result) || !in_array(10, $result) || !in_array(15, $result)) {
			throw new Exception('array_between failed to filter values in range');
		}

		if (in_array(1, $result) || in_array(20, $result)) {
			throw new Exception('array_between included values outside range');
		}

		########################################################################
		## array_column_intact
		########################################################################

		$data = ['a' => ['name' => 'Alpha'], 'b' => ['name' => 'Beta']];
		$result = f::array_column_intact($data, 'name', true);

		if ($result !== ['a' => 'Alpha', 'b' => 'Beta']) {
			throw new Exception('array_column_intact failed to retain original keys');
		}

		########################################################################
		## array_each
		########################################################################

		$result = f::array_each([1, 2, 3, 4], function($v) {
			return $v > 2 ? $v * 10 : null;
		});

		if (!in_array(30, $result) || !in_array(40, $result) || count($result) !== 2) {
			throw new Exception('array_each failed to map and filter');
		}

		########################################################################
		## array_update — only updates existing keys
		########################################################################

		$original = ['name' => 'old', 'status' => 1, 'type' => 'a'];
		$updates = ['name' => 'new', 'extra' => 'ignored'];
		$result = f::array_update($original, $updates);

		if ($result['name'] !== 'new') {
			throw new Exception('array_update failed to update existing key');
		}

		if (isset($result['extra'])) {
			throw new Exception('array_update inserted a new key that should be ignored');
		}

		########################################################################
		## array_intersect_compare
		########################################################################

		$var1 = ['name' => 'Test', 'nested' => ['a' => 1]];
		$var2 = ['name' => 'Test', 'nested' => ['a' => 1, 'b' => 2], 'extra' => 'ok'];

		if (!f::array_intersect_compare($var1, $var2)) {
			throw new Exception('array_intersect_compare failed on matching subset');
		}

		$var1 = ['name' => 'Test'];
		$var2 = ['name' => 'Different'];

		if (f::array_intersect_compare($var1, $var2)) {
			throw new Exception('array_intersect_compare passed on non-matching values');
		}

		########################################################################
		## array_flatten / array_unflatten
		########################################################################

		$nested = ['a' => ['b' => 1, 'c' => 2]];
		$flat = f::array_flatten($nested);

		if ($flat !== ['a.b' => 1, 'a.c' => 2]) {
			throw new Exception('array_flatten failed');
		}

		$unflat = f::array_unflatten($flat);

		if ($unflat !== $nested) {
			throw new Exception('array_unflatten failed to reverse flatten');
		}

		########################################################################
		## array_depth
		########################################################################

		if (f::array_depth([1, 2, 3]) !== 1) {
			throw new Exception('array_depth failed on flat array');
		}

		if (f::array_depth(['a' => ['b' => ['c' => 1]]]) !== 3) {
			throw new Exception('array_depth failed on nested array');
		}

		########################################################################
		## array_grep
		########################################################################

		$data = ['name' => 'John', 'email' => 'john@test.com', 'password' => 'secret'];
		$result = f::array_grep($data, ['name', 'email']);

		if (isset($result['password'])) {
			throw new Exception('array_grep failed to filter keys');
		}

		if ($result['name'] !== 'John' || $result['email'] !== 'john@test.com') {
			throw new Exception('array_grep lost expected values');
		}

		########################################################################
		## array_diff_assoc_recursive
		########################################################################

		$arr1 = ['a' => 1, 'b' => ['c' => 2, 'd' => 3]];
		$arr2 = ['a' => 1, 'b' => ['c' => 2, 'd' => 4]];
		$diff = f::array_diff_assoc_recursive($arr1, $arr2);

		if (!isset($diff['b']['d']) || $diff['b']['d'] !== 3) {
			throw new Exception('array_diff_assoc_recursive failed to detect difference');
		}

		if (isset($diff['a'])) {
			throw new Exception('array_diff_assoc_recursive reported identical values as different');
		}

		return true;

	} catch (Exception $e) {

		echo ' [Failed]'. PHP_EOL . 'Error: '. $e->getMessage();
		return false;
	}
