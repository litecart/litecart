<?php

	class ref_country extends abs_reference_entity {

		protected $_country_code;
		protected $_language_codes;

		function __construct($country_code) {

			if (!preg_match('#[A-Z]{2}#', $country_code)) {
				trigger_error('Invalid country code ('. $country_code .')', E_USER_WARNING);
			}

			$this->_country_code = $country_code;
		}

		protected function _load($field) {

			switch($field) {

				case 'zones':

					$this->_data['zones'] = database::query(
						"select * from ". DB_TABLE_PREFIX ."zones
						where country_code = '". database::input($this->_country_code) ."'
						order by name;"
					)->fetch_all();

					break;

				default:

					$row = database::query(
						"select * from ". DB_TABLE_PREFIX ."countries
						where iso_code_2 = '". database::input($this->_country_code) ."'
						limit 1;"
					)->fetch();

					if (!$row) {
						$country = new ent_country();
						$this->_data = $country->data;
						return;
					}

					foreach ($row as $key => $value) {
						$this->_data[$key] = $value;
					}

					break;
			}
		}

		public function format_address($address) {
			trigger_error('The method format_address() is deprecated. Use functions::format_address() instead.', E_USER_DEPRECATED);
			return functions::format_address($address);
		}

		public function in_geo_zone($geo_zones, $address=[]) {

			$args = func_get_args();

			if (is_numeric($args[1]) || (is_array($args[1]) && count($args[1]) === count(array_filter($args[1], 'is_array')) && count($args[1]) === count(array_filter($args[1], 'is_numeric')))) {
				trigger_error('Passing geo zone last preceeded by zone is deprecated. Instead do \$country->in_geo_zone($geo_zones, $address)', E_USER_DEPRECATED);
				list($zone_code, $geo_zones) = $args;
				$address = [
					'country_code' => $this->_country_code,
					'zone_code' => $zone_code,
					'city' => '',
				];
			}

			if (!is_array($geo_zones)) {
				$geo_zones = [$geo_zones];
			}

			if (database::query(
				"select id from ". DB_TABLE_PREFIX ."zones_to_geo_zones
				where geo_zone_id in ('". implode("', '", database::input($geo_zones)) ."')
				". (!empty($address['country_code']) ? "and (country_code = '' or country_code = '". database::input($address['country_code']) ."')" : "and (country_code = '' or country_code = '". database::input($this->_country_code) ."')") ."
				". (!empty($address['zone_code']) ? "and (zone_code = '' or zone_code = '". database::input($address['zone_code']) ."')" : "and zone_code = ''") ."
				". (!empty($address['city']) ? "and (city = '' or city like '". addcslashes(database::input($address['city']), '%_') ."')" : "and city = ''") ."
				limit 1;"
			)->num_rows) {
				return true;
			}

			return false;
		}
	}
