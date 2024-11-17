<?php

	#[AllowDynamicProperties]
	class job_error_reporter extends abs_module {
		public $name = 'Error Reporter';
		public $description = '';
		public $author = 'LiteCart Dev Team';
		public $version = '1.0';
		public $website = 'https://www.litecart.net/';
		public $priority = 0;

		public function process($force, $last_run) {

			if (!$force) {

				// Abort if no log file is set
				if (!$log_file = ini_get('error_log')) return;

					// Abort if log file is missing
				if (!is_file($log_file)) return;

					// Make sure this is not an urgent matter of a huge log file (100+ MB)
				if (filesize($log_file) < 100e6) {

						// Abort if disabled
					if (!$this->settings['status']) return;

						// Abort if not within working hours
					if (!empty($this->settings['working_hours'])) {
						list($from_time, $to_time) = explode('-', $this->settings['working_hours']);
						if (time() < strtotime("Today $from_time") || time() > strtotime("Today $to_time")) return;
					}

						// Abort if the frequency for running this job is not met
					if (strtotime($last_run) > functions::datetime_last_by_interval($this->settings['frequency'], $last_run)) return;
				}
			}

				// Disable RAM memory limit usage (in case we are dealing with some major big)
			ini_set('memory_limit', -1);

			if (!$contents = file_get_contents($log_file)) return;

			$contents = preg_replace('#(\r\n?|\n)#', "\n", $contents);

			$errors = [];
			$occurrences = [];

			if (preg_match_all('#\[(\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/_]+)\] ([^\n]*)((?:(?!\n\[|$).)*)#s', $contents, $matches)) {
				foreach (array_keys($matches[0]) as $i) {
					$checksum = crc32($matches[2][$i]);

					$errors[$checksum] = [
						'error' => $matches[2][$i],
						'backtrace' => trim($matches[3][$i], "\n"),
						'last_occurrence' => $matches[1][$i],
					];

					if (isset($occurrences[$checksum])) {
						$occurrences[$checksum]++;
					} else {
						$occurrences[$checksum] = 1;
					}
				}
			}

			$buffer = '';
			foreach ($errors as $checksum => $error) {
				$buffer .= "[$error[last_occurrence]] ". ($occurrences[$checksum] > 1 ? "[$occurrences[$checksum] times] " : "") ."$error[error]\n"
								 . (!empty($error['backtrace']) ? "$error[backtrace]\n\n" : "\n");
			}

			if (!$this->settings['email_recipient']) {
				$this->settings['email_recipient'] = settings::get('store_email');
			}

			echo 'Sending report to '. $this->settings['email_recipient'];

			$email = new ent_email();
			$email->add_recipient($this->settings['email_recipient'])
						->set_subject('[Error Report] '. settings::get('store_name'))
						->add_body(PLATFORM_NAME .' '. PLATFORM_VERSION ."\r\n\r\n". $buffer);

			if ($email->send() !== true) {
				echo ' [Failed]';
				return;
			}

			file_put_contents($log_file, '');
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
					'key' => 'working_hours',
					'default_value' => '07:00-21:00',
					'title' => language::translate(__CLASS__.':title_working_hours', 'Working Hours'),
					'description' => language::translate(__CLASS__.':description_working_hours', 'During which hours of the day the job should operate e.g. 07:00-21:00.'),
					'function' => 'text()',
				],
				[
					'key' => 'frequency',
					'default_value' => 'Weekly',
					'title' => language::translate(__CLASS__.':title_frequency', 'Frequency'),
					'description' => language::translate(__CLASS__.':description_frequency', 'How often this job should be processed.'),
					'function' => 'radio("Hourly","Daily","Weekly","Monthly")',
				],
				[
					'key' => 'email_recipient',
					'default_value' => settings::get('store_email'),
					'title' => language::translate(__CLASS__.':title_email_recipient', 'Email Recipient'),
					'description' => language::translate(__CLASS__.':description_email_recipient', 'The email address where reports will be sent.'),
					'function' => 'text()',
				],
				[
					'key' => 'priority',
					'default_value' => '-1',
					'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
					'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
					'function' => 'number()',
				],
			];
		}
	}
