<?php

	class job_shipping_tracker extends abs_module {

		public $id = __CLASS__;
		public $name = 'Shipping Tracker';
		public $description = '';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $support_link = '';
		public $website = 'https://www.litecart.net/';
		public $priority = 0;

		public function process($force, $last_run) {

			if (!$force) {
				if (!$this->settings['status']) return;
				if (strtotime($last_run) > functions::datetime_last_by_interval($this->settings['frequency'], $last_run)) return;
			}

			$orders = database::query(
				"select id, shipping_option_id, shipping_tracking_id
				from ". DB_TABLE_PREFIX ."orders
				where shipping_tracking_id != ''
				and order_status_id in (
					select id from ". DB_TABLE_PREFIX ."order_statuses
					where is_trackable
				)
				and created_at > '". date('Y-m-d H:i:s', strtotime('-30 days')) ."'
				order by created_at asc
				limit 10;"
			)->fetch_all();

			echo 'Found '. count($orders) .' orders to track' . PHP_EOL;

			foreach ($orders as $order) {
				$order = new ent_order($order['id']);

				try {

					echo 'Tracking order '. $order->data['id'] .' with tracking no '. $order->data['shipping_tracking_id'] . '...';

					list($module_id, $option_id) = explode(':', $order->data['shipping_option']['id']);

					if (!$module_id) {
						throw new Exception('No module ID');
					}

					if (!$result = $order->shipping->run('track', $module_id, $order)) {
						throw new Exception('Nothing returned, skipping.');
					}

					if (!empty($result['error'])) {
						throw new Exception($result['error']);
					}

					echo ' [OK]'. PHP_EOL;

				} catch (Exception $e) {
					echo ' [Error]' . PHP_EOL . $e->getMessage() . PHP_EOL;
				}
			}
		}

		function settings() {

			return [
				[
					'key' => 'status',
					'default_value' => '1',
					'title' => t(__CLASS__.':title_status', 'Status'),
					'description' => t(__CLASS__.':description_status', 'Enables or disables the module.'),
					'function' => 'toggle("e/d")',
				],
				[
					'key' => 'frequency',
					'default_value' => 'Hourly',
					'title' => t(__CLASS__.':title_frequency', 'Frequency'),
					'description' => t(__CLASS__.':description_check_frequency', 'How often the modification scanner should run.'),
					'function' => 'radio("15 min","Hourly","3 Hours","6 Hours","12 Hours","Daily")',
				],
				[
					'key' => 'priority',
					'default_value' => '0',
					'title' => t(__CLASS__.':title_priority', 'Priority'),
					'description' => t(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
