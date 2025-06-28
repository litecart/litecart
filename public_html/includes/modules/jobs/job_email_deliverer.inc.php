<?php

	class job_email_deliverer extends abs_module {

		public $id = __CLASS__;
		public $name = 'Email Deliverer';
		public $description = 'Deliver emails scheduled for delivery.';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net/';
		public $priority = 0;

		public function process($force, $last_run) {

			if (empty($force)) {
				if (empty($this->settings['status'])) return;

				if (!empty($this->settings['working_hours'])) {
					list($from_time, $to_time) = explode('-', $this->settings['working_hours']);
					if (time() < strtotime("Today $from_time") || time() > strtotime("Today $to_time")) return;
				}

				switch ($this->settings['frequency']) {
					case 'Hourly':
						if (date('Ymdh', strtotime($last_run)) == date('Ymdh')) return;
						break;
				}
			}

			$sent = 0;

			database::query(
				"select * from ". DB_TABLE_PREFIX ."emails
				where status = 'scheduled'
				and date_scheduled < '". date('Y-m-d H:i:s') ."'
				order by date_scheduled, id
				limit ". (int)$this->settings['delivery_limit'] .";"
			)->each(function($email) use (&$sent) {
				$email = new ent_email($email['id']);

				echo 'Delivering email to '. implode(', ', array_column($email->data['recipients'], 'email'));

				if ($email->send()) {
					echo ' [OK]' . PHP_EOL;
					$sent++;
				} else {
					echo ' [Failed]' . PHP_EOL;
				}
			});

			if (!$sent)	{
				echo 'No emails to deliver' . PHP_EOL;
			} else {
				echo 'Delivered '. $sent .' emails' . PHP_EOL;
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
					'default_value' => 'Weekly',
					'title' => t(__CLASS__.':title_frequency', 'Frequency'),
					'description' => t(__CLASS__.':description_frequency', 'How often the job should be executed.'),
					'function' => 'radio("Hourly")',
				],
				[
					'key' => 'working_hours',
					'default_value' => '07:00-21:00',
					'title' => t(__CLASS__.':title_working_hours', 'Working Hours'),
					'description' => t(__CLASS__.':description_working_hours', 'During what hours of the day the job would operate e.g. 07:00-21:00.'),
					'function' => 'text()',
				],
				[
					'key' => 'delivery_limit',
					'default_value' => '100',
					'title' => t(__CLASS__.':title_delivery_limit', 'Delivery Limit'),
					'description' => t(__CLASS__.':description_delivery_limit', 'The maximum amount of emails to be delivered at each launch of the process.'),
					'function' => 'number()',
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

		public function install() {
		}

		public function uninstall() {
		}
	}
