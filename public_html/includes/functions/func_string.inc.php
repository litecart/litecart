<?php

	function string_split($string, $delimiter=',') {
		return preg_split('#\s*'. preg_quote($delimiter, '#') .'\s*#', $string, -1, PREG_SPLIT_NO_EMPTY);
	}
