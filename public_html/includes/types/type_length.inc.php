<?php

	/*
		Example usage:
		$length = new type_length(150, 'cm');
		echo $length->convert('m'); // 1.5 m
		echo $length; // 1.5 m
	*/

	class type_length implements JsonSerializable {

		public $value;
		public $unit;

		public const UNITS = [
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

		public function __construct($value, $unit) {

			if (!isset(self::UNITS[$unit])) {
				trigger_error('The unit '. $unit .' is not a valid length unit.', E_USER_WARNING);
			}

			$this->value = (float)$value;
			$this->unit = $unit;
		}

		public function __toString() {
			return $this->format();
		}

		public function jsonSerialize(): mixed {
			return $this->value;
		}

		public function convert($to) {

			$to = strtolower($to);

			if ($this->value == 0) {
				return $this;
			}

			if ($this->unit == $to) {
				return $this;
			}

			if (!isset(self::UNITS[$to])) {
				trigger_error('The unit '. $to .' is not a valid length unit.', E_USER_WARNING);
				return false;
			}

			$this->value = $this->value * (self::UNITS[$to]['value'] / self::UNITS[$this->unit]['value']);
			$this->unit = $to;

			return $this;
		}

		public function format() {
			$decimals = self::UNITS[$this->unit]['decimals'];
			$formatted = f::format_number($this->value, $decimals) .' '. self::UNITS[$this->unit]['unit'];
			return $formatted;
		}
	}
