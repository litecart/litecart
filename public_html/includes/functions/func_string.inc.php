<?php

	// Turns "a, b,,c," into ['a', 'b', 'c'], also works with \r\n
	function string_split($string, $delimiters=',') {
		return preg_split('#(\s*['. preg_quote($delimiters, '#') .']\s*)+#', $string, -1, PREG_SPLIT_NO_EMPTY);
	}

	// Turns string into str... or ...str
	function string_ellipsis($string, $length=0, $ellipsis='…') {
		if (!$string) return '';
		if (mb_strlen($string) <= $length) return $string;
		if ($length <= 0) return $ellipsis . mb_substr($string, $length);
		return mb_substr($string, 0, $length) . $ellipsis;
	}
