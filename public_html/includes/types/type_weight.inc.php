<?php

	/*
		Example usage:
		$weight = new type_weight(150, 'g');
		echo $weight->convert('kg'); // 0.15 kg
		echo $weight; // 0.15 kg
	*/

	class type_weight implements JsonSerializable {

		public $value;
		public $unit;

		public const UNITS = [
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

		public function __construct(float $value, string $unit) {

			$unit = strtolower($unit);

			if (!isset(self::UNITS[$unit])) {
				trigger_error('The unit '. $unit .' is not a valid weight unit.', E_USER_WARNING);
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

		public function convert(string $to): self {

			$to = strtolower($to);

			if ($this->value == 0) {
				return $this;
			}

			if ($this->unit == $to) {
				return $this;
			}

			if (!isset(self::UNITS[$to])) {
				trigger_error('The unit '. $to .' is not a valid weight unit.', E_USER_WARNING);
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
