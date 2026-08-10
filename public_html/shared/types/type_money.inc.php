<?php

	/*
		Represents a monetary amount with currency awareness. Two construction modes:

		1) Single amount + currency — internally normalised to the store currency
		   and converted on demand. Use this for calculated values (totals, taxes).

		    $price = new type_money(99.99, 'USD');
		    echo $price->in('EUR');             // float, converted from USD
		    echo $price;                        // 'USD 99.99' formatted
		    $price->convert('EUR')->format();   // chainable — returns new type_money

		2) Fixed amounts per currency — explicit merchant-set prices that should
		   NOT drift with FX-rate refreshes. Use this for catalog prices.

		    $price = new type_money(['USD' => 99.99, 'EUR' => 92.00, 'SEK' => 1050]);
		    echo $price->in('EUR');             // 92.00 — verbatim, no conversion
		    echo $price->in('GBP');             // converts from store-currency entry

		Drop-in for entity JSON columns that already store per-currency maps —
		jsonSerialize() returns ['amounts' => [...], 'mode' => 'fixed'|'converted'].
	*/

	class type_money implements \JsonSerializable {

		const MODE_CONVERTED = 'converted';
		const MODE_FIXED     = 'fixed';

		private $_amounts = [];
		private $_mode;
		private $_origin_currency;

		public function __construct(float|array|type_money $input = 0, ?string $currency_code = null) {

			if ($input instanceof type_money) {
				$this->_amounts         = $input->_amounts;
				$this->_mode            = $input->_mode;
				$this->_origin_currency = $input->_origin_currency;
				return;
			}

			$store_currency = settings::get('store_currency_code');

			if (is_array($input)) {

				// Fixed-amounts-per-currency mode — explicit merchant prices, no conversion.
				if (isset($input['amounts']) && is_array($input['amounts'])) {
					// jsonSerialize round-trip — accept the serialised shape verbatim.
					$this->_amounts         = array_map('floatval', $input['amounts']);
					$this->_mode            = ($input['mode'] ?? self::MODE_FIXED) === self::MODE_CONVERTED ? self::MODE_CONVERTED : self::MODE_FIXED;
					$this->_origin_currency = $input['origin_currency'] ?? $store_currency;
					return;
				}

				// Plain map of currency_code => amount.
				$this->_amounts         = array_map('floatval', $input);
				$this->_mode            = self::MODE_FIXED;
				$this->_origin_currency = array_key_first($this->_amounts) ?: $store_currency;
				return;
			}

			// Single amount mode — normalise to store currency for consistent math.
			$amount   = (float)$input;
			$currency = $currency_code ?: $store_currency;
			$this->_origin_currency = $currency;
			$this->_mode            = self::MODE_CONVERTED;
			$this->_amounts         = [
				$store_currency => $currency === $store_currency ? $amount : (float)currency::convert($amount, $currency, $store_currency),
			];
		}

		public function __get(string $name): mixed {

			switch ($name) {

				case 'amount':
					// Default to the origin currency in single-amount mode, or to the
					// store-currency entry in fixed-map mode if present, else first.
					return $this->in($this->_origin_currency);

				case 'currency':
				case 'currency_code':
					return $this->_origin_currency;

				case 'mode':
					return $this->_mode;

				case 'is_fixed':
					return $this->_mode === self::MODE_FIXED;

				case 'currencies':
					return array_keys($this->_amounts);
			}

			trigger_error("Unknown money component ($name)", E_USER_WARNING);
			return null;
		}

		public function __set(string $name, $value): void {
			trigger_error("type_money is immutable — use convert() / with_amount() for derived values", E_USER_WARNING);
		}

		/*
			Returns the amount as a float in the requested currency.
			Fixed-mode: returns the verbatim merchant-set amount if present,
			otherwise falls back to converting from the store-currency entry.
			Converted-mode: always converts from the store-currency reference.
		*/
		public function in(?string $currency_code = null): float {

			$currency = $currency_code ?: $this->_origin_currency;

			if ($this->_mode === self::MODE_FIXED && isset($this->_amounts[$currency])) {
				return $this->_amounts[$currency];
			}

			$store_currency = (string)settings::get('store_currency_code');
			$base_currency  = isset($this->_amounts[$store_currency]) ? $store_currency : array_key_first($this->_amounts);
			$base_amount    = $this->_amounts[$base_currency] ?? 0.0;

			if ($base_currency === $currency) {
				return $base_amount;
			}

			if (empty(currency::$currencies[$base_currency]) || empty(currency::$currencies[$currency])) {
				return (float)$base_amount;
			}

			return (float)currency::convert($base_amount, $base_currency, $currency);
		}

		/*
			Returns a new type_money in the requested currency (chainable).
			Single-amount mode preserves the converted value. Fixed-mode
			preserves all entries but changes the default currency.
		*/
		public function convert(string $currency_code): type_money {

			if ($this->_mode === self::MODE_FIXED) {
				$copy = new type_money($this);
				$copy->_origin_currency = $currency_code;
				return $copy;
			}

			return new type_money($this->in($currency_code), $currency_code);
		}

		/*
			Returns a new type_money with a different amount in the same currency.
			Useful for tax math and discount applications.
		*/
		public function with_amount(float $amount, ?string $currency_code = null): type_money {
			return new type_money($amount, $currency_code ?: $this->_origin_currency);
		}

		public function format(?string $currency_code = null, $auto_decimals = true): string {
			$currency = $currency_code ?: $this->_origin_currency;
			return currency::format($this->in($currency), $auto_decimals, $currency);
		}

		public function format_html(?string $currency_code = null, $auto_decimals = true): string {
			$currency = $currency_code ?: $this->_origin_currency;
			return currency::format_html($this->in($currency), $auto_decimals, $currency);
		}

		public function format_raw(?string $currency_code = null): string {
			$currency = $currency_code ?: $this->_origin_currency;
			return currency::format_raw($this->in($currency), $currency);
		}

		public function __toString(): string {
			return $this->format();
		}

		#[\ReturnTypeWillChange] // Fix PHP 8.1
		public function jsonSerialize() {
			return [
				'amounts'         => $this->_amounts,
				'mode'            => $this->_mode,
				'origin_currency' => $this->_origin_currency,
			];
		}
	}
