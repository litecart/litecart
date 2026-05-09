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

			##

			echo "Cleaning up old and expired event logs..." . PHP_EOL;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."event_logs
				where (expires_at is not null and expires_at < '". date('Y-m-d H:i:s') ."')
				or (expires_at is null and created_at < '". date('Y-m-d H:i:s', strtotime('-3 months')) ."');"
			);

			echo '- Removed '. f::format_number(database::affected_rows()) .' log(s).' . PHP_EOL . PHP_EOL;

			##

			echo "Cleaning up expired sessions..." . PHP_EOL;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."sessions
				where expires_at < '". date('Y-m-d H:i:s') ."';"
			);

			echo '- Removed '. f::format_number(database::affected_rows()) .' session(s).' . PHP_EOL . PHP_EOL;

			##

			echo "Cleaning up old email history..." . PHP_EOL;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."emails
				where status in ('sent', 'error')
				and updated_at < '". date('Y-m-d 00:00:00', strtotime('-1 month')) ."';"
			);

			echo '- Removed '. f::format_number(database::affected_rows()) .' email(s).' . PHP_EOL . PHP_EOL;

			##

			echo "Cleaning up old not found logs..." . PHP_EOL;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."not_found
				where last_requested < '". date('Y-m-d 00:00:00', strtotime('-90 days')) ."';"
			);

			echo '- Removed '. f::format_number(database::affected_rows()) .' file(s).' . PHP_EOL . PHP_EOL;

			##

			echo "Cleaning up old visitor statistics..." . PHP_EOL;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."visitors
				where created_at < '". date('Y-m-d 00:00:00', strtotime('-1 month')) ."';"
			);

			echo '- Removed '. f::format_number(database::affected_rows()) .' row(s).' . PHP_EOL . PHP_EOL;

			##

			echo "Cleaning up old webhook requests..." . PHP_EOL;

			database::query(
				"delete from ". DB_TABLE_PREFIX ."webhook_requests
				where created_at < '". database::input(date('Y-m-d H:i:s', strtotime('-30 days'))) ."';"
			);

			echo '- Removed '. f::format_number(database::affected_rows()) .' webhook request(s).' . PHP_EOL . PHP_EOL;

			##

			echo "Cleaning up old log files..." . PHP_EOL;

			$deleted_files = 0;
			$max_age = strtotime('-30 days');

			clearstatcache();

			foreach (f::file_search('storage://logs/**/*.log') as $file) {

				if (filemtime($file) > $max_age) continue;

				unlink($file);
				$deleted_files++;
			}

			echo '- Removed '. f::format_number($deleted_files) .' file(s).' . PHP_EOL . PHP_EOL;

			##

			echo "Cleaning up old cache files..." . PHP_EOL;

			$deleted_files = 0;
			$deleted_dirs = 0;
			$max_age = strtotime('-24 hours');

			clearstatcache();

			foreach (f::file_search('storage://cache/*', GLOB_ONLYDIR) as $dir) {

				foreach (f::file_search($dir.'/*.cache') as $file) {

					if (!is_file($file)) continue;
					if (filemtime($file) > $max_age) continue;

					unlink($file);
					$deleted_files++;
				}

				try {
					$is_empty_dir = !(new \FilesystemIterator($dir))->valid();
				} catch (\UnexpectedValueException $e) {
					continue;
				}

				if ($is_empty_dir) {
					rmdir($dir);
					$deleted_dirs++;
				}
			}

			echo '- Removed '. f::format_number($deleted_files) .' file(s) and '. f::format_number($deleted_dirs) .' folder(s).' . PHP_EOL . PHP_EOL;

			##

			echo 'Done!';
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
