<?php

	/*
		Represents a monetary amount that knows its tax class. Composes naturally
		with type_money — inherits dual-mode money handling and JSON serialization,
		adds net/gross/tax accessors that delegate to the tax:: node.

		    $amount = new type_taxable_amount(100, 'USD', $tax_class_id);
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

	class type_taxable_amount extends type_money {

		private $_tax_class_id;

		public function __construct($input = 0, ?string $currency_code = null, $tax_class_id = null) {

			// Copy from a sibling instance — must come before parent's type_money
			// check so the tax_class_id carries over.
			if ($input instanceof type_taxable_amount) {
				parent::__construct($input);
				$this->_tax_class_id = $input->_tax_class_id;
				return;
			}

			// jsonSerialize() round-trip — peel tax_class_id and let the parent
			// constructor handle the rest of the array shape.
			if (is_array($input) && array_key_exists('tax_class_id', $input)) {
				$this->_tax_class_id = $input['tax_class_id'];
				unset($input['tax_class_id']);
				parent::__construct($input, $currency_code);
				return;
			}

			parent::__construct($input, $currency_code);
			$this->_tax_class_id = $tax_class_id;
		}

		public function __get($name) {

			switch ($name) {

				case 'net':
					return $this->in($this->currency_code);

				case 'gross':
					return (float)tax::get_price($this->net, $this->_tax_class_id, true);

				case 'tax':
					return (float)tax::get_tax($this->net, $this->_tax_class_id);

				case 'tax_rate':
					$rates = tax::get_rates($this->_tax_class_id);
					if (empty($rates)) return 0.0;
					$sum = 0;
					foreach ($rates as $rate) {
						$sum += (float)($rate['rate'] ?? 0);
					}
					return $sum / count($rates);

				case 'tax_class_id':
					return $this->_tax_class_id;
			}

			return parent::__get($name);
		}

		/*
			Region-aware variants — accept a customer array (or preset string)
			and resolve tax against that context instead of the default.
		*/
		public function gross_for($customer): float {
			return (float)tax::get_price($this->net, $this->_tax_class_id, true, $customer);
		}

		public function tax_for($customer): float {
			return (float)tax::get_tax($this->net, $this->_tax_class_id, $customer);
		}

		#[\ReturnTypeWillChange] // Fix PHP 8.1
		public function jsonSerialize() {
			return array_merge((array)parent::jsonSerialize(), [
				'tax_class_id' => $this->_tax_class_id,
			]);
		}
	}
