<?php

	// Turns "a, b,,c," into ['a', 'b', 'c']. For line breaks pass $delimiter \r\n.
	function string_split(string $string, string $delimiters=','): array {
		return preg_split('#(\s*['. preg_quote($delimiters, '#') .']\s*)+#', $string, -1, PREG_SPLIT_NO_EMPTY);
	}

	// Turns string into str... or ...str
	function string_ellipsis(string $string, int $length=0, string $ellipsis='…'): string {

		if (!$string) return '';

		if ($length < 0) {
			return $ellipsis . mb_substr($string, $length);
		}

		if (mb_strlen($string) <= $length) {
			return $string;
		}

		if ($length <= 0) {
			return $ellipsis . mb_substr($string, $length);
		}

		return mb_substr($string, 0, $length) . $ellipsis;
	}
