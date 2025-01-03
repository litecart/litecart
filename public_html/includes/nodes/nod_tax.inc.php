<?php

	class tax {

		private static $_cache = [];

		######################################################################

		public static function get_price($value, $tax_class_id, $calculate_tax=null, $customer=null) {

			if ($calculate_tax === null) {
				$calculate_tax = !empty(customer::$data['display_prices_including_tax']);
			}

			if ($calculate_tax) {
				return $value + self::get_tax($value, $tax_class_id, $customer);
			} else {
				return (float)$value;
			}
		}

		public static function get_tax($value, $tax_class_id, $customer=null) {

			if (!$value || !$tax_class_id) return 0;

			$tax_rates = self::get_rates($tax_class_id, $customer);

			$tax = 0;
			foreach ($tax_rates as $tax_rate) {
				$tax += $value * $tax_rate['rate'] / 100;
			}

			return $tax;
		}

		public static function get_rates($tax_class_id, $customer=null) {

			if (empty($tax_class_id)) return [];

			if (empty($customer)) {
				$customer = 'customer';
			}

			// Presets
			if (is_string($customer)) {
				switch(strtolower($customer)) {

					case 'site':
					case 'store':

						$customer = [
							'tax_id' => '',
							'billing_address' => [
								'company' => '',
								'country_code' => settings::get('store_country_code'),
								'zone_code' => settings::get('store_zone_code'),
								'city' => '',
							],
							'shipping_address' => [
								'company' => '',
								'country_code' => settings::get('store_country_code'),
								'zone_code' => settings::get('store_zone_code'),
								'city' => '',
							],
						];

						break;

					case 'customer':

						$customer = [
							'tax_id' => !empty(customer::$data['tax_id']) ? true : false,
							'company' => !empty(customer::$data['company']) ? true : false,
							'country_code' => customer::$data['country_code'],
							'zone_code' => customer::$data['zone_code'],
							'city' => customer::$data['city'],
						];
						break;

					default:
						trigger_error('Unknown preset for customer ('. functions::escape_html($customer) .')', E_USER_WARNING);
						break;
				}
			}

			if (empty($customer['billing_address']['country_code'])) {
				if (!empty(customer::$data['country_code'])) {
					$customer['billing_address']['country_code'] = customer::$data['country_code'];
				} else {
					$customer['billing_address']['country_code'] = settings::get('default_country_code');
				}
			}

			if (!isset($customer['billing_address']['zone_code'])) {
				if (!empty(customer::$data['zone_code'])) {
					$customer['billing_address']['zone_code'] = customer::$data['zone_code'];
				} else {
					$customer['billing_address']['zone_code'] = settings::get('default_zone_code');
				}
			}

			if (!isset($customer['billing_address']['city'])) {
				$customer['billing_address']['city'] = '';
			}

			if (!isset($customer['shipping_address'])) {
				$customer['shipping_address'] = $customer['billing_address'];
			}

			if (!isset($customer['shipping_address']['city'])) {
				$customer['shipping_address']['city'] = '';
			}

			$checksum = crc32(http_build_query($customer));

			if (isset(self::$_cache['rates'][$tax_class_id][$checksum])) {
				return self::$_cache['rates'][$tax_class_id][$checksum];
			}

			$tax_rates = database::query(
				"select * from ". DB_TABLE_PREFIX ."tax_rates
				where tax_class_id = ". (int)$tax_class_id ."
				and (
					(
						address_type = 'payment'
						and geo_zone_id in (
							select geo_zone_id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
							where country_code = '". database::input($customer['billing_address']['country_code']) ."'
							and (zone_code = '' or zone_code = '". database::input($customer['billing_address']['zone_code']) ."')
							and (city = '' or lower(city) like '". (!empty($customer['billing_address']['city']) ? database::input(mb_strtolower($customer['billing_address']['city'])) : '') ."')
						)
						". ((!empty($customer['billing_address']['company']) && !empty($customer['billing_address']['tax_id'])) ? "and rule_companies_with_tax_id" : "") ."
						". ((!empty($customer['billing_address']['company']) && empty($customer['billing_address']['tax_id'])) ? "and rule_companies_without_tax_id" : "") ."
						". ((empty($customer['billing_address']['company']) && !empty($customer['billing_address']['tax_id'])) ? "and rule_individuals_with_tax_id" : "") ."
						". ((empty($customer['billing_address']['company']) && empty($customer['billing_address']['tax_id'])) ? "and rule_individuals_without_tax_id" : "") ."
					) or (
						address_type = 'shipping'
						and geo_zone_id in (
							select geo_zone_id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
							where country_code = '". database::input($customer['shipping_address']['country_code']) ."'
							and (zone_code = '' or zone_code = '". database::input($customer['shipping_address']['zone_code']) ."')
							and (city = '' or city like '". addcslashes(database::input($customer['shipping_address']['city']), '%_') ."')
						)
						". ((!empty($customer['shipping_address']['company']) && !empty($customer['shipping_address']['tax_id'])) ? "and rule_companies_with_tax_id" : "") ."
						". ((!empty($customer['shipping_address']['company']) && empty($customer['shipping_address']['tax_id'])) ? "and rule_companies_without_tax_id" : "") ."
						". ((empty($customer['shipping_address']['company']) && !empty($customer['shipping_address']['tax_id'])) ? "and rule_individuals_with_tax_id" : "") ."
						". ((empty($customer['shipping_address']['company']) && empty($customer['shipping_address']['tax_id'])) ? "and rule_individuals_without_tax_id" : "") ."
					)
				)
				;"
			)->fetch_all();

			self::$_cache['rates'][$tax_class_id][$checksum] = $tax_rates;

			return $tax_rates;
		}
	}
