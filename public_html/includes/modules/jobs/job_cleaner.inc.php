<?php

	class job_cleaner extends abs_module {

		public $name = 'Cleaner';
		public $description = 'Keeps the platform tidy by cleaning up old things.';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net';
		public $priority = 0;

		public function process($force, $last_run) {

			if (!$this->settings['status']) return;

			if ($last_run && !$force) {
				if (strtotime($last_run) > f::datetime_last_by_interval('Hourly', $last_run)) return;
			}

			// Cleanup expired event logs
			database::query(
				"delete from ". DB_TABLE_PREFIX ."event_logs
				where (expires_at is not null and expires_at < '". date('Y-m-d H:i:s') ."')
				or (expires_at is null and created_at < '". date('Y-m-d H:i:s', strtotime('-3 months')) ."');"
			);

			echo 'Removed '. f::format_number(database::affected_rows()) .' old and expired event logs.' . PHP_EOL . PHP_EOL;

			// Cleanup expired sessions
			database::query(
				"delete from ". DB_TABLE_PREFIX ."sessions
				where expires_at < '". date('Y-m-d H:i:s') ."';"
			);

			echo 'Removed '. f::format_number(database::affected_rows()) .' expired sessions.' . PHP_EOL . PHP_EOL;

			// Cleanup old email history
			database::query(
				"delete from ". DB_TABLE_PREFIX ."emails
				where status in ('sent', 'error')
				and updated_at < '". date('Y-m-d 00:00:00', strtotime('-1 month')) ."';"
			);

			echo 'Removed '. f::format_number(database::affected_rows()) .' old emails.' . PHP_EOL . PHP_EOL;

			// Cleanup old not found logs
			database::query(
				"delete from ". DB_TABLE_PREFIX ."not_found
				where last_requested < '". date('Y-m-d 00:00:00', strtotime('-90 days')) ."';"
			);

			echo 'Removed '. language::number_format(database::affected_rows()) .' old not found logs.' . PHP_EOL . PHP_EOL;

			// Cleanup old visitor statistics
			database::query(
				"delete from ". DB_TABLE_PREFIX ."visitors
				where created_at < '". date('Y-m-d 00:00:00', strtotime('-1 month')) ."';"
			);

			echo 'Removed '. f::format_number(database::affected_rows()) .' old visitor statistics.' . PHP_EOL . PHP_EOL;

			// Cleanup old log files
			$deleted_files = 0;
			$max_age = strtotime('-30 days');

			clearstatcache();

			foreach (f::file_search(FS_DIR_STORAGE .'logs/**/*.log') as $file) {

				if (filemtime($file) > $max_age) continue;

				unlink($file);
				$deleted_files++;
			}

			echo 'Removed '. f::format_number($deleted_files) .' old log files.' . PHP_EOL . PHP_EOL;

			// Cleanup old cache files
			$deleted_files = 0;
			$deleted_dirs = 0;
			$max_age = strtotime('-24 hours');

			clearstatcache();

			foreach (f::file_search(FS_DIR_STORAGE .'cache/*', GLOB_ONLYDIR) as $dir) {

				foreach (f::file_search($dir.'/*.cache') as $file) {

					if (!is_file($file)) continue;
					if (filemtime($file) > $max_age) continue;

					unlink($file);
					$deleted_files++;
				}

				$is_empty_dir = !(new \FilesystemIterator($dir))->valid();

				if ($is_empty_dir) {
					rmdir($dir);
					$deleted_dirs++;
				}
			}

			echo 'Removed '. f::format_number($deleted_files) .' cached files and '. f::format_number($deleted_dirs) .' directories' . PHP_EOL . PHP_EOL;

			echo 'Job completed successfully.';
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
					'key' => 'priority',
					'default_value' => '0',
					'title' => t(__CLASS__.':title_priority', 'Priority'),
					'description' => t(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
