<?php

	class mod_shipping extends abs_modules {

		private $_cache = [];
		private $_currency_code = '';
		private $_customer = [];
		private $_items = [];
		private $_options	= [];

		private $_total = [
			'amount' => [
				'value' => 0,
				'tax' => 0,
			],
			'weight' => [
				'value' => 0,
				'unit' => null,
			],
			'volume' => [
				'value' => 0,
				'unit' => null,
			],
		];

		public $selected = [];

		public function __construct(array $items = [], string|null $currency_code = null, array $customer = [], array $selected = []) {

			$currency_code = $currency_code ?: currency::$selected['code'];

			$this->_total = [
				'amount' => [
					'value' => 0,
					'tax' => 0,
					'currency_code' => $currency_code,
				],
				'weight' => [
					'value' => 0,
					'unit' => settings::get('store_weight_unit'),
				],
				'volume' => [
					'value' => 0,
					'unit' => settings::get('store_length_unit').'3',
				],
			];

			foreach ($items as $item) {

				$this->_items[] = [
					'product_id' => $item['product_id'] ?? null,
					'name' => $item['name'] ?? null,
					'image' => $item['image'] ?? null,
					'quantity' => $item['quantity'] ?? null,
					'regular_price' => $item['regular_price'] ?? null,
					'final_price' => $item['final_price'] ?? null,
					'tax_class_id' => $item['tax_class_id'] ?? null,
					'stock_items' => $item['stock_items'] ?? [],
					'sum' => $item['sum'] ?? null,
					'sum_tax' => $item['sum_tax'] ?? null,
				];

				$this->_total['amount']['value'] += $item['sum'];
				$this->_total['amount']['tax'] += $item['sum_tax'];

				foreach (($item['stock_items'] ?? []) as $stock_item) {
					$this->_total['weight']['value'] += f::convert_weight($stock_item['weight'], $stock_item['weight_unit'], $this->_total['weight']['unit']) * $item['quantity'] * $stock_item['quantity'];
					$this->_total['volume']['value'] += f::convert_volume($stock_item['length'] * $stock_item['width'] * $stock_item['height'], $stock_item['length_unit'].'3', $this->_total['volume']['unit']) * $item['quantity'] * $stock_item['quantity'];
				}
			}

			$this->_currency_code = $currency_code ?: currency::$selected['code'];
			$this->_customer = $customer ?: customer::$data;

			$this->selected = $selected;

			// Load modules
			$this->load();

			// Rettach userdata to module
			if (!empty($this->selected['userdata']) && !empty($this->modules[$this->selected['module_id']])) {
				$this->modules[$this->selected['module_id']]->userdata = &$this->selected['userdata'];
			}

			parent::__construct();
		}

		public function select(string $id, array $userdata = []) {

			$this->selected = [];

			$options = $this->options();

			if (!$options) return;

			if (($key = array_search($id, array_combine(array_keys($options), array_column($options, 'id')))) === false) return;
			if (!empty($this->data['options'][$key]['error'])) return;

			list($module_id, $option_id) = explode(':', $id);
			if (method_exists($this->modules[$module_id], 'select')) {
				if ($error = $this->modules[$module_id]->select($option_id)) {
					notices::add('errors', $error);
				}
			}

			$this->selected = [
				'id' => $module_id.':'.$option_id,
				'module_id' => $module_id,
				'option_id' => $option_id,
				'icon' => $options[$key]['icon'],
				'name' => $options[$key]['name'],
				'fee' => $options[$key]['fee'],
				'tax_class_id' => $options[$key]['tax_class_id'],
				'incoterm' => $options[$key]['incoterm'],
				'userdata' => $userdata,
			];
		}

		public function options() {

			if (!$this->modules || !$this->_items) {
				return [];
			}

			$subtotal = ['amount' => 0, 'tax' => 0];
			foreach ($this->_items as $item) {
				$subtotal['amount'] += $item['sum'];
				$subtotal['tax'] += $item['sum_tax'];
			}

			$checksum = crc32(f::format_json($this->_items, false));

			if (!empty($this->_cache[$checksum]['options'])) {
				return $this->_cache[$checksum]['options'];
			}

			$this->_options = [];

			foreach ($this->modules as $module) {

				$options = $module->options($this->_items, $this->_total, $this->_customer);

				if (!$options) continue;

				if (!empty($options['options'])) {
					$options = $options['options']; // Backwards compatibility LiteCart <3.0.0
				}

				foreach ($options as $option) {

					if (empty($option['title']) && isset($option['name'])) {
						$option['title'] = $option['name']; // Backwards compatibility LiteCart <3.0.0
					}

					if (empty($option['fee']) && isset($option['cost'])) {
						$option['fee'] = $option['cost']; // Backwards compatibility LiteCart <3.0.0
					}

					$this->_cache[$checksum]['options'][] = [
						'id' => $module->id.':'.$option['id'],
						'module_id' => $module->id,
						'option_id' => $option['id'],
						'icon' => $option['icon'],
						'vendor' => $option['vendor'] ?? '',
						'name' => $option['name'],
						'description' => $option['description'] ?? '',
						'fields' => $option['fields'] ?? [],
						'fee' => (float)$option['fee'],
						'tax_class_id' => (int)$option['tax_class_id'],
						'incoterm' => $option['incoterm'] ?? settings::get('default_incoterm'),
						'exclude_cheapest' => !empty($option['exclude_cheapest']),
						'error' => $option['error'] ?? false,
					];
				}
			}

			// Sort options by fee
			uasort($this->_cache[$checksum]['options'], function($a, $b) {
				if ($a['fee'] == $b['fee']) return 0;
				return ($a['fee'] > $b['fee']) ? 1 : -1;
			});

			return $this->_cache[$checksum]['options'];
		}

		public function after_process(ent_order $order) {

			if (empty($this->selected['module_id'])) return;
			if (empty($this->modules[$this->selected['module_id']])) return;
			if (!method_exists($this->modules[$this->selected['module_id']], 'after_process')) return;

			return $this->modules[$this->selected['module_id']]->after_process($order);
		}

		public function cheapest(): array|false|null {

			if (!$this->_options) {
				$options = $this->options();
			}

			if (!$options) {
				return false;
			}

			$cheapest = null;

			foreach ($options as $option) {
				if (!empty($option['error'])) continue;
				if (!empty($option['exclude_cheapest'])) continue;
				if (!$cheapest || $option['fee'] < $cheapest['fee']) {
					$cheapest = $option;
				}
			}

			return $cheapest;
		}
	}
