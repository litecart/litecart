<?php

	class weight {

		public static $units = [
			'kg' => [
				'name' => 'Kilograms',
				'unit' => 'kg',
				'value' => 1,
				'decimals' => 2,
			],
			'g' => [
				'name' => 'Grams',
				'unit' => 'g',
				'value' => 1000,
				'decimals' => 0,
			],
			'dwt' => [
				'name' => 'Pennyweights',
				'unit' => 'dwt',
				'value' => 643.01493137256,
				'decimals' => 0,
			],
			'lb' => [
				'name' => 'Pounds',
				'unit' => 'lb',
				'value' => 2.2046,
				'decimals' => 2,
			],
			'oz' => [
				'name' => 'Ounces',
				'unit' => 'oz',
				'value' => 35.274,
				'decimals' => 1,
			],
			'st' => [
				'name' => 'Stones',
				'unit' => 'st',
				'value' => 0.1575,
				'decimals' => 2,
			],
		];

		public static function convert($value, $from, $to) {

			if ($value == 0) {
				return 0;
			}

			if ($from == $to) {
				return (float)$value;
			}

			if (!isset(self::$units[$from])) {
				trigger_error('The unit '. $from .' is not a valid weight class.', E_USER_WARNING);
				return false;
			}

			if (!isset(self::$units[$to])) {
				trigger_error('The unit '. $to .' is not a valid weight class.', E_USER_WARNING);
				return false;
			}

			if (self::$units[$from]['value'] == 0 || self::$units[$to]['value'] == 0) {
				return 0;
			}

			return $value * (self::$units[$to]['value'] / self::$units[$from]['value']);
		}

		public static function format($value, $unit) {

			if (!isset(self::$units[$unit])) {
				trigger_error('Invalid weight unit ('. $unit .')', E_USER_WARNING);
				return language::number_format((float)$value, 2);
			}

			$decimals = self::$units[$unit]['decimals'];

			$formatted_value = language::number_format((float)$value, $decimals) .' '. self::$units[$unit]['unit'];

			return $formatted_value;
		}
	}
