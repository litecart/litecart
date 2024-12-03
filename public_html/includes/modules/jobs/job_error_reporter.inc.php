<?php

  #[AllowDynamicProperties]
  class job_error_reporter {
    public $id = __CLASS__;
    public $name = 'Error Reporter';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'https://www.litecart.net';
    public $priority = 0;

    public function process($force, $last_run) {

    // Abort if log file is not set or missing
      if ((!$log_file = ini_get('error_log')) || !is_file($log_file)) {
        return;
      }

      if (empty($force)) {

        // Truncate a large log file over 512 MB
        if (filesize($log_file) > 512e6) {
          file_put_contents($log_file, '');
          trigger_error('Truncating a large log file over 512 MBytes', E_USER_WARNING);
          return;
        }

      // Make sure this is NOT an urgent matter of a huge log file (100+ MB)
        if (filesize($log_file) < 100e6) {

        // Abort if disabled
          if (empty($this->settings['status'])) return;

        // Abort if not within working hours
          if (!empty($this->settings['working_hours'])) {
            list($from_time, $to_time) = explode('-', $this->settings['working_hours']);
            if (time() < strtotime("Today $from_time") || time() > strtotime("Today $to_time")) return;
          }

        // Abort if the frequency for running this job is not met
          switch ($this->settings['frequency']) {

            case 'Hourly':
              if (strtotime($last_run) >= mktime(date('H'), 0, 0)) return;
              break;

            case 'Daily':
              if (strtotime($last_run) >= mktime(0, 0, 0)) return;
              break;

            case 'Weekly':
              if (strtotime($last_run) >= strtotime('This week 00:00:00')) return;
              break;

            case 'Monthly':
              if (strtotime($last_run) >= mktime(0, 0, 0, null, 1)) return;
              break;
          }
        }
      }

    // Disable RAM memory limit usage (in case we are dealing with something major big)
      ini_set('memory_limit', -1);

      if (!$contents = file_get_contents($log_file)) return;

      $contents = preg_replace('#(\r\n?|\n)#', "\n", $contents);

      $errors = [];

      if (preg_match_all('#\[(\d{1,2}-[a-zA-Z]+-\d{4} \d\d\:\d\d\:\d\d [a-zA-Z/_]+)\] ([^\n]*)((?:(?!\n\[|$).)*)#s', $contents, $matches)) {
        foreach (array_keys($matches[0]) as $i) {
          $checksum = md5($matches[2][$i]);

          if (!isset($errors[$checksum])) {
            $errors[$checksum] = [
              'error' => $matches[2][$i],
              'backtrace' => trim($matches[3][$i], "\n"),
              'occurrences' => 1,
              'last_occurrence' => $matches[1][$i],
              'critical' => preg_match('#(Parse|Fatal) error:#s', $matches[2][$i]) ? true : false,
            ];
          } else {
            $errors[$checksum]['occurrences']++;
            $errors[$checksum]['last_occurrence'] = strtotime($matches[1][$i]);
          }
        }
      }

      uasort($errors, function($a, $b) {

        if ($a['critical'] == $b['critical']) {

          if ($a['occurrences'] == $b['occurrences']) {
            return ($a['last_occurrence'] > $b['last_occurrence']) ? -1 : 1;
          }

          return ($a['occurrences'] > $b['occurrences']) ? -1 : 1;
        }

        return ($a['critical'] > $b['critical']) ? -1 : 1;
      });

      $buffer = '';
      foreach ($errors as $checksum => $error) {
        $buffer .= "[$error[last_occurrence]] ". ($error['occurrences'] > 1 ? "[$error[occurrences] times] " : "") ."$error[error]\n"
                 . (!empty($error['backtrace']) ? "$error[backtrace]\n\n" : "\n");
      }

      if (empty($this->settings['email_recipient'])) {
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
          'description' => language::translate(__CLASS__.':description_frequency', 'How often the reports should be sent.'),
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
