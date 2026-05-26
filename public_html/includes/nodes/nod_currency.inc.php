<?php

	class currency {

		public static $currencies;
		public static $selected;

		public static function init(): void {

			// Bind selected to session
			if (!isset(session::$data['currency']) || !is_array(session::$data['currency'])) {
				session::$data['currency'] = [];
			}

			self::$selected = &session::$data['currency'];

			// Load currencies
			self::load();

			// Identify/set currency
			self::set();
		}

		## Node specific methods

		public static function load(): void {

			// Get currencies from database
			self::$currencies = database::query(
				"select * from ". DB_TABLE_PREFIX ."currencies
				where status
				order by priority;"
			)->fetch_all(null, 'code');
		}

		public static function set(string $code=''): void {

			if (empty($code)) $code = self::identify();

			if (!isset(self::$currencies[$code])) {
				trigger_error('Cannot set unsupported currency ('. $code .')', E_USER_WARNING);
				$code = self::identify();
			}

			session::$data['currency'] = self::$currencies[$code];

			// Sort by relevance / fallback order
			uasort(self::$currencies, function($a, $b){
				$pos_a = array_search($a['code'], [self::$selected['code'], settings::get('store_currency_code')]);
				$pos_b = array_search($b['code'], [self::$selected['code'], settings::get('store_currency_code')]);

				if ($pos_a === false && $b === false) return 0;
				else if ($pos_a === false) return 1;
				else if ($b === false) return -1;
				else return $pos_a - $pos_b;
			});

			if (!empty($_COOKIE['cookies_accepted']) || !settings::get('cookie_policy')) {
				header('Set-Cookie: currency_code='. $code .'; Path='. WS_DIR_APP .'; Expires='. gmdate('r', strtotime('+3 months')) .'; SameSite=Lax', false);
			}
		}

		public static function identify(): string {

			$all_currencies = array_keys(self::$currencies);

			$enabled_currencies = [];
			foreach (self::$currencies as $currency) {
				if (administrator::check_login() || $currency['status'] == 1) {
					$enabled_currencies[] = $currency['code'];
				}
			}

			// Return currency from URI query
			if (!empty($_GET['currency'])) {
				if (in_array($_GET['currency'], $all_currencies)){
					return $_GET['currency'];
				}
			}

			// Return currency from session
			if (isset(self::$selected['code']) && in_array(self::$selected['code'], $all_currencies)){
				return self::$selected['code'];
			}

			// Set currency from cookie
			if (!empty($_COOKIE['currency_code']) && in_array($_COOKIE['currency_code'], $all_currencies)) {
				return $_COOKIE['currency_code'];
			}

			// Get currency from country
			if (!empty(customer::$data['country_code'])) {

				$currency = database::query(
					"select currency_code from ". DB_TABLE_PREFIX ."countries
					where iso_code_2 = '". database::input(customer::$data['country_code']) ."'
					limit 1;"
				)->fetch('currency_code');

				if (in_array($currency, $enabled_currencies)) {
					return $currency;
				}
			}

			// Get currency from country (via TLD)
			if (preg_match('#\.([a-z]{2})$#', $_SERVER['HTTP_HOST'], $matches)) {

				$currency = database::query(
					"select currency_code from ". DB_TABLE_PREFIX ."countries
					where iso_code_2 = '". database::input(strtoupper($matches[1])) ."'
					limit 1;"
				)->fetch('currency_code');

				if (in_array($currency, $enabled_currencies)) {
					return $currency;
				}
			}

			// Return default currency
			if (in_array(settings::get('default_currency_code'), $all_currencies)){
				return settings::get('default_currency_code');
			}

			// Return store currency
			if (in_array(settings::get('store_currency_code'), $all_currencies)) {
				return settings::get('store_currency_code');
			}

			// Return first currency
			return (!empty($enabled_currencies)) ? $enabled_currencies[0] : $all_currencies[0];
		}

		public static function calculate(float $value, ?string $to=null, ?string $from=null): float {

			if (!$to) {
				$to = self::$selected['code'];
			}

			if (!$from) {
				$from = settings::get('store_currency_code');
			}

			if (!isset(self::$currencies[$from])){
				trigger_error("Cannot convert from currency $from as the currency does not exist", E_USER_WARNING);
			}

			if (!isset(self::$currencies[$to])){
				trigger_error("Cannot convert to currency $to as the currency does not exist", E_USER_WARNING);
			}

			return ($value * self::$currencies[$from]['value']) / self::$currencies[$to]['value'];
		}

		public static function convert(float $value, string $from, string $to=''): float {

			if (!$to) {
				$to = settings::get('store_currency_code');
			}

			return self::calculate($value, $to, $from);
		}

		public static function format(float|null $value, bool $auto_decimals=true, string $currency_code='', ?float $currency_value=null): string {

			if ($value === null) {
				return '';
			}

			if (empty($currency_code)) {
				$currency_code = self::$selected['code'];
			}

			if (empty(self::$currencies[$currency_code]) && empty($currency_value)) {
				trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);
			}

			if (empty($currency_value)) {
				if (empty(self::$currencies[$currency_code]['value'])) return false;
				$currency_value = self::$currencies[$currency_code]['value'];
			}

			$amount = (float)self::format_raw($value, $currency_code, $currency_value);
			$decimals = isset(self::$currencies[$currency_code]['decimals']) ? (int)self::$currencies[$currency_code]['decimals'] : 2;
			$prefix = self::$currencies[$currency_code]['prefix'] ?? '';
			$suffix = self::$currencies[$currency_code]['suffix'] ?? ' ' . $currency_code;

			$is_negative = $amount < 0;

			if ($auto_decimals) {
				if ($amount == floor($amount)) $decimals = 0;
			}

			return ($is_negative ? '-' : '') . $prefix . number_format(abs((float)$amount), (int)$decimals, language::$selected['decimal_point'], language::$selected['thousands_sep']) . $suffix;
		}

		public static function format_html(float|null $value, bool $auto_decimals=true, ?string $currency_code=null, ?float $currency_value=null): string|false {

			if ($value === null) {
				return '';
			}

			if (!$currency_code) {
				$currency_code = self::$selected['code'];
			}

			if (!$currency_value) {
				if (!empty(self::$currencies[$currency_code]['value'])) {
					$currency_value = self::$currencies[$currency_code]['value'];
				} else {
					trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);
					return false;
				}
			}

			$amount = (float)self::format_raw($value, $currency_code, $currency_value);
			$decimals = isset(self::$currencies[$currency_code]['decimals']) ? (int)self::$currencies[$currency_code]['decimals'] : 2;
			$prefix = self::$currencies[$currency_code]['prefix'];
			$suffix = self::$currencies[$currency_code]['suffix'];

			if ($auto_decimals) {
				if ($amount == floor($amount)) {
					$decimals = 0;
				}
			}

			if ($decimals) {
				list($integers, $fractions) = explode('.', number_format($amount, $decimals, '.', ''));
			} else {
				$integers = $amount;
				$fractions = 0;
			}

			$is_negative = $integers < 0;

			return strtr(implode('', [
				'<span class="currency-amount">',
					'<small class="currency">{currency_code}</small>',
					'&nbsp;',
					'{negative}{prefix}{integers}' . ($fractions ? '<span class="decimals">{decimal_point}{fractions}</span>' : ''),
					'{suffix}',
				'</span>',
			]), [
				'{negative}' => $is_negative ? '-' : '',
				'{currency_code}' => $currency_code,
				'{prefix}' => $prefix,
				'{integers}' => number_format(abs((int)$integers), 0, '', language::$selected['thousands_sep']),
				'{decimal_point}' => language::$selected['decimal_point'],
				'{fractions}' => $fractions,
				'{suffix}' => $suffix,
			]);
		}

		public static function format_raw(float|null $value, ?string $currency_code=null, ?float $currency_value=null): float|false {

			settype($value, 'float');

			if (!$value) {
				return 0;
			}

			if (!$currency_code) {
				$currency_code = self::$selected['code'];
			}

			if (!empty(self::$currencies[$currency_code])) {
				$decimals = self::$currencies[$currency_code]['decimals'];
			} else {
				$decimals = 2;
			}

			if (!$currency_value) {
				if (!empty(self::$currencies[$currency_code])) {
					$currency_value = self::$currencies[$currency_code]['value'];
				} else {
					trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);
					return false;
				}
			}

			return number_format($value / $currency_value, $decimals, '.', '');
		}

		// Round a store currency amount in a remote currency
		public static function round(int|float|string $value, string $currency_code): float {

			settype($value, 'float');

			if (!$value) {
				return 0;
			}

			if (!$currency_code) {
				$currency_code = self::$selected['code'];
			}

			if (!isset(self::$currencies[$currency_code])) {
				trigger_error("Cannot format amount as currency $currency_code does not exist", E_USER_WARNING);
				return $value;
			}

			$decimals = (int)self::$currencies[$currency_code]['decimals'];

			$value = self::convert($value, settings::get('store_currency_code'), $currency_code);
			$value = round($value, $decimals);
			$value = self::convert($value, $currency_code, settings::get('store_currency_code'));

			return $value;
		}
	}
