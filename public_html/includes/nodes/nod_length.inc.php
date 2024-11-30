<?php

	class length {

		public static $units = [
			'm' => [
				'name' => 'Metres',
				'unit' => 'm',
				'value' => 1,
				'decimals' => 2,
			],
			'cm' => [
				'name' => 'Centimetres',
				'unit' => 'cm',
				'value' => 100,
				'decimals' => 1,
			],
			'dm' => [
				'name' => 'Decimetres',
				'unit' => 'dm',
				'value' => 10,
				'decimals' => 2,
			],
			'ft' => [
				'name' => 'Feet',
				'unit' => 'ft',
				'value' => 3.2808,
				'decimals' => 2,
			],
			'in' => [
				'name' => 'Inches',
				'unit' => 'in',
				'value' => 39.37,
				'decimals' => 2,
			],
			'km' => [
				'name' => 'Kilometres',
				'unit' => 'km',
				'value' => 0.001,
				'decimals' => 2,
			],
			'mi' => [
				'name' => 'Miles',
				'unit' => 'mi',
				'value' => 0.00062137119224,
				'decimals' => 2,
			],
			'mm' => [
				'name' => 'Millimetres',
				'unit' => 'mm',
				'value' => 1000,
				'decimals' => 0,
			],
			'yd' => [
				'name' => 'Yards',
				'unit' => 'yd',
				'value' => 1.0936133,
				'decimals' => 2,
			],
		];

		public static function convert($value, $from, $to) {

			if ((float)$value == 0) {
				return 0;
			}

			if ($from == $to) {
				return (float)$value;
			}

			if (!isset(self::$units[$from])) {
				trigger_error('The unit '. $from .' is not a valid length class.', E_USER_WARNING);
				return false;
			}

			if (!isset(self::$units[$to])) {
				trigger_error('The unit '. $to .' is not a valid length class.', E_USER_WARNING);
				return false;
			}

			if ((float)self::$units[$from]['value'] == 0 || (float)self::$units[$to]['value'] == 0) {
				return 0;
			}

			return $value * (self::$units[$to]['value'] / self::$units[$from]['value']);
		}

		public static function format($value, $unit) {

			if (!isset(self::$units[$unit])) {
				trigger_error('Invalid length unit ('. $unit .')', E_USER_WARNING);
				return language::number_format((float)$value, 2);
			}

			$decimals = self::$units[$unit]['decimals'];

			$formatted_value = language::number_format((float)$value, (int)$decimals) .' '. self::$units[$unit]['unit'];

			return $formatted_value;
		}
	}
