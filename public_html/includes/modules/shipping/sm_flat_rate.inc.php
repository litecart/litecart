<?php

	class sm_flat_rate extends abs_module {

		public $id = __CLASS__;
		public $name = 'Flat Rate';
		public $description = '';
		public $author = 'Wachhund';
		public $version = '1.0';
		public $website = '';

		public function __construct() {
			$this->name = t(__CLASS__.':title_flat_rate', 'Flat Rate');
			parent::__construct();
		}

		public function options(array $items, array $total, string $currency_code, array $customer): ?array {

			if (empty($this->settings['status'])) {
				return null;
			}

			if (!empty($this->settings['geo_zones'])) {
				if (!reference::country($customer['shipping_address']['country_code'])->in_geo_zone($this->settings['geo_zones'], $customer['shipping_address'])) return null;
			}

			return [
				[
					'id' => 'flat_rate',
					'icon' => $this->settings['icon'],
					'name' => $this->settings['name'] ?: $this->name,
					'description' => $this->settings['description'] ?: '',
					'fields' => '',
					'fee' => (float)$this->settings['fee'],
					'tax_class_id' => $this->settings['tax_class_id'],
				],
			];
		}

		public function settings(): array {

			return [
				[
					'key' => 'status',
					'default_value' => '0',
					'title' => t(__CLASS__.':title_status', 'Status'),
					'description' => t(__CLASS__.':description_status', 'Enables or disables the module.'),
					'function' => 'toggle("e/d")',
				],
				[
					'key' => 'name',
					'default_value' => 'Standard Shipping',
					'title' => t(__CLASS__.':title_name', 'Name'),
					'description' => t(__CLASS__.':description_name', 'The name displayed to the customer.'),
					'function' => 'text()',
				],
				[
					'key' => 'description',
					'default_value' => '',
					'title' => t(__CLASS__.':title_description', 'Description'),
					'description' => t(__CLASS__.':description_description', 'A short description displayed to the customer.'),
					'function' => 'text()',
				],
				[
					'key' => 'icon',
					'default_value' => '',
					'title' => t(__CLASS__.':title_icon', 'Icon'),
					'description' => t(__CLASS__.':description_icon', 'Path to an image to be displayed.'),
					'function' => 'text()',
				],
				[
					'key' => 'fee',
					'default_value' => '0',
					'title' => t(__CLASS__.':title_fee', 'Shipping Fee'),
					'description' => t(__CLASS__.':description_fee', 'The flat rate shipping fee.'),
					'function' => 'decimal()',
				],
				[
					'key' => 'tax_class_id',
					'default_value' => '',
					'title' => t(__CLASS__.':title_tax_class', 'Tax Class'),
					'description' => t(__CLASS__.':description_tax_class', 'The tax class for the shipping fee.'),
					'function' => 'tax_class()',
				],
				[
					'key' => 'geo_zones[]',
					'default_value' => '',
					'title' => t('title_geo_zone_limitation', 'Geo Zone Limitation'),
					'description' => t('modules:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise, leave it blank.'),
					'function' => 'geo_zone()',
				],
				[
					'key' => 'priority',
					'default_value' => '0',
					'title' => t('title_priority', 'Priority'),
					'description' => t('modules:description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
