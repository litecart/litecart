<?php

	/*
		Example usage:
		$volume = new type_volume(1.5, 'l');
		echo $volume->convert('cl'); // 150 cl
		echo $volume; // 150 cl
	*/

	class type_volume implements JsonSerializable {

		public $value;
		public $unit;

		public const UNITS = [
			'l' => [
				'name' => 'Liters',
				'unit' => 'l',
				'value' => 1,
				'decimals' => 2,
			],
			'cl' => [
				'name' => 'Centiliters',
				'unit' => 'cl',
				'value' => 0.01,
				'decimals' => 0,
			],
			'dl' => [
				'name' => 'Deciliters',
				'unit' => 'dl',
				'value' => 0.1,
				'decimals' => 1,
			],
			'dm3' => [
				'name' => 'Cubic Decimeters',
				'unit' => 'dm3',
				'value' => 1,
				'decimals' => 2,
			],
			'cm3' => [
				'name' => 'Cubic Centimeters',
				'unit' => 'cm3',
				'value' => 1000,
				'decimals' => 3,
			],
			'ft3' => [
				'name' => 'Cubic Feet',
				'unit' => 'ft3',
				'value' => 0.035314666721,
				'decimals' => 0,
			],
			'gal' => [
				'name' => 'Gallons (US, liquid)',
				'unit' => 'gal',
				'value' =>	0.26417205236,
				'decimals' => 2,
			],
			'in3' => [
				'name' => 'Cubic Inches',
				'unit' => 'in3',
				'value' => 61.023744095,
				'decimals' => 0,
			],
			'm3' => [
				'name' => 'Cubic Metres',
				'unit' => 'm3',
				'value' => 0.001,
				'decimals' => 3,
			],
			'ml' => [
				'name' => 'Milliliters',
				'unit' => 'ml',
				'value' => 0.001,
				'decimals' => 0,
			],
			'oz' => [
				'name' => 'Ounces (US, liquid)',
				'unit' => 'oz',
				'value' => 33.814022701,
				'decimals' => 0,
			],
			'pt' => [
				'name' => 'Pints (UK, liquid)',
				'unit' => 'pt',
				'value' => 1.7597539864,
				'decimals' => 2,
			],
			'qt' => [
				'name' => 'Quarts (US, liquid)',
				'unit' => 'qt',
				'value' => 1.0566882094,
				'decimals' => 2,
			],
			'tbs' => [
				'name' => 'Tablespoons',
				'unit' => 'tbs',
				'value' => 66.666666667,
				'decimals' => 0,
			],
			'tsp' => [
				'name' => 'Teaspoons',
				'unit' => 'tsp',
				'value' => 200,
				'decimals' => 0,
			],
			'yd3' => [
				'name' => 'Cubic Yards',
				'unit' => 'yd3',
				'value' => 0.0013079506193,
				'decimals' => 3,
			],
		];

		public function __construct(float $value, string $unit) {

			$unit = strtolower($unit);

			if (!isset(self::UNITS[$unit])) {
				trigger_error('The unit '. $unit .' is not a valid volume unit.', E_USER_WARNING);
			}

			$this->value = (float)$value;
			$this->unit = $unit;
		}

		public function __toString(): string {
			return $this->format();
		}

		public function jsonSerialize(): mixed {
			return $this->value;
		}

		public function add(float|type_volume $value, $unit = null): self {

			if ($value instanceof type_volume) {
				$volume = $value;
			} else {
				$volume = new self($value, $unit);
			}

			$volume->convert($this->unit);
			$this->value += $volume->value;

			return $this;
		}

		public function convert(string $to): self {

			$to = strtolower($to);

			if ($this->value == 0) {
				return $this;
			}

			if ($this->unit == $to) {
				return $this;
			}

			if (!isset(self::UNITS[$to])) {
				trigger_error('The unit '. $to .' is not a valid volume unit.', E_USER_WARNING);
				return $this;
			}

			$this->value = $this->value * (self::UNITS[$to]['value'] / self::UNITS[$this->unit]['value']);
			$this->unit = $to;

			return $this;
		}

		public function format(): string {
			$decimals = self::UNITS[$this->unit]['decimals'];
			$formatted = f::format_number($this->value, $decimals) .' '. self::UNITS[$this->unit]['unit'];
			return $formatted;
		}
	}
