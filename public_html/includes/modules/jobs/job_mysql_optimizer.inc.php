<?php

	class job_mysql_optimizer extends abs_module {

		public $id = __CLASS__;
		public $name = 'MySQL Optimizer';
		public $description = 'Defragment your MySQL database';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';
		public $priority = 0;

		public function process($force, $last_run) {

			if (!$force) {
				if (!$this->settings['status']) return;
				if (strtotime($last_run) > functions::datetime_last_by_interval($this->settings['frequency'], $last_run)) return;
			}

			echo 'Optimizing MySQL Tables...' . PHP_EOL . PHP_EOL;

			database::query(
				"select table_name
				from `information_schema`.`tables`
				where table_schema = '". DB_DATABASE ."'
				and table_name like '". DB_TABLE_PREFIX ."%';"
			)->each(function($row){
				echo '  - ' . $row['table_name'] . PHP_EOL;
				database::query("optimize table ". $row['table_name'] .";");
			});

			echo PHP_EOL . 'Done!';
		}

		function settings() {

			return [
				[
					'key' => 'status',
					'default_value' => '1',
					'title' => language::translate(__CLASS__.':title_status', 'Status'),
					'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
					'function' => 'toggle("e/d")',
				],
				[
					'key' => 'frequency',
					'default_value' => 'Monthly',
					'title' => language::translate(__CLASS__.':title_frequency', 'Frequency'),
					'description' => language::translate(__CLASS__.':description_frequency', 'How often the job should be processed.'),
					'function' => 'radio("Daily","Weekly","Monthly")',
				],
				[
					'key' => 'priority',
					'default_value' => '0',
					'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
					'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
