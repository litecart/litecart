<?php

	class job_cleaner extends abs_module {

		public $name = 'Cleaner';
		public $description = 'Wipe out old cache files that are starting to collect dust.';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';
		public $priority = 0;

		public function process($force, $last_run) {

			if (!$this->settings['status']) return;

			if ($last_run || !$force) {
				if (strtotime($last_run) > functions::datetime_last_by_interval('Hourly', $last_run)) return;
			}

			echo 'Clean expired customer activity...' . PHP_EOL;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."customers_activity
				where (date_expires is not null and date_expires < '". date('Y-m-d H:i:s') ."')
				or (date_expires is null and date_created < '". date('Y-m-d H:i:s', strtotime('-12 months')) ."');"
			);

			echo 'Wipe out old cache files...' . PHP_EOL;

			$deleted_files = 0;
			$deleted_dirs = 0;
			$timestamp = strtotime('-24 hours');

			clearstatcache();

			foreach (functions::file_search(FS_DIR_STORAGE .'cache/*', GLOB_ONLYDIR) as $dir) {

				foreach (functions::file_search($dir.'/*.cache') as $file) {

					if (!is_file($file)) continue;
					if (filemtime($file) > $timestamp) continue;

					echo '  Deleting ' . basename($file) . PHP_EOL;
					unlink($file);

					$deleted_files++;
				}

				$is_empty_dir = !(new \FilesystemIterator($dir))->valid();

				if ($is_empty_dir) {
					rmdir($dir);
					$deleted_dirs++;
				}
			}

			echo PHP_EOL . "Cleaned up $deleted_files files and $deleted_dirs directories" . PHP_EOL;
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
					'key' => 'priority',
					'default_value' => '0',
					'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
					'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
					'function' => 'int()',
				],
			];
		}
	}
