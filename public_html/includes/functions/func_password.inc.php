<?php

	function password_generate($length=8, $min_lowercases=1, $min_uppercases=1, $min_numbers=1, $min_specials=0) {

		$lowercases = 'abcdefghijklmnopqrstuvwxyz';
		$uppercases = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$numbers = '0123456789';
		$specials = '!#%&/(){}[]+-';

		// Pick $count random characters from $charset using CSPRNG
		$pick = function($charset, $count) {
			$result = '';
			$max = strlen($charset) - 1;
			for ($i = 0; $i < $count; $i++) {
				$result .= $charset[random_int(0, $max)];
			}
			return $result;
		};

		$absolutes = '';
		if ($min_lowercases && !is_bool($min_lowercases)) $absolutes .= $pick($lowercases, $min_lowercases);
		if ($min_uppercases && !is_bool($min_uppercases)) $absolutes .= $pick($uppercases, $min_uppercases);
		if ($min_numbers && !is_bool($min_numbers)) $absolutes .= $pick($numbers, $min_numbers);
		if ($min_specials && !is_bool($min_specials)) $absolutes .= $pick($specials, $min_specials);

		$remaining = $length - strlen($absolutes);

		$pool = '';
		if ($min_lowercases !== false) $pool .= $lowercases;
		if ($min_uppercases !== false) $pool .= $uppercases;
		if ($min_numbers !== false) $pool .= $numbers;
		if ($min_specials !== false) $pool .= $specials;

		$password = $absolutes . $pick($pool, $remaining);

		// Fisher-Yates shuffle with CSPRNG
		$chars = str_split($password);
		for ($i = count($chars) - 1; $i > 0; $i--) {
			$j = random_int(0, $i);
			[$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
		}

		return implode('', $chars);
	}

	function password_check_strength($password) {

		if (strlen($password) < 12) return false;

		preg_replace('#[a-z]#', '', $password, -1, $lowercases);
		preg_replace('#[A-Z]#', '', $password, -1, $uppercases);
		preg_replace('#\d#', '', $password, -1, $numbers);
		preg_replace('#[^\w]#', '', $password, -1, $symbols);

		$score = ($numbers * 9) + ($lowercases * 11.25) + ($uppercases * 11.25) + ($symbols * 15)
					 + ($numbers ? 10 : 0) + ($lowercases ? 10 : 0) + ($uppercases ? 10 : 0) + ($symbols ? 10 : 0);

		return ($score >= 80);
	}
