<?php

	// Return array values alphanumerically between $from and $to
	function array_between(array $array, $from, $to):array {
		return array_filter($array, function($node) use ($from, $to) {
			return ($node >= $from && $node < $to);
		});
	}

	// Retain the original array keys when extracting an array column by passing $index_key = true
	function array_column_intact(array $array, int|string|null $column_key, bool|int|string|null $index_key = null): array {
		if ($index_key === true) {
			return array_combine(array_keys($array), array_column($array, $column_key));
		}
		return array_column($array, $column_key, $index_key);
	}

	// Return an array of values that have matching keys and a non-nil value
	// (See array_update() for any matching keys regardless of value)
	function array_collect(array $array, array ...$treasures): array {

		foreach ($treasures as $treasure) {
			foreach ($array as $key => &$value) {
				if (is_array($value) && isset($treasure[$key]) && is_array($treasure[$key])) {
					// Recursively merge nested arrays
					$value = array_update($value, $treasure[$key]);
				} elseif (array_key_exists($key, $treasure) && !nil($treasure[$key])) {
					// Overwrite scalar values if key exists in both arrays
					$value = $treasure[$key];
				}
			}

			// Handle numerical index arrays (merge without regard to keys)
			if (array_is_list($array) && array_is_list($treasure)) {
				$array = array_values(array_unique(array_merge($array, $treasure))); // Prevent duplicates
			}
		}

		return $array;
	}

	// Same as array_map but without speaking Yoda (by doing the callable function last)
	function array_each(array $array, callable $function):array {
		$result = [];

		foreach ($array as $key => $value) {
			$mapped = $function($value, $key);
			if ($mapped !== null) {
				$result[$key] = $mapped;
			}
		}

		return $result;
	}

	// Return an array of values not defined by the given keys
	function array_exclude_keys(array $array, array $excluded_keys):array {
		return array_diff_key($array, array_flip($excluded_keys));
	}

	// Update an array with values that have keys present in another arrays, without inserting new keys.
	// (See array_collect() for not grabbing non-nil values.)
	function array_update(array $array, array ...$replacements): array {

		foreach ($replacements as $updates) {
			foreach ($array as $key => &$value) {
				if (is_array($value) && isset($updates[$key]) && is_array($updates[$key])) {
					// Recursively merge nested arrays
					$value = array_update($value, $updates[$key]);
				} elseif (array_key_exists($key, $updates)) {
					// Overwrite scalar values if key exists in both arrays
					$value = $updates[$key];
				}
			}

			// Handle numerical index arrays (merge without regard to keys)
			if (array_is_list($array) && array_is_list($updates)) {
				$array = array_values(array_unique(array_merge($array, $updates))); // Prevent duplicates
			}
		}

		return $array;
	}

	// Return a filtered array of values from a given list of keys
	function array_grep(array $array, array $matching_keys):array {
		return array_intersect_key($array, array_flip($matching_keys));
	}

	// Function to map array_keys instead of values
	function array_map_keys($callback, $array, ...$args): array {
		$new_keys = array_map($callback, array_keys($array), ...$args);
		return array_combine($new_keys, $array);
	}

	// Get first value from array without shifting it or moving internal cursor
	if (!function_exists('array_first')) { // PHP 8.0+
		function array_first(array $array):mixed {
			if (!$array || !is_array($array)) return false;
			//return $array[array_key_first($array)] || false; // PHP 7.3+
			return reset($array) || false;
		}
	}

	// Get last value from array without shifting it or moving internal cursor
	if (!function_exists('array_last')) { // PHP 8.0+
		function array_last(array $array):mixed {
			if (!$array || !is_array($array)) return false;
			//return $array[array_key_last($array)] || false; // PHP 7.3+
			return end($array) || false;
		}
	}

	// Get a random node from array
	function array_get_random(array $array):mixed {
		if (!$array || !is_array($array)) return false;
		shuffle($array);
		return current($array) || false;
	}

	// Determine the maximum depth of a multidimensional array
	function array_depth(array $array) {
		$max_depth = 1;

		foreach ($array as $value) {
			if (is_array($value)) {
				$depth = array_depth($value) + 1;

				if ($depth > $max_depth) {
					$max_depth = $depth;
				}
			}
		}

		return $max_depth;
	}

	// Filter an array recursively
	function array_filter_recursive($array) {

		foreach ($array as $index_key => $node) {
			if (is_array($node)) {
				$array[$index_key] = array_filter_recursive($node);
			}
		}

		return array_filter($array, function($value) {
			return is_array($value) ? !empty($value) : strlen(trim($value));
		});
	}

	// Turn an array of [foo => [bar => ...]] into foo.bar => ...
	function array_flatten($array, $delimiter='.', $preceding='') {

		$result = [];

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result = $result + array_flatten($value, $delimiter, $key.$delimiter);
			} else {
				$result[$preceding.$key] = $value;
			}
		}

		return $result;
	}

	// Turn an array of [foo.bar => ...] into [foo => [bar => ...]]
	function array_unflatten($array, $delimiter='.') {

		$result = [];

		foreach ($array as $key => $value) {
			$keys = explode($delimiter, $key);
			$temp = &$result;

			foreach ($keys as $k) {
				$temp = &$temp[$k];
			}

			$temp = $value;
		}

		return $result;
	}

	// Group values of matching keys array_group_keys(['a' => '1', 'b' => '1'], ['a' => '2', 'b' => '2']) : ['a' => ['1', '2'], ['b' => ['1', '2']]
	function array_group_keys(...$arrays) {
		return array_merge_recursive(...$arrays);
	}

	// Checking if array keys in var1 is present in var2 and their values equals the same
	function array_intersect_compare($var1, $var2) {

		// Check if both variables are arrays
		if (is_array($var1) && is_array($var2)) {

			// Check if $var1 has a numerical index
			$is_numerical_index = array_is_list($var1);

			foreach ($var1 as $key => $value) {

				if ($is_numerical_index) {

					// Check if the value exists somewhere in the indexed array
					if (!in_array($value, $var2, true)) {
						return false;
					}

				} else {

					// If associative array key does not exist in $var2
					if (!array_key_exists($key, $var2)) {
						continue;
					}

					// Recursively compare values
					if (!array_intersect_compare($value, $var2[$key])) {
						return false;
					}
				}
			}

			return true;
		}

		// Compare values directly
		return $var1 == $var2;
	}

	function array_diff_assoc_recursive(array $array1, array $array2): array {
		$result = [];

		foreach ($array1 as $key => $value) {
			if (array_key_exists($key, $array2)) {
				if (is_array($value)) {
					$recursive_diff = array_diff_assoc_recursive($value, $array2[$key]);
					if (!empty($recursive_diff)) {
						$result[$key] = $recursive_diff;
					}
				} elseif ($value !== $array2[$key]) {
					$result[$key] = $value;
				}
			} else {
				$result[$key] = $value;
			}
		}

		return $result;
	}
