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

      $last_run = settings::get(__CLASS__.':last_run');

      if (empty($force)) {
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

      $file = ini_get('error_log');
      $contents = file_get_contents($file);
      if (!empty($contents)) {
        $from = !empty($this->settings['email_receipient']) ? $this->settings['email_receipient'] : settings::get('store_email');
        $to = $this->settings['email_receipient'];
        $result = functions::email_send($from, $to, '[Error Report] '. settings::get('store_name'), PLATFORM_NAME .' '. PLATFORM_VERSION ."\r\n\r\n". $contents);
        if ($result === true) {
          file_put_contents($file, '');
        }
      }
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
          'key' => 'email_receipient',
          'default_value' => settings::get('store_email'),
          'title' => language::translate(__CLASS__.':title_email_receipient', 'E-mail Receipient'),
          'description' => language::translate(__CLASS__.':description_email_receipient', 'The e-mail address where reports will be sent.'),
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

?>