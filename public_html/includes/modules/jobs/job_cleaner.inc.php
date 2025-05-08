<?php

	class job_cleaner extends abs_module {

		public $name = 'Cleaner';
		public $description = 'Keep the platform tidy by cleaning up old things.';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';
		public $priority = 0;

		public function process($force, $last_run) {

			if (!$this->settings['status']) return;

			if ($last_run || !$force) {
				if (strtotime($last_run) > functions::datetime_last_by_interval('Hourly', $last_run)) return;
			}

			// Customer Activity

			echo 'Remove old and expired customer activity...' . PHP_EOL;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."customers_activity
				where (expires_at is not null and expires_at < '". date('Y-m-d H:i:s') ."')
				or (expires_at is null and created_at < '". date('Y-m-d H:i:s', strtotime('-12 months')) ."');"
			);

			// Logs

			echo 'Wiping out old log files...' . PHP_EOL;

			$deleted_files = 0;
			$max_age = strtotime('-30 days');

			clearstatcache();

			foreach (functions::file_search(FS_DIR_STORAGE .'logs/**/*.log') as $file) {

				if (filemtime($file) > $max_age) continue;

				echo '  Deleting ' . basename($file) . PHP_EOL;
				unlink($file);

				$deleted_files++;
			}

			// Cache

			echo 'Wiping out old cache files...' . PHP_EOL;

			$deleted_files = 0;
			$deleted_dirs = 0;
			$max_age = strtotime('-24 hours');

			clearstatcache();

			foreach (functions::file_search(FS_DIR_STORAGE .'cache/*', GLOB_ONLYDIR) as $dir) {

				foreach (functions::file_search($dir.'/*.cache') as $file) {

					if (!is_file($file)) continue;
					if (filemtime($file) > $max_age) continue;

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
					'function' => 'number()',
				],
			];
		}
	}
