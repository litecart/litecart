<?php

  class job_error_reporter {
    public $id = __CLASS__;
    public $name = 'Error Reporter';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    public $priority = 0;

    public function process($force) {

      if (empty($this->settings['status'])) return;

      if (empty($this->settings['email_recipient'])) $this->settings['email_recipient'] = settings::get('store_email');

      $last_run = settings::get(__CLASS__.':last_run');

      if (empty($force)) {
        if (!empty($this->settings['working_hours'])) {
          list($from_time, $to_time) = explode('-', $this->settings['working_hours']);
          if (time() < strtotime("Today $from_time") || time() > strtotime("Today $to_time")) return;
        }

        switch ($this->settings['report_frequency']) {
          case 'Immediately':
            break;
          case 'Hourly':
            if (strtotime($last_run) > strtotime('-1 hour')) return;
            break;
          case 'Daily':
            if (strtotime($last_run) > strtotime('-1 day')) return;
            break;
          case 'Weekly':
            if (strtotime($last_run) > strtotime('-1 week')) return;
            break;
          case 'Monthly':
            if (strtotime($last_run) > strtotime('-1 month')) return;
            break;
        }
      }

      database::query(
        "update ". DB_TABLE_SETTINGS ."
        set value = '". date('Y-m-d H:i:s') . "'
        where `key` = '".__CLASS__.":last_run'
        limit 1;"
      );

      $error_log_file = ini_get('error_log');
      $contents = file_get_contents($error_log_file);

      if (empty($contents)) {
        echo 'Nothing to report';
        return;
      }

      echo 'Sending report to '. $this->settings['email_recipient'];

      $email = new email();
      $email->add_recipient($this->settings['email_recipient'])
            ->set_subject('[Error Report] '. settings::get('store_name'))
            ->add_body(PLATFORM_NAME .' '. PLATFORM_VERSION ."\r\n\r\n". $contents);

      if ($email->send() !== true) return;

      file_put_contents($error_log_file, '');
    }

    function settings() {

      return array(
        array(
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ),
        array(
          'key' => 'report_frequency',
          'default_value' => 'Weekly',
          'title' => language::translate(__CLASS__.':title_report_frequency', 'Report Frequency'),
          'description' => language::translate(__CLASS__.':description_report_frequency', 'How often the reports should be sent.'),
          'function' => 'radio("Immediately","Hourly","Daily","Weekly","Monthly")',
        ),
        array(
          'key' => 'working_hours',
          'default_value' => '07:00-21:00',
          'title' => language::translate(__CLASS__.':title_working_hours', 'Working Hours'),
          'description' => language::translate(__CLASS__.':description_working_hours', 'During what hours of the day the job would operate e.g. 07:00-21:00.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'email_recipient',
          'default_value' => settings::get('store_email'),
          'title' => language::translate(__CLASS__.':title_email_recipient', 'Email Recipient'),
          'description' => language::translate(__CLASS__.':description_email_recipient', 'The email address where reports will be sent.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }

    public function install() {
      database::query(
        "insert into ". DB_TABLE_SETTINGS ."
        (title, description, `key`, value, date_created, date_updated)
        values ('Errors Last Reported', 'Time when errors where last reported by the background job.', '".__CLASS__.":last_run', '', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
      );
    }

    public function uninstall() {
      database::query(
        "delete from ". DB_TABLE_SETTINGS ."
        where `key` = '".__CLASS__.":last_run'
        limit 1;"
      );
    }
  }
