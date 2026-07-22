<?php

	/*
		Represents a monetary amount that knows its tax class. Composes naturally
		with type_money — inherits dual-mode money handling and JSON serialization,
		adds net/gross/tax accessors that delegate to the tax:: node.

		    $amount = new type_taxable_amount(100, $tax_class_id, 'USD');
		    echo $amount->net;        // 100
		    echo $amount->gross;      // 100 + tax via tax::get_price()
		    echo $amount->tax;        // tax via tax::get_tax()
		    echo $amount->tax_rate;   // average rate (sum / count)
		    echo $amount;             // formatted in $amount->currency_code

		Customer context defaults to the active customer (tax::get_rates() 'customer'
		preset). For region-aware resolution pass a customer array via gross_for()
		/ tax_for().

		Drop-in for entity layers that store (amount, tax_class_id, currency)
		triplets — jsonSerialize() preserves tax_class_id alongside the money payload.
	*/

	class type_taxable_amount extends type_money implements \JsonSerializable {

		private $_amount;
		private $_tax_class_id;

		public function __construct(mixed $amount = 0, int|null $tax_class_id = null, ?string $currency_code = null) {

			if ($amount instanceof self) {
				$this->_tax_class_id = $tax_class_id ?? $amount->tax_class_id;
				parent::__construct($amount, $currency_code);
				return;
			}

			if (is_array($amount) && array_key_exists('tax_class_id', $amount)) {
				$tax_class_id = $tax_class_id ?? ($amount['tax_class_id'] === null ? null : (int)$amount['tax_class_id']);
			}

			$this->_tax_class_id = $tax_class_id;

			if (is_array($amount) && array_key_exists('amount', $amount)) {
				parent::__construct($amount['amount'], $currency_code);
				return;
			}

			parent::__construct($amount, $currency_code);
		}

		public function __get(string $name): mixed {

			switch ($name) {

				case 'net':
					return $this->in($this->currency_code);

				case 'gross':
					return $this->gross_for('customer');

				case 'tax':
					return $this->tax_for('customer');

				case 'tax_rate':
					$rates = tax::get_rates($this->_tax_class_id);
					if (empty($rates)) return 0.0;
					$sum = 0.0;
					foreach ($rates as $rate) {
						$sum += (float)($rate['rate'] ?? 0);
					}
					return $sum / count($rates);

				case 'tax_class_id':
					return $this->_tax_class_id;
			}

			return parent::__get($name);
		}

		public function gross_for($customer): float {
			if ($this->_tax_class_id === null) return $this->net;
			return tax::get_price($this->net, $this->_tax_class_id, true, $customer);
		}

		public function tax_for($customer): float {
			if ($this->_tax_class_id === null) return 0.0;
			return tax::get_tax($this->net, $this->_tax_class_id, $customer);
		}

		public function jsonSerialize(): mixed {
			return [
				...parent::jsonSerialize(),
				'tax_class_id' => $this->_tax_class_id,
			];
		}

	}
