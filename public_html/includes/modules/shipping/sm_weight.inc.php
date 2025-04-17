<?php

	class sm_weight extends abs_module {

		public $id = __CLASS__;
		public $name = 'Weight Based Shipping';
		public $description = '';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';

		public function __construct() {
			$this->name = language::translate(__CLASS__.':title_weight_based_shipping', 'Weight Based Shipping');
		}

		public function options($items, $subtotal, $tax, $currency_code, $customer) {

			if (!$this->settings['status']) {
				return;
			}

		// Calculate cart weight
			$total_weight = 0;
			foreach ($items as $item) {
				$total_weight += weight::convert($item['quantity'] * $item['weight'], $item['weight_unit'], $this->settings['weight_unit']);
			}

			$rate_tables_map = preg_split('#\R+#', trim($this->settings['rate_tables_map']), -1, PREG_SPLIT_NO_EMPTY);
			$rate_tables = preg_split('#\R+#', trim($this->settings['rate_tables']), -1, PREG_SPLIT_NO_EMPTY);

			$options = [];

			// Get rate table name from destination
			foreach ($rate_tables_map as $map) {

				list($zones, $table) = preg_split('#\s*;\s*#', $map);
				$zones = preg_split('#\s*,\s*#', $map, -1, PREG_SPLIT_NO_EMPTY);

				foreach ($zones as $zone) {

					switch (true) {

						case (preg_match('^([A_Z]{2}):?$', $zone, $matches) && $customer['shipping_address']['country_code'] == $matches[1]):
							$table_name = $table;
							break 2;

						case (preg_match('^([A_Z]{2}):(.+)$', $zone, $matches) && $customer['shipping_address']['country_code'] == $matches[1] && $customer['shipping_address']['zone_code'] == $matches[2]):
							$table_name = $table;
							break 2;

						case (preg_match('^([0-9]+)$', $zone, $matches) && reference::country($customer['shipping_address']['country_code'])->in_geo_zone($zone, $customer['shipping_address'])):
							$table_name = $table;
							break 2;

						default:
							continue 2;
					}

					$table = null;
					$cost = null;

				// Find and extract rate table by name
					foreach ($rate_tables as $row) {
						$row = preg_split('#;#', trim($row, ';'));
						if ($row[0] == $table_name) {
							$rates = array_slice($row, 1);
							break;
						}
					}

					if ($rates) {
						continue;
					}

				// Calculate cost
					foreach ($rates as $rate) {
						list($max_weight, $charge) = preg_split('#:#', $rate);
						if (!isset($cost) || $weight >= $max_weight) {
							$cost = $charge;
						}
					}

					if ($cost === null) {
						continue;
					}

					$options[] = [
						'id' => $table_name,
						'icon' => $this->settings['icon'],
						'name' => reference::country($customer['shipping_address']['country_code'])->name,
						'description' => weight::format($total_weight, $this->settings['weight_unit']),
						'fields' => '',
						'cost' => $cost,
						'tax_class_id' => $this->settings['tax_class_id'],
						'exclude_cheapest' => false,
					];
				}
			}

			if (!$options) return;

			return [
				'title' => $this->name,
				'options' => $options,
			];
		}

		function settings() {
			return [
				[
					'key' => 'status',
					'default_value' => '1',
					'title' => language::translate(__CLASS__.':title_status', 'Status'),
					'description' => language::translate(__CLASS__.':description_status', ''),
					'function' => 'toggle("e/d")',
				],
				[
					'key' => 'icon',
					'default_value' => '',
					'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
					'description' => language::translate(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
					'function' => 'text()',
				],
				[
					'key' => 'weight_unit',
					'default_value' => '',
					'title' => language::translate(__CLASS__.':title_weight_unit', 'Weight Unit'),
					'description' => language::translate(__CLASS__.':description_weight_unit', 'The weight class for the rate table.'),
					'function' => 'weight_unit()',
				],
				[
					'key' => 'rate_tables_map',
					'default_value' => implode("\n", [
						'countries;table_name',
						'US,CA;Table1',
						';Table1',
					]),
					'title' => language::translate(__CLASS__.':title_zone_mapping', 'Zone Mapping'),
					'description' => language::translate(__CLASS__.':description_zone_mapping', 'Mapping geo zones or countries to rate tables. Zones are identified by geo zone ID e.g. 123, a country code e.g. US, or a country code and zone/state code e.g. US:TX. Multiple zones can be separated by commas. Leave zone blank to match all zones.'),
					'function' => 'bigtext()',
				],
				[
					'key' => 'rate_tables',
					'default_value' => implode("\n", [
						'table_name;weight:cost;weight:cost',
						'Table1;1:10;2:20;3:30',
					]),
					'title' => language::translate(__CLASS__.':title_rate_tables', 'Rate Tables'),
					'description' => language::translate(__CLASS__.':description_rate_tables', 'Rate tables separated by line breaks. Each table should start with a table name followed by weight:cost pairs.'),
					'function' => 'bigtext()',
				],
				[
					'key' => 'tax_class_id',
					'default_value' => '',
					'title' => language::translate(__CLASS__.':title_tax_class', 'Tax Class'),
					'description' => language::translate(__CLASS__.':description_tax_class', 'The tax class for the shipping cost.'),
					'function' => 'tax_class()',
				],
				[
					'key' => 'priority',
					'default_value' => '0',
					'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
					'description' => language::translate(__CLASS__.':description_priority', 'Process this module by the given priority value.'),
					'function' => 'number()',
				],
			];
		}

		public function install() {}

		public function uninstall() {}
	}
