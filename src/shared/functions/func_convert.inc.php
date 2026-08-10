<?php

	function convert_characters(mixed $variable, string $from_charset, string $to_charset): mixed {

		if ($from_charset == $to_charset) {
			return $variable;
		}

		if (!$from_charset) {
			$from_charset = mb_internal_encoding();
		}

		if (!$to_charset) {
			$to_charset = mb_internal_encoding();
		}

		if (!in_array(strtoupper($from_charset), mb_list_encodings())) {
			trigger_error('Unknown charset: '. f::escape_html($from_charset), E_USER_WARNING);
			return false;
		}

		if (!in_array(strtoupper($to_charset), mb_list_encodings())) {
			trigger_error('Unknown charset: '. f::escape_html($to_charset), E_USER_WARNING);
			return false;
		}

		if (!mb_convert_variables($to_charset, $from_charset, $variable)) {
			trigger_error('Could not encode variable from '. f::escape_html($from_charset) .' to '. f::escape_html($to_charset), E_USER_WARNING);
			return false;
		}

		return $variable;
	}

	function convert_currency(float $amount, string $from_currency_code, string $to_currency_code): float|false {

		if ($from_currency_code == $to_currency_code) {
			return $amount;
		}

		if (!isset(type_currency::CURRENCIES[$from_currency_code])) {
			trigger_error('Unknown currency code: '. f::escape_html($from_currency_code), E_USER_WARNING);
			return false;
		}

		if (!isset(type_currency::CURRENCIES[$to_currency_code])) {
			trigger_error('Unknown currency code: '. f::escape_html($to_currency_code), E_USER_WARNING);
			return false;
		}

		return (float)$amount * type_currency::CURRENCIES[$to_currency_code]['value'] / type_currency::CURRENCIES[$from_currency_code]['value'];
	}

	function convert_length(float|null $length, string $from_unit, string $to_unit): float|false {

		if ($length === null) {
			return null;
		}

		if ($from_unit == $to_unit) {
			return $length;
		}

		if (!isset(type_length::UNITS[$from_unit])) {
			trigger_error('Unknown length unit: '. f::escape_html($from_unit), E_USER_WARNING);
			return false;
		}

		if (!isset(type_length::UNITS[$to_unit])) {
			trigger_error('Unknown length unit: '. f::escape_html($to_unit), E_USER_WARNING);
			return false;
		}

		return (float)$length * type_length::UNITS[$to_unit] / type_length::UNITS[$from_unit];
	}

	function convert_weight(float|null $weight, string $from_unit, string $to_unit): float|false {

		if ($weight === null) {
			return null;
		}

		if ($from_unit == $to_unit) {
			return $weight;
		}

		if (!isset(type_weight::UNITS[$from_unit])) {
			trigger_error('Unknown weight unit: '. f::escape_html($from_unit), E_USER_WARNING);
			return false;
		}

		if (!isset(type_weight::UNITS[$to_unit])) {
			trigger_error('Unknown weight unit: '. f::escape_html($to_unit), E_USER_WARNING);
			return false;
		}

		return (float)$weight * type_weight::UNITS[$to_unit] / type_weight::UNITS[$from_unit];
	}

	function convert_volume(float|null $volume, string $from_unit, string $to_unit): float|false {

		if ($volume === null) {
			return null;
		}

		if ($from_unit == $to_unit) {
			return $volume;
		}

		if (!isset(type_volume::UNITS[$from_unit])) {
			trigger_error('Unknown volume unit: '. f::escape_html($from_unit), E_USER_WARNING);
			return false;
		}

		if (!isset(type_volume::UNITS[$to_unit])) {
			trigger_error('Unknown volume unit: '. f::escape_html($to_unit), E_USER_WARNING);
			return false;
		}

		return (float)$volume * type_volume::UNITS[$to_unit] / type_volume::UNITS[$from_unit];
	}
